import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Alert } from 'react-native';
import { setCache, getCache, queueOfflineAction, syncOfflineQueue, saveOfflineVente } from '../utils/offlineSync';

// Évite de réafficher plusieurs fois la même alerte de blocage d'offre
let offerBlockAlertShown = false;

// Callback pour notifier la fin de synchronisation
let onSyncCompleteCallback = null;

export function setOnSyncComplete(callback) {
    onSyncCompleteCallback = callback;
}

// Callback pour notifier la déconnexion sur 401 (session expirée)
let onUnauthorizedCallback = null;

export function setOnUnauthorized(callback) {
    onUnauthorizedCallback = callback;
}

export const BASE_URL = 'https://pilotix.alwaysdata.net'; // 'http://192.168.1.13:8000' //
const API_URL = `${BASE_URL}/api`;

const client = axios.create({
    baseURL: API_URL,
    timeout: 10000,
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

function makeCacheKey(config) {
    const url = config?.url || '';
    if (!config?.params || typeof config.params !== 'object' || Object.keys(config.params).length === 0) {
        return url;
    }
    const paramsStr = Object.keys(config.params)
        .sort()
        .map(k => `${encodeURIComponent(k)}=${encodeURIComponent(config.params[k])}`)
        .join('&');
    return `${url}?${paramsStr}`;
}

client.interceptors.response.use(
    async (response) => {
        // Mise à jour transparente du cache local pour les GET (hors profile)
        const url = response.config?.url || '';
        const shouldCache = response.config?.method?.toLowerCase() === 'get'
            && url
            && !url.includes('/profile');

        if (shouldCache) {
            const cacheKey = makeCacheKey(response.config);
            setCache(cacheKey, response.data);
            if (cacheKey !== url) {
                setCache(url, response.data);
            }
        }

        // Si le réseau refonctionne, tenter de vider la file d'attente hors-ligne
        try {
            const syncRes = await syncOfflineQueue();
            if (syncRes && syncRes.synced > 0 && onSyncCompleteCallback) {
                onSyncCompleteCallback();
            }
        } catch (e) {
            // Ignore sync errors silencieusement
        }

        return response;
    },
    async (error) => {
        const config = error.config || {};
        const method = (config.method || 'get').toLowerCase();
        const requestUrl = config.url || '';
        const isLoginRequest = requestUrl.includes('/login');

        // ─── MODE HORS-LIGNE : pas de réponse serveur ───
        if (!error.response && !isLoginRequest) {

            if (method === 'get') {
                // 1. Essayer le cache avec paramètres, puis le cache de base
                const cacheKey = makeCacheKey(config);
                let cachedData = await getCache(cacheKey);
                if (!cachedData && cacheKey !== requestUrl) {
                    cachedData = await getCache(requestUrl);
                }

                if (cachedData) {
                    if (__DEV__) console.log(`[HORS-LIGNE] Cache utilisé: ${cacheKey}`);
                    return Promise.resolve({
                        data: cachedData,
                        status: 200,
                        statusText: 'OK (Cache local)',
                        headers: {},
                        config,
                        isOfflineCache: true,
                    });
                }
                // 2. Pas de cache → réponse vide silencieuse
                if (__DEV__) console.log(`[HORS-LIGNE] Aucun cache pour ${requestUrl}, liste vide renvoyée.`);
                return Promise.resolve({
                    data: { data: [], total: 0, last_page: 1, message: 'Hors-ligne' },
                    status: 200,
                    statusText: 'OK (Hors-ligne)',
                    headers: {},
                    config,
                    isOfflineEmpty: true,
                });
            }

            if (['post', 'put', 'delete'].includes(method)) {
                // Mise en file d'attente hors-ligne
                let payload = {};
                try {
                    payload = typeof config.data === 'string'
                        ? JSON.parse(config.data)
                        : (config.data || {});
                } catch (_) {
                    payload = config.data || {};
                }

                if (__DEV__) console.log(`[HORS-LIGNE] ${method.toUpperCase()} ${requestUrl} mis en file.`);
                await queueOfflineAction({
                    type: `${method.toUpperCase()}_OFFLINE`,
                    endpoint: requestUrl,
                    method: method.toUpperCase(),
                    payload,
                });

                // Vente hors-ligne → affichage immédiat dans la liste
                if (method === 'post' && requestUrl.includes('/ventes')) {
                    const offlineVente = {
                        ...payload,
                        reference: 'OFF-' + Date.now(),
                        _offline: true,
                        montant_total: payload.ventes?.[0]?.lignes?.reduce(
                            (sum, l) => sum + (l.prix_vente * l.quantite) + ((l.prix_cartouche || 0) * (l.quantite_cartouche || 0)),
                            0
                        ) || 0,
                        montant_paye: payload.ventes?.[0]?.a_credit
                            ? (parseFloat(payload.ventes?.[0]?.montant_paye) || 0)
                            : (payload.ventes?.[0]?.montant_remis || 0),
                        montant_reste: 0,
                        statut_paiement: payload.ventes?.[0]?.a_credit ? 'credit' : 'paye',
                        client: payload.client_id
                            ? { id: payload.client_id, nom: '', prenom: '' }
                            : { nom: 'Anonyme' },
                        magasin: { nom: 'Hors-ligne' },
                        lignes: (payload.ventes?.[0]?.lignes || []).map(l => ({
                            ...l,
                            produit: l.produit || { nom: l.nom || 'Article' }
                        })),
                        created_at: new Date().toISOString(),
                    };
                    await saveOfflineVente(offlineVente);
                }

                return Promise.resolve({
                    data: {
                        success: true,
                        offline: true,
                        message: 'Opération enregistrée hors-ligne. Elle sera synchronisée dès le retour de la connexion.',
                    },
                    status: 200,
                    statusText: 'OK (Mode hors-ligne)',
                    headers: {},
                    config,
                    isOfflineQueue: true,
                });
            }
        }

        // ─── Session expirée ───
        if (error.response && error.response.status === 401 && !isLoginRequest) {
            if (__DEV__) console.log('[Auth] Session expirée (401), nettoyage token.');
            await AsyncStorage.multiRemove(['auth_token', 'user']);
            if (onUnauthorizedCallback) {
                onUnauthorizedCallback();
            }
        }

        // ─── Offre en pause / expirée ───
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
        return "Serveur injoignable. Vous êtes en mode hors-ligne.";
    }

    const status = error.response.status;

    if (status >= 500) {
        return `Erreur serveur (${status}). Réessayez plus tard.`;
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
