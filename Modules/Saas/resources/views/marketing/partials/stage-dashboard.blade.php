{{--
    Hero product stage.

    These are live HTML recreations of the application UI rather than exported
    screenshots: they stay crisp at any zoom, reflow on small screens, animate
    without video, weigh a few kilobytes, and never leak real student data.

    The figures are illustrative sample data for a fictional school. Do not
    substitute real tenant numbers here.
--}}
<div class="stage" role="img"
     aria-label="{{ __('saas::marketing.hero.preview_label') }}">
    <div class="stage__scene">

        <div class="chip3d chip3d--a" aria-hidden="true">
            <span class="chip3d__ico" style="background:rgba(5,150,105,.18);color:#6ee7b7">
                <svg><use href="#i-check"></use></svg>
            </span>
            <span><strong>Fee receipt #4821</strong><span>Posted to ledger · 09:14</span></span>
        </div>

        <div class="chip3d chip3d--b" aria-hidden="true">
            <span class="chip3d__ico" style="background:rgba(234,88,12,.2);color:#fdba74">
                <svg><use href="#i-alert"></use></svg>
            </span>
            <span><strong>12 absentees</strong><span>Guardians notified by SMS</span></span>
        </div>

        <div class="panel panel--main" aria-hidden="true">
            <div class="app">
                <div class="app__bar">
                    <span class="app__dots"><i></i><i></i><i></i></span>
                    <span class="app__url">alnoor.{{ $tenantHost }}</span>
                </div>
                <div class="app__body">
                    <aside class="app__side">
                        <h6>Main campus</h6>
                        <ul class="app__nav">
                            <li class="is-active"><i></i> Dashboard</li>
                            <li><i></i> Students</li>
                            <li><i></i> Academics</li>
                            <li><i></i> Finance</li>
                            <li><i></i> Employees</li>
                            <li><i></i> Examinations</li>
                            <li><i></i> Transport</li>
                            <li><i></i> Library</li>
                        </ul>
                    </aside>

                    <div class="app__main" data-inview>
                        <div class="app__head">
                            <h5>Overview</h5>
                            <span>Term 2 · 2025&ndash;26</span>
                        </div>

                        <div class="kpis">
                            <div class="kpi"><b>1,284</b><small>Students</small></div>
                            <div class="kpi"><b>96.2%</b><small>Attendance</small> <em>+1.4</em></div>
                            <div class="kpi"><b>142</b><small>Staff</small></div>
                            <div class="kpi"><b>87%</b><small>Fees collected</small></div>
                        </div>

                        <div class="card-lite">
                            <header><h6>Fee collection by month</h6><span>Current session</span></header>
                            <div class="chart">
                                @foreach ([38, 52, 46, 71, 64, 92, 58, 74] as $height)
                                    <span class="bar {{ $height === 92 ? 'is-peak' : '' }}" style="--h:{{ $height }}%"></span>
                                @endforeach
                            </div>
                            <div class="chart-x">
                                @foreach (['Sep','Oct','Nov','Dec','Jan','Feb','Mar','Apr'] as $month)
                                    <span>{{ $month }}</span>
                                @endforeach
                            </div>
                        </div>

                        <div class="card-lite">
                            <header><h6>Attendance · Grade 9-B</h6><span>Last 14 sessions</span></header>
                            <div class="heat">
                                @foreach (['3','3','2','3','3','x','3','2','3','3','1','3','3','2'] as $value)
                                    <i data-v="{{ $value }}"></i>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel--float-a" aria-hidden="true">
            <div class="app">
                <div class="app__main" style="min-block-size:auto" data-inview>
                    <div class="app__head"><h5>Fee ledger</h5><span>Grade 9-B</span></div>
                    <table class="tbl">
                        <thead><tr><th>Student</th><th>Head</th><th>Status</th></tr></thead>
                        <tbody>
                            <tr><td>Layla H.</td><td>Tuition · Q3</td><td><span class="pill pill--ok">Paid</span></td></tr>
                            <tr><td>Omar S.</td><td>Transport</td><td><span class="pill pill--part">Partial</span></td></tr>
                            <tr><td>Yusuf A.</td><td>Tuition · Q3</td><td><span class="pill pill--due">Due</span></td></tr>
                            <tr><td>Maryam K.</td><td>Lab fee</td><td><span class="pill pill--ok">Paid</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="panel panel--float-b" aria-hidden="true">
            <div class="app">
                <div class="app__main" style="min-block-size:auto">
                    <div class="app__head"><h5>Timetable</h5><span>9-B</span></div>
                    <div class="tt">
                        <b></b><b>M</b><b>T</b><b>W</b><b>T</b><b>F</b>
                        <b>1</b><span class="a">Math</span><span>Ar</span><span class="c">Sci</span><span>Eng</span><span class="a">Math</span>
                        <b>2</b><span>Eng</span><span class="b">PE</span><span class="a">Math</span><span class="c">Sci</span><span>Ar</span>
                        <b>3</b><span class="c">Sci</span><span class="a">Math</span><span>Ar</span><span class="b">Art</span><span>Eng</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
