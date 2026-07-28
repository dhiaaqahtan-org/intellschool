<?php

namespace Modules\Saas\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\Saas\Enums\TenantStatus;

class PlatformTenantIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        $operator = $this->user('platform');

        return $operator !== null
            && Gate::forUser($operator)->allows('viewAnyTenant');
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(TenantStatus::class)],
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
