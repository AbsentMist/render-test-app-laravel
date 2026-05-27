import { setActivePinia, createPinia } from 'pinia';
import { vi } from 'vitest';
import { URL as NodeURL } from 'url';

// Polyfill URL in Node environment (axios XHR helper needs it)
if (typeof globalThis.URL === 'undefined') {
  globalThis.URL = NodeURL;
}

// Activate a global Pinia instance for tests
setActivePinia(createPinia());

// Stub FlowbiteInstances used by some components
globalThis.FlowbiteInstances = {
  get: () => ({ show: () => {}, hide: () => {}, toggle: () => {} }),
};

// Provide a unified mock for vue-router used in many tests/components
vi.mock('vue-router', async () => {
  const actual = await vi.importActual('vue-router');
  return {
    ...actual,
    useRouter: () => ({ push: vi.fn(), replace: vi.fn(), back: vi.fn() }),
    useRoute: () => ({ params: {}, query: {}, name: undefined }),
  };
});
// Provide a lightweight global mock for axios to avoid XHR adapter usage in tests
vi.mock('axios', () => {
  const m = {
    get: vi.fn(() => Promise.resolve({ data: {} })),
    post: vi.fn(() => Promise.resolve({ data: {} })),
    put: vi.fn(() => Promise.resolve({ data: {} })),
    delete: vi.fn(() => Promise.resolve({ data: {} })),
    request: vi.fn(() => Promise.resolve({ data: {} })),
  };
  return {
    default: {
      ...m,
      create: () => ({
        ...m,
        interceptors: {
          request: { use: () => {} },
          response: { use: () => {} },
        },
      }),
    },
    ...m,
  };
});

// Minimal matchMedia stub
if (typeof window !== 'undefined' && !window.matchMedia) {
  window.matchMedia = () => ({ matches: false, addListener: () => {}, removeListener: () => {} });
}
