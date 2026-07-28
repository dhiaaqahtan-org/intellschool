<?php

namespace Modules\Saas\Models\Landlord;

use Illuminate\Database\Eloquent\Model;
use Modules\Saas\Models\Concerns\UsesLandlordConnection;

/**
 * Base for every control-plane model.
 *
 * The connection is declared EXPLICITLY (plan §7). This is the whole point:
 * tenant resolution swaps the *default* connection so that unmodified ERP
 * models land on the tenant database. A landlord model that inherited the
 * default would follow it into whichever tenant happened to be active and
 * either fail or — worse — read a table with the same name.
 *
 * Never remove getConnectionName(). Never let a landlord model rely on the
 * default connection.
 */
abstract class LandlordModel extends Model
{
    use UsesLandlordConnection;
}
