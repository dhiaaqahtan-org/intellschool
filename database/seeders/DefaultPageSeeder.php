<?php

namespace Database\Seeders;

use App\Enums\Site\MenuPlacement;
use App\Models\Config\Config;
use App\Models\Site\Menu;
use App\Models\Site\Page;
use Illuminate\Database\Seeder;

class DefaultPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->enableArabicWebsite();

        $pages = [
            [
                'name' => 'Home',
                'title' => 'مرحباً بكم في '.config('app.name'),
                'sub_title' => 'بوابتكم الإلكترونية لخدمات المدرسة ومعلوماتها',
                'content' => 'يوفر الموقع نافذة موحدة للتعريف بالمدرسة والوصول إلى خدمات التسجيل والدفع الإلكتروني وتسجيل الدخول إلى نظام الإدارة.',
                'seo' => [
                    'is_public' => true,
                    'slug' => 'home',
                    'meta_title' => config('app.name'),
                    'meta_description' => 'الموقع الإلكتروني الرسمي لـ '.config('app.name'),
                    'meta_keywords' => 'مدرسة، تعليم، تسجيل، خدمات إلكترونية',
                ],
                'menu' => [
                    'name' => 'الرئيسية',
                    'slug' => 'Home',
                    'position' => 1,
                    'is_default' => true,
                ],
            ],
            [
                'name' => 'Contact',
                'title' => 'تواصل معنا',
                'sub_title' => 'قنوات التواصل الرسمية',
                'content' => 'يمكنكم التواصل معنا عبر بيانات الاتصال الرسمية الظاهرة في الموقع.',
                'seo' => [
                    'is_public' => true,
                    'slug' => 'contact',
                    'meta_title' => 'تواصل معنا - '.config('app.name'),
                    'meta_description' => 'بيانات التواصل مع '.config('app.name'),
                    'meta_keywords' => 'تواصل، مدرسة، بريد إلكتروني، هاتف',
                ],
                'menu' => [
                    'name' => 'تواصل معنا',
                    'slug' => 'contact',
                    'position' => 2,
                    'is_default' => false,
                ],
            ],
        ];

        foreach ($pages as $pageData) {
            $menuData = $pageData['menu'];
            unset($pageData['menu']);

            $page = Page::query()->firstOrNew(['name' => $pageData['name']]);

            if (! $page->exists) {
                $page->forceFill($pageData)->save();
            } else {
                $this->translatePlaceholderPage($page, $pageData);
            }

            $menu = Menu::query()->where('slug', $menuData['slug'])->first();

            if (! $menu) {
                Menu::query()->forceCreate([
                    ...$menuData,
                    'placement' => MenuPlacement::HEADER,
                    'page_id' => $page->id,
                ]);

                continue;
            }

            $updates = [];

            if (! $menu->page_id) {
                $updates['page_id'] = $page->id;
            }

            if (! $menu->placement) {
                $updates['placement'] = MenuPlacement::HEADER;
            }

            if (in_array($menu->name, [null, '', 'Home', 'Contact'], true)) {
                $updates['name'] = $menuData['name'];
            }

            if ($menuData['is_default'] && ! $menu->is_default) {
                $updates['is_default'] = true;
            }

            if ($updates) {
                $menu->forceFill($updates)->save();
            }
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

    private function translatePlaceholderPage(Page $page, array $pageData): void
    {
        $updates = [];

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
