<script setup>
import { nextTick, ref } from 'vue';

/**
 * Demo request island.
 *
 * The server is the only authority on validity. This component adds pending
 * state, a duplicate-submit guard, inline error placement and focus management
 * on failure — it never decides that a submission succeeded on its own.
 */
const props = defineProps({
  action: { type: String, required: true },
  csrf: { type: String, required: true },
  sizes: { type: Array, default: () => [] },
  t: { type: Object, required: true },
  privacyUrl: { type: String, default: '#' },
});

const form = ref({ name: '', school: '', email: '', size: '', message: '', consent: false, website: '' });
const errors = ref({});
const status = ref('idle'); // idle | pending | success | error
const banner = ref('');
const formEl = ref(null);

let controller = null;

function fieldId(name) {
  return `demo-${name}`;
}

async function focusFirstError() {
  await nextTick();
  const first = Object.keys(errors.value)[0];
  if (!first) return;
  formEl.value?.querySelector(`#${fieldId(first)}`)?.focus();
}

async function submit() {
  if (status.value === 'pending') return; // duplicate-submit guard

  controller?.abort();
  controller = new AbortController();

  status.value = 'pending';
  errors.value = {};
  banner.value = '';

  try {
    const response = await fetch(props.action, {
      method: 'POST',
      signal: controller.signal,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': props.csrf,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(form.value),
    });

    if (response.status === 422) {
      const body = await response.json();
      errors.value = Object.fromEntries(
        Object.entries(body.errors ?? {}).map(([key, messages]) => [key, messages[0]])
      );
      status.value = 'error';
      banner.value = props.t.error_validation;
      await focusFirstError();
      return;
    }

    if (response.status === 419) {
      // Session expired — a reload is the only honest recovery.
      status.value = 'error';
      banner.value = props.t.error_expired;
      return;
    }

    if (response.status === 429) {
      status.value = 'error';
      banner.value = props.t.error_throttled;
      return;
    }

    if (!response.ok) {
      status.value = 'error';
      banner.value = props.t.error_server;
      return;
    }

    const body = await response.json().catch(() => ({}));
    status.value = 'success';
    banner.value = body.message || props.t.success;
    form.value = { name: '', school: '', email: '', size: '', message: '', consent: false, website: '' };
  } catch (err) {
    if (err.name === 'AbortError') return;
    status.value = 'error';
    banner.value = props.t.error_network;
  }
}
</script>

<template>
  <form ref="formEl" class="form card" :action="action" method="post" novalidate @submit.prevent="submit">
    <p v-if="banner" class="alert" :class="status === 'success' ? 'alert--ok' : 'alert--err'" role="alert" aria-live="assertive">
      {{ banner }}
    </p>

    <div class="form__row">
      <div class="field">
        <label :for="fieldId('name')">{{ t.name }} <span class="req" aria-hidden="true">*</span></label>
        <input
          :id="fieldId('name')"
          v-model="form.name"
          type="text"
          name="name"
          autocomplete="name"
          required
          :aria-invalid="!!errors.name"
          :aria-describedby="errors.name ? fieldId('name') + '-err' : undefined"
        >
        <span v-if="errors.name" :id="fieldId('name') + '-err'" class="err">{{ errors.name }}</span>
      </div>

      <div class="field">
        <label :for="fieldId('school')">{{ t.school }} <span class="req" aria-hidden="true">*</span></label>
        <input
          :id="fieldId('school')"
          v-model="form.school"
          type="text"
          name="school"
          autocomplete="organization"
          required
          :aria-invalid="!!errors.school"
          :aria-describedby="errors.school ? fieldId('school') + '-err' : undefined"
        >
        <span v-if="errors.school" :id="fieldId('school') + '-err'" class="err">{{ errors.school }}</span>
      </div>
    </div>

    <div class="form__row">
      <div class="field">
        <label :for="fieldId('email')">{{ t.email }} <span class="req" aria-hidden="true">*</span></label>
        <input
          :id="fieldId('email')"
          v-model="form.email"
          type="email"
          name="email"
          inputmode="email"
          autocomplete="email"
          required
          :aria-invalid="!!errors.email"
          :aria-describedby="`${fieldId('email')}-hint${errors.email ? ' ' + fieldId('email') + '-err' : ''}`"
        >
        <span :id="fieldId('email') + '-hint'" class="hint">{{ t.email_hint }}</span>
        <span v-if="errors.email" :id="fieldId('email') + '-err'" class="err">{{ errors.email }}</span>
      </div>

      <div class="field">
        <label :for="fieldId('size')">{{ t.size }}</label>
        <select :id="fieldId('size')" v-model="form.size" name="size">
          <option value="">{{ t.size_placeholder }}</option>
          <option v-for="option in sizes" :key="option" :value="option">{{ option }}</option>
        </select>
      </div>
    </div>

    <div class="field">
      <label :for="fieldId('message')">{{ t.message }}</label>
      <textarea :id="fieldId('message')" v-model="form.message" name="message" rows="4" />
    </div>

    <!-- Honeypot: hidden from users and assistive tech, attractive to bots. -->
    <div class="visually-hidden" aria-hidden="true">
      <label for="demo-website">Website</label>
      <input id="demo-website" v-model="form.website" type="text" name="website" tabindex="-1" autocomplete="off">
    </div>

    <label class="consent">
      <input v-model="form.consent" type="checkbox" name="consent" required :aria-invalid="!!errors.consent">
      <span>
        {{ t.consent }}
        <a :href="privacyUrl">{{ t.privacy_link }}</a>
      </span>
    </label>
    <span v-if="errors.consent" class="err">{{ errors.consent }}</span>

    <button class="btn btn--accent btn--lg btn--block" type="submit" :disabled="status === 'pending'">
      <span v-if="status === 'pending'">{{ t.submitting }}</span>
      <span v-else>{{ t.submit }}</span>
    </button>
  </form>
</template>
