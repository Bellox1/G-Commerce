import React, { useState, useCallback, useEffect } from 'react';
import {
    View, Text, StyleSheet, FlatList, TouchableOpacity, ScrollView,
    TextInput, ActivityIndicator, RefreshControl, StatusBar, Alert, Image, Modal
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client, { BASE_URL } from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import TopHeaderNav from '../../components/TopHeaderNav';

const formatMoney = (val) => {
    if (val === null || val === undefined || val === '') return '0 F';
    return Math.round(Number(val)).toLocaleString('fr-FR') + ' F';
};

const ProduitsScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const [produits, setProduits] = useState([]);
    const [magasins, setMagasins] = useState([]);
    const [selectedMagasinId, setSelectedMagasinId] = useState('all');
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [search, setSearch] = useState('');
    const [expandedId, setExpandedId] = useState(null);
    const PER_PAGE = 50;

    useEffect(() => {
        client.get('/magasins').then(res => {
            setMagasins(res.data?.data || res.data || []);
        }).catch(() => {});
    }, []);

    const extractList = (resp) => {
        const payload = resp.data?.data;
        if (Array.isArray(payload)) return { list: payload, last: 1 };
        if (payload && Array.isArray(payload.data)) {
            return { list: payload.data, last: payload.last_page ?? 1 };
        }
        return { list: [], last: 1 };
    };

    const fetchProduits = useCallback(async () => {
        try {
            setLoading(true);
            let page = 1;
            let all = [];
            let lastPage = 1;
            const params = { page, per_page: PER_PAGE, q: search };
            if (selectedMagasinId && selectedMagasinId !== 'all') {
                params.magasin_id = selectedMagasinId;
            }
            do {
                const resp = await client.get('/produits', { params });
                const { list, last } = extractList(resp);
                all = [...all, ...list];
                lastPage = last;
                page += 1;
            } while (page <= lastPage);

            if (selectedMagasinId && selectedMagasinId !== 'all') {
                all = all.filter(p => Number(p.stock || 0) > 0 || Number(p.stock_cartouches || 0) > 0);
            }
            setProduits(all);
        } catch (e) {
            console.error('Error fetching produits:', e);
            setProduits([]);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, [search, selectedMagasinId]);

    useFocusEffect(
        useCallback(() => {
            fetchProduits();
        }, [fetchProduits])
    );

    const onRefresh = () => {
        setRefreshing(true);
        fetchProduits();
    };

    useEffect(() => {
        const t = setTimeout(() => {
            fetchProduits();
        }, 400);
        return () => clearTimeout(t);
    }, [search]);

    const handleDeleteProduit = (id, produitName) => {
        Alert.alert(
            'Suppression',
            `Êtes-vous sûr de vouloir supprimer "${produitName}" ?`,
            [
                { text: 'Annuler', style: 'cancel' },
                {
                    text: 'Supprimer',
                    style: 'destructive',
                    onPress: async () => {
                        try {
                            await client.delete(`/produits/${id}`);
                            Alert.alert('Succès', 'Produit supprimé');
                            fetchProduits();
                        } catch (e) {
                            Alert.alert('Erreur', 'Impossible de supprimer ce produit.');
                        }
                    }
                }
            ]
        );
    };

    const safeProduits = Array.isArray(produits) ? produits : [];
    const filteredProduits = safeProduits.filter(p =>
        p && (
            (p.nom && p.nom.toLowerCase().includes(search.toLowerCase())) ||
            (p.code && p.code.toLowerCase().includes(search.toLowerCase()))
        )
    );

    const getImgUrl = (image) => {
        if (!image) return null;
        if (image.startsWith('http')) return image;
        return `${BASE_URL}/storage/${image}`;
    };

    return (
        <View style={styles.container}>
            {/* Top Navigation Header replacing Drawer */}
            <TopHeaderNav navigation={navigation} activeCategory="produits" />

            {/* View Action Header */}
            <View style={styles.viewHeaderRow}>
                <View style={{ flex: 1 }}>
                    <Text style={styles.headerTitle}>Catalogue Produits</Text>
                    <Text style={styles.headerSub}>{filteredProduits.length} produit(s) au total</Text>
                </View>

                {/* Stylish Add (+) Button */}
                <TouchableOpacity
                    style={styles.btnAddPill}
                    onPress={() => navigation.navigate('ProduitCreate')}
                    activeOpacity={0.88}
                >
                    <Ionicons name="add-circle" size={18} color="#FFFFFF" />
                    <Text style={styles.btnAddPillText}>Produit</Text>
                </TouchableOpacity>
            </View>

            {/* Search Bar */}
            <View style={styles.searchBarContainer}>
                <View style={styles.searchBar}>
                    <Ionicons name="search" size={18} color={Colors.textLight} />
                    <TextInput
                        style={styles.searchInput}
                        placeholder="Rechercher un produit ou un code..."
                        placeholderTextColor={Colors.textLight}
                        value={search}
                        onChangeText={setSearch}
                    />
                    {search ? (
                        <TouchableOpacity onPress={() => setSearch('')}>
                            <Ionicons name="close-circle" size={18} color={Colors.textLight} />
                        </TouchableOpacity>
                    ) : null}
                </View>
            </View>

            {/* Filtre Magasins */}
            {magasins.length > 0 && (
                <View style={{ marginBottom: 12 }}>
                    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ paddingHorizontal: 16, gap: 8 }}>
                        <TouchableOpacity
                            style={[styles.magChip, selectedMagasinId === 'all' && styles.magChipActive]}
                            onPress={() => setSelectedMagasinId('all')}
                        >
                            <Text style={[styles.magChipText, selectedMagasinId === 'all' && styles.magChipTextActive]}>Tous les produits</Text>
                        </TouchableOpacity>
                        {magasins.map(m => {
                            const active = selectedMagasinId === m.id;
                            return (
                                <TouchableOpacity
                                    key={m.id}
                                    style={[styles.magChip, active && styles.magChipActive]}
                                    onPress={() => setSelectedMagasinId(m.id)}
                                >
                                    <Text style={[styles.magChipText, active && styles.magChipTextActive]}>{m.nom}</Text>
                                </TouchableOpacity>
                            );
                        })}
                    </ScrollView>
                </View>
            )}

            {loading ? (
                <View style={styles.centerLoader}>
                    <ActivityIndicator size="large" color={Colors.primary} />
                    <Text style={styles.loaderText}>Chargement du catalogue...</Text>
                </View>
            ) : (
                <FlatList
                    data={filteredProduits}
                    keyExtractor={(item) => item.id.toString()}
                    contentContainerStyle={styles.listContent}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
                    ListEmptyComponent={
                        <View style={styles.emptyBox}>
                            <Ionicons name="cube-outline" size={50} color={Colors.border} />
                            <Text style={styles.emptyTitle}>Aucun produit enregistré</Text>
                        </View>
                    }
                    renderItem={({ item }) => {
                        const img = getImgUrl(item.image);
                        const expanded = expandedId === item.id;

                        const stk = Number(item.stock ?? item.stock_initial ?? 0);
                        const seuil = Number(item.seuil_alerte ?? 5);
                        const isRupture = stk <= 0;
                        const isAlerte = stk <= seuil;

                        const stockBg = isRupture ? '#FEE2E2' : (isAlerte ? '#FEF3C7' : '#F1F5F9');
                        const stockTextCol = isRupture ? '#DC2626' : (isAlerte ? '#D97706' : '#0F172A');
                        const stockBorderCol = isRupture ? '#FECACA' : (isAlerte ? '#FDE68A' : '#CBD5E1');

                        return (
                            <View style={[styles.card, expanded && { zIndex: 10 }]}>
                                <TouchableOpacity
                                    style={styles.cardMain}
                                    onPress={() => navigation.navigate('ProduitShow', { id: item.id })}
                                >
                                    <View style={styles.cardHeader}>
                                        <View style={styles.thumbWrap}>
                                            {img ? (
                                                <Image source={{ uri: img }} style={styles.thumbImg} resizeMode="cover" />
                                            ) : (
                                                <View style={styles.thumbPlaceholder}>
                                                    <Ionicons name="image-outline" size={20} color={Colors.textLight} />
                                                </View>
                                            )}
                                        </View>

                                        <View style={{ flex: 1, marginLeft: 12, marginRight: 28 }}>
                                            <Text style={styles.prodName}>{item.nom}</Text>
                                            <Text style={styles.prodCode}>Réf: PRD-{item.id} {item.code ? `(${item.code})` : ''}</Text>
                                        </View>
                                    </View>

                                    <View style={styles.detailsGrid}>
                                        <View style={styles.detailBox}>
                                            <Text style={styles.detailLabel}>Prix de vente</Text>
                                            <Text style={styles.detailVal}>{formatMoney(item.prix_vente_conseille || item.prix)}</Text>
                                        </View>

                                        {item.a_cartouche ? (
                                            <View style={styles.detailBox}>
                                                <Text style={styles.detailLabel}>Prix cartouche</Text>
                                                <Text style={styles.detailVal}>{formatMoney(item.prix_cartouche_effectif)}</Text>
                                            </View>
                                        ) : null}

                                        <View style={[styles.detailBox, { backgroundColor: stockBg, borderColor: stockBorderCol, borderWidth: 1, borderRadius: 8 }]}>
                                            <Text style={[styles.detailLabel, { color: stockTextCol, fontWeight: '700' }]}>
                                                Stock {isRupture ? '(Rupture)' : (isAlerte ? '(Alerte)' : '')}
                                            </Text>
                                            <Text style={[styles.detailVal, { color: stockTextCol, fontWeight: '900' }]}>
                                                {stk} Ctn
                                                {item.a_cartouche && item.stock_cartouches ? ` +${item.stock_cartouches} Ctr` : ''}
                                            </Text>
                                        </View>
                                    </View>
                                </TouchableOpacity>

                                <TouchableOpacity
                                    style={styles.kebab}
                                    onPress={() => setExpandedId(expanded ? null : item.id)}
                                >
                                    <Ionicons name="ellipsis-vertical" size={20} color={Colors.textLight} />
                                </TouchableOpacity>

                                {expanded && (
                                    <View style={styles.dropdown}>
                                        <TouchableOpacity
                                            style={styles.dropdownItem}
                                            onPress={() => navigation.navigate('ProduitEdit', { item })}
                                        >
                                            <Ionicons name="create-outline" size={18} color={Colors.primary} />
                                            <Text style={styles.dropdownText}>Modifier</Text>
                                        </TouchableOpacity>
                                        <View style={styles.dropdownSep} />
                                        <TouchableOpacity
                                            style={styles.dropdownItem}
                                            onPress={() => handleDeleteProduit(item.id, item.nom)}
                                        >
                                            <Ionicons name="trash-outline" size={18} color={Colors.error} />
                                            <Text style={[styles.dropdownText, { color: Colors.error }]}>Supprimer</Text>
                                        </TouchableOpacity>
                                    </View>
                                )}
                            </View>
                        );
                    }}
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
        paddingBottom: 8,
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
    simOverlay: {
        flex: 1,
        backgroundColor: 'rgba(0,0,0,0.5)',
        justifyContent: 'flex-end',
    },
    simCard: {
        backgroundColor: Colors.surface,
        borderTopLeftRadius: 24,
        borderTopRightRadius: 24,
        padding: 20,
        height: '88%',
    },
    simHeader: {
        flexDirection: 'row',
        alignItems: 'flex-start',
        justifyContent: 'space-between',
        marginBottom: 12,
    },
    simClose: { padding: 4, marginLeft: 8 },
    simSub: {
        fontSize: 12,
        fontFamily: 'PlusJakartaSans_400Regular',
        color: Colors.textLight,
        marginTop: 4,
    },
    simList: { paddingBottom: 12 },
    simRow: {
        flexDirection: 'row',
        alignItems: 'flex-start',
        paddingVertical: 10,
        borderBottomWidth: 1,
        borderBottomColor: Colors.border,
    },
    simToolbar: {
        flexDirection: 'row',
        gap: 10,
        paddingHorizontal: 4,
        paddingVertical: 8,
    },
    simToolbarBtn: {
        paddingHorizontal: 14,
        paddingVertical: 7,
        borderRadius: 16,
        backgroundColor: Colors.primaryLight,
        borderWidth: 1,
        borderColor: Colors.primary,
    },
    simToolbarBtnText: {
        fontSize: 12,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: Colors.primary,
    },
    simRowExcluded: {
        backgroundColor: '#F1F5F9',
        borderRadius: 10,
        paddingHorizontal: 10,
        opacity: 0.8,
    },
    simName: { fontSize: 14, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text },
    simPrice: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 2 },
    simExcludedText: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 2 },
    simQtyInput: {
        width: 64,
        borderWidth: 1,
        borderColor: Colors.border,
        borderRadius: 8,
        paddingHorizontal: 8,
        paddingVertical: 6,
        fontSize: 14,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.text,
        textAlign: 'center',
        backgroundColor: '#f8fafc',
    },
    simRemove: { padding: 4, marginTop: 2 },
    simRestore: {
        paddingHorizontal: 12,
        paddingVertical: 6,
        borderRadius: 8,
        backgroundColor: Colors.primary,
    },
    simRestoreText: { fontSize: 12, fontFamily: 'PlusJakartaSans_700Bold', color: '#FFFFFF' },
    simLine: {
        width: 96,
        textAlign: 'right',
        fontSize: 14,
        fontFamily: 'SpaceGrotesk_700Bold',
        color: Colors.text,
        marginTop: 2,
    },
    simTotalBar: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        backgroundColor: Colors.primaryLight,
        borderRadius: 14,
        paddingHorizontal: 16,
        paddingVertical: 14,
        marginTop: 8,
    },
    simTotalLabel: { fontSize: 13, fontFamily: 'PlusJakartaSans_600SemiBold', color: Colors.text },
    simTotalVal: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.primary },
    searchBarContainer: {
        paddingHorizontal: 16,
        paddingBottom: 10,
        backgroundColor: '#FFFFFF',
    },
    searchBar: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#F8FAFC',
        borderRadius: 14,
        paddingHorizontal: 12,
        height: 44,
        borderWidth: 1,
        borderColor: '#E2E8F0',
    },
    searchInput: { flex: 1, marginLeft: 8, fontFamily: 'PlusJakartaSans_400Regular', fontSize: 13, color: Colors.text },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center', gap: 10 },
    loaderText: { fontSize: 14, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight },
    listContent: { padding: 16, paddingBottom: 80 },
    emptyBox: { alignItems: 'center', paddingVertical: 60, gap: 10 },
    emptyTitle: { fontSize: 16, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text },
    card: {
        backgroundColor: Colors.surface, borderRadius: 16, padding: 16, marginBottom: 12, elevation: 1, shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.05, shadowRadius: 4
    },
    cardHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 12 },
    cardMain: { paddingBottom: 4 },
    thumbWrap: { width: 44, height: 44, borderRadius: 8, overflow: 'hidden' },
    thumbImg: { width: '100%', height: '100%' },
    thumbPlaceholder: { width: '100%', height: '100%', backgroundColor: '#f1f5f9', justifyContent: 'center', alignItems: 'center' },
    prodName: { fontSize: 15, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text },
    prodCode: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight },
    detailsGrid: { flexDirection: 'row', gap: 8, paddingTop: 10, borderTopWidth: 1, borderTopColor: Colors.border },
    detailBox: { flex: 1, backgroundColor: '#f8fafc', padding: 8, borderRadius: 8, alignItems: 'center' },
    detailLabel: { fontSize: 10, fontFamily: 'PlusJakartaSans_500Medium', color: Colors.textLight },
    detailVal: { fontSize: 12, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text, marginTop: 2 },
    kebab: { position: 'absolute', top: 12, right: 12, zIndex: 2, padding: 4 },
    dropdown: {
        position: 'absolute', top: 44, right: 12, zIndex: 10, minWidth: 170,
        backgroundColor: '#FFF', borderRadius: 10, borderWidth: 1, borderColor: Colors.border,
        elevation: 6, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.15, shadowRadius: 6
    },
    dropdownItem: { flexDirection: 'row', alignItems: 'center', gap: 10, paddingVertical: 12, paddingHorizontal: 14 },
    dropdownSep: { height: 1, backgroundColor: Colors.border },
    dropdownText: { fontSize: 14, fontFamily: 'PlusJakartaSans_600SemiBold', color: Colors.text },
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
    modalCard: { backgroundColor: Colors.surface, borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
    modalTitle: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.primary },
    fieldGroup: { marginBottom: 12 },
    fieldLabel: { fontSize: 12, fontFamily: 'PlusJakartaSans_500Medium', color: Colors.text, marginBottom: 4 },
    input: { borderWidth: 1, borderColor: Colors.border, borderRadius: 8, padding: 10, fontSize: 14, backgroundColor: '#f8fafc' },
    submitBtn: { backgroundColor: Colors.primary, borderRadius: 12, paddingVertical: 14, alignItems: 'center', marginTop: 16 },
    submitBtnText: { color: '#FFF', fontSize: 14, fontFamily: 'PlusJakartaSans_700Bold' },
    magChip: { paddingHorizontal: 14, paddingVertical: 8, borderRadius: 20, backgroundColor: '#F1F5F9', borderWidth: 1, borderColor: '#E2E8F0' },
    magChipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    magChipText: { fontSize: 12.5, fontFamily: 'PlusJakartaSans_600SemiBold', color: '#64748B' },
    magChipTextActive: { color: '#FFFFFF', fontFamily: 'PlusJakartaSans_700Bold' },
});

export default ProduitsScreen;
