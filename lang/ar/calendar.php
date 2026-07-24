<?php

return [
    'calendar' => 'التقويم',
    'holiday' => [
        'holiday' => 'العطلة',
        'module_title' => 'إدارة جميع العطلات',
        'module_description' => 'حدّد العطلات لطلابك وموظفيك، وتُستخدم لتوليد سجلات الحضور والرواتب.',
        'exists' => ':attribute مُعلّم بالفعل كعطلة.',
        'range_exists' => 'توجد عطلة بالفعل بين :start و :end.',
        'props' => [
            'name' => 'الاسم',
            'duration' => 'المدة',
            'days' => 'الأيام',
            'type' => 'النوع',
            'type_range' => 'نطاق',
            'type_dates' => 'تواريخ',
            'type_weekend' => 'نهاية الأسبوع',
            'dates' => 'التواريخ',
            'start_date' => 'تاريخ البدء',
            'end_date' => 'تاريخ الانتهاء',
            'description' => 'الوصف',
        ],
    ],
    'celebration' => [
        'celebration' => 'المناسبة',
        'celebrations' => 'المناسبات',
        'module_title' => 'عرض جميع المناسبات',
        'module_description' => 'إدارة جميع المناسبات',
    ],
    'event' => [
        'event' => 'الفعالية',
        'events' => 'الفعاليات',
        'module_title' => 'عرض جميع الفعاليات',
        'module_description' => 'إدارة جميع الفعاليات',
        'props' => [
            'code_number' => 'رقم الفعالية',
            'title' => 'العنوان',
            'type' => 'النوع',
            'start_date' => 'تاريخ البدء',
            'start_time' => 'وقت البدء',
            'end_date' => 'تاريخ الانتهاء',
            'end_time' => 'وقت الانتهاء',
            'venue' => 'المكان',
            'is_public' => 'عام',
            'for_alumni' => 'للخريجين',
            'for' => 'لـ',
            'audience' => 'الجمهور',
            'excerpt' => 'مقتطف',
            'description' => 'الوصف',
            'cover_image' => 'صورة الغلاف',
        ],
        'type' => [
            'type' => 'نوع الفعالية',
            'types' => 'أنواع الفعاليات',
            'module_title' => 'إدارة جميع أنواع الفعاليات',
            'module_description' => 'عرض جميع أنواع الفعاليات',
            'props' => [
                'name' => 'الاسم',
                'description' => 'الوصف',
            ],
        ],
        'config' => [
            'props' => [
                'number_prefix' => 'بادئة رقم الفعالية',
                'number_suffix' => 'لاحقة رقم الفعالية',
                'number_digit' => 'عدد خانات رقم الفعالية',
            ],
        ],
    ],
    'event_incharge' => [
        'event_incharge' => 'مسؤول الفعالية',
        'event_incharges' => 'مسؤولو الفعاليات',
        'module_title' => 'عرض جميع مسؤولي الفعاليات',
        'module_description' => 'إدارة جميع مسؤولي الفعاليات',
    ],
    'config' => [
        'config' => 'الإعدادات',
        'props' => [
            'show_celebration_in_dashboard' => 'إظهار المناسبات في لوحة التحكم',
        ],
    ],
];
