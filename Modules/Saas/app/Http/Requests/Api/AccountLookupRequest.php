<?php

namespace Modules\Saas\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Validates a pre-login account-discovery request on the control-plane host.
 *
 * Lookup is by email today: the only cross-database identity reference we hold
 * in the landlord DB is saas_tenant_owners.email. Phone discovery needs a phone
 * column on that table first, so `phone` is accepted but ignored until then.
 */
class AccountLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
        ];
    }

    /**
     * Normalised email used for the case-insensitive owner lookup.
     */
    public function normalisedEmail(): string
    {
        return Str::lower(trim((string) $this->input('email')));
    }
}
