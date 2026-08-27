import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Alert } from 'react-native';
import { setCache, getCache, queueOfflineAction, syncOfflineQueue } from '../utils/offlineSync';

// Évite de réafficher plusieurs fois la même alerte de blocage d'offre
let offerBlockAlertShown = false;

export const BASE_URL = 'https://pilotix.alwaysdata.net'; //'http://192.168.1.13:8000'  // IP locale actuelle du serveur Laravel
const API_URL = `${BASE_URL}/api`;

const client = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

client.interceptors.request.use(async (config) => {
    const token = await AsyncStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

client.interceptors.response.use(
    async (response) => {
        // En cas de succès d'une requête GET, mise à jour transparente du cache local
        if (response.config?.method?.toLowerCase() === 'get' && response.config?.url) {
            setCache(response.config.url, response.data);
        }

        // Si le réseau refonctionne, tenter de vider la file d'attente hors-ligne
        syncOfflineQueue().catch(() => {});

        return response;
    },
    async (error) => {
        const config = error.config || {};
        const method = (config.method || 'get').toLowerCase();
        const requestUrl = config.url || '';
        const isLoginRequest = requestUrl.includes('/login');

        // GESTION DU MODE HORS-LIGNE (Pas de réponse du serveur / réseau coupé)
        if (!error.response && !isLoginRequest) {
            if (method === 'get') {
                // Requête de lecture : renvoyer les données du cache local si disponibles
                const cachedData = await getCache(requestUrl);
                if (cachedData) {
                    console.log(`[HORS-LIGNE] Chargement depuis le cache local pour ${requestUrl}`);
                    return Promise.resolve({
                        data: cachedData,
                        status: 200,
                        statusText: 'OK (Cache local)',
                        headers: {},
                        config,
                        isOfflineCache: true
                    });
                }
            } else if (['post', 'put', 'delete'].includes(method)) {
                // Requête de modification (création vente, produit, client...) : mettre en file d'attente
                let payload = {};
                try {
                    payload = typeof config.data === 'string' ? JSON.parse(config.data) : (config.data || {});
                } catch (e) {
                    payload = config.data || {};
                }

                console.log(`[HORS-LIGNE] Enregistrement de l'action ${method.toUpperCase()} ${requestUrl} en file d'attente.`);
                await queueOfflineAction({
                    type: `${method.toUpperCase()}_OFFLINE`,
                    endpoint: requestUrl,
                    method: method.toUpperCase(),
                    payload
                });

                return Promise.resolve({
                    data: {
                        success: true,
                        offline: true,
                        message: 'Opération enregistrée hors-ligne. Elle sera synchronisée dès le retour de la connexion.'
                    },
                    status: 200,
                    statusText: 'OK (Mode hors-ligne)',
                    headers: {},
                    config,
                    isOfflineQueue: true
                });
            }
        }

        if (error.response && error.response.status === 401 && !isLoginRequest) {
            console.log('Session expirée (401), nettoyage du token.');
            await AsyncStorage.multiRemove(['auth_token', 'user']);
        }

        // Offre en pause / expirée : informer l'utilisateur (fonctionnalités bloquées)
        if (error.response && error.response.status === 403) {
            const code = error.response.data?.code;
            if ((code === 'offer_paused' || code === 'offer_expired') && !offerBlockAlertShown) {
                offerBlockAlertShown = true;
                const msg = error.response.data?.message || 'Votre offre est suspendue.';
                Alert.alert('Accès bloqué', msg, [
                    { text: 'OK', onPress: () => { offerBlockAlertShown = false; } },
                ]);
            }
        }

        error.userMessage = apiErrorMessage(error, { isLogin: isLoginRequest });
        return Promise.reject(error);
    }
);

export function apiErrorMessage(error, { isLogin = false } = {}) {
    if (!error.response) {
        return "Serveur injoignable. L'opération a été basculée en mode hors-ligne.";
    }

    const status = error.response.status;

    if (status >= 500) {
        return `Erreur serveur (code ${status}). Réessayez plus tard.`;
    }

    if (status === 401) {
        return isLogin
            ? "Email ou mot de passe incorrect."
            : "Session expirée, veuillez vous reconnecter.";
    }

    if (status === 422) {
        const errors = error.response.data?.errors;
        if (errors && Object.keys(errors).length) {
            return errors[Object.keys(errors)[0]][0];
        }
    }

    if (error.response.data?.message) {
        return error.response.data.message;
    }

    return "Une erreur est survenue. Réessayez plus tard.";
}

export default client;
