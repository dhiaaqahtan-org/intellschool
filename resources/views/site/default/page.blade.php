<x-site.default.layout :meta-title="$metaTitle" :meta-description="$metaDescription" :meta-keywords="$metaKeywords">

    <section>
        @if (Arr::get($page->assets, 'cover'))
            <img src="{{ $page->cover_image }}" alt="{{ $page->title }}" class="h-auto w-full">
        @endif
    </section>

    @if (route('site.home') != request()->url())
        <x-ui.breadcrumb :navs="$navs" />
    @endif

    <section class="mb-4 mt-10">
        <div class="container">
            <h1 class="text-2xl font-bold text-gray-800">{{ \App\Support\LocalizedContent::get($page->title) }}</h1>
            @if ($page->sub_title)
                <h2 class="mt-2 text-xl text-gray-700">{{ \App\Support\LocalizedContent::get($page->sub_title) }}</h2>
            @endif
        </div>
    </section>

    @php
        $previousPart = null;
    @endphp
    @foreach ($parts as $part)
        <section class="{{ $previousPart && $previousPart['type'] != $part['type'] ? 'my-10' : 'my-2' }}">
            <div class="container">
                <div class="text-gray-700">
                    @if ($part['type'] == 'html')
                        <div class="md-content">
                            {!! $part['content'] !!}
                        </div>
                    @endif

                    @if ($part['type'] == 'youtube')
                        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <div class="aspect-h-9 aspect-w-16">
                                <iframe src="https://www.youtube.com/embed/{{ $part['content'] }}" frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen=""></iframe>
                            </div>
                        </div>
                    @elseif ($part['type'] == 'twitter')
                        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <div class="flex justify-center">
                                <blockquote class="twitter-tweet"><a href="{{ $part['content'] }}">X</a></blockquote>
                                <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
                            </div>
                        </div>
                    @endif

                    @if ($part['type'] == 'widgets')
                        <div class="grid-cols-{{ count($part['content']) }} grid gap-4">
                            @foreach ($part['content'] as $widgetName)
                                @if ($widgetName == 'CONTACT')
                                    <x-site.contact />
                                @elseif ($widgetName == 'PROGRAM_DETAIL')
                                    <x-site.program-detail />
                                @elseif ($widgetName == 'BLOG_LIST')
                                    <x-site.blog-list :slug="$pageSlug" />
                                @elseif ($widgetName == 'BLOG_SUMMARY')
                                    <x-site.blog-list :slug="$pageSlug" type="summary" />
                                @elseif ($widgetName == 'NEWS_LIST')
                                    <x-site.news-list :slug="$pageSlug" />
                                @elseif ($widgetName == 'NEWS_SUMMARY')
                                    <x-site.news-list :slug="$pageSlug" type="summary" />
                                @elseif ($widgetName == 'EVENT_LIST')
                                    <x-site.event-list />
                                @elseif ($widgetName == 'EVENT_SUMMARY')
                                    <x-site.event-list type="summary" />
                                @elseif ($widgetName == 'ANNOUNCEMENT_LIST')
                                    <x-site.announcement-list />
                                @elseif ($widgetName == 'ANNOUNCEMENT_SUMMARY')
                                    <x-site.announcement-list type="summary" />
                                @elseif ($widgetName == 'GALLERY_LIST')
                                    <x-site.gallery-list />
                                @elseif ($widgetName == 'GALLERY_SUMMARY')
                                    <x-site.gallery-list type="summary" />
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
        @php
            $previousPart = $part;
        @endphp
    @endforeach

    <section class="my-10">
        <div class="container">
            @if ($page->media->isNotEmpty())
                <h2 class="mb-4 mt-8 text-xl font-bold text-gray-700">Attachments</h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($page->media as $media)
                        <a href="/app/site/pages/{{ $page->uuid }}/media/{{ $media->uuid }}">
                            <div
                                class="flex items-center space-x-3 rounded-lg border border-gray-200 bg-white p-4 shadow-md transition-shadow duration-200 hover:shadow-lg">
                                <i class="fas {{ $media->getIcon() }} fa-2xl text-gray-600"></i>
                                <div class="overflow-hidden">
                                    <div class="truncate font-medium text-gray-800">{{ $media->file_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $media->size }}</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @include('site.default.cta', ['page' => $page])

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.hljs.highlightAll();
        });
    </script>

</x-site.default.layout>
