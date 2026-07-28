@extends('saas::marketing.layouts.app')

@php
    $titles = [
        'terms'   => __('saas::marketing.footer.links.terms'),
        'privacy' => __('saas::marketing.footer.links.privacy'),
        'dpa'     => __('saas::marketing.footer.links.dpa'),
    ];
    $title = $titles[$doc] ?? $doc;
@endphp

@section('title', $title.' — '.config('saas.brand.name'))
@section('description', $title)

@push('head')
    {{-- An unreviewed legal placeholder must never be indexed or presented as
         if it were the operative agreement. --}}
    <meta name="robots" content="noindex,nofollow">
@endpush

@section('content')

<section class="section">
    <div class="wrap" style="max-inline-size:48rem">
        <h1>{{ $title }}</h1>

        <div class="notice" style="margin-block:var(--sp-6)">
            <svg aria-hidden="true"><use href="#i-alert"></use></svg>
            <span>{{ __('saas::marketing.legal.notice') }}</span>
        </div>

        <p style="color:var(--c-muted)">
            {{ __('saas::marketing.legal.required') }}
        </p>
        <ul class="list-check" style="margin-block-start:var(--sp-4)">
            @foreach (__('saas::marketing.legal.items') as $item)
                <li><svg aria-hidden="true"><use href="#i-check"></use></svg><span>{{ $item }}</span></li>
            @endforeach
        </ul>

        <p style="margin-block-start:var(--sp-6)">
            <a class="btn btn--ghost" href="{{ route('saas.marketing.home') }}">{{ __('saas::marketing.legal.back_home') }}</a>
        </p>
    </div>
</section>

@endsection
