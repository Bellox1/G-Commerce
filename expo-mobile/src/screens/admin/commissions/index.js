import React, { useState, useCallback } from 'react';
import {
    View, Text, StyleSheet, FlatList, TouchableOpacity,
    ActivityIndicator, RefreshControl, StatusBar, Alert
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useNavigation, useFocusEffect, DrawerActions } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import Colors from '../../../theme/Colors';
import client from '../../../api/client';
import { Header, Loader, Empty, StatusBadge, COMMISSION_STATUS } from '../components';

const formatMoney = (val) => (val || val === 0 ? Number(val).toLocaleString('fr-FR') + ' FCFA' : '—');
const formatDate = (d) => {
    if (!d) return '—';
    try { return new Date(d).toLocaleDateString('fr-FR'); } catch { return '—'; }
};

const CommissionsScreen = () => {
    const insets = useSafeAreaInsets();
    const navigation = useNavigation();
    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const fetchData = useCallback(async () => {
        try {
            const res = await client.get('/commissions');
            const list = res.data?.data || (Array.isArray(res.data) ? res.data : []);
            setItems(list);
        } catch (e) {
            console.error('Error fetching commissions:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useFocusEffect(useCallback(() => { fetchData(); }, [fetchData]));

    const toggleStatut = async (item) => {
        const next = item.statut === 'en_attente' ? 'reglee' : 'en_attente';
        try {
            await client.post(`/commissions/${item.id}/statut`, { statut: next });
            fetchData();
        } catch (e) {
            Alert.alert('Erreur', e.response?.data?.message || 'Mise à jour impossible');
        }
    };

    const renderItem = ({ item }) => {
        const isPending = item.statut === 'en_attente';
        return (
            <View style={styles.card}>
                <View style={styles.row}>
                    <Text style={styles.date}>{formatDate(item.created_at)}</Text>
                    <StatusBadge statut={item.statut} map={COMMISSION_STATUS} />
                </View>
                <Text style={styles.partner}>{item.partenaire?.name || 'Partenaire supprimé'}</Text>
                {item.partenaire?.email ? <Text style={styles.sub}>{item.partenaire.email}</Text> : null}
                <Text style={styles.company}>
                    {item.tenant?.nom || 'Société supprimée'}
                    {item.tenant?.marque ? ` (${item.tenant.marque})` : ''}
                </Text>
                <Text style={[styles.amount, { color: Colors.success }]}>{formatMoney(item.montant)}</Text>
                <TouchableOpacity
                    style={[styles.actionBtn, { backgroundColor: isPending ? Colors.success : Colors.textLight }]}
                    onPress={() => toggleStatut(item)}
                >
                    <Ionicons name={isPending ? 'checkmark-circle' : 'refresh'} size={16} color="#fff" />
                    <Text style={styles.actionText}>{isPending ? 'Marquer réglée' : 'Remettre en attente'}</Text>
                </TouchableOpacity>
            </View>
        );
    };

    return (
        <View style={[styles.container, { paddingTop: insets.top }]}>
            <StatusBar barStyle="dark-content" backgroundColor={Colors.background} />
            <Header title="Commissions Partenaires" onMenu={() => navigation.dispatch(DrawerActions.openDrawer())} onBack={() => navigation.goBack()} />
            <View style={styles.body}>
                {loading ? <Loader /> : (
                    <FlatList
                        data={items}
                        keyExtractor={(it) => String(it.id)}
                        renderItem={renderItem}
                        contentContainerStyle={styles.list}
                        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={fetchData} />}
                        ListEmptyComponent={<Empty text="Aucune commission enregistrée." />}
                    />
                )}
            </View>
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: Colors.background },
    body: { flex: 1 },
    list: { padding: 16, gap: 12 },
    card: { backgroundColor: Colors.cardBg, borderRadius: 16, padding: 16, borderWidth: 1, borderColor: Colors.border },
    row: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
    date: { fontSize: 13, fontFamily: 'Poppins_500Medium', color: Colors.textLight },
    partner: { fontSize: 16, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    sub: { fontSize: 12.5, fontFamily: 'Poppins_400Regular', color: Colors.textLight, marginTop: 2 },
    company: { fontSize: 13.5, fontFamily: 'Poppins_500Medium', color: Colors.text, marginTop: 6 },
    amount: { fontSize: 17, fontFamily: 'Poppins_700Bold', marginTop: 8 },
    actionBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6, marginTop: 12, paddingVertical: 10, borderRadius: 10 },
    actionText: { color: '#fff', fontSize: 13.5, fontFamily: 'Poppins_600SemiBold' },
});

export default CommissionsScreen;
