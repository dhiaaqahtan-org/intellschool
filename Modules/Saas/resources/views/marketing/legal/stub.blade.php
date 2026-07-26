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
            <span>
                This document has not been drafted or reviewed yet. It is a routing
                placeholder so that footer links resolve during development.
                <strong>The service must not be offered commercially until legal
                counsel supplies the operative text</strong> — including the
                governing law, the data-controller/processor split, retention and
                deletion periods, the subprocessor list, and the incident
                notification commitment.
            </span>
        </div>

        <p style="color:var(--c-muted)">
            Required before launch (implementation plan §10.3, §12 and §20):
        </p>
        <ul class="list-check" style="margin-block-start:var(--sp-4)">
            <li><svg aria-hidden="true"><use href="#i-check"></use></svg><span>Terms of service and acceptable use</span></li>
            <li><svg aria-hidden="true"><use href="#i-check"></use></svg><span>Privacy notice covering student and guardian personal data</span></li>
            <li><svg aria-hidden="true"><use href="#i-check"></use></svg><span>Data processing addendum with the subprocessor list</span></li>
            <li><svg aria-hidden="true"><use href="#i-check"></use></svg><span>Retention, export and deletion policy, with consent version tracking</span></li>
        </ul>

        <p style="margin-block-start:var(--sp-6)">
            <a class="btn btn--ghost" href="{{ route('saas.marketing.home') }}">&larr; Back to home</a>
        </p>
    </div>
</section>

@endsection
