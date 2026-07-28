@extends('saas::platform.layouts.app')

@section('title', $plan->plan_code.' v'.$plan->version)

@section('content')
    @php $locked = $subscriberCount > 0; @endphp

    <div class="page-header">
        <h1>{{ $plan->display_name }} <span class="badge badge-gray">{{ $plan->plan_code }} v{{ $plan->version }}</span></h1>
        <p>
            {{ $plan->price_cents > 0 ? number_format($plan->price_cents / 100, 2).' '.$plan->currency.' / '.$plan->billing_interval : 'Negotiated pricing' }}
            · {{ $plan->trial_days }}-day trial
            · {{ $subscriberCount }} subscriber{{ $subscriberCount === 1 ? '' : 's' }}
        </p>
    </div>

    @if ($locked)
        <div class="card" style="background:#fef3c7;border:1px solid #fcd34d">
            <strong>This version is locked.</strong>
            {{ $subscriberCount }} tenant{{ $subscriberCount === 1 ? ' is' : 's are' }} subscribed to it.
            Editing its features would retroactively change what they are entitled to, with no record of the change
            on their subscription. Create a new version instead — existing subscribers stay on this one until they
            are explicitly moved.
        </div>
    @endif

    <div class="card">
        <div class="card-title" style="display:flex;justify-content:space-between;align-items:center">
            <span>Visibility</span>
            <form method="POST" action="{{ route('saas.platform.plans.toggle-public', $plan) }}">
                @csrf
                <button type="submit" class="btn btn-primary">
                    {{ $plan->is_public ? 'Hide from website' : 'Publish to website' }}
                </button>
            </form>
        </div>
        <p style="color:var(--color-gray-500);font-size:.875rem">
            @if ($plan->is_public)
                Visible on the public pricing page (when <code>SAAS_CLAIM_PRICING</code> is enabled).
            @else
                Not shown publicly. New versions start hidden so a draft price cannot leak.
            @endif
        </p>
    </div>

    <div class="card">
        <div class="card-title">Features ({{ $plan->features->count() }})</div>

        <table>
            <thead>
                <tr><th>Feature code</th><th>Enabled</th><th>Limit</th><th>Type</th>@unless($locked)<th></th>@endunless</tr>
            </thead>
            <tbody>
                @foreach ($plan->features->sortBy('feature_code') as $feature)
                    <tr>
                        <td><code>{{ $feature->feature_code }}</code></td>
                        <td>
                            <span class="badge {{ $feature->enabled ? 'badge-success' : 'badge-gray' }}">
                                {{ $feature->enabled ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td>{{ $feature->limit_value === null ? 'Unlimited' : number_format($feature->limit_value) }}</td>
                        <td>{{ $feature->limit_type }}</td>
                        @unless ($locked)
                            <td>
                                <form method="POST" action="{{ route('saas.platform.plans.features.update', $plan) }}" style="display:flex;gap:.5rem;align-items:center">
                                    @csrf
                                    <input type="hidden" name="feature_code" value="{{ $feature->feature_code }}">
                                    <select name="enabled" style="padding:.25rem">
                                        <option value="1" @selected($feature->enabled)>On</option>
                                        <option value="0" @selected(! $feature->enabled)>Off</option>
                                    </select>
                                    <input type="number" name="limit_value" min="0" placeholder="∞"
                                           value="{{ $feature->limit_value }}" style="width:6rem;padding:.25rem">
                                    <select name="limit_type" style="padding:.25rem">
                                        <option value="hard" @selected($feature->limit_type === 'hard')>hard</option>
                                        <option value="soft" @selected($feature->limit_type === 'soft')>soft</option>
                                    </select>
                                    <button class="btn btn-primary" type="submit">Save</button>
                                </form>
                            </td>
                        @endunless
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="card-title">Create the next version</div>
        <p style="color:var(--color-gray-500);font-size:.875rem;margin-bottom:1rem">
            Copies every feature from v{{ $plan->version }} into a new, hidden version you can edit freely.
            Existing subscribers are not moved.
        </p>
        <form method="POST" action="{{ route('saas.platform.plans.versions.store', $plan) }}"
              style="display:grid;gap:.75rem;max-width:26rem">
            @csrf
            <label>Display name
                <input type="text" name="display_name" value="{{ $plan->display_name }}" required
                       style="width:100%;padding:.5rem;margin-top:.25rem">
            </label>
            <label>Price (in cents — {{ $plan->currency }})
                <input type="number" name="price_cents" min="0" value="{{ $plan->price_cents }}" required
                       style="width:100%;padding:.5rem;margin-top:.25rem">
            </label>
            <label>Trial days
                <input type="number" name="trial_days" min="0" max="365" value="{{ $plan->trial_days }}" required
                       style="width:100%;padding:.5rem;margin-top:.25rem">
            </label>
            <button class="btn btn-primary" type="submit">Create v{{ $plan->version + 1 }}</button>
        </form>
    </div>

    @if ($errors->any())
        <div class="card" style="background:#fee2e2;border:1px solid #fca5a5">
            <ul style="margin-inline-start:1rem">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif
@endsection
