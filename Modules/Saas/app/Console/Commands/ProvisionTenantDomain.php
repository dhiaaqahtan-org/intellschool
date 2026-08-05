<?php

namespace Modules\Saas\Console\Commands;

use Illuminate\Console\Command;
use Modules\Saas\Domain\Tenancy\HostNormalizer;
use Modules\Saas\Models\Landlord\AuditEvent;
use Modules\Saas\Models\Landlord\TenantDomain;
use Symfony\Component\Process\Process;

/**
 * Issue a TLS certificate and nginx vhost for a school's own domain.
 *
 * A wildcard certificate for *.intellschool.com does not cover tamjeed.com, so
 * every custom domain needs its own. This drives the root-owned
 * `tenant-domain-provision` script through sudo; see Modules/Saas/deploy for
 * the install steps.
 *
 * VPS ONLY. On shared hosting the domain and its certificate are added through
 * the hosting control panel and this command has nothing to call.
 */
class ProvisionTenantDomain extends Command
{
    protected $signature = 'saas:provision-domain
        {domain : The custom domain to serve, e.g. tamjeed.com}
        {--email= : Let\'s Encrypt contact address}
        {--rewrite : Replace an existing vhost (a backup is kept)}
        {--force : Force certificate renewal}
        {--staging : Use the Let\'s Encrypt staging CA while testing}
        {--allow-unverified : Issue before DNS ownership has been proven}
        {--dry-run : Show what would run without executing}';

    protected $description = 'Issue TLS and an nginx vhost for a tenant custom domain (VPS only)';

    private const WRAPPER = '/usr/local/sbin/tenant-domain-provision';

    public function handle(): int
    {
        $domain = HostNormalizer::normalize((string) $this->argument('domain'));

        if ($domain === null) {
            $this->error('That is not a usable hostname.');

            return self::FAILURE;
        }

        $record = TenantDomain::query()->where('hostname', $domain)->first();

        // Never issue a certificate for a domain this platform does not serve.
        // Certbot will happily prove control of any domain pointed at this
        // server, so without this check the command becomes a way to mint
        // certificates for hosts nobody registered.
        if ($record === null) {
            $this->error("[{$domain}] is not registered to any tenant.");
            $this->line('Add it in the platform panel first, under the tenant it belongs to.');

            return self::FAILURE;
        }

        if (! $record->isRoutable() && ! $this->option('allow-unverified')) {
            $this->error("[{$domain}] has not passed DNS verification yet.");
            $this->line('The school must publish the TXT record shown in the platform panel.');
            $this->line('Certificate issuance also needs the A record already pointing here,');
            $this->line('so verify first — or pass --allow-unverified if you know it resolves.');

            return self::FAILURE;
        }

        $command = $this->buildCommand($domain);

        if ($this->option('dry-run')) {
            $this->info('[DRY RUN] '.implode(' ', $command));

            return self::SUCCESS;
        }

        if (! is_file(self::WRAPPER)) {
            $this->error('Provisioning wrapper missing at '.self::WRAPPER);
            $this->line('Install it from Modules/Saas/deploy/tenant-domain-provision — see the header of that file.');

            return self::FAILURE;
        }

        // ACME round trips are slow, and a renewal under load slower still.
        $process = new Process($command, base_path(), null, null, 600);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        $ok = $process->isSuccessful();

        AuditEvent::record(
            action: $ok ? 'domain.tls_issued' : 'domain.tls_failed',
            tenantUuid: $record->tenant_uuid,
            context: ['hostname' => $domain, 'exit_code' => $process->getExitCode()],
            actorType: 'platform',
        );

        if (! $ok) {
            $this->error("Failed to provision {$domain} (exit {$process->getExitCode()}).");
            $this->line('If this was a rate limit, re-run with --staging until the config is right.');

            return self::FAILURE;
        }

        if ($record->tls_status !== 'active') {
            $record->forceFill(['tls_status' => 'active', 'tls_issued_at' => now()])->save();
        }

        $this->info("{$domain} is serving over HTTPS.");

        return self::SUCCESS;
    }

    /**
     * Built as an argument array, never a shell string. Process then execs
     * directly with no shell in between, so a hostname is an argument even if
     * it contains characters a shell would act on — the injection this would
     * otherwise invite runs as root.
     *
     * @return array<int, string>
     */
    private function buildCommand(string $domain): array
    {
        $command = ['/usr/bin/sudo', '-n', self::WRAPPER, $domain];

        if ($email = trim((string) $this->option('email'))) {
            $command[] = "--email={$email}";
        }

        foreach (['rewrite', 'force', 'staging'] as $flag) {
            if ($this->option($flag)) {
                $command[] = "--{$flag}";
            }
        }

        return $command;
    }
}
