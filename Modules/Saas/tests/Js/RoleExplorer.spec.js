import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import RoleExplorer from '../../resources/assets/js/marketing/components/RoleExplorer.vue';

const roles = [
  { key: 'owner', name: 'Owner', permissions: 12, summary: 'Full school oversight.', sample: ['billing.view'] },
  { key: 'teacher', name: 'Teacher', permissions: 4, summary: 'Classroom work.', sample: ['attendance.create'] },
  { key: 'guardian', name: 'Guardian', permissions: 2, summary: 'Family access.', sample: ['grades.view'] },
];

describe('RoleExplorer', () => {
  it('keeps tabs and panels connected and updates the selected role', async () => {
    const wrapper = mount(RoleExplorer, { props: { roles, labels: { tablist: 'Roles', permissions: 'permissions' } } });
    const tabs = wrapper.findAll('[role="tab"]');

    expect(tabs[0].attributes('aria-selected')).toBe('true');
    expect(wrapper.find('[role="tabpanel"]').attributes('aria-labelledby')).toBe('role-tab-owner');

    await tabs[1].trigger('click');

    expect(wrapper.find('[role="tabpanel"]').text()).toContain('Teacher');
    expect(wrapper.find('#role-tab-teacher').attributes('aria-selected')).toBe('true');
  });

  it('uses RTL-aware arrow navigation with a roving tabindex', async () => {
    const wrapper = mount(RoleExplorer, { attachTo: document.body, props: { roles, dir: 'rtl' } });
    const list = wrapper.find('[role="tablist"]');

    await list.trigger('keydown', { key: 'ArrowLeft' });

    expect(wrapper.find('#role-tab-teacher').attributes('tabindex')).toBe('0');
    expect(document.activeElement?.id).toBe('role-tab-teacher');

    wrapper.unmount();
  });
});
