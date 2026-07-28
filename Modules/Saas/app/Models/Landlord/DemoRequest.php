<?php

namespace Modules\Saas\Models\Landlord;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DemoRequest extends LandlordModel
{
    use HasUuids;

    protected $table = 'saas_demo_requests';

    protected $fillable = [
        'uuid', 'name', 'school', 'email', 'phone', 'school_size', 'message',
        'locale', 'status', 'consent_at', 'ip_hash', 'user_agent_hash',
        'notified_at', 'purge_after',
    ];

    protected $casts = [
        'consent_at' => 'datetime',
        'notified_at' => 'datetime',
        'purge_after' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
