<?php

namespace Database\Seeders;

use App\Models\Config\Config;
use App\Models\Site\Page;
use Illuminate\Database\Seeder;

class DefaultPageSeeder extends Seeder
{
    public function run(): void
    {
        $this->enableArabicWebsite();

        $pages = [
            [
                'names' => ['Home', 'الرئيسية', 'الرئيسية || Home'],
                'name' => 'الرئيسية || Home',
                'title' => 'مرحباً بكم في '.config('app.name'),
                'sub_title' => 'بوابتكم الإلكترونية لخدمات المدرسة ومعلوماتها',
                'content' => 'يوفر الموقع نافذة موحدة للتعريف بالمدرسة والوصول إلى خدمات التسجيل والدفع الإلكتروني وتسجيل الدخول إلى نظام الإدارة.',
                'seo' => [
                    'robots' => true,
                    'is_public' => true,
                    'slug' => 'home',
                    'meta_title' => config('app.name'),
                    'meta_description' => 'الموقع الإلكتروني الرسمي لـ '.config('app.name'),
                    'meta_keywords' => 'مدرسة، تعليم، تسجيل، خدمات إلكترونية',
                ],
            ],
            [
                'names' => ['Contact', 'تواصل معنا', 'تواصل معنا || Contact'],
                'name' => 'تواصل معنا || Contact',
                'title' => 'تواصل معنا || Contact us',
                'sub_title' => 'قنوات التواصل الرسمية || Official contact channels',
                'content' => '##CONTACT##',
                'seo' => [
                    'robots' => true,
                    'is_public' => true,
                    'slug' => 'contact',
                    'meta_title' => 'تواصل معنا || Contact us',
                    'meta_description' => 'بيانات التواصل الرسمية مع المدرسة || Official school contact information',
                    'meta_keywords' => 'تواصل، مدرسة، بريد إلكتروني، هاتف || contact, school, email, phone',
                ],
            ],
        ];

        foreach ($pages as $pageData) {
            $knownNames = $pageData['names'];
            unset($pageData['names']);

            $page = Page::query()
                ->where('seo->slug', $pageData['seo']['slug'])
                ->first()
                ?? Page::query()->whereIn('name', $knownNames)->first()
                ?? new Page;

            if (! $page->exists) {
                $page->forceFill($pageData)->save();

                continue;
            }

            $this->updatePlaceholderPage($page, $pageData, $knownNames);
        }
    }

    private function enableArabicWebsite(): void
    {
        $siteConfig = Config::query()->firstOrNew([
            'name' => 'site',
            'team_id' => null,
        ]);

        $siteConfig->value = array_merge($siteConfig->value ?? [], [
            'enable_site' => true,
            'show_public_view' => true,
            'theme' => 'default',
            'locale' => 'ar',
        ]);

        $siteConfig->save();
    }

    private function updatePlaceholderPage(Page $page, array $pageData, array $knownNames): void
    {
        $updates = [];

        if (in_array($page->name, $knownNames, true)) {
            $updates['name'] = $pageData['name'];
        }

        if (! $page->title || str_starts_with($page->title, 'Welcome to ')) {
            $updates['title'] = $pageData['title'];
        }

        if (! $page->sub_title) {
            $updates['sub_title'] = $pageData['sub_title'];
        }

        if (! $page->content || str_starts_with(trim($page->content), 'Welcome to ')) {
            $updates['content'] = $pageData['content'];
        }

        if (empty($page->seo)) {
            $updates['seo'] = $pageData['seo'];
        }

        if ($updates) {
            $page->forceFill($updates)->save();
        }
    }
}
