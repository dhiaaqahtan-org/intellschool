<?php

namespace Modules\Saas\Models\Concerns;

/**
 * Pins a model to the control-plane database even while the application's
 * default connection has been switched to a tenant database.
 */
trait UsesLandlordConnection
{
    public function getConnectionName(): string
    {
        return config('saas.database.landlord_connection', 'landlord');
    }
}
