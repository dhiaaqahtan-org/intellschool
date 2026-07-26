<?php

namespace Modules\Saas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:120'],
            'school'  => ['required', 'string', 'max:180'],
            'email'   => ['required', 'email:rfc,dns', 'max:190'],
            'phone'   => ['nullable', 'string', 'max:40'],
            'size'    => ['nullable', 'string', 'max:40'],
            'message' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],

            // Honeypot. Bots fill every field; a real browser leaves this empty
            // because it is hidden and has no label.
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'consent.accepted'  => __('saas::marketing.form.consent_required'),
            'website.prohibited' => __('saas::marketing.form.rejected'),
        ];
    }

    public function attributes(): array
    {
        return [
            'name'    => __('saas::marketing.form.name'),
            'school'  => __('saas::marketing.form.school'),
            'email'   => __('saas::marketing.form.email'),
            'size'    => __('saas::marketing.form.size'),
            'message' => __('saas::marketing.form.message'),
        ];
    }
}
