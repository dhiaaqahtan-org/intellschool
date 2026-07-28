import { mountComponents } from './mount.js';

export function bootSaasComponents(root = document) {
  mountComponents(root);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => bootSaasComponents(), { once: true });
} else {
  bootSaasComponents();
}
