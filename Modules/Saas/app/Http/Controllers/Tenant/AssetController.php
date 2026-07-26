<?php

namespace Modules\Saas\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Saas\Contracts\CurrentTenant;
use Modules\Saas\Contracts\TenantStorage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves tenant-scoped file downloads via signed URLs.
 *
 * The URL signature includes the tenant UUID, so a URL generated for
 * Tenant A cannot be replayed on Tenant B's host. The path is encrypted
 * in the URL to prevent enumeration.
 *
 * Flow:
 *  1. TenantUrlGenerator generates a signed URL with encrypted path.
 *  2. Client requests the URL.
 *  3. This controller verifies the signature (Laravel does this via
 *     the 'signed' middleware or manual check).
 *  4. Decrypts the path and validates it belongs to the active tenant.
 *  5. Streams the file.
 */
class AssetController extends Controller
{
    public function __construct(
        private readonly CurrentTenant $currentTenant,
        private readonly TenantStorage $storage,
    ) {
    }

    /**
     * GET /saas/asset/{path} or /saas/download/{path}
     *
     * Serves a file from the tenant's storage prefix.
     */
    public function download(Request $request, string $path): StreamedResponse
    {
        // Verify the URL signature.
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired download link.');
        }

        $context = $this->currentTenant->getOrFail();

        // The path may be encrypted (from TenantUrlGenerator) or plain.
        $decryptedPath = $this->resolvePath($request, $path);

        // Validate the path belongs to this tenant (defense in depth).
        $fullPath = $this->storage->path($decryptedPath);
        $this->storage->assertPathBelongsToTenant($fullPath);

        $disk = $this->storage->disk();

        if (! $disk->exists($fullPath)) {
            abort(404, 'File not found.');
        }

        // Determine the download filename.
        $filename = basename($decryptedPath);

        return $disk->download($fullPath, $filename);
    }

    /**
     * Resolve the actual file path from the request.
     *
     * Supports both encrypted paths (from signed URLs) and plain paths
     * (for direct asset access within the tenant prefix).
     */
    private function resolvePath(Request $request, string $path): string
    {
        // Check for encrypted path parameter (from TenantUrlGenerator).
        $encryptedPath = $request->query('path');

        if ($encryptedPath) {
            try {
                return decrypt($encryptedPath);
            } catch (\Throwable) {
                abort(400, 'Invalid path parameter.');
            }
        }

        // Plain path from the URL segment.
        return $path;
    }
}
