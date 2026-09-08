import React, { useState, useEffect, useCallback } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity, TextInput,
    ActivityIndicator, Alert, StatusBar
} from 'react-native';
import KeyboardAwareScrollView from '../../components/KeyboardAwareScrollView';
import { useRoute } from '@react-navigation/core';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { Header } from '../../components/ui';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

const TransfertEditScreen = ({ navigation }) => {
    const route = useRoute();
    const insets = useSafeAreaInsets();
    const { id } = route.params || {};

    const [loading, setLoading] = useState(true);
    const [magasins, setMagasins] = useState([]);
    const [produits, setProduits] = useState([]);
    const [produitsJson, setProduitsJson] = useState([]);
    const [sourceId, setSourceId] = useState('');
    const [destId, setDestId] = useState('');
    const [notes, setNotes] = useState('');
    const [search, setSearch] = useState('');
    const [lines, setLines] = useState([]); // [{ produit_id, nom, stock, quantite }]
    const [submitting, setSubmitting] = useState(false);

    const getStock = useCallback((produitId, srcId, pj) => {
        const list = pj || produitsJson;
        const m = list.find(x => x.id === produitId);
        if (!m) return 0;
        return m.stockParMagasin ? (m.stockParMagasin[srcId] || 0) : (m.stocks ? (m.stocks[srcId] || 0) : 0);
    }, [produitsJson]);

    const fetchData = useCallback(async () => {
        try {
            const resp = await client.get(`/transferts/${id}/edit`);
            const d = resp.data?.data || resp.data;
            const t = d.transfert || {};
            const pj = d.produitsJson || [];
            setMagasins(d.magasins || []);
            setProduits(d.produits || []);
            setProduitsJson(pj);
            setSourceId(String(t.magasin_source_id || (d.magasins?.[0]?.id ?? '')));
            setDestId(String(t.magasin_destination_id || (d.magasins?.[1]?.id ?? '')));
            setNotes(t.notes || '');
            const initLines = (t.produits || []).map(p => ({
                produit_id: p.produit_id,
                nom: p.produit?.nom || 'Produit',
                stock: getStock(p.produit_id, t.magasin_source_id, pj),
                quantite: p.quantite,
            }));
            setLines(initLines);
        } catch (e) {
            const msg = e.response?.data?.message || 'Seuls les transferts en transit peuvent être modifiés.';
            Alert.alert('Modification impossible', msg);
            navigation.goBack();
        } finally {
            setLoading(false);
        }
    }, [id, getStock]);

    useEffect(() => {
        if (id) fetchData();
    }, [id, fetchData]);

    const stockDispo = (produitId) => getStock(produitId, sourceId, produitsJson);

    const lineProduitIds = lines.map(l => l.produit_id);

    const addProduit = (p) => {
        if (lineProduitIds.includes(p.id)) return;
        setLines(prev => [
            ...prev,
            { produit_id: p.id, nom: p.nom, stock: stockDispo(p.id), quantite: 1 }
        ]);
    };

    const updateQty = (produitId, t) => {
        const cleaned = (t || '').replace(/[^0-9]/g, '');
        setLines(prev => prev.map(l => l.produit_id === produitId ? { ...l, quantite: cleaned } : l));
    };

    const handleBlurQty = (produitId, raw) => {
        const v = parseInt(raw || '1', 10);
        setLines(prev => prev.map(l => l.produit_id === produitId ? { ...l, quantite: isNaN(v) || v <= 0 ? 1 : v } : l));
    };

    const removeLine = (produitId) => setLines(prev => prev.filter(l => l.produit_id !== produitId));

    const handleSave = async () => {
        if (!sourceId || !destId || sourceId === destId) {
            Alert.alert('Erreur', 'Veuillez choisir deux magasins différents.');
            return;
        }
        if (lines.length === 0) {
            Alert.alert('Erreur', 'Ajoutez au moins un produit à transférer.');
            return;
        }
        for (const l of lines) {
            if (l.quantite > stockDispo(l.produit_id)) {
                Alert.alert('Erreur', `Stock insuffisant pour ${l.nom} (${stockDispo(l.produit_id)} dispo).`);
                return;
            }
        }
        setSubmitting(true);
        try {
            await client.put(`/transferts/${id}`, {
                magasin_source_id: sourceId,
                magasin_destination_id: destId,
                notes,
                produits: lines.map(l => ({ produit_id: l.produit_id, quantite: l.quantite })),
            });
            Alert.alert('Succès', 'Transfert mis à jour avec succès.');
            navigation.navigate('TransfertShow', { id });
        } catch (e) {
            Alert.alert('Erreur', e.response?.data?.message || 'Impossible de mettre à jour le transfert.');
        } finally {
            setSubmitting(false);
        }
    };

    if (loading) {
        return (
            <View style={styles.container}>
                <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                    <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                        <Ionicons name="arrow-back" size={20} color={Colors.text} />
                    </TouchableOpacity>
                    <Text style={styles.topTitle}>Modifier le Transfert</Text>
                    <View style={{ width: 36 }} />
                </View>
                <View style={styles.centerLoader}>
                    <ActivityIndicator size="large" color={Colors.primary} />
                </View>
            </View>
        );
    }

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            {/* Top Bar */}
            <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={20} color={Colors.text} />
                </TouchableOpacity>
                <Text style={styles.topTitle}>Modifier le Transfert</Text>
                <View style={{ width: 36 }} />
            </View>

            <KeyboardAwareScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>

                <View style={styles.card}>
                    <Text style={styles.cardTitle}>Magasins</Text>

                    <Text style={styles.label}>1. Dépôt de Départ (Source)</Text>
                    <View style={{ gap: 8, marginBottom: 12 }}>
                        {magasins.map(m => {
                            const isSelected = sourceId === String(m.id);
                            return (
                                <TouchableOpacity
                                    key={m.id}
                                    style={[styles.magasinCardItem, isSelected && styles.magasinCardItemActive]}
                                    onPress={() => setSourceId(String(m.id))}
                                    activeOpacity={0.7}
                                >
                                    <Ionicons name={isSelected ? 'radio-button-on' : 'radio-button-off'} size={20} color={isSelected ? Colors.primary : Colors.textLight} />
                                    <Ionicons name="storefront-outline" size={18} color={isSelected ? Colors.primary : Colors.textLight} style={{ marginLeft: 6 }} />
                                    <Text style={[styles.magasinCardName, isSelected && styles.magasinCardNameActive]}>{m.nom}</Text>
                                    {isSelected && <Text style={{ fontSize: 11, color: Colors.primary, fontFamily: 'Poppins_600SemiBold' }}>Sélectionné</Text>}
                                </TouchableOpacity>
                            );
                        })}
                    </View>

                    <Text style={styles.label}>2. Dépôt d'Arrivée (Destination)</Text>
                    <View style={{ gap: 8, marginBottom: 12 }}>
                        {magasins.map(m => {
                            const isSource = sourceId === String(m.id);
                            const isSelected = destId === String(m.id);
                            return (
                                <TouchableOpacity
                                    key={m.id}
                                    style={[styles.magasinCardItem, isSelected && styles.magasinCardItemActive, isSource && { opacity: 0.45, backgroundColor: '#F1F5F9' }]}
                                    onPress={() => {
                                        if (isSource) {
                                            Alert.alert('Attention', 'Ce dépôt est déjà le dépôt source.');
                                            return;
                                        }
                                        setDestId(String(m.id));
                                    }}
                                    disabled={isSource}
                                    activeOpacity={0.7}
                                >
                                    <Ionicons name={isSelected ? 'radio-button-on' : 'radio-button-off'} size={20} color={isSelected ? Colors.primary : Colors.textLight} />
                                    <Ionicons name="storefront-outline" size={18} color={isSelected ? Colors.primary : Colors.textLight} style={{ marginLeft: 6 }} />
                                    <Text style={[styles.magasinCardName, isSelected && styles.magasinCardNameActive]}>
                                        {m.nom} {isSource ? '(Dépôt source)' : ''}
                                    </Text>
                                    {isSelected && <Text style={{ fontSize: 11, color: Colors.primary, fontFamily: 'Poppins_600SemiBold' }}>Sélectionné</Text>}
                                </TouchableOpacity>
                            );
                        })}
                    </View>

                    <Text style={styles.label}>Notes (optionnel)</Text>
                    <TextInput
                        style={styles.input}
                        value={notes}
                        onChangeText={setNotes}
                        placeholder="Motif ou commentaire..."
                        multiline
                    />
                </View>

                <View style={styles.card}>
                    <Text style={styles.cardTitle}>Produits à transférer</Text>
                    <View style={styles.searchBar}>
                        <Ionicons name="search" size={18} color={Colors.textLight} />
                        <TextInput
                            style={styles.searchInput}
                            value={search}
                            onChangeText={setSearch}
                            placeholder="Rechercher un produit..."
                            placeholderTextColor={Colors.textLight}
                        />
                        {search ? (
                            <TouchableOpacity onPress={() => setSearch('')}>
                                <Ionicons name="close-circle" size={18} color={Colors.textLight} />
                            </TouchableOpacity>
                        ) : null}
                    </View>

                    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipRow} style={styles.prodScroll}>
                        {produits
                            .filter(p => !search || (p.nom || '').toLowerCase().includes(search.toLowerCase()))
                            .map(p => {
                                const dispo = stockDispo(p.id);
                                const inLine = lineProduitIds.includes(p.id);
                                return (
                                    <TouchableOpacity
                                        key={p.id}
                                        style={[styles.prodChip, (dispo <= 0 || inLine) && styles.prodChipDisabled]}
                                        onPress={() => addProduit(p)}
                                        disabled={dispo <= 0 || inLine}
                                    >
                                        <Text style={[styles.prodChipText]}>{p.nom}</Text>
                                        <Text style={styles.prodChipStock}>{dispo} dispo</Text>
                                    </TouchableOpacity>
                                );
                            })}
                    </ScrollView>

                    {lines.length === 0 ? (
                        <View style={styles.emptyCart}>
                            <Ionicons name="cart-outline" size={36} color={Colors.border} />
                            <Text style={styles.emptyCartText}>Ajoutez des produits ci-dessus pour composer ce transfert.</Text>
                        </View>
                    ) : (
                        lines.map(l => (
                            <View key={l.produit_id} style={styles.ligneRow}>
                                <View style={styles.ligneTop}>
                                    <Text style={styles.ligneName}>{l.nom}</Text>
                                    <TouchableOpacity onPress={() => removeLine(l.produit_id)} style={styles.trashBtn}>
                                        <Ionicons name="trash-outline" size={20} color={Colors.error} />
                                    </TouchableOpacity>
                                </View>
                                <Text style={styles.ligneStock}>Stock disponible : {l.stock}</Text>
                                <View style={styles.ligneQtyRow}>
                                    <Text style={styles.qtyLabel}>Quantité (cartons)</Text>
                                    <TextInput
                                        style={styles.qtyInput}
                                        keyboardType="number-pad"
                                        value={String(l.quantite ?? '')}
                                        onChangeText={(t) => updateQty(l.produit_id, t)}
                                        onBlur={() => handleBlurQty(l.produit_id, l.quantite)}
                                        placeholder="1"
                                    />
                                </View>
                            </View>
                        ))
                    )}
                </View>

                <TouchableOpacity style={styles.submitBtn} onPress={handleSave} disabled={submitting}>
                    {submitting ? <ActivityIndicator color="#FFF" /> : <Text style={styles.submitBtnText}>Enregistrer les modifications</Text>}
                </TouchableOpacity>
            </KeyboardAwareScrollView>

        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    topBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: '#FFFFFF', paddingHorizontal: 16, paddingBottom: 12, borderBottomWidth: 1, borderBottomColor: '#E2E8F0' },
    backBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center' },
    topTitle: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    scrollContent: { padding: 16, paddingBottom: 30 },
    card: { backgroundColor: Colors.surface, borderRadius: 14, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    cardTitle: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.primary, marginBottom: 12 },
    label: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text, marginTop: 8, marginBottom: 4 },
    input: { backgroundColor: Colors.background, borderRadius: 10, paddingHorizontal: 12, paddingVertical: 10, fontSize: 14, borderWidth: 1, borderColor: Colors.border, marginTop: 4, textAlignVertical: 'top' },
    chipRow: { gap: 8 },
    chip: { paddingHorizontal: 12, paddingVertical: 6, borderRadius: 16, backgroundColor: Colors.background, borderWidth: 1, borderColor: Colors.border, alignItems: 'center', minWidth: 90 },
    chipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    chipText: { fontSize: 12, fontFamily: 'Poppins_500Medium', color: Colors.text },
    chipTextActive: { color: '#FFF' },
    searchBar: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 12, paddingHorizontal: 12, paddingVertical: 10, marginBottom: 12 },
    searchInput: { flex: 1, marginLeft: 8, fontFamily: 'Poppins_400Regular', fontSize: 13, color: Colors.text },
    prodScroll: { maxHeight: 140 },
    prodChip: { paddingHorizontal: 12, paddingVertical: 8, borderRadius: 12, backgroundColor: Colors.background, borderWidth: 1, borderColor: Colors.border, alignItems: 'center', minWidth: 90 },
    prodChipDisabled: { opacity: 0.4 },
    prodChipText: { fontSize: 12, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    prodChipStock: { fontSize: 10, color: Colors.textLight, marginTop: 2 },
    emptyCart: { backgroundColor: Colors.background, borderRadius: 14, padding: 20, alignItems: 'center', gap: 8, borderWidth: 1, borderColor: Colors.border, marginTop: 12 },
    emptyCartText: { fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.textLight, textAlign: 'center' },
    ligneRow: { backgroundColor: Colors.background, borderRadius: 14, padding: 12, marginBottom: 10, borderWidth: 1, borderColor: Colors.border },
    ligneTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 },
    ligneName: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.text, flex: 1 },
    ligneStock: { fontSize: 11, fontFamily: 'Poppins_500Medium', color: Colors.textLight, marginBottom: 8 },
    ligneQtyRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    qtyLabel: { fontSize: 12, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    qtyInput: { borderWidth: 1, borderColor: Colors.primary, borderRadius: 10, paddingHorizontal: 14, paddingVertical: 8, fontSize: 15, fontFamily: 'Poppins_700Bold', color: Colors.text, width: 110, textAlign: 'center', backgroundColor: '#fff' },
    trashBtn: { padding: 6, justifyContent: 'center' },
    submitBtn: { backgroundColor: Colors.primary, borderRadius: 14, paddingVertical: 14, alignItems: 'center', marginTop: 8 },
    submitBtnText: { color: '#FFF', fontSize: 15, fontFamily: 'Poppins_700Bold' },
    magasinCardItem: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 14,
        paddingVertical: 12,
        borderRadius: 12,
        backgroundColor: '#F8FAFC',
        borderWidth: 1,
        borderColor: '#E2E8F0',
    },
    magasinCardItemActive: {
        backgroundColor: '#EFF6FF',
        borderColor: Colors.primary,
    },
    magasinCardName: {
        fontSize: 14,
        fontFamily: 'Poppins_500Medium',
        color: Colors.text,
        marginLeft: 8,
        flex: 1,
    },
    magasinCardNameActive: {
        fontFamily: 'Poppins_700Bold',
        color: Colors.primary,
    },
});

export default TransfertEditScreen;
