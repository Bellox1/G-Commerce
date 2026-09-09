import React, { useState, useEffect, useCallback } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    ActivityIndicator, RefreshControl
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import Colors from '../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../api/client';
import { formatDateFr } from '../utils/formatDate';
import ScreenHeader from '../components/ScreenHeader';

const BADGE_COLORS = {
    info: { bg: '#dbeafe', fg: '#1e40af' },
    warning: { bg: '#fef3c7', fg: '#92400e' },
    danger: { bg: '#fee2e2', fg: '#991b1b' },
    success: { bg: '#dcfce7', fg: '#166534' },
};

import { getCache } from '../utils/offlineSync';

const AlertesScreen = () => {
    const navigation = useNavigation();

    const [alertes, setAlertes] = useState([]);
    const [notifs, setNotifs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [notifLoading, setNotifLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const fetchData = useCallback(async () => {
        try {
            const resp = await client.get('/notifications');
            const body = resp.data;
            let stockAlerts = Array.isArray(body?.stockAlertes) ? body.stockAlertes : null;
            let notifItems = Array.isArray(body?.items) ? body.items : null;

            if (!stockAlerts) {
                const cachedNotifs = await getCache('/notifications');
                if (cachedNotifs && Array.isArray(cachedNotifs.stockAlertes)) {
                    stockAlerts = cachedNotifs.stockAlertes;
                    notifItems = Array.isArray(cachedNotifs.items) ? cachedNotifs.items : [];
                } else {
                    const cachedProduitsRes = (await getCache('/produits?per_page=1000')) || (await getCache('/produits'));
                    const produitsList = Array.isArray(cachedProduitsRes?.data?.data)
                        ? cachedProduitsRes.data.data
                        : (Array.isArray(cachedProduitsRes?.data)
                            ? cachedProduitsRes.data
                            : (Array.isArray(cachedProduitsRes) ? cachedProduitsRes : []));
                    
                    stockAlerts = produitsList
                        .filter(p => {
                            const stk = p.stock ?? p.stock_disponible ?? p.stock_actuel ?? 0;
                            const seuil = p.seuil_alerte ?? 5;
                            return stk <= seuil;
                        })
                        .map(p => ({
                            id: p.id,
                            nom: p.nom,
                            stock: p.stock ?? p.stock_disponible ?? p.stock_actuel ?? 0,
                            seuil_alerte: p.seuil_alerte ?? 5,
                            produit: p
                        }));
                    notifItems = [];
                }
            }

            setAlertes(stockAlerts || []);
            setNotifs(notifItems || []);
        } catch (e) {
            console.error('Erreur alertes/notifs:', e);
        } finally {
            setLoading(false);
            setNotifLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    const onRefresh = () => {
        setRefreshing(true);
        fetchData().finally(() => setRefreshing(false));
    };

    const markRead = async (id) => {
        setNotifs((prev) => prev.map((n) => (n.id === id ? { ...n, read_at: n.read_at || 'lu' } : n)));
        try {
            await client.patch(`/notifications/${id}/read`);
        } catch (e) { /* ignore */ }
    };

    const renderStockItem = ({ item }) => {
        const nom = item.produit?.nom || item.nom || 'Produit';
        const stock = item.stock ?? 0;
        const seuil = item.produit?.seuil_alerte ?? item.seuil_alerte;
        const rupture = stock <= 0;
        const isLow = seuil ? stock <= seuil : stock <= 5;
        const badgeColor = rupture ? Colors.error : Colors.warning;
        const produitId = item.produit?.id ?? item.id;

        return (
            <TouchableOpacity style={styles.card} activeOpacity={0.7} onPress={() => navigation.navigate('ProduitShow', { id: produitId })}>
                <View style={styles.cardHeader}>
                    <View style={[styles.iconBadge, { backgroundColor: badgeColor + '18' }]}>
                        <Ionicons name={rupture ? 'alert-circle' : 'warning'} size={20} color={badgeColor} />
                    </View>
                    <View style={styles.prodInfo}>
                        <Text style={styles.prodName} numberOfLines={1}>{nom}</Text>
                        <Text style={styles.prodMeta}>Seuil : {seuil != null ? seuil : 5} carton(s)</Text>
                    </View>
                    <View style={[styles.stockBadge, { backgroundColor: badgeColor + '20' }]}>
                        <Text style={[styles.stockBadgeText, { color: badgeColor }]}>{stock} en stock</Text>
                    </View>
                </View>
            </TouchableOpacity>
        );
    };

    const renderNotif = ({ item }) => {
        const c = BADGE_COLORS[item.badge] || BADGE_COLORS.info;
        const read = !!item.read_at;
        return (
            <View style={[styles.card, read && styles.cardRead]}>
                <View style={styles.cardHeader}>
                    <View style={[styles.iconBadge, { backgroundColor: c.bg }]}>
                        <Ionicons name={item.badge === 'danger' || item.badge === 'warning' ? 'warning' : 'notifications'} size={20} color={c.fg} />
                    </View>
                    <View style={styles.prodInfo}>
                        <Text style={styles.prodName}>{item.titre}</Text>
                        <Text style={styles.prodMeta}>{formatDateFr(item.created_at)}</Text>
                    </View>
                    {!read && <View style={styles.dot} />}
                </View>
                <Text style={styles.notifMsg}>{item.message}</Text>
                <View style={styles.notifActions}>
                    {!read && (
                        <TouchableOpacity style={styles.miniBtn} onPress={() => markRead(item.id)}>
                            <Text style={styles.miniBtnText}>Marquer comme lu</Text>
                        </TouchableOpacity>
                    )}
                    <TouchableOpacity style={styles.miniBtnPrimary} onPress={() => navigation.navigate('Offre')}>
                        <Ionicons name="star-outline" size={14} color="#fff" />
                        <Text style={styles.miniBtnTextLight}>Voir mon offre</Text>
                    </TouchableOpacity>
                </View>
            </View>
        );
    };

    return (
        <View style={styles.container}>
            <ScreenHeader
                navigation={navigation}
                title="Alertes"
                subtitle={loading ? 'Chargement...' : `${alertes.length} alerte(s) de stock`}
            />

            <ScrollView
                style={styles.scroll}
                contentContainerStyle={styles.scrollContent}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} tintColor={Colors.primary} />}
            >
                <Text style={styles.sectionTitle}>Alertes de stock</Text>
                {loading ? (
                    <View style={styles.miniLoader}><ActivityIndicator size="small" color={Colors.primary} /></View>
                ) : alertes.length === 0 ? (
                    <View style={styles.emptyMini}>
                        <Ionicons name="checkmark-circle" size={28} color={Colors.border} />
                        <Text style={styles.emptyMiniText}>Aucun produit en alerte</Text>
                    </View>
                ) : (
                    <View style={styles.groupedAlertCard}>
                        <View style={styles.groupedAlertHeader}>
                            <Ionicons name="warning" size={18} color="#991B1B" />
                            <Text style={styles.groupedAlertTitle}>Alertes & Ruptures de Stock ({alertes.length})</Text>
                        </View>
                        <View style={styles.groupedAlertBody}>
                            {alertes.map((it, idx) => {
                                const nom = it.produit?.nom || it.nom || 'Produit';
                                const stock = it.stock ?? 0;
                                const seuil = it.produit?.seuil_alerte ?? it.seuil_alerte ?? 5;
                                const rupture = stock <= 0;
                                const badgeColor = rupture ? '#DC2626' : '#D97706';
                                const produitId = it.produit?.id ?? it.id;

                                return (
                                    <TouchableOpacity
                                        key={'s' + idx}
                                        style={[styles.groupedAlertRow, idx > 0 && styles.groupedAlertBorder]}
                                        onPress={() => navigation.navigate('ProduitShow', { id: produitId })}
                                    >
                                        <View style={{ flex: 1 }}>
                                            <Text style={styles.groupedProdName}>{nom}</Text>
                                            <Text style={styles.groupedProdMeta}>Seuil : {seuil} carton(s)</Text>
                                        </View>
                                        <View style={[styles.groupedStockBadge, { backgroundColor: badgeColor + '18' }]}>
                                            <Text style={[styles.groupedStockText, { color: badgeColor }]}>
                                                {stock} en stock {rupture ? '(Rupture)' : '(Faible)'}
                                            </Text>
                                        </View>
                                    </TouchableOpacity>
                                );
                            })}
                        </View>
                    </View>
                )}

                {alertes.length > 0 && (
                    <TouchableOpacity style={styles.commanderBtn} onPress={() => navigation.navigate('ArrivageCreate')}>
                        <Ionicons name="bus-outline" size={16} color="#FFF" />
                        <Text style={styles.commanderText}>Commander un arrivage</Text>
                    </TouchableOpacity>
                )}

                <Text style={[styles.sectionTitle, { marginTop: 24 }]}>Notifications d'abonnement</Text>
                {notifLoading ? (
                    <View style={styles.miniLoader}><ActivityIndicator size="small" color={Colors.primary} /></View>
                ) : notifs.length === 0 ? (
                    <View style={styles.emptyMini}>
                        <Ionicons name="notifications-off-outline" size={28} color={Colors.border} />
                        <Text style={styles.emptyMiniText}>Aucune notification d'abonnement</Text>
                    </View>
                ) : (
                    notifs.map((it, i) => <View key={'n' + i}>{renderNotif({ item: it })}</View>)
                )}

                <View style={styles.bottomSpacer} />
            </ScrollView>
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    scroll: { flex: 1 },
    scrollContent: { padding: 16, paddingBottom: 32 },
    sectionTitle: { fontSize: 15, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text, marginBottom: 12 },
    miniLoader: { paddingVertical: 24, alignItems: 'center' },
    emptyMini: { alignItems: 'center', paddingVertical: 24, gap: 8, backgroundColor: Colors.surface, borderRadius: 16, borderWidth: 1, borderColor: '#E2E8F0' },
    emptyMiniText: { fontSize: 13, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight },
    listContent: { padding: 16, paddingBottom: 32 },
    card: { backgroundColor: Colors.surface, borderRadius: 16, padding: 16, marginBottom: 12, elevation: 1, borderWidth: 1, borderColor: '#E2E8F0' },
    cardRead: { opacity: 0.6 },
    cardHeader: { flexDirection: 'row', alignItems: 'center', gap: 12 },
    iconBadge: { width: 40, height: 40, borderRadius: 20, justifyContent: 'center', alignItems: 'center' },
    prodInfo: { flex: 1 },
    prodName: { fontSize: 15, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    prodMeta: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 2 },
    stockBadge: { paddingHorizontal: 12, paddingVertical: 6, borderRadius: 12 },
    stockBadgeText: { fontSize: 12, fontFamily: 'PlusJakartaSans_700Bold' },
    dot: { width: 10, height: 10, borderRadius: 5, backgroundColor: Colors.primary },
    notifMsg: { fontSize: 13.5, fontFamily: 'PlusJakartaSans_400Regular', color: '#475569', marginTop: 10, lineHeight: 20 },
    notifActions: { flexDirection: 'row', gap: 8, marginTop: 12 },
    miniBtn: { paddingHorizontal: 12, paddingVertical: 8, borderRadius: 10, borderWidth: 1, borderColor: Colors.border },
    miniBtnText: { fontSize: 12.5, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text },
    miniBtnPrimary: { flexDirection: 'row', alignItems: 'center', gap: 6, paddingHorizontal: 12, paddingVertical: 8, borderRadius: 10, backgroundColor: Colors.primary },
    miniBtnTextLight: { fontSize: 12.5, fontFamily: 'PlusJakartaSans_700Bold', color: '#fff' },
    commanderBtn: {
        flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8,
        backgroundColor: Colors.primary, borderRadius: 12, paddingVertical: 14, marginTop: 12,
    },
    commanderText: { color: '#FFF', fontSize: 15, fontFamily: 'PlusJakartaSans_700Bold' },
    bottomSpacer: { height: 24 },
    groupedAlertCard: { backgroundColor: '#FFF', borderRadius: 16, borderColor: '#FECACA', overflow: 'hidden', marginBottom: 12, borderWidth: 1 },
    groupedAlertHeader: { backgroundColor: '#FEE2E2', paddingHorizontal: 16, paddingVertical: 12, flexDirection: 'row', alignItems: 'center', gap: 8, borderBottomWidth: 1, borderBottomColor: '#FECACA' },
    groupedAlertTitle: { fontSize: 14, fontFamily: 'SpaceGrotesk_700Bold', color: '#991B1B' },
    groupedAlertBody: { padding: 4 },
    groupedAlertRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 12, paddingVertical: 10 },
    groupedAlertBorder: { borderTopWidth: 1, borderTopColor: '#FEE2E2' },
    groupedProdName: { fontSize: 14, fontFamily: 'SpaceGrotesk_700Bold', color: '#1E293B' },
    groupedProdMeta: { fontSize: 11.5, fontFamily: 'PlusJakartaSans_400Regular', color: '#64748B', marginTop: 2 },
    groupedStockBadge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12 },
    groupedStockText: { fontSize: 11.5, fontFamily: 'PlusJakartaSans_700Bold' },
});

export default AlertesScreen;
