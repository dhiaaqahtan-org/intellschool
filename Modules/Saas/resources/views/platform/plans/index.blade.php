@extends('saas::platform.layouts.app')

@section('title', 'Plans')

@section('content')
    <div class="page-header">
        <h1>Plans</h1>
        <p>Versioned plan definitions. A price or feature change creates a new version — it never rewrites what an existing customer already agreed to.</p>
    </div>

    @unless (app(Modules\Saas\Domain\Website\ClaimGate::class)->pricing())
        <div class="card" style="background:#fef3c7;border:1px solid #fcd34d">
            <strong>Pricing is not published.</strong>
            These figures are configured here but hidden from the public website until
            <code>SAAS_CLAIM_PRICING=true</code>. Publishing unapproved pricing is a contractual exposure.
        </div>
    @endunless

    @forelse ($plans as $planCode => $versions)
        <div class="card">
            <div class="card-title" style="display:flex;justify-content:space-between;align-items:center">
                <span>{{ $planCode }}</span>
                <span class="badge badge-gray">{{ $versions->count() }} version{{ $versions->count() === 1 ? '' : 's' }}</span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Interval</th>
                        <th>Trial</th>
                        <th>Features</th>
                        <th>Subscribers</th>
                        <th>Public</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($versions as $plan)
                        <tr>
                            <td><strong>v{{ $plan->version }}</strong></td>
                            <td>{{ $plan->display_name }}</td>
                            <td>
                                @if ($plan->price_cents > 0)
                                    {{ number_format($plan->price_cents / 100, 2) }} {{ $plan->currency }}
                                @else
                                    <span class="badge badge-gray">Negotiated</span>
                                @endif
                            </td>
                            <td>{{ $plan->billing_interval }}</td>
                            <td>{{ $plan->trial_days }} days</td>
                            <td>{{ $plan->features_count }}</td>
                            <td>
                                @if ($plan->subscriptions_count > 0)
                                    <span class="badge badge-success">{{ $plan->subscriptions_count }}</span>
                                @else
                                    <span class="badge badge-gray">0</span>
                                @endif
                            </td>
                            <td>
                                @if ($plan->is_public)
                                    <span class="badge badge-success">Public</span>
                                @else
                                    <span class="badge badge-gray">Hidden</span>
                                @endif
                            </td>
                            <td><a class="btn btn-primary" href="{{ route('saas.platform.plans.show', $plan) }}">Open</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="card">
            <p>No plans yet. Seed the defaults:</p>
            <pre style="background:var(--color-gray-100);padding:1rem;border-radius:.375rem;overflow-x:auto"><code>php artisan db:seed --class="Modules\Saas\Database\Seeders\PlanSeeder"</code></pre>
        </div>
    @endforelse
@endsection
