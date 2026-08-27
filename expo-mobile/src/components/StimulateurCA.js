import React, { useState, useCallback } from 'react';
import {
    View, Text, StyleSheet, TouchableOpacity, Modal, FlatList
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
    const [loading, setLoading] = useState(false);
    const [excluded, setExcluded] = useState({});

    const fetchProduits = useCallback(async () => {
        try {
            setLoading(true);
            let page = 1;
            let all = [];
            let last = 1;
            do {
                const resp = await client.get('/produits', { params: { page, per_page: 50 } });
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
        setOpen(true);
        fetchProduits();
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

    return (
        <>
            <TouchableOpacity style={styles.btnSimPill} onPress={openSim} activeOpacity={0.88}>
                <Icon name="calculator-outline" size={18} color="#FFFFFF" />
                <Text style={styles.btnSimPillText}>Stimulateur CA</Text>
            </TouchableOpacity>

            <Modal visible={open} animationType="slide" transparent onRequestClose={() => setOpen(false)}>
                <View style={styles.simOverlay}>
                    <View style={styles.simCard}>
                        <View style={styles.simHeader}>
                            <View style={{ flex: 1 }}>
                                <Text style={styles.modalTitle}>Stimulateur de CA</Text>
                                <Text style={styles.simSub}>Les produits sans stock sont ignorés. Total = Σ (stock × prix).</Text>
                            </View>
                            <TouchableOpacity onPress={() => setOpen(false)} style={styles.simClose}>
                                <Icon name="close" size={24} color={Colors.text} />
                            </TouchableOpacity>
                        </View>

                        <View style={styles.simToolbar}>
                            <TouchableOpacity style={styles.simToolbarBtn} onPress={() => setExcluded(Object.fromEntries(simulable.map(p => [p.id, true])))}>
                                <Text style={styles.simToolbarBtnText}>Tout retirer</Text>
                            </TouchableOpacity>
                            <TouchableOpacity style={styles.simToolbarBtn} onPress={() => setExcluded({})}>
                                <Text style={styles.simToolbarBtnText}>Tout ajouter</Text>
                            </TouchableOpacity>
                        </View>

                        <FlatList
                            data={simulable}
                            keyExtractor={(item) => item.id.toString()}
                            style={{ flex: 1 }}
                            contentContainerStyle={styles.simList}
                            ListEmptyComponent={
                                <View style={styles.emptyBox}>
                                    <Icon name="cube-outline" size={50} color={Colors.border} />
                                    <Text style={styles.emptyTitle}>Aucun produit en stock</Text>
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

                        <View style={styles.simTotalBar}>
                            <Text style={styles.simTotalLabel}>Chiffre d'affaires estimé</Text>
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
    emptyBox: { alignItems: 'center', paddingVertical: 60, gap: 10 },
    emptyTitle: { fontSize: 16, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text },
});

export default StimulateurCA;
