@php($school = config('config.general.app_name', config('app.name', 'Our School')))
@php($hero = '/images/site-background.jpg')
<x-site.default.layout
    :meta-title="$school"
    :meta-description="$school . ' — an inspiring curriculum that develops knowledge, character, creativity and global confidence.'"
    :meta-keywords="'school, education, admission, academics, ' . $school">

    {{-- ================= HERO ================= --}}
    <section class="hero">
        <div class="hero__bg" style="background-image:url('{{ $hero }}')"></div>
        <div class="wrap hero__inner">
            <div class="hero__crumb"><span>{{ __('website.home') ?? 'Home' }}</span> / <b>Academics</b></div>
            <h1>Academic Excellence,<br>Built for the Future</h1>
            <p>An inspiring curriculum that develops knowledge, character, creativity and global confidence.</p>
            <div class="hero__cta">
                <a href="/pages/admissions" class="btn btn-gold"><i class="fa-solid fa-paper-plane"></i> Apply for Admission</a>
                <a href="/pages/about" class="btn btn-ghost">Book a Tour</a>
            </div>
        </div>
    </section>

    {{-- ================= LEARNING JOURNEY (split) ================= --}}
    <section class="sec">
        <div class="wrap split">
            <div class="split__aside">
                <span class="eyebrow">Welcome to our Academics</span>
                <h2 class="h-sec">A Learning Journey for Every Stage</h2>
                <p>At {{ $school }}, our academic programs are thoughtfully designed to nurture curiosity,
                   build strong foundations, and challenge students to reach their full potential — every step of the way.</p>
                <a href="/pages/academics" class="btn btn-navy">Curriculum Overview <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="cards-3">
                @foreach ([
                    ['fa-child-reaching','Early Years','Ages 3–5','Play-based learning that sparks curiosity, creativity, and social growth in a nurturing environment.'],
                    ['fa-book-open','Primary School','Ages 6–11','A strong academic foundation built on inquiry, collaboration, and global perspectives.'],
                    ['fa-graduation-cap','Secondary School','Ages 12–18','Rigorous academics, leadership opportunities, and preparation for a bright future.'],
                ] as $p)
                    <article class="pcard">
                        <div class="pcard__media">
                            <div class="pcard__badge"><i class="fa-solid {{ $p[0] }}"></i></div>
                        </div>
                        <div class="pcard__body">
                            <h3>{{ $p[1] }}</h3>
                            <div class="pcard__age">{{ $p[2] }}</div>
                            <p>{{ $p[3] }}</p>
                            <a href="/pages/academics" class="btn-text">Learn More <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= CURRICULUM (subject grid) ================= --}}
    <section class="sec sec--paper2">
        <div class="wrap">
            <div class="center">
                <span class="eyebrow">Our Curriculum</span>
                <h2 class="h-sec">A Balanced, Future-Ready Curriculum</h2>
                <p class="sub">A dynamic curriculum that blends academic rigor with real-world relevance.</p>
            </div>
            <div class="subjects">
                @foreach ([
                    ['fa-comments','Languages','Develop strong communication skills in multiple languages to thrive in a global world.'],
                    ['fa-square-root-variable','Mathematics','Build problem-solving skills and numerical fluency through hands-on, conceptual learning.'],
                    ['fa-flask','Science','Foster curiosity and critical thinking through inquiry-based experiments and discovery.'],
                    ['fa-laptop-code','Technology','Equip students with digital literacy, coding, and innovation skills for the future.'],
                    ['fa-globe','Humanities','Explore cultures, history, and societies to build empathy and global awareness.'],
                    ['fa-palette','Creative Arts','Encourage imagination and self-expression through visual arts, music, and drama.'],
                    ['fa-person-running','Physical Education','Promote health, teamwork, and discipline through sport and active living.'],
                    ['fa-heart','Wellbeing','Support mental, emotional, and social wellbeing to help students thrive every day.'],
                ] as $s)
                    <div class="subject">
                        <div class="subject__ic"><i class="fa-solid {{ $s[0] }}"></i></div>
                        <h3>{{ $s[1] }}</h3>
                        <p>{{ $s[2] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= INNOVATION (split) ================= --}}
    <section class="sec">
        <div class="wrap split">
            <div class="split__aside">
                <span class="eyebrow">Teaching that Inspires</span>
                <h2 class="h-sec">Innovation in Every Classroom</h2>
                <p>Learning is active, engaging, and connected to the real world — where every lesson
                   builds confidence, curiosity, and a lifelong love of discovery.</p>
                <a href="/pages/about" class="btn btn-navy">Learn More About Teaching <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="cards-3">
                @foreach ([
                    ['fa-cubes','Hands-On Learning','Students explore, build, and create through practical, project-based work.'],
                    ['fa-magnifying-glass','Inquiry & Discovery','We encourage questions, research, and a genuine love of discovery.'],
                    ['fa-people-group','Collaborative Thinking','Teamwork and communication are at the heart of how we learn.'],
                ] as $c)
                    <article class="pcard">
                        <div class="pcard__media">
                            <div class="pcard__badge"><i class="fa-solid {{ $c[0] }}"></i></div>
                        </div>
                        <div class="pcard__body">
                            <h3>{{ $c[1] }}</h3>
                            <p style="margin-top:.5rem;">{{ $c[2] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= CAMPUS FACILITIES ================= --}}
    <section class="sec sec--paper2">
        <div class="wrap">
            <div class="center">
                <span class="eyebrow">State-of-the-Art Facilities</span>
                <h2 class="h-sec">Explore Our Campus</h2>
            </div>
            <div class="facilities">
                @foreach ([
                    ['fa-book-open-reader','Library','A space to read, research, and be inspired.'],
                    ['fa-microchip','Innovation Lab','Designed for creativity, coding, and exploration.'],
                    ['fa-futbol','Sports Facilities','Modern facilities for a wide range of sports.'],
                    ['fa-music','Performing Arts','Stage, music, and drama that bring talent to life.'],
                    ['fa-users','Student Commons','A vibrant hub to connect, relax, and collaborate.'],
                ] as $f)
                    <article class="fcard">
                        <div class="fcard__media"></div>
                        <div class="fcard__body">
                            <div class="fcard__ic"><i class="fa-solid {{ $f[0] }}"></i></div>
                            <h3>{{ $f[1] }}</h3>
                            <p>{{ $f[2] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= OUTCOMES + QUOTE ================= --}}
    <section class="sec">
        <div class="wrap outcomes">
            <div>
                <span class="eyebrow">Achieving Together</span>
                <h2 class="h-sec" style="font-size:clamp(1.7rem,2.6vw,2.2rem);">Outcomes That Open Doors</h2>
            </div>
            <div class="stats4">
                @foreach ([
                    ['fa-graduation-cap','98%','University Placement'],
                    ['fa-earth-americas','40+','Nationalities Represented'],
                    ['fa-trophy','25+','Years of Excellence'],
                    ['fa-user-group','18:1','Student–Teacher Ratio'],
                ] as $st)
                    <div class="stat">
                        <div class="stat__ic"><i class="fa-solid {{ $st[0] }}"></i></div>
                        <div class="stat__num">{{ $st[1] }}</div>
                        <div class="stat__lbl">{{ $st[2] }}</div>
                    </div>
                @endforeach
            </div>
            <div class="quote-card">
                <div class="qm">&ldquo;</div>
                <p>{{ $school }} challenges me to think bigger and do my best every day. The teachers truly care and help me grow.</p>
                <div class="quote-card__who">
                    <span class="av">A</span>
                    <div><b>Ananya S.</b><br><small>Grade 11 Student</small></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= FAQ ================= --}}
    <section class="sec sec--paper2">
        <div class="wrap">
            <div class="center">
                <span class="eyebrow">Frequently Asked Questions</span>
                <h2 class="h-sec">Everything You Need to Know</h2>
            </div>
            <div class="faq">
                @foreach ([
                    ['What curriculum does '.$school.' follow?','A balanced, inquiry-led curriculum that blends academic rigor with real-world skills, aligned to international standards.'],
                    ['How do you prepare students for university?','Through dedicated academic guidance, exam preparation, and university counselling from the secondary years onward.'],
                    ['How do you support students with different learning needs?','A learning-support team provides personalised plans so every student can access the curriculum and thrive.'],
                    ['What are the class sizes?','We keep an 18:1 student–teacher ratio so every child receives individual attention and mentorship.'],
                    ['What languages are offered?','A strong multilingual program, with additional languages introduced progressively across the year groups.'],
                    ['How do you ensure student wellbeing?','Dedicated counselling, pastoral care, and a safe, respectful campus keep wellbeing at the centre of school life.'],
                ] as $q)
                    <details>
                        <summary>{{ $q[0] }}</summary>
                        <p>{{ $q[1] }}</p>
                    </details>
                @endforeach
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
                        <h2>Discover What Your Child Can Become</h2>
                        <p>Join a community that inspires excellence and shapes futures.</p>
                    </div>
                    <div class="cta__btns">
                        <a href="/pages/admissions" class="btn btn-gold" style="justify-content:center;"><i class="fa-solid fa-paper-plane"></i> Apply Now</a>
                        <a href="/pages/contact" class="btn btn-ghost" style="justify-content:center;">Book a Tour</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-site.default.layout>
