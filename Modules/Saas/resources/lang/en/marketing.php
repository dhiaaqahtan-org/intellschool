<?php

return [

    'nav' => [
        'platform' => 'Platform',
        'modules'  => 'Modules',
        'roles'    => 'Roles',
        'security' => 'Security',
        'pricing'  => 'Pricing',
        'faq'      => 'FAQ',
        'signin'   => 'Sign in',
        'demo'     => 'Book a demo',
        'primary'  => 'Primary',
        'language' => 'Language',
        'open_menu'=> 'Open menu',
        'skip'     => 'Skip to main content',
    ],

    'hero' => [
        'badge'    => 'Multi-tenant · one database per school',
        'title_a'  => 'Run the whole school',
        'title_b'  => 'on one platform.',
        'lede'     => 'Admissions, academics, attendance, exams, fees, payroll, transport, library and parent communication — in a single system, with every school\'s records held in its own isolated database.',
        'secondary'=> 'See the platform',
        'locale_note' => 'Arabic and English interface, right-to-left ready from day one.',
        'preview_label' => 'Product interface preview',
        'facts' => [
            'modules'     => 'Modules',
            'endpoints'   => 'API endpoints',
            'roles'       => 'Built-in roles',
            'permissions' => 'Permissions',
        ],
    ],

    'platform' => [
        'eyebrow' => 'The platform',
        'title'   => 'One system instead of six disconnected tools',
        'lede'    => 'Most schools run admissions in one place, fees in another, timetables in a spreadsheet and parent messages in a chat group. Everything here lives in the same record, so a change in one place is visible everywhere it matters.',
        'cards'   => [
            'enrolment'  => ['Enrolment to alumni', 'Online registration, document checklists, verification, roll numbers, batch allocation, promotion, transfer and alumni records — one continuous student file.'],
            'fees'       => ['Fees that reconcile', 'Fee heads, groups, structures, concessions, late fees, instalments, partial and head-wise payments, receipts, cancellations and a day-closure ledger.'],
            'academics'  => ['Academics and exams', 'Courses, batches, divisions, subjects, timetables, lesson plans, assignments, online classes, exam schedules, grades, marksheets and certificates.'],
            'staff'      => ['Staff and payroll', 'Employee records, designations, work shifts, timesheets, leave allocation and requests, salary templates and structures, and payroll processing.'],
            'operations' => ['Daily operations', 'Transport routes and vehicle records, hostel allocation, mess menus, inventory and stock, library circulation, gate passes, visitors and enquiries.'],
            'parents'    => ['Reaching parents', 'Announcements, email, SMS, WhatsApp and push messaging with delivery logs, plus a guardian portal, helpdesk tickets and complaint tracking.'],
        ],
    ],

    'admissions' => [
        'eyebrow' => 'Admissions',
        'title'   => 'From enquiry to enrolled, without re-typing anything',
        'lede'    => 'A reception enquiry becomes an online registration, then a verified student record with a fee structure already allocated. Each stage is a permissioned action with its own audit trail.',
        'points'  => [
            ['Public registration form', 'with document upload, qualification history and guardian details.'],
            ['Staged review', '— verify, assign fee, request edits, reject with reason, or undo a rejection.'],
            ['Registration payment', 'through a configured gateway, or skipped and recorded manually.'],
            ['Automatic roll numbers', 'and batch/division allocation on approval.'],
        ],
    ],

    'modules' => [
        'eyebrow' => 'Coverage',
        'title'   => ':count modules, grouped by the job they do',
        'lede'    => 'The number beside each module is how many API endpoints it exposes — a reasonable proxy for how deep the module actually goes. Nothing here is a roadmap item; these ship in the product today.',
        'names'   => [
            'student' => 'Student', 'employee' => 'Employee', 'academic' => 'Academic',
            'core' => 'Core', 'exam' => 'Exam', 'finance' => 'Finance',
            'transport' => 'Transport', 'inventory' => 'Inventory', 'reception' => 'Reception',
            'resource' => 'Resource', 'library' => 'Library', 'hostel' => 'Hostel',
            'task' => 'Tasks', 'communication' => 'Communication', 'helpdesk' => 'Helpdesk',
            'approval' => 'Approvals', 'asset' => 'Assets', 'calendar' => 'Calendar',
            'mess' => 'Mess', 'contact' => 'Contacts', 'blog' => 'Blog', 'news' => 'News',
            'auth' => 'Authentication', 'activity' => 'Activities', 'guardian' => 'Guardian',
            'form' => 'Forms', 'recruitment' => 'Recruitment', 'site' => 'School site',
            'post' => 'Posts', 'gallery' => 'Gallery', 'chat' => 'Chat',
            'discipline' => 'Discipline', 'device' => 'Devices', 'misc' => 'Misc',
        ],
    ],

    'roles' => [
        'eyebrow'     => 'Permissions',
        'title'       => 'Everyone sees their own school, not everyone else\'s data',
        'lede'        => ':count roles ship configured out of the box, each holding a specific permission set that is enforced on the server — not by hiding menu items. Roles are scoped to a campus, so a branch accountant never sees another branch\'s ledger.',
        'tablist'     => 'Roles',
        'permissions' => 'permissions',
        'items' => [
            'admin' => [
                'name'    => 'School admin',
                'summary' => 'Full control of the school\'s own account: configuration, users, roles, academic setup, finance, payroll, backups and the public school website. No visibility into any other school.',
            ],
            'principal' => [
                'name'    => 'Principal',
                'summary' => 'Academic and staff oversight across the campus: timetables, exams, attendance, discipline, approvals and reporting, without the finance and system-configuration surface an admin holds.',
            ],
            'accountant' => [
                'name'    => 'Accountant',
                'summary' => 'Fee collection, receipts, concessions, ledgers, transactions and day closure. Sensitive academic and HR records stay out of reach unless explicitly granted.',
            ],
            'staff' => [
                'name'    => 'Teacher / staff',
                'summary' => 'Attendance marking, marks entry for assigned subjects, lesson plans, assignments, student diary and learning material — limited to the batches and subjects they are in charge of.',
            ],
            'guardian' => [
                'name'    => 'Guardian',
                'summary' => 'Their own children only: attendance, results, fee dues and payment, timetable, announcements, diary, leave requests and helpdesk tickets.',
            ],
            'student' => [
                'name'    => 'Student',
                'summary' => 'Timetable, assignments, learning material, online classes, exam schedule and results, library issues, leave and service requests.',
            ],
        ],
    ],

    'isolation' => [
        'eyebrow' => 'Tenant isolation',
        'title'   => 'Every school gets its own database. Not a shared table with a filter.',
        'lede'    => 'Most school platforms put every customer\'s students in one set of tables and rely on the application remembering to filter by school on every query. One forgotten condition exposes another school\'s records. Here the school is resolved from the hostname and the database connection is switched before authentication runs, so a missed filter has nothing to leak into.',
        'points'  => [
            ['Database per tenant.', 'The physical database is the boundary. Application bugs cannot cross it.'],
            ['Resolution before authentication.', 'Unknown, unverified or suspended hosts fail closed rather than falling back to a default.'],
            ['Tenant-scoped everything.', 'Cache keys, locks, rate limits, queued jobs, file paths, broadcasts and exports all carry the tenant identifier.'],
            ['No universal super-user.', 'Platform operators use a separate guard; support access is time-limited, scoped, approved and fully audited.'],
            ['Custom domains verified by DNS', 'before they route, with exact normalized host matching.'],
            ['Per-school backup and deletion.', 'Offboarding removes one database and one storage prefix, not rows scattered across shared tables.'],
        ],
        'flow' => [
            'request'  => ['Request', 'yourschool.:host'],
            'resolve'  => ['Resolve tenant', 'Verified host → tenant identifier'],
            'switch'   => ['Switch connection', 'Dedicated tenant database'],
            'auth'     => ['Authenticate', 'User looked up inside the tenant'],
            'blocked'  => ['Cross-tenant read', 'Guessed ID from another school'],
        ],
        'tags' => [
            'host' => 'Host', 'landlord' => 'Control plane', 'isolated' => 'Isolated',
            'guard' => 'Guard', 'denied' => 'Denied',
        ],
        'no_claims' => 'Availability, recovery-time and certification claims are deliberately absent from this page until monitoring, restore drills and an external security review produce evidence to support them.',
    ],

    'campus' => [
        'eyebrow' => 'Groups and branches',
        'title'   => 'One account, many campuses',
        'lede'    => 'A school group signs up once. Inside that account it can run several campuses, each with its own staff, students, timetable and ledger — while group leadership sees the consolidated picture.',
        'items'   => [
            ['Account', 'The billing, legal and security boundary. One isolated database, one subscription, one set of custom domains. This is what a competitor can never see into.'],
            ['Organisation', 'The education-domain grouping inside the account — a trust, a group, or simply the single school. Holds shared configuration and reporting.'],
            ['Campus', 'A school, college, institute or branch. Users are assigned per campus and switch between the ones they are entitled to; permissions are evaluated per campus.'],
        ],
    ],

    'mobile' => [
        'eyebrow' => 'Mobile',
        'title'   => 'A companion app for parents, students and staff',
        'lede'    => 'The mobile client is built on the same permissioned API as the web app, with local storage for reading attendance, timetables and announcements when the network drops.',
        'points'  => [
            ['Arabic and English', 'with full right-to-left layout.'],
            ['Local cache and an outbox', 'so actions taken offline are queued and replayed.'],
            ['Per-school storage partitioning', '— one device can hold two schools without their data mixing.'],
        ],
        'status'  => 'In development — not yet released',
        'caveat'  => 'We are not publishing store links, screenshots or offline guarantees until the client builds cleanly, passes its two-school isolation tests and completes a field trial. This section will state a release date once it exists.',
    ],

    'pricing' => [
        'eyebrow'     => 'Pricing',
        'title'       => 'Priced per school, by size',
        'lede'        => 'Every plan includes all :modules modules, the guardian and student portals, Arabic and English, and an isolated database. Plans differ on capacity and operational commitments, not on withholding core features.',
        'placeholder' => 'Placeholder figures.',
        'placeholder_body' => 'These tiers illustrate the structure only. Replace every price, limit and currency with commercially approved values before this page goes live — publishing unapproved pricing creates a contractual exposure.',
        'unavailable' => 'Pricing is not published yet. Contact us and we will quote against your campus count and student numbers.',
        'per_month'   => '/ month',
        'custom'      => 'Custom',
        'popular'     => 'Most schools',
        'talk'        => 'Talk to us',
        'contact'     => 'Contact sales',
    ],

    'implementation' => [
        'eyebrow' => 'Getting started',
        'title'   => 'What the first term actually looks like',
        'lede'    => 'No school switches systems mid-year on a whim. This is the sequence we work through, and roughly where the effort sits.',
        'steps'   => [
            ['Scoping call', 'Campuses, student and staff counts, academic calendar, fee structure complexity, and which existing systems have to be imported.'],
            ['Provisioning', 'Your account, database, subdomain and first campus are created automatically. You get an owner login and a setup checklist.'],
            ['Import & configure', 'Students, guardians, staff and fee structures are imported with a dry run and a validation report before anything is written.'],
            ['Pilot then go live', 'One grade or one campus runs live first. Once attendance, fees and reports reconcile against your old system, the rest follows.'],
        ],
    ],

    'faq' => [
        'eyebrow' => 'Questions',
        'title'   => 'Frequently asked',
        'items'   => [
            ['Where is our data stored, and who can see it?', 'Your school gets a dedicated database. Other schools on the platform run against different databases and have no route to yours. Platform staff authenticate against a separate system and have no standing access to school data; support access requires an approved, time-limited, audited session.'],
            ['Can we use our own domain?', 'Yes. You start on a subdomain such as yourschool.:host. A custom domain can be added once DNS ownership is verified; until verification completes, the domain does not route.'],
            ['Does it work in Arabic?', 'Arabic and English are both first-class, including right-to-left layout throughout the interface, printed documents and the parent-facing portal. Users choose their own language independently of the school default.'],
            ['Can we run several campuses under one account?', 'Yes. One account can contain an organisation with multiple campuses. Staff are assigned per campus and switch between the ones they are entitled to; group leadership can report across all of them.'],
            ['What happens if we stop paying?', 'Access moves to a defined grace period, then to a read-only state that still allows export and billing. School records are not deleted because an invoice failed. Deletion only happens through an explicit, confirmed offboarding process after the retention window.'],
            ['Can we get our data out?', 'Yes — structured exports by domain, with a manifest, schema version and checksums, so the export can be verified and re-imported rather than just downloaded.'],
            ['How does it integrate with what we already run?', 'The platform exposes a permissioned REST API covering every module. Payment, SMS, WhatsApp and email providers are configured per school rather than shared across the platform.'],
        ],
    ],

    'demo' => [
        'eyebrow' => 'Talk to us',
        'title'   => 'Book a demo',
        'lede'    => 'A 30-minute walkthrough against your actual situation — number of campuses, how your fee structure works, and what has to come across from your current system. No production student data is used in demos.',
        'points'  => [
            'We reply within one business day.',
            'Your details are used to arrange the demo only.',
            'Available in Arabic or English.',
        ],
    ],

    'form' => [
        'name'             => 'Your name',
        'school'           => 'School or group',
        'email'            => 'Work email',
        'email_hint'       => 'We will send the meeting invite here.',
        'size'             => 'Students',
        'size_placeholder' => 'Select a range',
        'message'          => 'What would you like to see?',
        'consent'          => 'I agree that my details may be used to contact me about this request, as described in the',
        'privacy_link'     => 'privacy notice',
        'consent_required' => 'Please confirm you agree before submitting.',
        'submit'           => 'Request a demo',
        'submitting'       => 'Sending…',
        'success'          => 'Thank you — your request has been received. We will reply within one business day.',
        'rejected'         => 'This submission could not be accepted.',
        'error_validation' => 'Please correct the highlighted fields.',
        'error_expired'    => 'Your session expired. Please reload the page and submit again.',
        'error_throttled'  => 'Too many requests from this connection. Please try again later.',
        'error_server'     => 'Something went wrong on our side. Please try again in a moment.',
        'error_network'    => 'We could not reach the server. Check your connection and try again.',
    ],

    'cta' => [
        'title' => 'See it running against your own numbers',
        'lede'  => 'Bring a fee structure, a timetable and a class list. We will show you what they look like in the system.',
        'see_pricing' => 'See pricing',
    ],

    'footer' => [
        'tagline' => 'School management for schools, colleges, institutes and academies. Delivered as a multi-tenant service.',
        'product' => 'Product',
        'trust'   => 'Trust',
        'company' => 'Company',
        'links'   => [
            'roles_permissions' => 'Roles & permissions',
            'security'  => 'Security & isolation',
            'privacy'   => 'Privacy notice',
            'terms'     => 'Terms of service',
            'dpa'       => 'Data processing addendum',
            'subprocessors' => 'Subprocessors',
            'contact'   => 'Contact',
            'help'      => 'Help centre',
            'status'    => 'Status',
        ],
        'legal_placeholder' => 'Company name, registration number and registered address go here.',
        'preview_badge'     => 'Preview build — placeholder brand & content',
    ],
];
