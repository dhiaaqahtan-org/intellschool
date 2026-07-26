import { createApp } from 'vue';

/**
 * Island mount registry.
 *
 * Rules enforced here (implementation plan §10.2):
 *  - No page-wide Vue root. Static marketing content stays server-rendered.
 *  - Components are lazily imported, so a page that has no islands ships no
 *    component code.
 *  - A component that throws must not blank the page — the Blade-rendered
 *    markup stays in the DOM and keeps working.
 *  - Props come from a JSON script tag, never from interpolated attributes,
 *    so quoting and escaping are the browser's problem, not ours.
 */
const registry = {
  'role-explorer': () => import('./components/RoleExplorer.vue'),
  'demo-form': () => import('./components/DemoForm.vue'),
};

function readProps(el) {
  const node = el.querySelector(':scope > script[type="application/json"][data-props]');
  if (!node) return {};

  try {
    return JSON.parse(node.textContent || '{}');
  } catch {
    return {};
  }
}

export function mountIslands(root = document) {
  root.querySelectorAll('[data-vue-component]').forEach(async (el) => {
    const name = el.dataset.vueComponent;
    const loader = registry[name];

    if (!loader) return;
    if (el.dataset.vueMounted === 'true') return;

    try {
      const module = await loader();
      const props = readProps(el);

      // Keep the server-rendered markup until the component is ready, then
      // swap it out in one step so there is no flash of empty container.
      const app = createApp(module.default, props);

      app.config.errorHandler = (err) => {
        // eslint-disable-next-line no-console
        console.error(`[saas] island "${name}" failed`, err);
      };

      el.innerHTML = '';
      app.mount(el);
      el.dataset.vueMounted = 'true';
    } catch (err) {
      // Leave the Blade fallback in place. The page remains fully usable.
      // eslint-disable-next-line no-console
      console.error(`[saas] could not mount island "${name}"`, err);
    }
  });
}
