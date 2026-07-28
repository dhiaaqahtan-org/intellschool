<?php

namespace Modules\Saas\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StorePlatformPlanRequest extends FormRequest
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
            'plan_code' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9][a-z0-9_-]*$/'],
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'billing_interval' => ['required', Rule::in(['monthly', 'annual'])],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'features' => ['nullable', 'array', 'max:250'],
            'features.*.feature_code' => [
                'required_with:features',
                'string',
                'max:80',
                'distinct',
                Rule::in(config('saas.entitlements.feature_codes', [])),
            ],
            'features.*.enabled' => ['required_with:features', 'boolean'],
            'features.*.limit_value' => ['nullable', 'integer', 'min:0'],
            'features.*.limit_type' => ['nullable', Rule::in(['hard', 'soft'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('currency')) {
            $this->merge(['currency' => strtoupper((string) $this->input('currency'))]);
        }
    }
}
