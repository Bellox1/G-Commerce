import React, { useState, useCallback, useRef } from 'react';
import {
    View, Text, StyleSheet, TouchableOpacity, Modal, FlatList, ActivityIndicator, ScrollView
} from 'react-native';
import Colors from '../theme/Colors';
import { Ionicons as Icon } from '@expo/vector-icons';
import client from '../api/client';

const formatMoney = (val) => {
    if (val === null || val === undefined || val === '') return '0 F';
    let n = Math.round(Number(val));
    if (!n || Math.abs(n) === 0) n = 0;
    return n.toLocaleString('fr-FR') + ' F';
};

const StimulateurCA = () => {
    const [open, setOpen] = useState(false);
    const [produits, setProduits] = useState([]);
    const [magasins, setMagasins] = useState([]);
    const [selectedMagasin, setSelectedMagasin] = useState('all'); // 'all' ou magasin.id
    const [loading, setLoading] = useState(false);
    const [excluded, setExcluded] = useState({});
    const currentMagasinRef = useRef('all');

    const fetchMagasins = useCallback(async () => {
        try {
            const resp = await client.get('/magasins');
            const d = resp.data?.data ?? resp.data;
            const list = Array.isArray(d) ? d : (Array.isArray(d?.data) ? d.data : []);
            setMagasins(list);
        } catch (e) {
            setMagasins([]);
        }
    }, []);

    const fetchProduits = useCallback(async (magasinId = 'all') => {
        try {
            setLoading(true);
            let page = 1;
            let all = [];
            let last = 1;
            const params = { page, per_page: 50 };
            if (magasinId && magasinId !== 'all') {
                params.magasin_id = magasinId;
            }
            do {
                params.page = page;
                const resp = await client.get('/produits', { params });
                const payload = resp.data?.data;
                let list = [];
                if (Array.isArray(payload)) {
                    list = payload;
                    last = 1;
                } else if (payload && Array.isArray(payload.data)) {
                    list = payload.data;
                    last = payload.last_page ?? 1;
                }
                all = [...all, ...list];
                page += 1;
            } while (page <= last);
            setProduits(all);
        } catch (e) {
            setProduits([]);
        } finally {
            setLoading(false);
        }
    }, []);

    const openSim = () => {
        setExcluded({});
        setSelectedMagasin('all');
        currentMagasinRef.current = 'all';
        setOpen(true);
        fetchMagasins();
        fetchProduits('all');
    };

    const handleSelectMagasin = (id) => {
        if (currentMagasinRef.current === id) return;
        currentMagasinRef.current = id;
        setSelectedMagasin(id);
        setExcluded({});
        fetchProduits(id);
    };

    const safe = Array.isArray(produits) ? produits : [];
    const simulable = safe.filter(p =>
        (Number(p.stock) || 0) > 0 || (Number(p.stock_cartouches) || 0) > 0
    );

    const computeLine = (p) => {
        const cartons = Number(p.stock ?? p.stock_actuel ?? 0);
        const cartouches = Number(p.stock_cartouches ?? 0);
        const prixCarton = Number(p.prix_vente_conseille || p.prix || 0);
        let line = cartons * prixCarton;
        if (p.a_cartouche) {
            const prixCartouche = Number(p.prix_cartouche_effectif || p.prix_cartouche || 0);
            line += cartouches * prixCartouche;
        }
        return line;
    };

    const totalCA = simulable
        .filter(p => !excluded[p.id])
        .reduce((sum, p) => sum + computeLine(p), 0);

    const selectedMagasinNom = selectedMagasin === 'all'
        ? 'Tous les dépôts'
        : (magasins.find(m => String(m.id) === String(selectedMagasin))?.nom ?? 'Dépôt');

    return (
        <>
            <TouchableOpacity style={styles.btnSimPill} onPress={openSim} activeOpacity={0.88}>
                <Icon name="calculator-outline" size={18} color="#FFFFFF" />
                <Text style={styles.btnSimPillText}>Stimulateur CA</Text>
            </TouchableOpacity>

            <Modal visible={open} animationType="slide" transparent onRequestClose={() => setOpen(false)}>
                <View style={styles.simOverlay}>
                    <View style={styles.simCard}>
                        {/* Header */}
                        <View style={styles.simHeader}>
                            <View style={{ flex: 1 }}>
                                <Text style={styles.modalTitle}>Stimulateur de CA</Text>
                                <Text style={styles.simSub}>Stock × Prix conseillé. Filtrez par dépôt.</Text>
                            </View>
                            <TouchableOpacity onPress={() => setOpen(false)} style={styles.simClose}>
                                <Icon name="close" size={24} color={Colors.text} />
                            </TouchableOpacity>
                        </View>

                        {/* Sélecteur de magasin */}
                        {magasins.length > 0 && (
                            <View style={styles.magasinSection}>
                                <Text style={styles.magasinLabel}>
                                    <Icon name="storefront-outline" size={13} color={Colors.textLight} /> Dépôt simulé :
                                </Text>
                                <ScrollView
                                    horizontal
                                    showsHorizontalScrollIndicator={false}
                                    contentContainerStyle={styles.magasinScrollContent}
                                >
                                    {/* Pill "Tous" */}
                                    <TouchableOpacity
                                        style={[styles.magasinPill, selectedMagasin === 'all' && styles.magasinPillActive]}
                                        onPress={() => handleSelectMagasin('all')}
                                        activeOpacity={0.75}
                                    >
                                        <Text style={[styles.magasinPillText, selectedMagasin === 'all' && styles.magasinPillTextActive]}>
                                            Tous les dépôts
                                        </Text>
                                    </TouchableOpacity>
                                    {magasins.map(m => {
                                        const isActive = String(selectedMagasin) === String(m.id);
                                        return (
                                            <TouchableOpacity
                                                key={m.id}
                                                style={[styles.magasinPill, isActive && styles.magasinPillActive]}
                                                onPress={() => handleSelectMagasin(String(m.id))}
                                                activeOpacity={0.75}
                                            >
                                                <Icon
                                                    name="storefront-outline"
                                                    size={12}
                                                    color={isActive ? '#FFFFFF' : Colors.textLight}
                                                    style={{ marginRight: 4 }}
                                                />
                                                <Text style={[styles.magasinPillText, isActive && styles.magasinPillTextActive]}>
                                                    {m.nom}
                                                </Text>
                                            </TouchableOpacity>
                                        );
                                    })}
                                </ScrollView>
                            </View>
                        )}

                        {/* Actions rapides */}
                        <View style={styles.simToolbar}>
                            <TouchableOpacity style={styles.simToolbarBtn} onPress={() => setExcluded(Object.fromEntries(simulable.map(p => [p.id, true])))}>
                                <Text style={styles.simToolbarBtnText}>Tout retirer</Text>
                            </TouchableOpacity>
                            <TouchableOpacity style={styles.simToolbarBtn} onPress={() => setExcluded({})}>
                                <Text style={styles.simToolbarBtnText}>Tout ajouter</Text>
                            </TouchableOpacity>
                        </View>

                        {/* Liste produits */}
                        {loading ? (
                            <View style={styles.centerLoader}>
                                <ActivityIndicator size="large" color={Colors.primary} />
                                <Text style={styles.loadingText}>Chargement du stock {selectedMagasin !== 'all' ? `du ${selectedMagasinNom}` : 'global'}…</Text>
                            </View>
                        ) : (
                            <FlatList
                                data={simulable}
                                keyExtractor={(item) => item.id.toString()}
                                style={{ flex: 1 }}
                                contentContainerStyle={styles.simList}
                                ListEmptyComponent={
                                    <View style={styles.emptyBox}>
                                        <Icon name="cube-outline" size={50} color={Colors.border} />
                                        <Text style={styles.emptyTitle}>Aucun produit en stock</Text>
                                        <Text style={styles.emptySubtitle}>
                                            {selectedMagasin !== 'all' ? `Le dépôt « ${selectedMagasinNom} » est vide.` : 'Aucun produit n\'a de stock.'}
                                        </Text>
                                    </View>
                                }
                                renderItem={({ item }) => {
                                    const isExcluded = !!excluded[item.id];
                                    const stock = Number(item.stock ?? item.stock_actuel ?? 0);
                                    const cartouchesRestantes = Number(item.stock_cartouches ?? 0);
                                    const prixCarton = Number(item.prix_vente_conseille || item.prix || 0);
                                    const prixCartouche = Number(item.prix_cartouche_effectif || item.prix_cartouche || 0);
                                    const line = computeLine(item);

                                    if (isExcluded) {
                                        return (
                                            <View style={[styles.simRow, styles.simRowExcluded]}>
                                                <View style={{ flex: 1, marginRight: 8 }}>
                                                    <Text style={styles.simName} numberOfLines={1}>{item.nom}</Text>
                                                    <Text style={styles.simExcludedText}>Retiré du simulateur</Text>
                                                </View>
                                                <TouchableOpacity style={styles.simRestore} onPress={() => setExcluded(prev => { const n = { ...prev }; delete n[item.id]; return n; })}>
                                                    <Text style={styles.simRestoreText}>Réintégrer</Text>
                                                </TouchableOpacity>
                                            </View>
                                        );
                                    }

                                    return (
                                        <View style={styles.simRow}>
                                            <View style={{ flex: 1, marginRight: 8 }}>
                                                <Text style={styles.simName} numberOfLines={1}>{item.nom}</Text>
                                                <Text style={styles.simPrice}>
                                                    {stock} Carton{(stock > 1) ? 's' : ''} × {formatMoney(prixCarton)}
                                                    {item.a_cartouche && cartouchesRestantes > 0 ? `   •   ${cartouchesRestantes} Cartouche${(cartouchesRestantes > 1) ? 's' : ''} × ${formatMoney(prixCartouche)}` : ''}
                                                </Text>
                                            </View>
                                            <TouchableOpacity style={styles.simRemove} onPress={() => setExcluded(prev => ({ ...prev, [item.id]: true }))}>
                                                <Icon name="close-circle" size={20} color={Colors.textLight} />
                                            </TouchableOpacity>
                                            <Text style={styles.simLine}>{formatMoney(line)}</Text>
                                        </View>
                                    );
                                }}
                            />
                        )}

                        {/* Total */}
                        <View style={styles.simTotalBar}>
                            <View>
                                <Text style={styles.simTotalLabel}>CA estimé</Text>
                                <Text style={styles.simTotalDepot}>{selectedMagasinNom}</Text>
                            </View>
                            <Text style={styles.simTotalVal}>{formatMoney(totalCA)}</Text>
                        </View>
                    </View>
                </View>
            </Modal>
        </>
    );
};

