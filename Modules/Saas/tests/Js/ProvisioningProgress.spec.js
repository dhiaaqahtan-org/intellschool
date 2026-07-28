import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, describe, expect, it, vi } from 'vitest';
import ProvisioningProgress from '../../resources/assets/js/components/onboarding/ProvisioningProgress.vue';

const labels = {
  title: 'Provisioning',
  state: 'State',
  step: 'Step',
  waiting: 'Waiting',
  empty: 'No run',
  refresh: 'Refresh',
  refreshing: 'Refreshing',
  expired: 'Expired',
  denied: 'Denied',
  rate_limited: 'Rate limited',
  failed: 'Failed',
};

afterEach(() => {
  vi.useRealTimers();
  vi.unstubAllGlobals();
});

describe('ProvisioningProgress', () => {
  it('exposes accessible progress and accepts a refreshed terminal state', async () => {
    vi.useFakeTimers();
    vi.stubGlobal('fetch', vi.fn(async () => ({
      ok: true,
      status: 200,
      json: async () => ({
        provisioning: { state: 'ready', step: 'verify', progress: 100 },
      }),
    })));

    const wrapper = mount(ProvisioningProgress, {
      props: {
        endpoint: '/api/saas/platform/tenants/id/provisioning',
        labels,
        initialRun: { state: 'migrating', step: 'migrate', progress: 40 },
        pollInterval: 60000,
      },
    });

    expect(wrapper.get('[role="progressbar"]').attributes('aria-valuenow')).toBe('40');

    await wrapper.get('button').trigger('click');
    await flushPromises();

    expect(wrapper.get('[role="progressbar"]').attributes('aria-valuenow')).toBe('100');
    expect(wrapper.text()).toContain('ready');
    wrapper.unmount();
  });
});
