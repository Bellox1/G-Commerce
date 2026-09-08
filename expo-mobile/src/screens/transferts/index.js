import React, { useState, useCallback } from 'react';
import {
    View, Text, StyleSheet, FlatList, TouchableOpacity,
    ActivityIndicator, RefreshControl, StatusBar, Alert
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import TopHeaderNav from '../../components/TopHeaderNav';

const getTransfertBadge = (statut) => {
    switch (statut) {
        case 'receptionne':
        case 'livre':
        case 'recu': return { label: '✓ Réceptionné', color: '#16a34a', bg: '#dcfce7' };
        case 'en_transit': return { label: '🚚 En transit', color: '#92400e', bg: '#fef3c7' };
        case 'en_attente_sync':
        case 'receptionne_offline': return { label: '⏳ En attente de connexion', color: '#c2410c', bg: '#fff7ed' };
        default: return { label: '⏳ En attente', color: '#475569', bg: '#f1f5f9' };
    }
};

const TransfertsScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const [transferts, setTransferts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const fetchData = useCallback(async () => {
        try {
            const respT = await client.get('/transferts');

            const listT = Array.isArray(respT.data?.data?.data)
                ? respT.data.data.data
                : Array.isArray(respT.data?.data)
                    ? respT.data.data
                    : Array.isArray(respT.data) ? respT.data : [];

            setTransferts(listT);
        } catch (e) {
            console.error('Error fetching transferts:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useFocusEffect(
        useCallback(() => {
            fetchData();
        }, [fetchData])
    );

    const onRefresh = () => {
        setRefreshing(true);
        fetchData();
    };

    const handleCreateTransfert = () => {
        navigation.navigate('TransfertsCreate');
    };

    return (
        <View style={styles.container}>
            {/* Top Navigation Header replacing Drawer */}
            <TopHeaderNav navigation={navigation} activeCategory="transferts" />

            {/* View Action Header */}
            <View style={styles.viewHeaderRow}>
                <View style={{ flex: 1 }}>
                    <Text style={styles.headerTitle}>Transferts de Stock</Text>
                    <Text style={styles.headerSub}>Mouvements entre dépôts et magasins</Text>
                </View>

                {/* Stylish Add (+) Button */}
                <TouchableOpacity
                    style={styles.btnAddPill}
                    onPress={handleCreateTransfert}
                    activeOpacity={0.88}
                >
                    <Ionicons name="add-circle" size={18} color="#FFFFFF" />
                    <Text style={styles.btnAddPillText}>Transfert</Text>
                </TouchableOpacity>
            </View>

            {loading ? (
                <View style={styles.centerLoader}>
                    <ActivityIndicator size="large" color={Colors.primary} />
                    <Text style={styles.loaderText}>Chargement des transferts...</Text>
                </View>
            ) : (
                <FlatList
                    data={transferts}
                    keyExtractor={(item) => String(item.id)}
                    contentContainerStyle={styles.listContent}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
                    ListEmptyComponent={
                        <View style={styles.emptyContainer}>
                            <Ionicons name="swap-horizontal-outline" size={48} color={Colors.textLight} />
                            <Text style={styles.emptyText}>Aucun transfert de stock enregistré</Text>
                        </View>
                    }
                    renderItem={({ item }) => (
                         <View style={styles.card}>
                             <View style={styles.cardHeader}>
                                 <View style={styles.refRow}>
                                     <Ionicons name="barcode-outline" size={18} color={Colors.primary} />
                                     <Text style={styles.refText}>{item.reference}</Text>
                                 </View>
                                     <View style={styles.headerRight}>
                                         {item.statut === 'en_transit' ? (
                                             <TouchableOpacity
                                                 style={styles.editBtn}
                                                 onPress={() => navigation.navigate('TransfertEdit', { id: item.id })}
                                                 hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}
                                             >
                                                 <Ionicons name="create-outline" size={18} color={Colors.primary} />
                                             </TouchableOpacity>
                                         ) : null}
                                         {(() => {
                                             const b = getTransfertBadge(item.statut);
                                             return (
                                                 <View style={[styles.badge, { backgroundColor: b.bg }]}>
                                                     <Text style={[styles.badgeText, { color: b.color }]}>{b.label}</Text>
                                                 </View>
                                             );
                                         })()}
                                     </View>
                             </View>
                             <TouchableOpacity activeOpacity={0.9} onPress={() => navigation.navigate('TransfertShow', { id: item.id })}>
                                 <View style={styles.routeRow}>
                                     <Text style={styles.magName}>{item.magasin_source?.nom || 'Source'}</Text>
                                     <Ionicons name="arrow-forward" size={18} color={Colors.primary} />
                                     <Text style={styles.magName}>{item.magasin_destination?.nom || 'Destination'}</Text>
                                 </View>

                                 <View style={styles.divider} />

                                 <View style={styles.prodList}>
                                     {item.produits && item.produits.length > 0 ? (
                                         item.produits.map((p, idx) => (
                                             <Text key={idx} style={styles.prodText}>
                                                 • {p.produit?.nom || 'Produit'} (x{p.quantite})
                                             </Text>
                                         ))
                                     ) : (
                                         <Text style={styles.prodText}>• {item.produit?.nom || 'Produit'} (x{item.quantite})</Text>
                                     )}
                                 </View>
                             </TouchableOpacity>
                         </View>
                    )}
                />
            )}
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    viewHeaderRow: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        paddingHorizontal: 16,
        paddingTop: 14,
        paddingBottom: 10,
        backgroundColor: '#FFFFFF',
    },
    headerTitle: { fontSize: 20, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    headerSub: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 2 },
    btnAddPill: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 6,
        backgroundColor: Colors.primary,
        paddingHorizontal: 14,
        paddingVertical: 8,
        borderRadius: 20,
        elevation: 2,
        shadowColor: Colors.primary,
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.2,
        shadowRadius: 4,
    },
    btnAddPillText: {
        color: '#FFFFFF',
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_700Bold',
    },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center', gap: 10 },
    loaderText: { fontSize: 14, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight },
    listContent: { padding: 16, paddingBottom: 32 },
    emptyContainer: { padding: 40, alignItems: 'center', gap: 12 },
    emptyText: { fontSize: 14, color: Colors.textLight, fontFamily: 'Poppins_400Regular' },
    card: { backgroundColor: Colors.surface, borderRadius: 16, padding: 16, marginBottom: 12, elevation: 1 },
    headerRight: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    editBtn: { width: 32, height: 32, borderRadius: 16, backgroundColor: Colors.background, borderWidth: 1, borderColor: Colors.border, justifyContent: 'center', alignItems: 'center' },
    cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 10 },
    refRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
    refText: { fontSize: 15, fontFamily: 'Poppins_700Bold', color: Colors.text },
    badge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12 },
    badgeSuccess: { backgroundColor: Colors.success + '15' },
    badgeSuccessText: { color: Colors.success, fontSize: 12, fontFamily: 'Poppins_600SemiBold' },
    badgeWarning: { backgroundColor: Colors.warning + '15' },
    badgeWarningText: { color: Colors.warning, fontSize: 12, fontFamily: 'Poppins_600SemiBold' },
    routeRow: { flexDirection: 'row', alignItems: 'center', gap: 10, marginVertical: 6 },
    magName: { fontSize: 14, fontFamily: 'Poppins_600SemiBold', color: Colors.primary },
    divider: { height: 1, backgroundColor: Colors.border, marginVertical: 10 },
    prodList: { gap: 4 },
    prodText: { fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.textLight },
});

export default TransfertsScreen;
