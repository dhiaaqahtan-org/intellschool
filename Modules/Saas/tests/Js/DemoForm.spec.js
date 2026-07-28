import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, describe, expect, it, vi } from 'vitest';
import DemoForm from '../../resources/assets/js/marketing/components/DemoForm.vue';

const t = {
  name: 'Name', school: 'School', email: 'Email', email_hint: 'Meeting address',
  size: 'Size', size_placeholder: 'Choose', message: 'Message', consent: 'I agree',
  privacy_link: 'Privacy', submit: 'Request demo', submitting: 'Sending', success: 'Received',
  error_validation: 'Correct the fields', error_expired: 'Expired', error_throttled: 'Slow down',
  error_server: 'Server error', error_network: 'Network error',
};

function render() {
  return mount(DemoForm, {
    attachTo: document.body,
    props: {
      action: '/demo',
      csrf: 'csrf',
      sizes: [{ value: 'up_to_300', label: 'Up to 300' }],
      t,
      privacyUrl: '/privacy',
    },
  });
}

afterEach(() => {
  vi.unstubAllGlobals();
  document.body.innerHTML = '';
});

describe('DemoForm', () => {
  it('guards submission and only shows success after the server accepts it', async () => {
    const pending = Promise.resolve({ ok: true, status: 200, json: async () => ({ message: 'Booked' }) });
    const fetchMock = vi.fn(() => pending);
    vi.stubGlobal('fetch', fetchMock);
    const wrapper = render();

    await wrapper.find('#demo-name').setValue('Aisha');
    await wrapper.find('#demo-school').setValue('Al Noor');
    await wrapper.find('#demo-email').setValue('aisha@example.test');
    await wrapper.find('input[name="consent"]').setValue(true);
    await wrapper.find('form').trigger('submit');

    expect(wrapper.find('button[type="submit"]').attributes()).toHaveProperty('disabled');
    await flushPromises();

    expect(fetchMock).toHaveBeenCalledTimes(1);
    expect(wrapper.text()).toContain('Booked');
    expect(wrapper.find('#demo-name').element.value).toBe('');

    wrapper.unmount();
  });

  it('places server validation beside the field and focuses the first error', async () => {
    vi.stubGlobal('fetch', vi.fn(async () => ({
      ok: false,
      status: 422,
      json: async () => ({ errors: { email: ['Use a work email.'] } }),
    })));
    const wrapper = render();

    await wrapper.find('form').trigger('submit');
    await flushPromises();

    const email = wrapper.find('#demo-email');
    expect(email.attributes('aria-invalid')).toBe('true');
    expect(wrapper.text()).toContain('Use a work email.');
    expect(document.activeElement).toBe(email.element);

    wrapper.unmount();
  });
});
