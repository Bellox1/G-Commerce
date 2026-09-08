import React, { useState, useEffect, useCallback } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    TextInput, ActivityIndicator, Alert, StatusBar
} from 'react-native';
import KeyboardAwareScrollView from '../../components/KeyboardAwareScrollView';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

const CreateTransfertScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const [magasins, setMagasins] = useState([]);
    const [produits, setProduits] = useState([]);
    const [loadingData, setLoadingData] = useState(true);

    const [source, setSource] = useState(null);
    const [destination, setDestination] = useState(null);
    const [search, setSearch] = useState('');
    const [lines, setLines] = useState([]); // [{ key, produit_id, nom, stock, quantite }]
    const [motif, setMotif] = useState('');
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        fetchFormData();
    }, []);

    const fetchFormData = async () => {
        try {
            const res = await client.get('/transferts/form');
            const data = res.data?.data || {};
            let magList = data.magasins || [];

            // Fallback : si /transferts/form retourne des magasins vides, on les charge directement
            if (magList.length === 0) {
                try {
                    const mRes = await client.get('/magasins');
                    magList = mRes.data?.data || mRes.data || [];
                    if (!Array.isArray(magList)) magList = [];
                } catch (_) {}
            }

            setMagasins(magList);
            if (magList.length > 0) setSource(magList[0].id);
            if (magList.length > 1) setDestination(magList[1].id);
            setProduits(data.produits || []);
        } catch (e) {
            console.error('Error fetching form data for transfert:', e);
            // Fallback total : essayer /magasins directement
            try {
                const mRes = await client.get('/magasins');
                const magList = Array.isArray(mRes.data?.data) ? mRes.data.data :
                                Array.isArray(mRes.data) ? mRes.data : [];
                setMagasins(magList);
                if (magList.length > 0) setSource(magList[0].id);
                if (magList.length > 1) setDestination(magList[1].id);
            } catch (_) {
                Alert.alert('Erreur', 'Impossible de charger les dépôts. Vérifiez votre connexion.');
            }
        } finally {
            setLoadingData(false);
        }
    };

    const normalizeText = (s) => (s || '').toString().toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim();

    const handleSelectSource = (id) => {
        setSource(id);
        if (destination === id) {
            const other = magasins.find(m => m.id !== id);
            if (other) setDestination(other.id);
        }
        setLines(prev => prev.map(l => {
            const p = produits.find(px => px.id === l.produit_id);
            const newSt = p ? (p.stocks?.[id] ?? 0) : l.stock;
            return { ...l, stock: newSt };
        }));
    };

    const handleSelectDestination = (id) => {
        if (source === id) {
            Alert.alert('Attention', 'Le dépôt destination doit être différent du dépôt source.');
            return;
        }
        setDestination(id);
    };

    const sourceStock = useCallback((p) => (source ? (p.stocks?.[source] ?? 0) : 0), [source]);

    const filteredProduits = produits.filter(p => {
        return !search || normalizeText(p.nom).includes(normalizeText(search));
    });

    const lineProduitIds = lines.map(l => l.produit_id);

    const addLine = (p) => {
        if (lineProduitIds.includes(p.id)) return;
        const st = sourceStock(p);
        if (st <= 0) {
            Alert.alert('Stock épuisé', `Le produit "${p.nom}" n'a aucun stock dans le dépôt source sélectionné.`);
            return;
        }
        setLines(prev => [
            ...prev,
            { key: `${p.id}_${Date.now()}`, produit_id: p.id, nom: p.nom, stock: st, quantite: 1 }
        ]);
    };

    const updateQty = (key, t) => {
        const cleaned = (t || '').replace(/[^0-9]/g, '');
        setLines(prev => prev.map(l => l.key === key ? { ...l, quantite: cleaned } : l));
    };

    const handleBlurQty = (key, raw) => {
        const v = parseInt(raw || '1', 10);
        setLines(prev => prev.map(l => l.key === key ? { ...l, quantite: isNaN(v) || v <= 0 ? 1 : v } : l));
    };

    const removeLine = (key) => {
        setLines(prev => prev.filter(l => l.key !== key));
    };

    const handleSubmit = async () => {
        if (!source || !destination) {
            Alert.alert('Erreur', 'Veuillez sélectionner le magasin source et le magasin de destination.');
            return;
        }
        if (source === destination) {
            Alert.alert('Erreur', 'Le magasin source et la destination doivent être différents.');
            return;
        }
        if (lines.length === 0) {
            Alert.alert('Erreur', 'Veuillez ajouter au moins un produit à transférer.');
            return;
        }
        const produitsPayload = lines.map(l => ({ produit_id: Number(l.produit_id), quantite: l.quantite }));

        setSubmitting(true);
        try {
            await client.post('/transferts', {
                magasin_source_id: source,
                magasin_destination_id: destination,
                produits: produitsPayload,
                notes: motif || null
            });

            Alert.alert('Succès', 'Transfert de stock effectué avec succès !');
            navigation.goBack();
        } catch (e) {
            const msg = e.response?.data?.message || 'Erreur lors du transfert de stock';
            Alert.alert('Erreur', msg);
        } finally {
            setSubmitting(false);
        }
    };

    if (loadingData) {
        return (
            <View style={styles.centerLoader}>
                <ActivityIndicator size="large" color={Colors.primary} />
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
                <Text style={styles.topTitle}>Nouveau Transfert</Text>
                <View style={{ width: 36 }} />
            </View>

            <KeyboardAwareScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>


                {/* 1. Magasin Source */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>1. Dépôt de Départ (Source)</Text>
                    <View style={{ gap: 8 }}>
                        {magasins.map(m => {
                            const isSelected = source === m.id;
                            return (
                                <TouchableOpacity
                                    key={m.id}
                                    style={[styles.magasinCardItem, isSelected && styles.magasinCardItemActive]}
                                    onPress={() => handleSelectSource(m.id)}
                                    activeOpacity={0.7}
                                >
                                    <Ionicons name={isSelected ? "radio-button-on" : "radio-button-off"} size={20} color={isSelected ? Colors.primary : Colors.textLight} />
                                    <Ionicons name="storefront-outline" size={18} color={isSelected ? Colors.primary : Colors.textLight} style={{ marginLeft: 6 }} />
                                    <Text style={[styles.magasinCardName, isSelected && styles.magasinCardNameActive]}>{m.nom}</Text>
                                    {isSelected && <Text style={{ fontSize: 11, color: Colors.primary, fontFamily: 'Poppins_600SemiBold' }}>Sélectionné</Text>}
                                </TouchableOpacity>
                            );
                        })}
                    </View>
                </View>

                {/* 2. Magasin Destination */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>2. Dépôt d'Arrivée (Destination)</Text>
                    <View style={{ gap: 8 }}>
                        {magasins.map(m => {
                            const isSource = source === m.id;
                            const isSelected = destination === m.id;
                            return (
                                <TouchableOpacity
                                    key={m.id}
                                    style={[styles.magasinCardItem, isSelected && styles.magasinCardItemActive, isSource && { opacity: 0.45, backgroundColor: '#F1F5F9' }]}
                                    onPress={() => handleSelectDestination(m.id)}
                                    disabled={isSource}
                                    activeOpacity={0.7}
                                >
                                    <Ionicons name={isSelected ? "radio-button-on" : "radio-button-off"} size={20} color={isSelected ? Colors.primary : Colors.textLight} />
                                    <Ionicons name="storefront-outline" size={18} color={isSelected ? Colors.primary : Colors.textLight} style={{ marginLeft: 6 }} />
                                    <Text style={[styles.magasinCardName, isSelected && styles.magasinCardNameActive]}>
                                        {m.nom} {isSource ? '(Dépôt source)' : ''}
                                    </Text>
                                    {isSelected && <Text style={{ fontSize: 11, color: Colors.primary, fontFamily: 'Poppins_600SemiBold' }}>Sélectionné</Text>}
                                </TouchableOpacity>
                            );
                        })}
                    </View>
                </View>

                {/* 3. Sélection des produits */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>3. Produits & Quantités</Text>
                    <Text style={styles.hintText}>Recherchez un article pour l'ajouter au transfert.</Text>

                    <View style={styles.searchBar}>
                        <Ionicons name="search" size={18} color={Colors.textLight} />
                        <TextInput
                            style={styles.searchInput}
                            placeholder="Rechercher par nom ou code..."
                            value={search}
                            onChangeText={setSearch}
                        />
                        {search ? (
                            <TouchableOpacity onPress={() => setSearch('')}>
                                <Ionicons name="close-circle" size={18} color={Colors.textLight} />
                            </TouchableOpacity>
                        ) : null}
                    </View>

                    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipRow} style={styles.prodChipScroll}>
                        {filteredProduits.length > 0 ? (
                            filteredProduits.map(p => {
                                const st = sourceStock(p);
                                const inLine = lineProduitIds.includes(p.id);
                                const disabled = !source || inLine || st <= 0;
                                return (
                                    <TouchableOpacity
                                        key={p.id}
                                        style={[styles.chip, disabled && styles.chipDisabled, st > 0 && !inLine && { borderColor: Colors.primary }]}
                                        onPress={() => addLine(p)}
                                        disabled={disabled}
                                    >
                                        <Text style={[styles.chipText, disabled && { color: Colors.textLight }]} numberOfLines={1}>
                                            {p.nom} ({st} dispo)
                                        </Text>
                                    </TouchableOpacity>
                                );
                            })
                        ) : (
                            <Text style={styles.emptyText}>Aucun produit disponible</Text>
                        )}
                    </ScrollView>

                    {lines.length === 0 ? (
                        <View style={styles.emptyCart}>
                            <Ionicons name="cart-outline" size={36} color={Colors.border} />
                            <Text style={styles.emptyCartText}>Ajoutez des produits ci-dessus pour composer ce transfert.</Text>
                        </View>
                    ) : (
                        lines.map(item => (
                            <View key={item.key} style={styles.ligneRow}>
                                <View style={styles.ligneTop}>
                                    <Text style={styles.ligneName}>{item.nom}</Text>
                                    <TouchableOpacity onPress={() => removeLine(item.key)} style={styles.trashBtn}>
                                        <Ionicons name="trash-outline" size={20} color={Colors.error} />
                                    </TouchableOpacity>
                                </View>
                                <Text style={styles.ligneStock}>Stock disponible : {item.stock}</Text>
                                <View style={styles.ligneQtyRow}>
                                    <Text style={styles.qtyLabel}>Quantité (cartons)</Text>
                                    <TextInput
                                        style={styles.qtyInput}
                                        keyboardType="number-pad"
                                        value={String(item.quantite ?? '')}
                                        onChangeText={(t) => updateQty(item.key, t)}
                                        onBlur={() => handleBlurQty(item.key, item.quantite)}
                                        placeholder="1"
                                    />
                                </View>
                            </View>
                        ))
                    )}
                </View>

                {/* 4. Note */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>4. Note / Motif</Text>
                    <TextInput
                        style={styles.input}
                        value={motif}
                        onChangeText={setMotif}
                        placeholder="ex: Réapprovisionnement boutique..."
                        multiline
                    />
                </View>

                <TouchableOpacity style={styles.submitBtn} onPress={handleSubmit} disabled={submitting}>
                    {submitting ? (
                        <ActivityIndicator color="#FFF" />
                    ) : (
                        <Text style={styles.submitBtnText}>Valider le Transfert</Text>
                    )}
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
    scrollContent: { padding: 16, paddingBottom: 40 },
    cardSection: { backgroundColor: '#FFF', borderRadius: 14, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    cardTitle: { fontSize: 15, fontFamily: 'Poppins_700Bold', color: Colors.primary, marginBottom: 12 },
    input: { borderWidth: 1, borderColor: Colors.border, borderRadius: 8, padding: 10, fontSize: 14, backgroundColor: '#f8fafc', minHeight: 44, textAlignVertical: 'top' },
    chipRow: { gap: 8 },
    chip: { paddingHorizontal: 14, paddingVertical: 8, borderRadius: 20, backgroundColor: Colors.surface, borderWidth: 1, borderColor: Colors.border, alignItems: 'center', minWidth: 96 },
    chipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    chipText: { fontSize: 13, fontFamily: 'Poppins_500Medium', color: Colors.text },
    chipTextActive: { color: '#FFF', fontFamily: 'Poppins_700Bold' },
    chipDisabled: { backgroundColor: '#f1f5f9', opacity: 0.6 },
    prodChipScroll: { marginTop: 8, marginBottom: 4 },
    searchBar: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 12, paddingHorizontal: 12, paddingVertical: 10, marginBottom: 12 },
    searchInput: { flex: 1, marginLeft: 8, fontFamily: 'Poppins_400Regular', fontSize: 13, color: Colors.text },
    emptyText: { fontSize: 13, color: Colors.textLight, paddingVertical: 8 },
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
    submitBtn: { backgroundColor: Colors.primary, paddingVertical: 16, borderRadius: 12, alignItems: 'center', marginTop: 10 },
    submitBtnText: { color: '#FFF', fontWeight: '800', fontSize: 15 },
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

export default CreateTransfertScreen;
