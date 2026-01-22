import axios from 'axios';
import { MobileBridge } from './mobile-bridge';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Mobile App Token Injection
window.axios.interceptors.request.use(async (config) => {
    if (MobileBridge.isMobileApp()) {
        try {
            // We use the bridge to get the token
            const result = await MobileBridge.auth.getToken();
            if (result && result.success && result.data) {
                config.headers.Authorization = `Bearer ${result.data}`;
            }
        } catch (error) {
            console.error('Error injecting mobile token:', error);
        }
    }
    return config;
});
