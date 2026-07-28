import { createApp } from 'vue';

const registry = {
  'provisioning-progress': () => import('./components/onboarding/ProvisioningProgress.vue'),
  'subscription-summary': () => import('./components/billing/SubscriptionSummary.vue'),
  'tenant-filter': () => import('./components/platform/TenantFilter.vue'),
};

function readProps(element) {
  const node = element.querySelector(':scope > script[type="application/json"][data-props]');

  if (!node) return {};

  try {
    return JSON.parse(node.textContent || '{}');
  } catch {
    return {};
  }
}

export function mountComponents(root = document) {
  root.querySelectorAll('[data-vue-component]').forEach(async (element) => {
    if (element.dataset.vueMounted === 'true') return;

    const name = element.dataset.vueComponent;
    const load = registry[name];

    if (!load) return;

    try {
      const component = await load();
      const app = createApp(component.default, readProps(element));

      app.config.errorHandler = (error) => {
        console.error(`[saas] component "${name}" failed`, error);
      };

      element.innerHTML = '';
      app.mount(element);
      element.dataset.vueMounted = 'true';
    } catch (error) {
      // Preserve the server-rendered fallback when an enhancement cannot load.
      console.error(`[saas] could not mount component "${name}"`, error);
    }
  });
}
