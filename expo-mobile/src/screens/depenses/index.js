import React, { useState, useEffect, useCallback } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    ActivityIndicator, RefreshControl, Alert
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';

import Colors from '../../theme/Colors';
import { useAuth } from '../../context/AuthContext';
import ScreenHeader from '../../components/ScreenHeader';
import DateRangeModal from '../../components/DateRangeModal';
import client, { BASE_URL } from '../../api/client';

const todayStr = () => {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
};

const formatMoney = (val) => {
    const n = Number(val || 0);
    return new Intl.NumberFormat('fr-FR').format(n) + ' FCFA';
};

const fmtFr = (str) => {
    if (!str) return '';
    const [y, m, d] = str.split('-');
    return `${d}/${m}/${y}`;
};

const DepensesScreen = () => {
    const navigation = useNavigation();
    const { user } = useAuth();

    const [debut, setDebut] = useState(todayStr());
    const [fin, setFin] = useState(todayStr());
    const [depenses, setDepenses] = useState([]);
    const [total, setTotal] = useState(0);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [showPeriod, setShowPeriod] = useState(false);

    const fetchDepenses = useCallback(async (d = debut, f = fin) => {
        try {
            const resp = await client.get('/depenses', { params: { date_debut: d, date_fin: f } });
            const body = resp.data;
            setDepenses(body?.data ?? []);
            setTotal(body?.total ?? 0);
        } catch (e) {
            console.error('Erreur depenses:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, [debut, fin]);

    useEffect(() => { fetchDepenses(); }, [fetchDepenses]);

    const onRefresh = () => { setRefreshing(true); fetchDepenses(); };

    const onApplyPeriod = (d, f) => {
        setDebut(d);
        setFin(f);
        setLoading(true);
        fetchDepenses(d, f);
    };

    const removeDepense = (item) => {
        Alert.alert('Supprimer', 'Supprimer cette dépense ?', [
            { text: 'Annuler', style: 'cancel' },
            {
                text: 'Supprimer', style: 'destructive', onPress: async () => {
                    try {
                        await client.delete(`/depenses/${item.id}`);
                        setDepenses((prev) => prev.filter((x) => x.id !== item.id));
                        setTotal((t) => t - Number(item.montant || 0));
                    } catch (e) { /* ignore */ }
                }
            }
        ]);
    };

    const periodLabel = debut === fin
        ? (debut === todayStr() ? "Aujourd'hui" : fmtFr(debut))
        : `${fmtFr(debut)} → ${fmtFr(fin)}`;

    const renderItem = ({ item }) => (
        <View style={styles.card}>
            <View style={styles.cardTop}>
                <View style={{ flex: 1 }}>
                    <Text style={styles.desc}>{item.description || 'Sans description'}</Text>
                    <Text style={styles.meta}>
                        <Ionicons name="calendar-outline" size={13} color={Colors.textLight} /> {fmtFr(item.date_depense)} · {item.user?.name || '—'}
                    </Text>
                </View>
                <Text style={styles.amount}>-{formatMoney(item.montant)}</Text>
            </View>

            <View style={styles.cardActions}>
                <TouchableOpacity style={styles.delBtn} onPress={() => removeDepense(item)}>
                    <Ionicons name="trash-outline" size={18} color={Colors.error} />
                </TouchableOpacity>
            </View>
        </View>
    );

    return (
        <View style={styles.container}>
            <ScreenHeader
                navigation={navigation}
                title="Dépenses"
                subtitle={loading ? 'Chargement...' : `${depenses.length} dépense(s)`}
            />

            <View style={styles.periodBar}>
                <TouchableOpacity style={styles.periodBtn} onPress={() => setShowPeriod(true)}>
                    <Ionicons name="calendar-outline" size={18} color={Colors.primary} />
                    <Text style={styles.periodText}>{periodLabel}</Text>
                    <Ionicons name="chevron-down" size={16} color={Colors.textLight} />
                </TouchableOpacity>
                <View style={styles.totalBadge}>
                    <Text style={styles.totalText}>-{formatMoney(total)}</Text>
                </View>
            </View>

            {loading ? (
                <View style={styles.centerLoader}>
                    <ActivityIndicator size="large" color={Colors.primary} />
                </View>
            ) : (
                <ScrollView
                    style={styles.scroll}
                    contentContainerStyle={styles.scrollContent}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} tintColor={Colors.primary} />}
                >
                    {depenses.length === 0 ? (
                        <View style={styles.emptyBox}>
                            <Ionicons name="cash-outline" size={48} color={Colors.border} />
                            <Text style={styles.emptyTitle}>Aucune dépense</Text>
                            <Text style={styles.emptySub}>Ajoutez une dépense depuis le tableau de bord.</Text>
                        </View>
                    ) : (
                        depenses.map((it, i) => <View key={i}>{renderItem({ item: it })}</View>)
                    )}
                    <View style={styles.bottomSpacer} />
                </ScrollView>
            )}

            <DateRangeModal
                visible={showPeriod}
                initialDebut={debut}
                initialFin={fin}
                onApply={onApplyPeriod}
                onClose={() => setShowPeriod(false)}
            />
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    scroll: { flex: 1 },
    scrollContent: { padding: 16, paddingBottom: 32 },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    periodBar: { flexDirection: 'row', alignItems: 'center', gap: 10, paddingHorizontal: 16, paddingVertical: 12, backgroundColor: '#fff', borderBottomWidth: 1, borderBottomColor: '#EEF2F6' },
    periodBtn: { flexDirection: 'row', alignItems: 'center', gap: 8, backgroundColor: '#F1F5F9', borderRadius: 12, paddingVertical: 8, paddingHorizontal: 12, flex: 1 },
    periodText: { fontSize: 14, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text, flex: 1 },
    totalBadge: { backgroundColor: 'rgba(220,38,38,0.1)', borderRadius: 12, paddingVertical: 8, paddingHorizontal: 12 },
    totalText: { fontSize: 14, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.error },
    card: { backgroundColor: '#fff', borderRadius: 16, padding: 16, marginBottom: 12, borderWidth: 1, borderColor: '#EEF2F6' },
    cardTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', gap: 12 },
    desc: { fontSize: 15, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    meta: { fontSize: 12.5, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 4 },
    amount: { fontSize: 15, fontFamily: 'PlusJakartaSans_800ExtraBold', color: Colors.error },
    cardActions: { flexDirection: 'row', alignItems: 'center', justifyContent: 'flex-end', marginTop: 12, paddingTop: 12, borderTopWidth: 1, borderTopColor: '#F1F5F9' },
    delBtn: { padding: 6 },
    emptyBox: { alignItems: 'center', paddingVertical: 60, gap: 10 },
    emptyTitle: { fontSize: 16, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    emptySub: { fontSize: 13, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, textAlign: 'center' },
    bottomSpacer: { height: 24 },
});

export default DepensesScreen;
