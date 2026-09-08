import React, { useState, useEffect } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    TextInput, ActivityIndicator, Alert, Modal, StatusBar,
    KeyboardAvoidingView, Platform
} from 'react-native';
import KeyboardAwareScrollView from '../../components/KeyboardAwareScrollView';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useRoute } from '@react-navigation/core';
import { Header } from '../../components/ui';

const DEVISES = [
    { code: 'XOF', label: 'FCFA', sym: 'FCFA' },
    { code: 'NGN', label: 'Naira', sym: '₦' },
    { code: 'EUR', label: 'Euro', sym: '€' },
    { code: 'USD', label: 'Dollar', sym: '$' },
    { code: 'CNY', label: 'Yuan (Chine)', sym: '¥' },
    { code: 'AUTRE', label: 'Autre', sym: '' },
];

const ArrivageEditScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const route = useRoute();
    const { item, id } = route.params || {};

    const [magasins, setMagasins] = useState([]);
    const [fournisseurs, setFournisseurs] = useState([]);
    const [produits, setProduits] = useState([]);
    const [allProduits, setAllProduits] = useState([]);
    const [produitSearch, setProduitSearch] = useState('');
    const [loadingData, setLoadingData] = useState(true);

    const [selectedMagasin, setSelectedMagasin] = useState(null);
    const [tauxChange, setTauxChange] = useState('0.65');
    const [selectedDevise, setSelectedDevise] = useState('NGN');
    const [tauxSource, setTauxSource] = useState('');

    const [fraisTransport, setFraisTransport] = useState('0');
    const [fraisDouane, setFraisDouane] = useState('0');
    const [fraisManutention, setFraisManutention] = useState('0');
    const [autresFrais, setAutresFrais] = useState('0');

    const [lignes, setLignes] = useState([
        { produit_id: null, quantite: '10', prix_unitaire_origine: '5000' }
    ]);

    const [submitting, setSubmitting] = useState(false);

    // Modal création fournisseur
    const [showFournModal, setShowFournModal] = useState(false);
    const [fournSaving, setFournSaving] = useState(false);
    const [newFourn, setNewFourn] = useState({ nom: '', pays: 'Nigeria', ville: '', telephone: '', devise: 'NGN' });

    const createFournisseur = async () => {
        if (!newFourn.nom.trim()) {
            Alert.alert('Erreur', 'Le nom du fournisseur est obligatoire.');
            return;
        }
        setFournSaving(true);
        try {
            const res = await client.post('/fournisseurs', {
                nom: newFourn.nom.trim(),
                pays: newFourn.pays.trim() || 'Nigeria',
                ville: newFourn.ville.trim(),
                telephone: newFourn.telephone.trim(),
                devise: newFourn.devise
            });
            const created = res.data?.data || res.data;
            const id = created?.id ?? created?.fournisseur?.id;
            if (id) {
                const fresh = { id, nom: newFourn.nom.trim() };
                setFournisseurs(prev => [...prev, fresh]);
                setShowFournModal(false);
                setNewFourn({ nom: '', pays: 'Nigeria', ville: '', telephone: '', devise: 'NGN' });
            }
        } catch (e) {
            const msg = e.response?.data?.message || 'Erreur lors de la création du fournisseur';
            Alert.alert('Erreur', msg);
        } finally {
            setFournSaving(false);
        }
    };

    useEffect(() => {
        fetchFormData();
    }, []);

    const selectDevise = async (code) => {
        setSelectedDevise(code);
        if (code === 'XOF') {
            setTauxChange('1');
            setTauxSource('FCFA = monnaie locale : aucun taux à définir.');
            return;
        }
        if (code === 'AUTRE') {
            setTauxSource('Devise personnalisée — saisissez le taux manuellement.');
            return;
        }
        try {
            const res = await client.get('/taux-change', { params: { from: code } });
            const r = res.data || {};
            if (r.rate) {
                setTauxChange(String(r.rate));
                setTauxSource(r.message || 'Taux de marché en temps réel (modifiable).');
            } else {
                setTauxSource(r.message || 'Taux indisponible — saisissez-le manuellement.');
            }
        } catch (e) {
            setTauxSource('Taux indisponible (hors ligne) — saisissez-le manuellement.');
        }
    };

    const fetchFormData = async () => {
        try {
            const [mRes, fRes, pRes] = await Promise.all([
                client.get('/magasins'),
                client.get('/fournisseurs').catch(() => ({ data: [] })),
                client.get('/produits', { params: { per_page: 1000 } })
            ]);

            const magList = mRes.data?.data || (Array.isArray(mRes.data) ? mRes.data : []);
            setMagasins(magList);

            const fournList = fRes.data?.data || (Array.isArray(fRes.data) ? fRes.data : []);
            setFournisseurs(fournList);

            const prodList = pRes.data?.data?.data || pRes.data?.data || (Array.isArray(pRes.data) ? pRes.data : []);
            setProduits(prodList);
            setAllProduits(prodList);

            // Pré-remplissage depuis l'item ou le GET show
            let d = item;
            if (!d || !d.produits) {
                const res = await client.get(`/arrivages/${id || item?.id}`);
                d = res.data?.data || res.data;
            }

            if (d) {
                const magId = d.magasin_id ?? magList[0]?.id ?? null;
                setSelectedMagasin(magId);

                setTauxChange(d.taux_change ? String(d.taux_change) : '0.65');
                setSelectedDevise(d.devise_origine || 'NGN');
                setTauxSource('Taux enregistré — changez la devise pour le taux de marché en temps réel.');
                setFraisTransport(String(Math.round(Number(d.frais_transport ?? 0))));
                setFraisDouane(String(Math.round(Number(d.frais_douane ?? 0))));
                setFraisManutention(String(Math.round(Number(d.frais_manutention ?? 0))));
                setAutresFrais(String(Math.round(Number(d.frais_divers ?? 0))));

                const loadedLignes = (d.produits || []).map(l => ({
                    produit_id: l.produit_id,
                    quantite: String(Math.round(Number(l.quantite || 1))),
                    prix_unitaire_origine: String(Math.round(Number(l.prix_unitaire_origine || 0))),
                    fournisseur_id: l.fournisseur_id ?? null
                }));
                if (loadedLignes.length > 0) setLignes(loadedLignes);
            } else {
                if (magList.length > 0) setSelectedMagasin(magList[0].id);
                if (prodList.length > 0) setLignes([{ produit_id: null, quantite: '10', prix_unitaire_origine: '5000', fournisseur_id: null }]);
            }
        } catch (e) {
            console.error('Error fetching data for arrivage edit:', e);
        } finally {
            setLoadingData(false);
        }
    };

    const addLigne = () => {
        setLignes([...lignes, { produit_id: null, quantite: '1', prix_unitaire_origine: '0', fournisseur_id: null }]);
    };

    const removeLigne = (index) => {
        if (lignes.length === 1) {
            Alert.alert('Attention', 'Vous devez avoir au moins un article dans l\'arrivage.');
            return;
        }
        setLignes(lignes.filter((_, i) => i !== index));
    };

    const updateLigne = (index, field, value) => {
        const updated = [...lignes];
        updated[index][field] = value;
        setLignes(updated);
    };

    const handleSubmit = async () => {
        if (!selectedMagasin) {
            Alert.alert('Erreur', 'Veuillez choisir un magasin de réception.');
            return;
        }

        const validLignes = lignes.filter(l => l.produit_id && Number(l.quantite) > 0);
        if (validLignes.length === 0) {
            Alert.alert('Erreur', 'Veuillez saisir au moins un article avec une quantité valide.');
            return;
        }

        setSubmitting(true);
        try {
            await client.put(`/arrivages/${id || item?.id}`, {
                magasin_id: selectedMagasin,
                devise_origine: selectedDevise,
                taux_change_naira_cfa: Number(tauxChange) || 0.65,
                frais_transport_cfa: Number(fraisTransport) || 0,
                frais_douane_cfa: Number(fraisDouane) || 0,
                frais_manutention_cfa: Number(fraisManutention) || 0,
                autres_frais_cfa: Number(autresFrais) || 0,
                produits: validLignes.map(l => ({
                    produit_id: l.produit_id,
                    quantite: Number(l.quantite),
                    prix_unitaire_origine: Number(l.prix_unitaire_origine),
                    fournisseur_id: l.fournisseur_id ?? null
                }))
            });

            Alert.alert('Succès', 'Arrivage modifié avec succès !');
            navigation.goBack();
        } catch (e) {
            const msg = e.response?.data?.message || 'Erreur lors de la modification de l\'arrivage';
            Alert.alert('Erreur', msg);
        } finally {
            setSubmitting(false);
        }
    };

    if (loadingData) {
        return (
            <View style={styles.container}>
                <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                    <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                        <Ionicons name="arrow-back" size={20} color={Colors.text} />
                    </TouchableOpacity>
                    <Text style={styles.topTitle}>Modifier Arrivage</Text>
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
                <Text style={styles.topTitle}>Modifier Arrivage</Text>
                <View style={{ width: 36 }} />
            </View>

            <KeyboardAwareScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>


                {/* 1. Logistique & Dépôt */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>1. Logistique & Destination</Text>

                    <Text style={styles.fieldLabel}>Magasin de réception *</Text>
                    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipRow}>
                        {magasins.map(m => (
                            <TouchableOpacity
                                key={m.id}
                                style={[styles.chip, selectedMagasin === m.id && styles.chipActive]}
                                onPress={() => setSelectedMagasin(m.id)}
                            >
                                <Text style={[styles.chipText, selectedMagasin === m.id && styles.chipTextActive]}>{m.nom}</Text>
                            </TouchableOpacity>
                        ))}
                    </ScrollView>

                    <Text style={styles.fieldLabel}>Devise d'origine</Text>
                    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipRow}>
                        {DEVISES.map(d => (
                            <TouchableOpacity
                                key={d.code}
                                style={[styles.chip, selectedDevise === d.code && styles.chipActive]}
                                onPress={() => selectDevise(d.code)}
                            >
                                <Text style={[styles.chipText, selectedDevise === d.code && styles.chipTextActive]}>
                                    {d.label}{d.sym ? ` (${d.sym})` : ''}
                                </Text>
                            </TouchableOpacity>
                        ))}
                    </ScrollView>

                    <Text style={styles.fieldLabel}>Taux de change ({DEVISES.find(d => d.code === selectedDevise)?.sym || 'origine'} → FCFA)</Text>
                    <TextInput
                        style={[styles.input, selectedDevise === 'XOF' && { backgroundColor: '#F1F5F9', color: Colors.textLight }]}
                        keyboardType="numeric"
                        value={selectedDevise === 'XOF' ? '1' : tauxChange}
                        onChangeText={setTauxChange}
                        editable={selectedDevise !== 'XOF'}
                        placeholder="0.65"
                    />
                    <Text style={styles.hintText}>Ex: 0.65 signifie 1 000 {DEVISES.find(d => d.code === selectedDevise)?.sym || 'unité'} = 650 FCFA</Text>
                    {tauxSource ? <Text style={[styles.hintText, { color: Colors.primary, marginTop: 4 }]}>{tauxSource}</Text> : null}
                </View>

                {/* 2. Produits de l'arrivage */}
                <View style={styles.cardSection}>
                    <View style={[styles.labelRow, { marginTop: 0, marginBottom: 6 }]}>
                        <Text style={styles.cardTitle}>2. Articles réceptionnés</Text>
                        <TouchableOpacity style={styles.addMiniBtn} onPress={() => setShowFournModal(true)}>
                            <Ionicons name="add" size={14} color={Colors.primary} />
                            <Text style={styles.addMiniBtnText}>Nouveau fournisseur</Text>
                        </TouchableOpacity>
                    </View>

                    <View style={styles.searchBox}>
                        <Ionicons name="search" size={16} color={Colors.textLight} />
                        <TextInput
                            style={styles.searchInput}
                            value={produitSearch}
                            onChangeText={(val) => {
                                setProduitSearch(val);
                                const q = val.toLowerCase();
                                setProduits(q
                                    ? allProduits.filter(p => (p.nom || '').toLowerCase().includes(q))
                                    : allProduits);
                            }}
                            placeholder="Rechercher un produit (si vous connaissez le nom)"
                        />
                        {produitSearch ? (
                            <TouchableOpacity onPress={() => { setProduitSearch(''); setProduits(allProduits); }}>
                                <Ionicons name="close-circle" size={16} color={Colors.textLight} />
                            </TouchableOpacity>
                        ) : null}
                    </View>

                    {lignes.map((l, idx) => (
                        <View key={idx} style={styles.ligneBox}>
                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }}>
                                <Text style={styles.ligneNum}>Article #{idx + 1}</Text>
                                <TouchableOpacity onPress={() => removeLigne(idx)}>
                                    <Ionicons name="trash-outline" size={18} color={Colors.error} />
                                </TouchableOpacity>
                            </View>

                            <Text style={styles.fieldLabel}>Sélectionner le produit</Text>
                            <View style={styles.selectedProd}>
                                <Ionicons name={l.produit_id ? 'checkmark-circle' : 'alert-circle'} size={16} color={l.produit_id ? Colors.success : Colors.textLight} />
                                <Text style={styles.selectedProdText}>
                                    {l.produit_id ? (allProduits.find(p => p.id === l.produit_id)?.nom || 'Produit sélectionné') : 'Aucun produit sélectionné'}
                                </Text>
                            </View>
                            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipRow}>
                                {produits.slice().sort((a, b) => (a.nom || '').localeCompare(b.nom || '')).map(p => (
                                    <TouchableOpacity
                                        key={p.id}
                                        style={[styles.chipMini, l.produit_id === p.id && styles.chipMiniActive]}
                                        onPress={() => updateLigne(idx, 'produit_id', p.id)}
                                    >
                                        <Text style={[styles.chipMiniText, l.produit_id === p.id && styles.chipMiniTextActive]}>{p.nom}</Text>
                                    </TouchableOpacity>
                                ))}
                            </ScrollView>

                            <Text style={styles.fieldLabel}>Fournisseur de cet article (optionnel)</Text>
                            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipRow}>
                                <TouchableOpacity
                                    style={[styles.chipMini, l.fournisseur_id === null && styles.chipMiniActive]}
                                    onPress={() => updateLigne(idx, 'fournisseur_id', null)}
                                >
                                    <Text style={[styles.chipMiniText, l.fournisseur_id === null && styles.chipMiniTextActive]}>Aucun</Text>
                                </TouchableOpacity>
                                {fournisseurs.map(f => (
                                    <TouchableOpacity
                                        key={f.id}
                                        style={[styles.chipMini, l.fournisseur_id === f.id && styles.chipMiniActive]}
                                        onPress={() => updateLigne(idx, 'fournisseur_id', f.id)}
                                    >
                                        <Text style={[styles.chipMiniText, l.fournisseur_id === f.id && styles.chipMiniTextActive]}>{f.nom}</Text>
                                    </TouchableOpacity>
                                ))}
                            </ScrollView>

                            <View style={{ flexDirection: 'row', gap: 10, marginTop: 8 }}>
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.fieldLabel}>Quantité (cartons)</Text>
                                    <TextInput
                                        style={styles.input}
                                        keyboardType="numeric"
                                        value={l.quantite}
                                        onChangeText={val => updateLigne(idx, 'quantite', val)}
                                        placeholder="Ex: 50"
                                    />
                                </View>
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.fieldLabel}>Prix U. (Naira ₦)</Text>
                                    <TextInput
                                        style={styles.input}
                                        keyboardType="numeric"
                                        value={l.prix_unitaire_origine}
                                        onChangeText={val => updateLigne(idx, 'prix_unitaire_origine', val)}
                                        placeholder="Ex: 5000"
                                    />
                                </View>
                            </View>
                        </View>
                    ))}
                </View>

                <TouchableOpacity onPress={addLigne} style={styles.btnAddLigneBottom}>
                    <Ionicons name="add" size={18} color={Colors.primary} />
                    <Text style={styles.btnAddLigneBottomText}>Ajouter un article</Text>
                </TouchableOpacity>

                {/* 3. Frais Logistiques (FCFA) */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>3. Frais de Transport & Route (FCFA)</Text>

                    <Text style={styles.fieldLabel}>Frais de Transport</Text>
                    <TextInput style={styles.input} keyboardType="numeric" value={fraisTransport} onChangeText={setFraisTransport} placeholder="0" />

                    <Text style={styles.fieldLabel}>Douanes & Taxes route</Text>
                    <TextInput style={styles.input} keyboardType="numeric" value={fraisDouane} onChangeText={setFraisDouane} placeholder="0" />

                    <Text style={styles.fieldLabel}>Manutention / Déchargement</Text>
                    <TextInput style={styles.input} keyboardType="numeric" value={fraisManutention} onChangeText={setFraisManutention} placeholder="0" />

                    <Text style={styles.fieldLabel}>Autres frais divers</Text>
                    <TextInput style={styles.input} keyboardType="numeric" value={autresFrais} onChangeText={setAutresFrais} placeholder="0" />
                </View>

                <TouchableOpacity style={styles.submitBtn} onPress={handleSubmit} disabled={submitting}>
                    {submitting ? (
                        <ActivityIndicator color="#FFF" />
                    ) : (
                        <Text style={styles.submitBtnText}>Enregistrer les modifications</Text>
                    )}
                </TouchableOpacity>

            </KeyboardAwareScrollView>


            {/* Modal Création Fournisseur */}
            <Modal visible={showFournModal} transparent animationType="slide" onRequestClose={() => setShowFournModal(false)}>
                <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={{ flex: 1 }}>
                <View style={styles.modalOverlay}>
                    <View style={styles.modalCard}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Nouveau Fournisseur</Text>
                            <TouchableOpacity onPress={() => setShowFournModal(false)}>
                                <Ionicons name="close" size={24} color={Colors.text} />
                            </TouchableOpacity>
                        </View>

                        <Text style={styles.fieldLabel}>Nom *</Text>
                        <TextInput style={styles.input} value={newFourn.nom} onChangeText={v => setNewFourn({ ...newFourn, nom: v })} placeholder="Ex: Lagos Trade Center" />

                        <View style={{ flexDirection: 'row', gap: 10 }}>
                            <View style={{ flex: 1 }}>
                                <Text style={styles.fieldLabel}>Pays</Text>
                                <TextInput style={styles.input} value={newFourn.pays} onChangeText={v => setNewFourn({ ...newFourn, pays: v })} placeholder="Nigeria" />
                            </View>
                            <View style={{ flex: 1 }}>
                                <Text style={styles.fieldLabel}>Ville</Text>
                                <TextInput style={styles.input} value={newFourn.ville} onChangeText={v => setNewFourn({ ...newFourn, ville: v })} placeholder="Lagos" />
                            </View>
                        </View>

                        <Text style={styles.fieldLabel}>Téléphone</Text>
                        <TextInput style={styles.input} value={newFourn.telephone} onChangeText={v => setNewFourn({ ...newFourn, telephone: v })} placeholder="+234..." />

                        <TouchableOpacity style={styles.submitBtn} onPress={createFournisseur} disabled={fournSaving}>
                            {fournSaving ? <ActivityIndicator color="#FFF" /> : <Text style={styles.submitBtnText}>Créer le fournisseur</Text>}
                        </TouchableOpacity>
                    </View>
                </View>

                </KeyboardAvoidingView>
            </Modal>
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
    fieldLabel: { fontSize: 12, fontWeight: '600', color: Colors.text, marginTop: 10, marginBottom: 4 },
    input: { borderWidth: 1, borderColor: Colors.border, borderRadius: 8, padding: 10, fontSize: 14, backgroundColor: '#f8fafc' },
    hintText: { fontSize: 11, color: Colors.textLight, marginTop: 4 },
    chipRow: { gap: 8, marginVertical: 6 },
    chip: { paddingHorizontal: 14, paddingVertical: 8, borderRadius: 8, backgroundColor: '#f1f5f9' },
    chipActive: { backgroundColor: Colors.primary },
    chipText: { fontSize: 12, fontWeight: '600', color: Colors.text },
    chipTextActive: { color: '#FFF' },
    chipMini: { paddingHorizontal: 10, paddingVertical: 6, borderRadius: 6, backgroundColor: '#f1f5f9' },
    chipMiniActive: { backgroundColor: Colors.secondary },
    chipMiniText: { fontSize: 11, fontWeight: '600', color: Colors.text },
    chipMiniTextActive: { color: '#FFF' },
    btnAddLigneBottom: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6, backgroundColor: '#eef2ff', borderWidth: 1, borderColor: Colors.primary, borderStyle: 'dashed', paddingVertical: 14, borderRadius: 12, marginBottom: 16 },
    btnAddLigneBottomText: { color: Colors.primary, fontSize: 14, fontWeight: '700' },
    labelRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 10 },
    addMiniBtn: { flexDirection: 'row', alignItems: 'center', gap: 3, backgroundColor: '#eef2ff', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6 },
    addMiniBtnText: { color: Colors.primary, fontSize: 11, fontWeight: '700' },
    searchBox: { flexDirection: 'row', alignItems: 'center', gap: 6, borderWidth: 1, borderColor: Colors.border, borderRadius: 8, paddingHorizontal: 10, backgroundColor: '#f8fafc', marginVertical: 6 },
    searchInput: { flex: 1, paddingVertical: 8, fontSize: 13 },
    ligneBox: { backgroundColor: '#f8fafc', borderRadius: 10, padding: 12, borderWidth: 1, borderColor: Colors.border, marginBottom: 10 },
    selectedProd: { flexDirection: 'row', alignItems: 'center', gap: 6, marginBottom: 6 },
    selectedProdText: { fontSize: 13, fontWeight: '700', color: Colors.text },
    ligneNum: { fontSize: 12, fontWeight: '700', color: Colors.primary },
    submitBtn: { backgroundColor: Colors.primary, paddingVertical: 16, borderRadius: 12, alignItems: 'center', marginTop: 10 },
    submitBtnText: { color: '#FFF', fontWeight: '800', fontSize: 15 },
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
    modalCard: { backgroundColor: Colors.surface, borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
    modalTitle: { fontSize: 18, fontFamily: 'Poppins_700Bold', color: Colors.primary },
});

export default ArrivageEditScreen;
