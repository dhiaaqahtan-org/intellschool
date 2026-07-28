<?php

namespace Modules\Saas\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\Saas\Enums\TenantStatus;
use Modules\Saas\Models\Landlord\Tenant;

class UpdatePlatformTenantStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $operator = $this->user('platform');
        $tenant = $this->route('tenant');

        return $operator !== null
            && $tenant instanceof Tenant
            && Gate::forUser($operator)->allows('updateTenant', $tenant);
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    TenantStatus::Active->value,
                    TenantStatus::Suspended->value,
                    TenantStatus::Cancelled->value,
                ]),
            ],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }
}
