{{-- Shared SVG sprite. Referenced as <use href="#i-name"> throughout the
     marketing views. Kept in one place so stroke width and corner radius stay
     consistent; do not inline one-off icons in a page view. --}}
<svg style="display:none" aria-hidden="true" focusable="false">
  <symbol id="i-logo" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
    <path d="M3 8.5 12 4l9 4.5-9 4.5-9-4.5Z"/><path d="M6.5 11v5.2c0 1.4 2.5 2.8 5.5 2.8s5.5-1.4 5.5-2.8V11"/>
  </symbol>
  <symbol id="i-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></symbol>
  <symbol id="i-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m4 12.5 5 5L20 6.5"/></symbol>
  <symbol id="i-shield" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7.5 3v5.6c0 4.6-3.1 8.5-7.5 9.9-4.4-1.4-7.5-5.3-7.5-9.9V6L12 3Z"/><path d="m9 12 2 2 4-4"/></symbol>
  <symbol id="i-db" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="6" rx="7.5" ry="3"/><path d="M4.5 6v12c0 1.7 3.4 3 7.5 3s7.5-1.3 7.5-3V6"/><path d="M4.5 12c0 1.7 3.4 3 7.5 3s7.5-1.3 7.5-3"/></symbol>
  <symbol id="i-users" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M2.8 20a6.2 6.2 0 0 1 12.4 0"/><path d="M16.5 5.4a3.2 3.2 0 0 1 0 5.2M18 14.4a6.2 6.2 0 0 1 3.2 5.6"/></symbol>
  <symbol id="i-cash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="5.5" width="19" height="13" rx="2.5"/><circle cx="12" cy="12" r="2.8"/><path d="M6 9v6M18 9v6"/></symbol>
  <symbol id="i-book" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4.5h6a3 3 0 0 1 3 3V20a2.4 2.4 0 0 0-2.4-2.4H4Z"/><path d="M20 4.5h-6a3 3 0 0 0-3 3V20a2.4 2.4 0 0 1 2.4-2.4H20Z"/></symbol>
  <symbol id="i-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="5" width="17" height="15.5" rx="2.4"/><path d="M3.5 10h17M8 3v4M16 3v4"/></symbol>
  <symbol id="i-bus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="4" width="17" height="12.5" rx="2.4"/><path d="M3.5 10.5h17M7.5 20v-3.5M16.5 20v-3.5"/><circle cx="7.8" cy="13.6" r=".9" fill="currentColor"/><circle cx="16.2" cy="13.6" r=".9" fill="currentColor"/></symbol>
  <symbol id="i-chat" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20.5 12.2c0 4.1-3.8 7.4-8.5 7.4a10 10 0 0 1-2.7-.4L4 21l1.3-3.7A7 7 0 0 1 3.5 12.2c0-4.1 3.8-7.4 8.5-7.4s8.5 3.3 8.5 7.4Z"/></symbol>
  <symbol id="i-phone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="2.5" width="12" height="19" rx="2.6"/><path d="M10.5 18.5h3"/></symbol>
  <symbol id="i-layers" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3 9 4.6-9 4.6-9-4.6L12 3Z"/><path d="m3 12.4 9 4.6 9-4.6M3 17l9 4.6 9-4.6"/></symbol>
  <symbol id="i-chart" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V4M4 20h16"/><path d="M8 16V11M12.5 16V7M17 16v-3"/></symbol>
  <symbol id="i-alert" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3.5 21.5 20H2.5L12 3.5Z"/><path d="M12 10v4.2M12 17.3h.01"/></symbol>
  <symbol id="i-globe" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.8"/><path d="M3.2 12h17.6M12 3.2c2.4 2.5 3.6 5.5 3.6 8.8s-1.2 6.3-3.6 8.8c-2.4-2.5-3.6-5.5-3.6-8.8S9.6 5.7 12 3.2Z"/></symbol>
  <symbol id="i-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="4.5" y="10.5" width="15" height="10.5" rx="2.4"/><path d="M8 10.5V7.6a4 4 0 0 1 8 0v2.9"/></symbol>
  <symbol id="i-clipboard" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="4.5" width="14" height="16.5" rx="2.4"/><path d="M9 4.5V3.4A1.4 1.4 0 0 1 10.4 2h3.2A1.4 1.4 0 0 1 15 3.4v1.1Z"/><path d="M9 11h6M9 15h4"/></symbol>
  <symbol id="i-box" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2.8 8.5 4.4v9.6L12 21.2 3.5 16.8V7.2L12 2.8Z"/><path d="M3.5 7.2 12 11.6l8.5-4.4M12 11.6v9.6"/></symbol>
</svg>

