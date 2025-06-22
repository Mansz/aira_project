import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
  interface Window {
    Pusher: typeof Pusher;
    Echo: any;
  }
}

window.Pusher = Pusher;

// Get environment variables with fallbacks
const pusherKey = import.meta.env?.VITE_PUSHER_APP_KEY || (typeof process !== 'undefined' && process.env?.REACT_APP_PUSHER_APP_KEY) || '';
const pusherCluster = import.meta.env?.VITE_PUSHER_APP_CLUSTER || (typeof process !== 'undefined' && process.env?.REACT_APP_PUSHER_APP_CLUSTER) || 'mt1';

// Only initialize Echo if we have the required configuration
if (pusherKey) {
  window.Echo = new Echo({
    broadcaster: 'pusher',
    key: pusherKey,
    cluster: pusherCluster,
    forceTLS: true
  });
} else {
  console.warn('Pusher configuration is missing. WebSocket features will not work.');
  // Create a mock Echo instance to prevent errors
  window.Echo = {
    channel: () => ({
      listen: () => {},
      stopListening: () => {}
    }),
    join: () => ({
      listen: () => {},
      stopListening: () => {}
    }),
    private: () => ({
      listen: () => {},
      stopListening: () => {}
    }),
    leave: () => {},
    disconnect: () => {}
  };
}

export default window.Echo;
