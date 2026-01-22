/**
 * LioSync Mobile Bridge Helper
 * Handles communication between Web App and Native Mobile App
 */

export const MobileBridge = {
    isMobileApp: () => typeof window.LioSyncMobile !== 'undefined',

    // Authentication
    auth: {
        getToken: () => {
            return new Promise((resolve) => {
                if (!MobileBridge.isMobileApp()) return resolve(null);
                try {
                    window.LioSyncMobile.auth.getToken((result) => {
                        resolve(result); // result is typically the token string or object in spec.
                        // Spec says: Returns "eyJ..." or null
                    });
                } catch (e) {
                    console.error("Mobile Bridge Error: auth.getToken", e);
                    resolve(null);
                }
            });
        },
        getUser: () => {
            return new Promise((resolve) => {
                if (!MobileBridge.isMobileApp()) return resolve(null);
                try {
                    window.LioSyncMobile.auth.getUser((result) => resolve(result));
                } catch (e) {
                    resolve(null);
                }
            });
        },
        logout: () => {
            return new Promise((resolve) => {
                if (!MobileBridge.isMobileApp()) return resolve(null);
                try {
                    window.LioSyncMobile.auth.logout((result) => resolve(result));
                } catch (e) {
                    resolve(null);
                }
            });
        }
    },

    // Scanner
    scanner: {
        scan: () => {
            return new Promise((resolve, reject) => {
                if (!MobileBridge.isMobileApp()) {
                    console.warn("Scanner only available in mobile app");
                    return resolve({ success: false, error: 'Not in mobile app' });
                }
                try {
                    window.LioSyncMobile.scanner.scan((result) => {
                        // Spec says: returns: { code: "123456", format: "QR_CODE", ... }
                        // But Section 4.3 says "Always check result.success"
                        // We will standardize the response structure here if needed, but assuming the native side follows the "success" wrapper pattern mentioned in 4.3.
                        // However, 1.0 example shows direct object return. 
                        // 4.2 says "All bridge methods use callbacks... result = { success: boolean, data: any }"
                        // I will assume the 4.2/4.3 pattern is the source of truth for the wrapper.
                        resolve(result);
                    });
                } catch (e) {
                    resolve({ success: false, error: e.message });
                }
            });
        }
    },

    // Printer
    printer: {
        print: (content, options = {}) => {
            return new Promise((resolve, reject) => {
                if (!MobileBridge.isMobileApp()) {
                    // Fallback to browser print
                    window.print();
                    return resolve({ success: true, method: 'browser' });
                }

                const defaultOptions = { format: 'receipt', copies: 1, ...options };
                try {
                    window.LioSyncMobile.printer.print(content, defaultOptions, (result) => resolve(result));
                } catch (e) {
                    resolve({ success: false, error: e.message });
                }
            });
        },
        getPrinters: () => {
            return new Promise((resolve) => {
                if (!MobileBridge.isMobileApp()) return resolve([]);
                try {
                    window.LioSyncMobile.printer.getPrinters((result) => resolve(result));
                } catch (e) {
                    resolve([]);
                }
            });
        }
    },

    // Storage
    storage: {
        get: (key) => {
            return new Promise((resolve) => {
                if (!MobileBridge.isMobileApp()) return resolve({ success: false });
                try {
                    window.LioSyncMobile.storage.get(key, (result) => resolve(result));
                } catch (e) {
                    resolve({ success: false, error: e.message });
                }
            });
        },
        set: (key, value) => {
            return new Promise((resolve) => {
                if (!MobileBridge.isMobileApp()) return resolve({ success: false });
                const val = typeof value === 'string' ? value : JSON.stringify(value);
                try {
                    window.LioSyncMobile.storage.set(key, val, (result) => resolve(result));
                } catch (e) {
                    resolve({ success: false, error: e.message });
                }
            });
        },
        remove: (key) => {
            return new Promise((resolve) => {
                if (!MobileBridge.isMobileApp()) return resolve({ success: false });
                try {
                    window.LioSyncMobile.storage.remove(key, (result) => resolve(result));
                } catch (e) {
                    resolve({ success: false, error: e.message });
                }
            });
        },
        clear: () => {
            return new Promise((resolve) => {
                if (!MobileBridge.isMobileApp()) return resolve({ success: false });
                try {
                    window.LioSyncMobile.storage.clear((result) => resolve(result));
                } catch (e) {
                    resolve({ success: false, error: e.message });
                }
            });
        }
    },

    // Device
    device: {
        getInfo: () => {
            return new Promise((resolve) => {
                if (!MobileBridge.isMobileApp()) return resolve(null);
                try {
                    window.LioSyncMobile.device.getInfo((result) => resolve(result));
                } catch (e) {
                    resolve(null);
                }
            });
        }
    },

    // Network
    network: {
        getStatus: () => {
            return new Promise((resolve) => {
                if (!MobileBridge.isMobileApp()) return resolve({ success: false }); // Or mock?
                try {
                    window.LioSyncMobile.network.getStatus((result) => resolve(result));
                } catch (e) {
                    resolve({ success: false, error: e.message });
                }
            });
        }
    }
};

// Make it globally available just in case, or import it
window.MobileBridge = MobileBridge;
