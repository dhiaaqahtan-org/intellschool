<?php

return [
    'site' => 'Site',
    'config' => [
        'props' => [
            'public_view' => 'Show in public',
            'color_scheme' => 'Color Scheme',
            'google_map_embed_url' => 'Google Map Embed URL',
        ],
    ],
    'page' => [
        'page' => 'Page',
        'pages' => 'Pages',
        'module_title' => 'List all pages',
        'module_description' => 'Manage public website pages',
        'props' => [
            'name' => 'Navigation name',
            'title' => 'Title',
            'slug' => 'Slug',
            'sub_title' => 'Sub Title',
            'content' => 'Content',
            'cta' => 'Call To Action',
            'cta_title' => 'CTA Title',
            'cta_description' => 'CTA Description',
            'cta_button_text' => 'CTA Button Text',
            'cta_button_link' => 'CTA Button Link',
        ],
    ],
    'seo' => [
        'seo' => 'SEO',
        'is_public' => 'Is Public',
        'slug' => 'Slug',
        'meta_title' => 'Meta Title',
        'meta_description' => 'Meta Description',
        'meta_keywords' => 'Meta Keywords',
        'robots' => 'Allow discovery by search engines',
    ],
    'assets' => [
        'cover' => 'Cover',
        'og' => 'OG',
        'custom_og' => 'Custom OG Image',
        'og_info' => 'Upload an OG Image (600x315). This image is used when the page is shared on social media.',
    ],
];
