<?php

namespace Database\Seeders;

use App\Models\Site\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class SchoolWebsiteSeeder extends Seeder
{
    private const DESIGN_VERSION = 1;

    public function run(): void
    {
        $this->seedNavigationPages();

        $page = Page::query()->where('seo->slug', 'home')->first();

        if (! $page || (int) $page->getMeta('home_design_version', 0) >= self::DESIGN_VERSION) {
            return;
        }

        $content = File::get(database_path('seeders/content/school-homepage.html'));
        $content = preg_replace('/>\s+</', '><', trim($content)) ?: trim($content);

        $page->forceFill([
            'name' => 'الرئيسية || Home',
            'title' => 'التميّز الأكاديمي لبناء المستقبل || Academic Excellence, Built for the Future',
            'sub_title' => 'منهج ينمّي المعرفة والشخصية والإبداع والثقة العالمية. || An inspiring curriculum that develops knowledge, character, creativity and global confidence.',
            'content' => $content,
            'meta' => array_merge($page->meta ?? [], [
                'home_design_version' => self::DESIGN_VERSION,
                'has_cta' => true,
                'cta_title' => 'اكتشف ما يمكن لطفلك أن يصبح عليه || Discover What Your Child Can Become',
                'cta_description' => 'انضم إلى مجتمع مدرسي يلهم التميّز ويصنع مستقبلاً أكثر إشراقاً. || Join a school community that inspires excellence and shapes a brighter future.',
                'cta_button_text' => 'قدّم الآن || Apply now',
                'cta_button_link' => url('/app/online-registration'),
            ]),
            'seo' => array_merge($page->seo ?? [], [
                'robots' => true,
                'is_public' => true,
                'slug' => 'home',
                'meta_title' => 'مدرسة هورايزون الدولية || Horizon International School',
                'meta_description' => 'تعليم متوازن ينمّي المعرفة والشخصية والإبداع والثقة العالمية. || A balanced education that develops knowledge, character, creativity and global confidence.',
                'meta_keywords' => 'مدرسة دولية، تعليم، تسجيل الطلاب || international school, education, student admissions',
            ]),
        ])->save();
    }

    private function seedNavigationPages(): void
    {
        $pages = [
            'home' => [
                'name' => 'الرئيسية || Home',
                'known_names' => ['Home', 'الرئيسية', 'الرئيسية || Home'],
                'title' => 'مرحباً بكم في مدرستنا || Welcome to our school',
                'sub_title' => 'تعليم ينمّي المعرفة والشخصية والإبداع || Education that develops knowledge, character and creativity',
                'content' => '<div data-site-locale="ar" lang="ar"><p>مرحباً بكم في الموقع الرسمي للمدرسة.</p></div><div data-site-locale="en" lang="en"><p>Welcome to the official school website.</p></div>',
            ],
            'about' => [
                'name' => 'من نحن || About',
                'title' => 'مجتمع مدرسي يضع الطالب أولاً || A school community built around every learner',
                'sub_title' => 'تعليم يجمع المعرفة والشخصية والانتماء || Education grounded in knowledge, character and belonging',
                'content' => '<div data-site-locale="ar" lang="ar"><h2>رؤيتنا التعليمية</h2><p>نبني بيئة مدرسية آمنة ومحفزة تساعد كل طالب على التعلم بثقة، وتنمّي لديه الفضول والمسؤولية والقدرة على العمل مع الآخرين.</p></div><div data-site-locale="en" lang="en"><h2>Our educational vision</h2><p>We build a safe, purposeful school environment where every learner can grow in confidence, curiosity, responsibility and collaboration.</p></div>',
            ],
            'academics' => [
                'name' => 'البرامج الأكاديمية || Academics',
                'title' => 'برامج أكاديمية لكل مرحلة || Academic programmes for every stage',
                'sub_title' => 'مسارات واضحة من الطفولة المبكرة حتى المرحلة الثانوية || Clear learning pathways from early years to secondary school',
                'content' => '<div data-site-locale="ar" lang="ar"><p>تُعرض البرامج الفعلية المسجلة في النظام أدناه، مع تفاصيل المراحل والمسارات المتاحة.</p></div><div data-site-locale="en" lang="en"><p>The programmes configured in the school system are listed below with their available stages and pathways.</p></div><p>##PROGRAM_DETAIL##</p>',
            ],
            'admissions' => [
                'name' => 'القبول والتسجيل || Admissions',
                'title' => 'ابدأ رحلة التسجيل || Start your application',
                'sub_title' => 'خطوات واضحة للالتحاق بالمدرسة || A clear path to joining the school',
                'content' => '<div data-site-locale="ar" lang="ar"><h2>التسجيل الإلكتروني</h2><p>ابدأ طلب التسجيل، ثم تابع البيانات والمستندات المطلوبة مع فريق القبول.</p><p><a class="school-button school-button--navy" href="/app/online-registration">ابدأ التسجيل</a></p></div><div data-site-locale="en" lang="en"><h2>Online registration</h2><p>Start an application and continue the required information and documents with the admissions team.</p><p><a class="school-button school-button--navy" href="/app/online-registration">Start registration</a></p></div>',
            ],
            'campus-life' => [
                'name' => 'الحياة المدرسية || Campus Life',
                'title' => 'حياة مدرسية غنية بالتجارب || A school day rich in experience',
                'sub_title' => 'أنشطة وفعاليات ومساحات تدعم التعلّم والانتماء || Activities, events and spaces that support learning and belonging',
                'content' => '<div data-site-locale="ar" lang="ar"><p>تعرف على أحدث الفعاليات والأنشطة التي تشكل الحياة اليومية في المدرسة.</p></div><div data-site-locale="en" lang="en"><p>Explore the events and activities that shape everyday school life.</p></div><p>##EVENT_SUMMARY##</p><p>##GALLERY_SUMMARY##</p>',
            ],
            'news' => [
                'name' => 'الأخبار || News',
                'title' => 'أخبار المدرسة || School news',
                'sub_title' => 'آخر الأخبار والتحديثات المنشورة || Recent published news and updates',
                'content' => '<p>##NEWS_LIST##</p>',
            ],
            'contact' => [
                'name' => 'تواصل معنا || Contact',
                'known_names' => ['Contact', 'تواصل معنا', 'تواصل معنا || Contact'],
                'title' => 'تواصل معنا || Contact us',
                'sub_title' => 'قنوات التواصل الرسمية || Official contact channels',
                'content' => '<p>##CONTACT##</p>',
            ],
        ];

        foreach ($pages as $slug => $pageData) {
            $page = Page::query()->where('seo->slug', $slug)->first();

            if ($page) {
                if (in_array($page->name, $pageData['known_names'] ?? [$pageData['name']], true)) {
                    $page->forceFill(['name' => $pageData['name']])->save();
                }

                continue;
            }

            Page::query()->forceCreate([
                'name' => $pageData['name'],
                'title' => $pageData['title'],
                'sub_title' => $pageData['sub_title'],
                'content' => $pageData['content'],
                'seo' => [
                    'robots' => true,
                    'is_public' => true,
                    'slug' => $slug,
                    'meta_title' => $pageData['title'],
                    'meta_description' => $pageData['sub_title'],
                    'meta_keywords' => 'مدرسة، تعليم || school, education',
                ],
            ]);
        }
    }
}
