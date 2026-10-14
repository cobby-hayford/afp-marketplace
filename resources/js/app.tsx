import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const key = import.meta.env.VITE_REVERB_APP_KEY as string | undefined;
if (key) {
  window.Pusher = Pusher;
  window.Echo = new Echo({
    broadcaster: 'reverb',
    key,
    wsHost: (import.meta.env.VITE_REVERB_HOST as string) || window.location.hostname,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT || 80),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT || 443),
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME as string) === 'https',
    enabledTransports: ['ws', 'wss'],
  });
}

createInertiaApp({
  title: (title) => title ? `${title} — African Film Producers` : 'African Film Producers',
  resolve: (name) => {
    const pages = import.meta.glob('./pages/**/*.tsx', { eager: true }) as Record<string, { default: React.ComponentType }>;
    return pages[`./pages/${name}.tsx`];
  },
  setup({ el, App, props }) { createRoot(el).render(<App {...props} />); },
  progress: { color: '#c8a85d' },
});
