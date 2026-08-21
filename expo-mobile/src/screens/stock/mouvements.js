import React, { useEffect, useState } from 'react';
import {
    View, Text, StyleSheet, FlatList, ActivityIndicator,
    TouchableOpacity, RefreshControl, StatusBar
} from 'react-native';
import client from '../../api/client';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { formatDateFr, formatDateTimeFr } from '../../utils/formatDate';

const MouvementsScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const [mouvements, setMouvements] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [filterType, setFilterType] = useState('tous');

    useEffect(() => {
        fetchMouvements();
    }, []);

    const fetchMouvements = async () => {
        try {
            const res = await client.get('/stock/mouvements');
            const list = res.data?.data?.data || res.data?.data || (Array.isArray(res.data) ? res.data : []);
            setMouvements(list);
        } catch (e) {
            console.error('Error fetching movimientos:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    const onRefresh = () => {
        setRefreshing(true);
        fetchMouvements();
    };

    const filteredMouvements = mouvements.filter(m => {
        if (filterType === 'tous') return true;
        return m.type === filterType;
    });

    const getTypeConfig = (type) => {
        switch (type) {
            case 'entree_arrivage':
                return { label: 'Arrivage (+)', color: Colors.success, bg: '#dcfce7', icon: 'arrow-down-circle' };
            case 'sortie_vente':
                return { label: 'Sortie Vente (-)', color: Colors.error, bg: '#fee2e2', icon: 'arrow-up-circle' };
            case 'transfert_entree':
                return { label: 'Transfert Entrée (+)', color: Colors.success, bg: '#dcfce7', icon: 'swap-horizontal' };
            case 'transfert_sortie':
                return { label: 'Transfert Sortie (-)', color: Colors.warning, bg: '#fef3c7', icon: 'swap-horizontal' };
            case 'ajustement_positif':
                return { label: 'Ajustement (+)', color: Colors.success, bg: '#dcfce7', icon: 'add-circle' };
            case 'ajustement_negatif':
                return { label: 'Ajustement (-)', color: Colors.error, bg: '#fee2e2', icon: 'remove-circle' };
            default:
                return { label: type || 'Mouvement', color: Colors.text, bg: '#f1f5f9', icon: 'sync' };
        }
    };

    return (
        <View style={styles.container}>
            <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />

            {/* Top Bar */}
            <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 20) + 8 }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={24} color="#FFF" />
                </TouchableOpacity>
                <Text style={styles.topTitle}>Historique des Mouvements</Text>
                <View style={{ width: 24 }} />
            </View>

            {loading ? (
                <View style={styles.centerLoader}>
                    <ActivityIndicator size="large" color={Colors.primary} />
                </View>
            ) : (
                <FlatList
                    data={filteredMouvements}
                    keyExtractor={item => item.id.toString()}
                    contentContainerStyle={styles.listContent}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
                    ListEmptyComponent={
                        <View style={styles.emptyBox}>
                            <Ionicons name="swap-horizontal-outline" size={50} color={Colors.border} />
                            <Text style={styles.emptyTitle}>Aucun mouvement de stock</Text>
                        </View>
                    }
                    renderItem={({ item }) => {
                        const cfg = getTypeConfig(item.type);
                        const dateStr = item.created_at ? formatDateTimeFr(item.created_at) : '';
                        const isPositive = item.quantite > 0 || item.type.includes('entree') || item.type.includes('positif');

                        return (
                            <View style={styles.mvtCard}>
                                <View style={styles.mvtHeader}>
                                    <View style={[styles.typeBadge, { backgroundColor: cfg.bg }]}>
                                        <Ionicons name={cfg.icon} size={14} color={cfg.color} />
                                        <Text style={[styles.typeBadgeText, { color: cfg.color }]}>{cfg.label}</Text>
                                    </View>
                                    <Text style={styles.mvtDate}>{dateStr}</Text>
                                </View>

                                <Text style={styles.prodName}>{item.produit?.nom || `Produit #${item.produit_id}`}</Text>
                                <Text style={styles.magasinText}>Magasin: {item.magasin?.nom || 'Dépôt Principal'}</Text>

                                <View style={styles.qtyRow}>
                                    <Text style={styles.qtyLabel}>Quantité déplacée :</Text>
                                    <Text style={[styles.qtyVal, { color: isPositive ? Colors.success : Colors.error }]}>
                                        {isPositive ? `+${item.quantite}` : item.quantite} carton(s)
                                    </Text>
                                </View>
                            </View>
                        );
                    }}
                />
            )}
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: Colors.background },
    topBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: Colors.primary, paddingHorizontal: 16, paddingBottom: 12 },
    backBtn: { padding: 4 },
    topTitle: { fontSize: 16, fontFamily: 'Poppins_700Bold', color: '#FFF' },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    listContent: { padding: 16, paddingBottom: 40 },
    emptyBox: { alignItems: 'center', paddingVertical: 60, gap: 10 },
    emptyTitle: { fontSize: 16, fontFamily: 'Poppins_700Bold', color: Colors.text },
    mvtCard: { backgroundColor: '#FFF', borderRadius: 14, padding: 14, marginBottom: 12, borderWidth: 1, borderColor: Colors.border },
    mvtHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
    typeBadge: { flexDirection: 'row', alignItems: 'center', gap: 4, paddingHorizontal: 8, paddingVertical: 4, borderRadius: 8 },
    typeBadgeText: { fontSize: 11, fontWeight: '700' },
    mvtDate: { fontSize: 11, color: Colors.textLight },
    prodName: { fontSize: 15, fontFamily: 'Poppins_700Bold', color: Colors.text },
    magasinText: { fontSize: 12, color: Colors.textLight, marginTop: 2 },
    qtyRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 8, paddingTop: 8, borderTopWidth: 1, borderTopColor: '#f1f5f9' },
    qtyLabel: { fontSize: 12, color: Colors.textLight },
    qtyVal: { fontSize: 15, fontWeight: '800' },
});

export default MouvementsScreen;
