<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { ApiError, requestJson } from '../../services/http.js';

const props = defineProps({
  initialRun: { type: Object, default: null },
  endpoint: { type: String, required: true },
  labels: { type: Object, required: true },
  pollInterval: { type: Number, default: 5000 },
});

const run = ref(props.initialRun);
const pending = ref(false);
const error = ref('');
let timer;

const isRunning = computed(() => {
  if (!run.value) return false;
  return !['ready', 'failed_manual_review'].includes(run.value.state);
});

const progress = computed(() => Math.max(0, Math.min(100, Number(run.value?.progress || 0))));

function schedule() {
  clearTimeout(timer);
  if (isRunning.value) timer = setTimeout(refresh, props.pollInterval);
}

async function refresh() {
  pending.value = true;
  error.value = '';

  try {
    const payload = await requestJson(props.endpoint);
    run.value = payload.provisioning;
  } catch (failure) {
    const status = failure instanceof ApiError ? failure.status : 0;
    error.value = {
      401: props.labels.expired,
      403: props.labels.denied,
      429: props.labels.rate_limited,
    }[status] || props.labels.failed;
  } finally {
    pending.value = false;
    schedule();
  }
}

onMounted(schedule);
onBeforeUnmount(() => clearTimeout(timer));
</script>

<template>
  <div
    class="provisioning-progress"
    aria-live="polite"
  >
    <template v-if="run">
      <div class="provisioning-progress__header">
        <strong>{{ labels.title }}</strong>
        <span>{{ progress }}%</span>
      </div>
      <div
        class="provisioning-progress__track"
        role="progressbar"
        :aria-label="labels.title"
        :aria-valuenow="progress"
        aria-valuemin="0"
        aria-valuemax="100"
      >
        <span :style="{ width: `${progress}%` }" />
      </div>
      <p>{{ labels.state }}: {{ run.state }} · {{ labels.step }}: {{ run.step || labels.waiting }}</p>
      <p
        v-if="run.error_summary"
        class="form-status is-error"
        role="alert"
      >
        {{ run.error_summary }}
      </p>
    </template>
    <p v-else>
      {{ labels.empty }}
    </p>

    <button
      type="button"
      class="btn"
      :disabled="pending"
      @click="refresh"
    >
      {{ pending ? labels.refreshing : labels.refresh }}
    </button>
    <p
      v-if="error"
      class="form-status is-error"
      role="alert"
    >
      {{ error }}
    </p>
  </div>
</template>
