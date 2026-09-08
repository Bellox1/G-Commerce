import React, { createContext, useState, useEffect, useContext } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import client from '../api/client';

const AuthContext = createContext({});

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [redirectTo, setRedirectTo] = useState('dashboard');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadStorageData();

        // Écouteur pour session expirée (401) -> déconnexion automatique
        const { setOnUnauthorized } = require('../api/client');
        setOnUnauthorized(() => {
            setUser(null);
        });

        // Synchro automatique hors-ligne au lancement et toutes les 30 secondes
        const { syncOfflineQueue } = require('../utils/offlineSync');
        syncOfflineQueue().catch(() => {});
        const timer = setInterval(() => {
            syncOfflineQueue().catch(() => {});
        }, 30000);

        return () => {
            clearInterval(timer);
            setOnUnauthorized(null);
        };
    }, []);

    async function loadStorageData() {
        try {
            const token = await AsyncStorage.getItem('auth_token');
            const savedUser = await AsyncStorage.getItem('user');

            if (token && savedUser) {
                try {
                    const res = await client.get('/profile');
                    const freshUser = res.data.data || res.data;
                    const mergedUser = { ...freshUser, tenant: res.data.tenant || freshUser.tenant };
                    setUser(mergedUser);
                    setRedirectTo(res.data.redirect_to || 'dashboard');
                    await AsyncStorage.setItem('user', JSON.stringify(mergedUser));
                } catch (profileError) {
                    if (!profileError.isOfflineCache) {
                        await AsyncStorage.multiRemove(['auth_token', 'user']);
                        setUser(null);
                    } else {
                        // Utilisation hors-ligne avec l'utilisateur sauvegardé
                        setUser(JSON.parse(savedUser));
                    }
                }
            }
        } catch (e) {
            console.error(e);
        } finally {
            setLoading(false);
        }
    }

    const login = async (email, password) => {
        const response = await client.post('/login', { email, password });
        const token = response.data.access_token || response.data.token;

        if (!token) {
            throw new Error(response.data.message || 'Erreur lors de la connexion');
        }

        await AsyncStorage.setItem('auth_token', token);

        const profileRes = await client.get('/profile');
        const freshUser = profileRes.data.data || profileRes.data;
        const mergedUser = { ...freshUser, tenant: profileRes.data.tenant || freshUser.tenant };
        const dest = profileRes.data.redirect_to || 'dashboard';

        await AsyncStorage.setItem('user', JSON.stringify(mergedUser));
        setUser(mergedUser);
        setRedirectTo(dest);
    };

    const logout = async () => {
        try {
            await client.post('/logout');
        } catch (e) { }
        await AsyncStorage.clear();
        setUser(null);
    };

    const updateProfile = async (data) => {
        await client.put('/profile', data);
        await refreshUser();
        return { success: true };
    };

    const hasPermission = (permission) => {
        if (!user) return false;
        return hasRole(permission);
    };

    const hasRole = (roleName) => {
        if (!user) return false;
        if (user.role === roleName) return true;
        if (Array.isArray(user.roles_secondaires)) {
            return user.roles_secondaires.includes(roleName);
        }
        return false;
    };

    const hasCapability = (capability) => {
        if (!user || !user.tenant || !user.tenant.capabilities) return false;
        return !!user.tenant.capabilities[capability];
    };

    const planLevel = () => user?.tenant?.plan_level ?? 0;

    const refreshUser = async () => {
        try {
            const res = await client.get('/profile');
            const freshUser = res.data.data || res.data;
            const mergedUser = { ...freshUser, tenant: res.data.tenant || freshUser.tenant };
            setUser(mergedUser);
            setRedirectTo(res.data.redirect_to || 'dashboard');
            await AsyncStorage.setItem('user', JSON.stringify(mergedUser));
            return mergedUser;
        } catch (e) {
            console.error('Refresh user failed', e);
        }
    };

    const userRole = user?.role || null;

    return (
        <AuthContext.Provider value={{
            user,
            loading,
            redirectTo,
            login,
            logout,
            updateProfile,
            refreshUser,
            isAuthenticated: !!user,
            hasPermission,
            hasRole,
            hasCapability,
            planLevel,
            userRole
        }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => useContext(AuthContext);
