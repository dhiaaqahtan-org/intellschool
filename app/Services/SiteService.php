<?php

namespace App\Services;

use App\Models\Site\Page;
use App\Support\LocalizedContent;
use App\Support\MarkdownParser;
use DOMDocument;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SiteService
{
    use MarkdownParser;

    public function getPage(?string $slug = '')
    {
        $slug = Str::slug($slug ?: 'home');

        $page = Page::query()
            ->where('seo->is_public', true)
            ->where('seo->slug', $slug)
            ->firstOrFail();

        $pageSlug = $page->slug;
        $navs = [[
            'name' => LocalizedContent::get($page->name),
            'url' => $page->url,
        ]];

        $metaTitle = LocalizedContent::get(Arr::get($page->seo, 'meta_title'));
        $metaDescription = LocalizedContent::get(Arr::get($page->seo, 'meta_description'));
        $metaKeywords = LocalizedContent::get(Arr::get($page->seo, 'meta_keywords'));

        $address = Arr::toAddress([
            'address_line1' => config('config.general.app_address_line1'),
            'address_line2' => config('config.general.app_address_line2'),
            'city' => config('config.general.app_city'),
            'state' => config('config.general.app_state'),
            'zipcode' => config('config.general.app_zipcode'),
            'country' => config('config.general.app_country'),
        ]);

        config([
            'config.general.app_address' => $address,
        ]);

        $content = $page->content;

        $content = $this->parse($content, ['skip_embedded_links' => true]);

        $content = preg_replace('/<p>(#CONTAINER#)<\/p>(.*?)<p>(#CONTAINER#)<\/p>/s', '<div style="margin: 40px 0;"><div class="flex-col flex md:flex-row gap-2">$2</div></div>', $content);

        $content = preg_replace('/<p>(#SECTION#)<\/p>(.*?)<p>(#SECTION#)<\/p>/s', '<div style="margin: 40px 0;">$2</div>', $content);

        $parts = $this->getParts($content);

        $view = request()->routeIs('site.home')
            ? config('config.site.view').'home'
            : config('config.site.view').'page';

        return view($view, compact('page', 'pageSlug', 'parts', 'metaTitle', 'metaDescription', 'metaKeywords', 'navs'));
    }

    public function getPageView(string $slug)
    {
        $page = Page::query()
            ->where('seo->is_public', true)
            ->where('seo->slug', $slug)
            ->firstOrFail();

        $content = $page->content;

        $content = $this->parse($content);

        return $content;
    }

    private function getTopElements(?string $content = null): array
    {
        if (! $content) {
            return [];
        }

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);

        // Use body wrapper to handle fragments properly
        $dom->loadHTML('<?xml encoding="UTF-8"><body>'.$content.'</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $body = $dom->getElementsByTagName('body')->item(0);

        if (! $body) {
            return [];
        }

        $topElements = [];
        foreach ($body->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $topElements[] = $dom->saveHTML($child);
            }
        }

        return $topElements;
    }

    private function getParts($content)
    {
        // first remove all new line characters
        $content = str_replace("\n", '', $content);

        // Split content at <p>## markers used for built-in page widgets.
        $parts = preg_split('/<p>##/', $content);

        $contentParts = [];

        // Handle the first part (everything before first ##)
        if (! empty($parts[0])) {
            $contentParts = $this->getContents($parts[0], $contentParts);
        }

        // Handle remaining parts
        for ($i = 1; $i < count($parts); $i++) {
            if (empty($parts[$i])) {
                continue;
            }

            // Split at </p> to separate widget names from regular content.
            $subParts = explode('</p>', $parts[$i], 2);

            // Extract built-in widget names.
            preg_match_all('/##([^#]+)##/', '<p>##'.$subParts[0], $matches);
            if (! empty($matches[1])) {
                $contentParts[] = [
                    'type' => 'widgets',
                    'content' => $matches[1],
                ];
            }

            // Add remaining content as HTML if it exists
            if (! empty($subParts[1])) {
                $contentParts = $this->getContents(trim($subParts[1]), $contentParts);
            }
        }

        return $contentParts;
    }

    private function getLinkProvider(string $url): ?string
    {
        $uri = uri($url);

        $host = strtolower($uri->host());
        $host = preg_replace('/^www\./', '', $host);

        return match ($host) {
            'youtube.com', 'youtu.be' => 'youtube',
            'x.com', 'twitter.com' => 'twitter',
            'facebook.com', 'fb.watch' => 'facebook',
            default => null,
        };
    }

    private function getContents(string $content, array &$contentParts): array
    {
        $topElements = $this->getTopElements($content);

        foreach ($topElements as $element) {
            if (preg_match_all('~<p>\s*(https?://[^\s<]+)\s*</p>~i', $element, $linkMatches)) {
                foreach ($linkMatches[1] ?? [] as $link) {
                    $provider = $this->getLinkProvider($link);

                    if ($provider == 'youtube') {
                        $link = $this->getYouTubeVideoId($link);
                    }

                    $contentParts[] = [
                        'type' => $provider,
                        'content' => $link,
                    ];
                }
            } else {
                $contentParts[] = [
                    'type' => 'html',
                    'content' => trim($element),
                ];
            }
        }

        return $contentParts;
    }

    private function getYouTubeVideoId(string $url): ?string
    {
        $uri = uri($url);

        $host = strtolower(preg_replace('/^www\./', '', $uri->host()));

        return match ($host) {
            'youtube.com' => Str::after($uri->query('v'), 'v='),
            'youtu.be' => ltrim($uri->path(), '/'),
            default => null,
        };
    }
}
