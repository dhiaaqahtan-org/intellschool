<script setup>
import { ref } from 'vue';

const props = defineProps({
  action: { type: String, required: true },
  initialSearch: { type: String, default: '' },
  initialStatus: { type: String, default: '' },
  statuses: { type: Array, required: true },
  labels: { type: Object, required: true },
});

const search = ref(props.initialSearch);
const status = ref(props.initialStatus);
const pending = ref(false);

function submit(event) {
  if (!event.currentTarget.checkValidity()) return;
  pending.value = true;
}
</script>

<template>
  <form
    method="GET"
    :action="action"
    class="filter-bar"
    @submit="submit"
  >
    <label
      class="field filter-grow"
      for="tenant-search-vue"
    >
      {{ labels.search }}
      <input
        id="tenant-search-vue"
        v-model.trim="search"
        type="search"
        name="search"
        maxlength="100"
        :placeholder="labels.placeholder"
      >
    </label>
    <label
      class="field"
      for="tenant-status-vue"
    >
      {{ labels.status }}
      <select
        id="tenant-status-vue"
        v-model="status"
        name="status"
      >
        <option value="">{{ labels.all }}</option>
        <option
          v-for="option in statuses"
          :key="option.value"
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>
    </label>
    <button
      type="submit"
      class="btn btn-primary"
      :disabled="pending"
    >
      {{ pending ? labels.filtering : labels.filter }}
    </button>
    <a
      v-if="search || status"
      class="btn"
      :href="action"
    >{{ labels.clear }}</a>
  </form>
</template>
