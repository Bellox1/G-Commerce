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
            const magList = data.magasins || [];
            setMagasins(magList);
            if (magList.length > 0) setSource(magList[0].id);
            if (magList.length > 1) setDestination(magList[1].id);
            setProduits(data.produits || []);
        } catch (e) {
            console.error('Error fetching form data for transfert:', e);
            Alert.alert('Erreur', 'Impossible de charger les données.');
        } finally {
            setLoadingData(false);
        }
    };

    const normalizeText = (s) => (s || '').toString().toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim();

    const sourceStock = useCallback((p) => (source ? (p.stocks?.[source] ?? 0) : 0), [source]);

    const filteredProduits = produits.filter(p => {
        const matchesSearch = !search || normalizeText(p.nom).includes(normalizeText(search));
        const hasStock = source ? sourceStock(p) > 0 : true;
        return matchesSearch && hasStock;
    });

    const lineProduitIds = lines.map(l => l.produit_id);

    const addLine = (p) => {
        if (lineProduitIds.includes(p.id)) return;
        setLines(prev => [
            ...prev,
            { key: `${p.id}_${Date.now()}`, produit_id: p.id, nom: p.nom, stock: sourceStock(p), quantite: 1 }
        ]);
    };

    const updateQty = (key, t) => {
        const v = parseInt(t || '0', 10);
        setLines(prev => prev.map(l => l.key === key ? { ...l, quantite: Math.max(1, isNaN(v) ? 1 : v) } : l));
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
                    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipRow}>
                        {magasins.map(m => (
                            <TouchableOpacity
                                key={m.id}
                                style={[styles.chip, source === m.id && styles.chipActive]}
                                onPress={() => setSource(m.id)}
                            >
                                <Text style={[styles.chipText, source === m.id && styles.chipTextActive]}>{m.nom}</Text>
                            </TouchableOpacity>
                        ))}
                    </ScrollView>
                </View>

                {/* 2. Magasin Destination */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>2. Dépôt d'Arrivée (Destination)</Text>
                    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipRow}>
                        {magasins.map(m => (
                            <TouchableOpacity
                                key={m.id}
                                style={[styles.chip, destination === m.id && styles.chipActive]}
                                onPress={() => setDestination(m.id)}
                            >
                                <Text style={[styles.chipText, destination === m.id && styles.chipTextActive]}>{m.nom}</Text>
                            </TouchableOpacity>
                        ))}
                    </ScrollView>
                </View>

                {/* 3. Sélection des produits */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>3. Produits & Quantités</Text>
                    <Text style={styles.hintText}>Recherchez un article pour l'ajouter au transfert.</Text>

                    <View style={styles.searchBox}>
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
                                const inLine = lineProduitIds.includes(p.id);
                                return (
                                    <TouchableOpacity
                                        key={p.id}
                                        style={[styles.chip, inLine && styles.chipDisabled]}
                                        onPress={() => addLine(p)}
                                        disabled={!source || inLine}
                                    >
                                        <Text style={[styles.chipText, inLine && { color: Colors.textLight }]} numberOfLines={1}>
                                            {p.nom}
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
                                        value={String(item.quantite)}
                                        onChangeText={(t) => updateQty(item.key, t)}
                                        placeholder="0"
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
    searchBar: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#f1f5f9', borderRadius: 12, paddingHorizontal: 12, paddingVertical: 10, marginBottom: 8 },
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
});

export default CreateTransfertScreen;
