<script setup>
import { computed, nextTick, ref } from 'vue';

/**
 * Upgrades the server-rendered role tabs. Everything shown here is passed in
 * from Blade — the component never fetches permission data, and never decides
 * what a role may do. It only presents what the server already published.
 */
const props = defineProps({
  roles: { type: Array, required: true },
  dir: { type: String, default: 'ltr' },
  labels: { type: Object, default: () => ({ permissions: 'permissions' }) },
});

const active = ref(0);
const tabRefs = ref([]);

const current = computed(() => props.roles[active.value] ?? null);
const isRtl = computed(() => props.dir === 'rtl');

async function select(index) {
  active.value = (index + props.roles.length) % props.roles.length;
  await nextTick();
  tabRefs.value[active.value]?.focus();
}

function onKeydown(event) {
  const forward = isRtl.value ? 'ArrowLeft' : 'ArrowRight';
  const backward = isRtl.value ? 'ArrowRight' : 'ArrowLeft';

  const moves = {
    [forward]: () => select(active.value + 1),
    ArrowDown: () => select(active.value + 1),
    [backward]: () => select(active.value - 1),
    ArrowUp: () => select(active.value - 1),
    Home: () => select(0),
    End: () => select(props.roles.length - 1),
  };

  const move = moves[event.key];
  if (!move) return;

  event.preventDefault();
  move();
}
</script>

<template>
  <div class="roles">
    <div
      class="roles__tabs"
      role="tablist"
      :aria-label="labels.tablist"
      @keydown="onKeydown"
    >
      <button
        v-for="(role, i) in roles"
        :id="`role-tab-${role.key}`"
        :key="role.key"
        ref="tabRefs"
        class="roles__tab"
        type="button"
        role="tab"
        :aria-selected="i === active"
        :aria-controls="`role-panel-${role.key}`"
        :tabindex="i === active ? 0 : -1"
        @click="active = i"
      >
        {{ role.name }}
        <span class="n">{{ role.permissions.toLocaleString() }}</span>
      </button>
    </div>

    <div
      v-if="current"
      :id="`role-panel-${current.key}`"
      class="roles__panel"
      role="tabpanel"
      :aria-labelledby="`role-tab-${current.key}`"
      tabindex="0"
    >
      <h3>{{ current.name }} &mdash; {{ current.permissions.toLocaleString() }} {{ labels.permissions }}</h3>
      <p>{{ current.summary }}</p>
      <ul class="tags">
        <li
          v-for="permission in current.sample"
          :key="permission"
          class="tag"
        >
          {{ permission }}
        </li>
      </ul>
    </div>
  </div>
</template>
