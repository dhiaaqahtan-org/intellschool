<?php

namespace Modules\Saas\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StorePlatformTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $operator = $this->user('platform');

        return $operator !== null
            && Gate::forUser($operator)->allows('createTenant');
    }

    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'min:2',
                'max:63',
                'alpha_dash:ascii',
                Rule::unique(config('saas.database.landlord_connection', 'landlord').'.saas_tenants', 'slug'),
            ],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'owner_name' => ['required_with:owner_email', 'nullable', 'string', 'max:255'],
            'owner_email' => ['nullable', 'email:rfc', 'max:255'],
            'locale' => ['nullable', Rule::in(config('localizer.supported_locales', ['en', 'ar']))],
            'timezone' => ['nullable', 'timezone'],
            'region' => ['nullable', 'string', 'max:32'],
            'tier' => ['nullable', 'string', 'max:32'],
        ];
    }
}
