@if (!empty($items))
    <header class="w-full overflow-hidden border-b border-gray-800 bg-black py-2 text-white">
        <div class="relative flex w-full overflow-hidden">
            <div x-data="{ isPaused: false }" @mouseenter="isPaused = true" @mouseleave="isPaused = false"
                :class="{ 'marquee-paused': isPaused }"
                class="animate-marquee flex space-x-2 transition-all duration-300 ease-in-out"
                style="--marquee-speed: {{ $speed ?? '30s' }};">
                @foreach ($items as $item)
                    <a href="{{ Arr::get($item, 'url') }}"
                        class="whitespace-nowrap px-2">{{ Arr::get($item, 'title') }}</a>
                @endforeach
                @foreach ($items as $item)
                    <a href="{{ Arr::get($item, 'url') }}"
                        class="whitespace-nowrap px-2">{{ Arr::get($item, 'title') }}</a>
                @endforeach
            </div>
        </div>
    </header>
@endif

<div class="bg-site-primary flex min-h-12 items-center px-4 py-2 text-gray-200 sm:px-10">
    <div class="sm:container">
        <div class="flex flex-col gap-2 text-sm sm:flex-row sm:items-center sm:justify-between">

            <div class="hidden sm:block">
                @if (config('config.general.app_email'))
                    {{ __('website.email') }}: {{ config('config.general.app_email') }}
                @endif
                @if (config('config.general.app_phone'))
                    <span class="mx-1" aria-hidden="true">|</span>
                    {{ __('website.phone') }}: {{ config('config.general.app_phone') }}
                @endif
            </div>

            <div>
                <a href="/app/payment">{{ __('website.online_fee_payment') }}</a>
                <span class="mx-1" aria-hidden="true">|</span>
                <a href="/app/online-registration">{{ __('website.online_registration') }}</a>
            </div>
        </div>
    </div>
</div>

@if ($announcementPopup)
    <x-site.popup-modal :title="$announcementPopup->title">
        {!! $announcementPopup->description !!}
    </x-site.popup-modal>
@endif
