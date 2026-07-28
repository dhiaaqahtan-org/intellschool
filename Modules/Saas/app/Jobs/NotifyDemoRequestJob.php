<?php

namespace Modules\Saas\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Modules\Saas\Mail\DemoRequestReceived;
use Modules\Saas\Models\Landlord\DemoRequest;
use Throwable;

class NotifyDemoRequestJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $uniqueFor = 3600;

    public function __construct(public readonly string $demoRequestUuid)
    {
        $this->onQueue((string) config('saas.leads.queue', 'notifications'));
    }

    public function uniqueId(): string
    {
        return $this->demoRequestUuid;
    }

    public function backoff(): array
    {
        return [60, 300, 900, 1800];
    }

    public function handle(): void
    {
        $recipient = (string) config('saas.leads.notify', '');
        if ($recipient === '') {
            return;
        }

        $lead = DemoRequest::query()->where('uuid', $this->demoRequestUuid)->first();
        if ($lead === null || $lead->notified_at !== null) {
            return;
        }

        Mail::to($recipient)->send(new DemoRequestReceived($lead));

        $lead->update(['status' => 'notified', 'notified_at' => now()]);
    }

    public function failed(Throwable $exception): void
    {
        DemoRequest::query()
            ->where('uuid', $this->demoRequestUuid)
            ->whereNull('notified_at')
            ->update(['status' => 'notification_failed']);
    }
}
