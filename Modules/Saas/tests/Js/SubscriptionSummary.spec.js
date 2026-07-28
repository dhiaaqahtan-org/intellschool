import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, describe, expect, it, vi } from 'vitest';
import SubscriptionSummary from '../../resources/assets/js/components/billing/SubscriptionSummary.vue';

const labels = {
  plan: 'Plan',
  status: 'Status',
  period_end: 'Period end',
  empty: 'None',
  refresh: 'Refresh',
  refreshing: 'Refreshing',
  updated: 'Updated',
  expired: 'Expired',
  denied: 'Denied',
  rate_limited: 'Rate limited',
  failed: 'Failed',
};

function render() {
  return mount(SubscriptionSummary, {
    props: {
      endpoint: '/api/saas/tenant/subscription',
      labels,
      statusLabels: { active: 'Active' },
      initialSubscription: {
        status: 'active',
        plan: { name: 'Growth' },
        current_period_end: '2026-08-01T00:00:00Z',
      },
    },
  });
}

afterEach(() => vi.unstubAllGlobals());

describe('SubscriptionSummary', () => {
  it('renders the Blade-provided state and refreshes from the server', async () => {
    vi.stubGlobal('fetch', vi.fn(async () => ({
      ok: true,
      status: 200,
      json: async () => ({
        subscription: {
          status: 'active',
          plan: { name: 'Enterprise' },
          current_period_end: '2026-09-01T00:00:00Z',
        },
      }),
    })));

    const wrapper = render();
    expect(wrapper.text()).toContain('Growth');

    await wrapper.get('button').trigger('click');
    await flushPromises();

    expect(wrapper.text()).toContain('Enterprise');
    expect(wrapper.text()).toContain('Updated');
  });

  it('announces authentication expiry without destroying current details', async () => {
    vi.stubGlobal('fetch', vi.fn(async () => ({
      ok: false,
      status: 401,
      json: async () => ({ message: 'Unauthenticated.' }),
    })));

    const wrapper = render();
    await wrapper.get('button').trigger('click');
    await flushPromises();

    expect(wrapper.text()).toContain('Expired');
    expect(wrapper.text()).toContain('Growth');
    expect(wrapper.get('[role="alert"]').exists()).toBe(true);
  });
});
