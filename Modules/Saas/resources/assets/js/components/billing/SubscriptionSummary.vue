<script setup>
import { computed, ref } from 'vue';
import { ApiError, requestJson } from '../../services/http.js';

const props = defineProps({
  initialSubscription: { type: Object, default: null },
  endpoint: { type: String, required: true },
  labels: { type: Object, required: true },
  statusLabels: { type: Object, default: () => ({}) },
});

const subscription = ref(props.initialSubscription);
const pending = ref(false);
const announcement = ref('');
const error = ref('');

const statusLabel = computed(() => {
  const status = subscription.value?.status;
  return status ? (props.statusLabels[status] || status.replaceAll('_', ' ')) : '';
});

function formatDate(value) {
  if (!value) return '—';

  return new Intl.DateTimeFormat(document.documentElement.lang || 'en', {
    dateStyle: 'medium',
  }).format(new Date(value));
}

async function refresh() {
  pending.value = true;
  error.value = '';
  announcement.value = '';

  try {
    const payload = await requestJson(props.endpoint);
    subscription.value = payload.subscription;
    announcement.value = props.labels.updated;
  } catch (failure) {
    const status = failure instanceof ApiError ? failure.status : 0;
    error.value = {
      401: props.labels.expired,
      403: props.labels.denied,
      429: props.labels.rate_limited,
    }[status] || props.labels.failed;
  } finally {
    pending.value = false;
  }
}
</script>

<template>
  <div class="subscription-summary">
    <dl v-if="subscription">
      <dt>{{ labels.plan }}</dt>
      <dd>{{ subscription.plan?.name || '—' }}</dd>
      <dt>{{ labels.status }}</dt>
      <dd><span class="badge">{{ statusLabel }}</span></dd>
      <dt>{{ labels.period_end }}</dt>
      <dd>{{ formatDate(subscription.current_period_end) }}</dd>
    </dl>
    <p v-else>
      {{ labels.empty }}
    </p>

    <button
      type="button"
      class="btn btn-secondary"
      :disabled="pending"
      @click="refresh"
    >
      {{ pending ? labels.refreshing : labels.refresh }}
    </button>

    <p
      v-if="announcement"
      class="form-status is-success"
      role="status"
    >
      {{ announcement }}
    </p>
    <p
      v-if="error"
      class="form-status is-error"
      role="alert"
    >
      {{ error }}
    </p>
  </div>
</template>
