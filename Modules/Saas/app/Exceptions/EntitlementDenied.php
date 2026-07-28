<?php

namespace Modules\Saas\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Thrown when a tenant's plan does not include the requested capability.
 *
 * This is a commercial/authorization boundary, not a bug. The HTTP layer
 * renders it as 402 Payment Required (or 403 when the feature simply does
 * not exist on any plan). The message is safe to show to the tenant owner.
 */
class EntitlementDenied extends HttpException
{
    public static function forFeature(string $featureCode, ?string $tenantUuid = null): self
    {
        $message = sprintf(
            'Your current plan does not include [%s]. Please upgrade or contact support.',
            $featureCode
        );

        $exception = new self(402, $message);
        $exception->featureCode = $featureCode;
        $exception->tenantUuid = $tenantUuid;

        return $exception;
    }

    /**
     * Create a denial with a custom message (e.g. path traversal, storage violation).
     */
    public static function withMessage(string $featureCode, string $message, int $statusCode = 403): self
    {
        $exception = new self($statusCode, $message);
        $exception->featureCode = $featureCode;

        return $exception;
    }

    public string $featureCode = '';

    public ?string $tenantUuid = null;

    /**
     * Render as JSON for API consumers.
     */
    public function render(): \Illuminate\Http\JsonResponse
    {
        $configuredUrl = config('saas.billing.upgrade_url');
        $upgradeUrl = is_string($configuredUrl) && filter_var($configuredUrl, FILTER_VALIDATE_URL)
            ? $configuredUrl
            : null;

        return response()->json([
            'message' => $this->getMessage(),
            'error' => 'entitlement_denied',
            'feature' => $this->featureCode,
            'upgrade_url' => $upgradeUrl,
        ], $this->getStatusCode());
    }
}
