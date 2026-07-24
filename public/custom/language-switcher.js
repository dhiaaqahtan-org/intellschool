/* Admin panel language switcher (English / Arabic).
   Injected via the app's custom-overlay mechanism (no Vue source needed).
   Clicking navigates to /admin-locale/{locale}, which sets a cookie honored by
   SetSystemConfig, then reloads the SPA in the chosen language + direction. */
(function () {
  function currentLocale() {
    var lang = (document.documentElement.getAttribute('lang') || '').toLowerCase();
    if (lang.indexOf('ar') === 0) return 'ar';
    return document.documentElement.getAttribute('dir') === 'rtl' ? 'ar' : 'en';
  }

  function inject() {
    if (document.getElementById('sch-lang-switch') || !document.body) return;
    var cur = currentLocale();
    var target = cur === 'ar' ? 'en' : 'ar';
    var a = document.createElement('a');
    a.id = 'sch-lang-switch';
    a.href = '/admin-locale/' + target;
    a.setAttribute('title', cur === 'ar' ? 'Switch to English' : 'التبديل إلى العربية');
    a.innerHTML =
      '<i class="fa-solid fa-globe" aria-hidden="true"></i>' +
      '<span>' + (cur === 'ar' ? 'English' : 'العربية') + '</span>';
    document.body.appendChild(a);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inject);
  } else {
    inject();
  }

  // The admin is a single-page app; re-inject if it replaces the DOM on navigation.
  try {
    new MutationObserver(function () {
      if (!document.getElementById('sch-lang-switch')) inject();
    }).observe(document.documentElement, { childList: true, subtree: true });
  } catch (e) {}
})();
