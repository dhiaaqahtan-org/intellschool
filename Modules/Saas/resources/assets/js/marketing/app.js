/**
 * Marketing entry point.
 *
 * Order matters: the framework-free enhancement layer runs first so scroll
 * reveal, the 3D stage and the tab fallback are live even if the Vue chunk
 * fails to load. Vue then upgrades individual islands in place.
 */
import './enhance.js';
import { mountIslands } from './mount.js';

mountIslands();
