@php($school = config('config.general.app_name', config('app.name', 'InstiKit School ERP')))
@php($hero = '/images/site-background.jpg')
<x-site.default.layout
    :meta-title="$school . ' — Enterprise School ERP & Flutter Mobile Platform'"
    :meta-description="$school . ' — Next-generation multi-tenant school management ERP with native Flutter mobile and desktop apps for iOS, Android, Windows, and Linux.'"
    :meta-keywords="'school erp, flutter school app, multi tenant school software, education management system, instikit'">

    {{-- ================= HERO SECTION ================= --}}
    <section class="hero-showcase">
        <div class="hero-showcase__bg"></div>
        <div class="wrap hero-showcase__inner">
            <div class="hero-badge">
                <span class="pulse-dot"></span>
                <span>InstiKit Enterprise v5.5 • Multi-Tenant SaaS & Flutter Client</span>
            </div>
            
            <h1>Smart School ERP &<br><span class="text-gradient">Native Mobile Platform</span></h1>
            
            <p class="hero-desc">
                An all-in-one education ecosystem empowering administrators, teachers, students, and parents.
                Featuring 34+ integrated modules, biometrics, online exams, 6+ payment gateways, and native Flutter apps.
            </p>

            <div class="hero-actions">
                <a href="/app/login" class="btn btn-gold btn-lg">
                    <i class="fa-solid fa-gauge-high"></i> Launch ERP Web System
                </a>
                <a href="#showcase" class="btn btn-ghost btn-lg">
                    <i class="fa-solid fa-mobile-screen-button"></i> View App & Screenshots
                </a>
            </div>

            <div class="hero-metrics">
                <div class="metric-item">
                    <span class="metric-value">34+</span>
                    <span class="metric-label">ERP Modules</span>
                </div>
                <div class="metric-divider"></div>
                <div class="metric-item">
                    <span class="metric-value">100%</span>
                    <span class="metric-label">Cross-Platform (Web/iOS/Android/Desktop)</span>
                </div>
                <div class="metric-divider"></div>
                <div class="metric-item">
                    <span class="metric-value">6+</span>
                    <span class="metric-label">Payment Gateways</span>
                </div>
                <div class="metric-divider"></div>
                <div class="metric-item">
                    <span class="metric-value">RTL & LTR</span>
                    <span class="metric-label">Native Arabic/English</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= SCREENSHOTS SHOWCASE (SYSTEM & FLUTTER WSL) ================= --}}
    <section id="showcase" class="sec showcase-sec">
        <div class="wrap">
            <div class="center">
                <span class="eyebrow"><i class="fa-solid fa-desktop"></i> Live Platform Showcase</span>
                <h2 class="h-sec">Experience the ERP & WSL Flutter Ecosystem</h2>
                <p class="sub">Direct captured live previews of our Tenant Web ERP and WSL-rendered Flutter Client application.</p>
            </div>

            {{-- Dual Preview Grid --}}
            <div class="showcase-grid">
                {{-- Web ERP System Mockup --}}
                <div class="showcase-card showcase-card--web">
                    <div class="showcase-header">
                        <div class="window-dots">
                            <span class="dot dot-red"></span>
                            <span class="dot dot-yellow"></span>
                            <span class="dot dot-green"></span>
                        </div>
                        <div class="window-address">
                            <i class="fa-solid fa-lock text-green"></i> https://tenant.instikit.com/app/login
                        </div>
                        <div class="window-badge"><i class="fa-solid fa-globe"></i> Web Tenant ERP</div>
                    </div>
                    <div class="showcase-media">
                        <img src="/images/showcase/web-erp-login.png" 
                             alt="InstiKit Web Tenant ERP System Screenshot" 
                             onerror="this.onerror=null; this.src='/images/site-background.jpg';"
                             class="showcase-img" />
                        <div class="showcase-overlay">
                            <div class="overlay-tags">
                                <span class="tag"><i class="fa-solid fa-server"></i> Laravel 12</span>
                                <span class="tag"><i class="fa-solid fa-layer-group"></i> Multi-Tenancy</span>
                                <span class="tag"><i class="fa-solid fa-shield-halved"></i> Spatie RBAC</span>
                            </div>
                        </div>
                    </div>
                    <div class="showcase-footer">
                        <h3><i class="fa-solid fa-laptop-code text-gold"></i> Web Tenant ERP Portal</h3>
                        <p>Complete web administrative portal with real-time dashboards, financial reporting, payroll engine, and fee management.</p>
                    </div>
                </div>

                {{-- Flutter Client App WSL Mockup --}}
                <div class="showcase-card showcase-card--flutter">
                    <div class="showcase-header">
                        <div class="window-dots">
                            <span class="dot dot-blue"></span>
                            <span class="dot dot-blue"></span>
                            <span class="dot dot-blue"></span>
                        </div>
                        <div class="window-address">
                            <i class="fa-solid fa-terminal text-gold"></i> WSL2 • Flutter Web & Desktop Client
                        </div>
                        <div class="window-badge badge-flutter"><i class="fa-brands fa-flutter"></i> Flutter App (WSL)</div>
                    </div>
                    <div class="showcase-media showcase-media--dual">
                        {{-- Desktop Flutter --}}
                        <div class="flutter-desk-frame">
                            <img src="/images/showcase/flutter-client-desktop.png" 
                                 alt="Flutter App Running in WSL Desktop View" 
                                 onerror="this.onerror=null; this.src='/images/site-background.jpg';"
                                 class="showcase-img" />
                        </div>
                        {{-- Mobile Flutter Overlay --}}
                        <div class="flutter-mobile-frame">
                            <img src="/images/showcase/flutter-client-mobile.png" 
                                 alt="Flutter App Mobile Viewport" 
                                 onerror="this.onerror=null; this.src='/images/site-background.jpg';"
                                 class="showcase-img-mobile" />
                        </div>
                    </div>
                    <div class="showcase-footer">
                        <h3><i class="fa-brands fa-flutter text-blue"></i> Cross-Platform Flutter Client (WSL)</h3>
                        <p>High-performance native Flutter app compiled for iOS, Android, Windows, and Linux with offline Drift database and push alerts.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= FULL FEATURE MATRIX ================= --}}
    <section class="sec sec--paper2">
        <div class="wrap">
            <div class="center">
                <span class="eyebrow"><i class="fa-solid fa-cubes"></i> System Capabilities</span>
                <h2 class="h-sec">Deep Step-by-Step Feature Breakdown</h2>
                <p class="sub">Built for seamless institution administration from student admissions to staff payroll.</p>
            </div>

            <div class="feature-grid">
                {{-- Feature 1 --}}
                <div class="feature-card">
                    <div class="feature-icon feature-icon--navy">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <h3>Academic & Curriculum</h3>
                    <p>Manage program types, academic sessions, course & batch structures, subject incharge assignments, timetable engine, and digital booklists.</p>
                    <ul class="feature-list">
                        <li><i class="fa-solid fa-check text-gold"></i> Dynamic Timetable Builder</li>
                        <li><i class="fa-solid fa-check text-gold"></i> Batch & Course Promotions</li>
                        <li><i class="fa-solid fa-check text-gold"></i> Subject Incharge Mapping</li>
                    </ul>
                </div>

                {{-- Feature 2 --}}
                <div class="feature-card">
                    <div class="feature-icon feature-icon--gold">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <h3>Student & Guardian Lifecycle</h3>
                    <p>Enquiry wizard, registration verification, provisional admissions, seat allocation, mentor assignment, digital ID cards, and student documents.</p>
                    <ul class="feature-list">
                        <li><i class="fa-solid fa-check text-gold"></i> QR Code & Biometric Attendance</li>
                        <li><i class="fa-solid fa-check text-gold"></i> Student Clock In / Clock Out</li>
                        <li><i class="fa-solid fa-check text-gold"></i> Parent & Guardian Portals</li>
                    </ul>
                </div>

                {{-- Feature 3 --}}
                <div class="feature-card">
                    <div class="feature-icon feature-icon--navy">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <h3>Financials & Fee Engine</h3>
                    <p>Multi-installment payments, fee concessions, head-wise collection reports, cashier day book closure, QR payment links, and receipt printouts.</p>
                    <ul class="feature-list">
                        <li><i class="fa-solid fa-check text-gold"></i> 6+ Payment Gateways</li>
                        <li><i class="fa-solid fa-check text-gold"></i> Cashier Day Book & Closure</li>
                        <li><i class="fa-solid fa-check text-gold"></i> Auto Round-Off & Concessions</li>
                    </ul>
                </div>

                {{-- Feature 4 --}}
                <div class="feature-card">
                    <div class="feature-icon feature-icon--gold">
                        <i class="fa-solid fa-users-gear"></i>
                    </div>
                    <h3>HR, Leave & Payroll</h3>
                    <p>Staff recruitment pipeline, biometrics, department-level permissions, half-day leave tracking, dynamic payroll formulas, and payment advice.</p>
                    <ul class="feature-list">
                        <li><i class="fa-solid fa-check text-gold"></i> Bulk Payroll Processing</li>
                        <li><i class="fa-solid fa-check text-gold"></i> Half-Day & Full-Day Leaves</li>
                        <li><i class="fa-solid fa-check text-gold"></i> Custom Payhead Formulas</li>
                    </ul>
                </div>

                {{-- Feature 5 --}}
                <div class="feature-card">
                    <div class="feature-icon feature-icon--navy">
                        <i class="fa-solid fa-file-pen"></i>
                    </div>
                    <h3>Exams & Marksheets</h3>
                    <p>Online examination module, competency-based evaluation, auto-locking exam marks, weightage calculation, attempt tracking, and report cards.</p>
                    <ul class="feature-list">
                        <li><i class="fa-solid fa-check text-gold"></i> Online Exam Simulator</li>
                        <li><i class="fa-solid fa-check text-gold"></i> Auto Marks Locking</li>
                        <li><i class="fa-solid fa-check text-gold"></i> Custom Marksheet Templates</li>
                    </ul>
                </div>

                {{-- Feature 6 --}}
                <div class="feature-card feature-card--highlight">
                    <div class="feature-icon feature-icon--flutter">
                        <i class="fa-brands fa-flutter"></i>
                    </div>
                    <h3>Flutter Mobile & Desktop Apps</h3>
                    <p>Offline-first cross-platform client built with Riverpod, Drift DB, and GoRouter for iOS, Android, Windows, Linux, and Web.</p>
                    <ul class="feature-list">
                        <li><i class="fa-solid fa-check text-gold"></i> Mobile Push Notifications</li>
                        <li><i class="fa-solid fa-check text-gold"></i> Biometric Auth (Fingerprint/FaceID)</li>
                        <li><i class="fa-solid fa-check text-gold"></i> Offline Local Data Sync</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= ECOSYSTEM CROSS PLATFORM ================= --}}
    <section class="sec ecosystem-sec">
        <div class="wrap split">
            <div class="split__aside">
                <span class="eyebrow"><i class="fa-solid fa-mobile-retro"></i> Unified Ecosystem</span>
                <h2 class="h-sec">One Codebase,<br>Every Device</h2>
                <p>
                    Whether your staff operates on desktop web browsers, administrators access from Windows/Linux workstations, 
                    or parents check student progress on mobile phones, InstiKit delivers a consistent, high-performance experience.
                </p>
                <div class="platform-badges">
                    <span class="p-badge"><i class="fa-brands fa-chrome"></i> Web</span>
                    <span class="p-badge"><i class="fa-brands fa-apple"></i> iOS</span>
                    <span class="p-badge"><i class="fa-brands fa-android"></i> Android</span>
                    <span class="p-badge"><i class="fa-brands fa-windows"></i> Windows</span>
                    <span class="p-badge"><i class="fa-brands fa-linux"></i> Linux (WSL)</span>
                </div>
            </div>
            <div class="cards-3">
                <article class="pcard">
                    <div class="pcard__media">
                        <div class="pcard__badge"><i class="fa-solid fa-shield-cat"></i></div>
                    </div>
                    <div class="pcard__body">
                        <h3>Multi-Tenant Architecture</h3>
                        <p>Isolated tenant spaces, customizable domain routing, and enterprise-grade security per school site.</p>
                    </div>
                </article>

                <article class="pcard">
                    <div class="pcard__media">
                        <div class="pcard__badge"><i class="fa-solid fa-language"></i></div>
                    </div>
                    <div class="pcard__body">
                        <h3>First-Class RTL Arabic</h3>
                        <p>Fully localized in English & Arabic with complete right-to-left layout reflow across Web and Flutter.</p>
                    </div>
                </article>

                <article class="pcard">
                    <div class="pcard__media">
                        <div class="pcard__badge"><i class="fa-solid fa-comments"></i></div>
                    </div>
                    <div class="pcard__body">
                        <h3>Omnichannel Messaging</h3>
                        <p>Integrated WhatsApp, SMS, Email notifications, and Pusher-powered real-time chat between users.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    {{-- ================= FAQ ================= --}}
    <section class="sec sec--paper2">
        <div class="wrap">
            <div class="center">
                <span class="eyebrow">FAQ</span>
                <h2 class="h-sec">Frequently Asked Questions</h2>
            </div>
            <div class="faq">
                <details>
                    <summary>What platforms are supported by the InstiKit Flutter Client?</summary>
                    <p>The Flutter client supports Android, iOS, Windows, Linux (including WSL2 environments), and Web platforms from a single codebase.</p>
                </details>
                <details>
                    <summary>How does multi-tenancy work in the Web ERP?</summary>
                    <p>Each tenant institution gets isolated database scoping, customized domain branding, fee structures, and administrative controls.</p>
                </details>
                <details>
                    <summary>Which payment gateways are integrated?</summary>
                    <p>Out-of-the-box integrations include Hubtel, Billdesk, CCAvenue, Amwalpay, Paystack, and Billplz, with automatic receipt generation and day book log.</p>
                </details>
            </div>
        </div>
    </section>

    {{-- ================= CTA ================= --}}
    <section class="sec sec--tight">
        <div class="wrap">
            <div class="cta">
                <div class="cta__bg" style="background-image:url('{{ $hero }}')"></div>
                <div class="cta__inner">
                    <div>
                        <h2>Transform Your Educational Institution Today</h2>
                        <p>Get started with InstiKit Web ERP System and Cross-Platform Flutter Mobile Apps.</p>
                    </div>
                    <div class="cta__btns">
                        <a href="/app/login" class="btn btn-gold"><i class="fa-solid fa-right-to-bracket"></i> ERP Web Portal</a>
                        <a href="/pages/contact" class="btn btn-ghost">Contact Sales</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-site.default.layout>
