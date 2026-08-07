# Marketing screenshots — drop your images here

The SaaS marketing home page (`/en`, `/ar`) renders empty, framed screenshot
slots that fill in automatically the moment a matching file exists in this
folder. No code change or rebuild is needed — just add the PNG and reload.

## Web admin console (desktop browser frames)
Recommended size: **1600 × 1000 px** (16:10), PNG.

| File name           | Slot caption            |
|---------------------|-------------------------|
| `web-dashboard.png` | Dashboard & analytics   |
| `web-students.png`  | Student records         |
| `web-finance.png`   | Fees & finance          |
| `web-exams.png`     | Exams & marksheets      |

## Mobile companion app (phone frames)
Recommended size: **1080 × 2340 px** (9:19.5), PNG.

| File name          | Slot caption |
|--------------------|--------------|
| `app-home.png`     | Home         |
| `app-attendance.png` | Attendance |
| `app-timetable.png`  | Timetable  |

Any slot with no file keeps showing a labelled empty placeholder, so a partial
set is fine. To change captions or add slots, edit
`Modules/Saas/resources/lang/{en,ar}/marketing.php` (`showcase` key) and the
arrays in `Modules/Saas/resources/views/marketing/partials/showcase.blade.php`
and `app-shots.blade.php`.