const styles = StyleSheet.create({
    btnSimPill: {
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
    btnSimPillText: {
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
        height: '92%',
    },
    simHeader: {
        flexDirection: 'row',
        alignItems: 'flex-start',
        justifyContent: 'space-between',
        marginBottom: 8,
    },
    simClose: { padding: 4, marginLeft: 8 },
    modalTitle: { fontSize: 17, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    simSub: {
        fontSize: 12,
        fontFamily: 'PlusJakartaSans_400Regular',
        color: Colors.textLight,
        marginTop: 2,
    },
    // ─── Sélecteur Magasin ───
    magasinSection: {
        marginBottom: 8,
    },
    magasinLabel: {
        fontSize: 12,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.textLight,
        marginBottom: 6,
    },
    magasinScrollContent: {
        flexDirection: 'row',
        gap: 8,
        paddingBottom: 4,
    },
    magasinPill: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 12,
        paddingVertical: 6,
        borderRadius: 16,
        borderWidth: 1.5,
        borderColor: Colors.border,
        backgroundColor: Colors.background,
    },
    magasinPillActive: {
        backgroundColor: Colors.primary,
        borderColor: Colors.primary,
    },
    magasinPillText: {
        fontSize: 12,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.textLight,
    },
    magasinPillTextActive: {
        color: '#FFFFFF',
    },
    // ─── Toolbar ───
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
    simTotalDepot: { fontSize: 11, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 2 },
    simTotalVal: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.primary },
    emptyBox: { alignItems: 'center', paddingVertical: 60, gap: 10 },
    emptyTitle: { fontSize: 16, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text },
    emptySubtitle: { fontSize: 13, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, textAlign: 'center' },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center', gap: 12 },
    loadingText: { fontSize: 13, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight },
});

export default StimulateurCA;
