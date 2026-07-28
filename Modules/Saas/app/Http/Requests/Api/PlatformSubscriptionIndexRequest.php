<?php

namespace Modules\Saas\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\Saas\Domain\Billing\SubscriptionStatus;

class PlatformSubscriptionIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        $operator = $this->user('platform');

        return $operator !== null
            && Gate::forUser($operator)->allows('manageBilling');
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(SubscriptionStatus::class)],
            'tenant_uuid' => ['nullable', 'uuid'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
