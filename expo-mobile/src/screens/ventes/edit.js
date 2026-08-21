import React, { useState, useEffect } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity, Image,
    TextInput, ActivityIndicator, Alert, Modal, StatusBar, Platform
} from 'react-native';
import KeyboardAwareScrollView from '../../components/KeyboardAwareScrollView';
import DateTimePicker from '@react-native-community/datetimepicker';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { getImageUrl } from '../../utils/image';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useRoute } from '@react-navigation/core';
import { formatDateFr, formatDateTimeFr } from '../../utils/formatDate';

const formatMoney = (val) => {
    if (val === null || val === undefined || val === '') return '0 F';
    return Math.round(Number(val)).toLocaleString('fr-FR') + ' F';
};

const normalizeText = (s) => (s || '')
    .toString()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .trim();

const uid = () => `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;

const cartoucheFallback = (produit) => {
    const conseille = produit.prix_vente_conseille || produit.prix || 0;
    const cartPar = produit.cartouche_par_carton || 1;
    return produit.prix_cartouche || Math.ceil((conseille / cartPar) / 100) * 100;
};

const lineTotal = (l) => (l.prix_vente * l.quantite);

const ECHEANCE_OPTIONS = [
    { key: 'today', label: "Aujourd'hui", days: 0 },
    { key: 'tomorrow', label: 'Demain', days: 1 },
    { key: 'week', label: 'Dans 1 semaine', days: 7 },
    { key: 'month', label: 'Dans 1 mois', days: 30 },
];

const ProduitRow = ({ produit, onAdd }) => {
    const hasCartouche = !!produit.a_cartouche;
    const [expanded, setExpanded] = useState(false);
    const [carton, setCarton] = useState(1);
    const [cartouche, setCartouche] = useState(0);
    const [prixC, setPrixC] = useState(produit.prix_vente_conseille || produit.prix || 0);
    const [prixK, setPrixK] = useState(cartoucheFallback(produit));

    const quickAdd = () => {
        onAdd({
            produit_id: produit.id,
            nom: produit.nom,
            image: produit.image,
            prix_vente: produit.prix_vente_conseille || produit.prix || 0,
            prix_cartouche: hasCartouche ? cartoucheFallback(produit) : null,
            quantite: 1,
            quantite_cartouche: 0,
            hasCartouche,
        });
    };

    const handleAdd = () => {
        if (carton <= 0 && (!hasCartouche || cartouche <= 0)) {
            Alert.alert('Erreur', 'Veuillez saisir une quantité (carton ou cartouche).');
            return;
        }
        onAdd({
            produit_id: produit.id,
            nom: produit.nom,
            image: produit.image,
            prix_vente: prixC,
            prix_cartouche: hasCartouche ? prixK : null,
            quantite: carton,
            quantite_cartouche: hasCartouche ? cartouche : 0,
            hasCartouche,
        });
        setExpanded(false);
    };

    return (
        <View style={styles.prodRowWrap}>
            <View style={styles.prodRowItem}>
                <TouchableOpacity style={{ flex: 1, flexDirection: 'row', alignItems: 'center' }} onPress={() => setExpanded(e => !e)}>
                    {getImageUrl(produit.image) ? (
                        <Image source={{ uri: getImageUrl(produit.image) }} style={styles.prodThumb} resizeMode="cover" />
                    ) : (
                        <View style={styles.prodThumb}><Ionicons name="image-outline" size={20} color={Colors.textLight} /></View>
                    )}
                    <View style={{ flex: 1 }}>
                        <Text style={styles.prodRowName} numberOfLines={1}>{produit.nom}</Text>
                        <Text style={styles.prodRowPrice}>
                            {formatMoney(produit.prix_vente_conseille || produit.prix)} / carton
                            {hasCartouche && produit.prix_cartouche ? `  ·  ${formatMoney(produit.prix_cartouche)} / cartouche` : ''}
                        </Text>
                    </View>
                </TouchableOpacity>
                <TouchableOpacity style={styles.addMini} onPress={quickAdd}>
                    <Ionicons name="add" size={18} color="#FFF" />
                </TouchableOpacity>
            </View>
            {expanded && (
                <View style={styles.prodExpand}>
                    <View style={styles.lineRow}>
                        <View style={styles.qtyBlock}>
                            <Text style={styles.qtyLabel}>Cartons</Text>
                            <View style={styles.stepper}>
                                <TouchableOpacity style={styles.stepBtn} onPress={() => setCarton(Math.max(0, carton - 1))}>
                                    <Ionicons name="remove" size={16} color={Colors.primary} />
                                </TouchableOpacity>
                                <TextInput style={styles.qtyInput} keyboardType="number-pad" value={String(carton)} onChangeText={(t) => setCarton(parseInt(t || '0', 10))} />
                                <TouchableOpacity style={styles.stepBtn} onPress={() => setCarton(carton + 1)}>
                                    <Ionicons name="add" size={16} color={Colors.primary} />
                                </TouchableOpacity>
                            </View>
                        </View>
                        <View style={styles.priceBlock}>
                            <Text style={styles.qtyLabel}>Prix carton</Text>
                            <TextInput style={styles.priceInput} keyboardType="number-pad" value={String(prixC)} onChangeText={(t) => setPrixC(parseFloat(t || '0'))} />
                        </View>
                    </View>

                    {hasCartouche && (
                        <View style={styles.lineRow}>
                            <View style={styles.qtyBlock}>
                                <Text style={styles.qtyLabel}>Cartouches</Text>
                                <View style={styles.stepper}>
                                    <TouchableOpacity style={styles.stepBtn} onPress={() => setCartouche(Math.max(0, cartouche - 1))}>
                                        <Ionicons name="remove" size={16} color={Colors.primary} />
                                    </TouchableOpacity>
                                    <TextInput style={styles.qtyInput} keyboardType="number-pad" value={String(cartouche)} onChangeText={(t) => setCartouche(parseInt(t || '0', 10))} />
                                    <TouchableOpacity style={styles.stepBtn} onPress={() => setCartouche(cartouche + 1)}>
                                        <Ionicons name="add" size={16} color={Colors.primary} />
                                    </TouchableOpacity>
                                </View>
                            </View>
                            <View style={styles.priceBlock}>
                                <Text style={styles.qtyLabel}>Prix cartouche</Text>
                                <TextInput style={styles.priceInput} keyboardType="number-pad" value={String(prixK)} onChangeText={(t) => setPrixK(parseFloat(t || '0'))} />
                            </View>
                        </View>
                    )}

                    <TouchableOpacity style={styles.submitBtn} onPress={handleAdd}>
                        <Text style={styles.submitBtnText}>Ajouter à la vente</Text>
                    </TouchableOpacity>
                </View>
            )}
        </View>
    );
};

const VenteEditScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const route = useRoute();
    const { item, id } = route.params || {};

    const [clients, setClients] = useState([]);
    const [magasins, setMagasins] = useState([]);
    const [produits, setProduits] = useState([]);
    const [magasinId, setMagasinId] = useState(null);
    const [magasinNom, setMagasinNom] = useState('—');
    const [loading, setLoading] = useState(true);

    const [selectedClient, setSelectedClient] = useState(null);
    const [lines, setLines] = useState([]);
    const [deletedIds, setDeletedIds] = useState([]);

    const [search, setSearch] = useState('');
    const [clientSearch, setClientSearch] = useState('');
    const [showClientModal, setShowClientModal] = useState(false);
    const [newClientNom, setNewClientNom] = useState('');
    const [newClientTel, setNewClientTel] = useState('');
    const [newClientAdresse, setNewClientAdresse] = useState('');

    const [aCredit, setACredit] = useState(false);
    const [acompte, setAcompte] = useState('');
    const [montantRemis, setMontantRemis] = useState('');
    const [dateEcheance, setDateEcheance] = useState(null);
    const [showDatePicker, setShowDatePicker] = useState(false);

    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        fetchInitialData();
    }, []);

    const fetchInitialData = async () => {
        try {
            const [cRes, mRes, pRes] = await Promise.all([
                client.get('/clients'),
                client.get('/magasins'),
                client.get('/produits', { params: { per_page: 1000 } })
            ]);

            setClients(cRes.data?.data || (Array.isArray(cRes.data) ? cRes.data : []));

            const magList = mRes.data?.data || (Array.isArray(mRes.data) ? mRes.data : []);
            setMagasins(magList);

            setProduits(pRes.data?.data?.data || pRes.data?.data || (Array.isArray(pRes.data) ? pRes.data : []));

            let d = item;
            if (!d || !d.lignes) {
                const res = await client.get(`/ventes/${id || item?.id}`);
                d = res.data?.data || res.data;
            }

            if (d) {
                const mag = magList.find(m => m.id === (d.magasin_id ?? null));
                setMagasinNom(mag ? mag.nom : '—');
                setMagasinId(d.magasin_id ?? (magList[0]?.id ?? null));

                setSelectedClient(d.client_id ?? null);

                const produitById = {};
                (pRes.data?.data?.data || pRes.data?.data || []).forEach(p => { produitById[p.id] = p; });

                const initialLines = (d.lignes || []).map(l => {
                    const p = l.produit || produitById[l.produit_id] || {};
                    return {
                        id: l.id,
                        produit_id: l.produit_id,
                        nom: p.nom || l.produit?.nom || 'Produit',
                        image: p.image || l.produit?.image,
                        unite: l.unite || 'carton',
                        quantite: l.quantite,
                        prix_vente: l.prix_vente || 0,
                        hasCartouche: !!p.a_cartouche,
                    };
                });
                setLines(initialLines);

                const credit = d.client_id && ['impaye', 'partiel'].includes(d.statut_paiement);
                setACredit(!!credit);
                setAcompte(credit ? String(d.montant_paye || 0) : '');
                setMontantRemis(d.montant_remis ? String(d.montant_remis) : '');
            }
        } catch (e) {
            console.error('Error fetching initial data for vente edit:', e);
        } finally {
            setLoading(false);
        }
    };

    const total = lines.reduce((s, l) => s + lineTotal(l), 0);

    const canSearchProduct = search.trim().length >= 2;
    const filteredProduits = canSearchProduct
        ? produits.filter(p => normalizeText(p.nom).includes(normalizeText(search)))
        : [];

    const canSearchClient = clientSearch.trim().length >= 2;
    const filteredClients = canSearchClient
        ? clients.filter(c => normalizeText(`${c.nom} ${c.prenom || ''}`).includes(normalizeText(clientSearch)))
        : [];

    const isCustomEcheance = !!dateEcheance && !ECHEANCE_OPTIONS.some(o => {
        const dt = new Date();
        dt.setDate(dt.getDate() + o.days);
        return dateEcheance === dt.toISOString().split('T')[0];
    });

    const findRow = (produitId, unite) => lines.find(l => l.produit_id === produitId && l.unite === unite);

    const appendRow = (row) => setLines(prev => [...prev, row]);

    const handleAddLine = (line) => {
        const hasCartonRow = line.quantite > 0;
        const hasCartoucheRow = line.hasCartouche && line.quantite_cartouche > 0;

        if (hasCartonRow) {
            const existing = findRow(line.produit_id, 'carton');
            if (existing) {
                updateRow(existing.produit_id, 'carton', 'quantite', existing.quantite + line.quantite);
            } else {
                appendRow({
                    id: null, produit_id: line.produit_id, nom: line.nom, image: line.image,
                    unite: 'carton', quantite: line.quantite, prix_vente: line.prix_vente, hasCartouche: line.hasCartouche,
                });
            }
        }

        if (hasCartoucheRow) {
            const existing = findRow(line.produit_id, 'cartouche');
            if (existing) {
                updateRow(existing.produit_id, 'cartouche', 'quantite', existing.quantite + line.quantite_cartouche);
            } else {
                appendRow({
                    id: null, produit_id: line.produit_id, nom: line.nom, image: line.image,
                    unite: 'cartouche', quantite: line.quantite_cartouche, prix_vente: line.prix_cartouche, hasCartouche: line.hasCartouche,
                });
            }
        }
    };

    const updateRow = (produitId, unite, key, value) => {
        setLines(prev => prev.map(l =>
            (l.produit_id === produitId && l.unite === unite) ? { ...l, [key]: value } : l
        ));
    };

    const removeRow = (row) => {
        if (row.id) {
            setDeletedIds(prev => prev.includes(row.id) ? prev : [...prev, row.id]);
        }
        setLines(prev => prev.filter(l => !(l.produit_id === row.produit_id && l.unite === row.unite)));
    };

    const openCreateClient = () => {
        setNewClientNom(clientSearch.trim());
        setNewClientTel('');
        setNewClientAdresse('');
        setShowClientModal(true);
    };

    const handleCreateClient = () => {
        const name = (newClientNom || '').trim();
        const tel = (newClientTel || '').trim();
        if (!name) {
            Alert.alert('Erreur', 'Le nom du client est requis.');
            return;
        }
        (async () => {
            try {
                const resp = await client.post('/clients', {
                    nom: name,
                    telephone: tel,
                    adresse: (newClientAdresse || '').trim(),
                });
                const created = resp.data?.client || null;
                let cid = null;
                if (created && created.id) {
                    cid = created.id;
                    setClients(prev => prev.some(c => c.id === created.id) ? prev : [...prev, created]);
                } else {
                    const r = await client.get('/clients');
                    const list = r.data?.data || (Array.isArray(r.data) ? r.data : []);
                    setClients(list);
                    const found = list.find(c => (c.nom || '').toLowerCase() === name.toLowerCase());
                    cid = found ? found.id : null;
                }
                if (cid) setSelectedClient(cid);
                setShowClientModal(false);
                setClientSearch('');
                setNewClientNom('');
                setNewClientTel('');
                setNewClientAdresse('');
            } catch (e) {
                Alert.alert('Erreur', e.response?.data?.message || 'Impossible de créer le client.');
            }
        })();
    };

    const handleSubmit = async () => {
        if (lines.length === 0) {
            Alert.alert('Erreur', 'La vente ne contient aucun article.');
            return;
        }
        if (aCredit && !selectedClient) {
            Alert.alert('Erreur', 'Un crédit nécessite de sélectionner un client.');
            return;
        }
        if (!selectedClient && !aCredit) {
            const remis = montantRemis ? parseFloat(montantRemis) : null;
            if (remis === null || remis < total) {
                Alert.alert('Erreur', `Vente anonyme : le montant remis doit couvrir le total (${formatMoney(total)}).`);
                return;
            }
        }

        const payload = {
            client_id: selectedClient || null,
            magasin_id: magasinId,
            montant_paye: aCredit ? (parseFloat(acompte) || 0) : total,
            montant_remis: aCredit ? null : (montantRemis ? parseFloat(montantRemis) : null),
            lignes_existantes: {},
            new_lignes: [],
            lignes_supprimees: deletedIds,
        };

        lines.forEach(l => {
            if (l.id) {
                payload.lignes_existantes[l.id] = { quantite: l.quantite, prix_vente: l.prix_vente };
            } else {
                payload.new_lignes.push({
                    produit_id: l.produit_id,
                    quantite: l.quantite,
                    prix_vente: l.prix_vente,
                    unite: l.unite,
                });
            }
        });

        setSubmitting(true);
        try {
            await client.put(`/ventes/${id || item?.id}`, payload);
            Alert.alert('Succès', 'Vente modifiée avec succès !');
            navigation.goBack();
        } catch (e) {
            const msg = e.response?.data?.message || 'Erreur lors de la modification de la vente';
            Alert.alert('Erreur', msg);
        } finally {
            setSubmitting(false);
        }
    };

    // Grouper les lignes par produit pour l'affichage
    const groups = [];
    const groupMap = {};
    lines.forEach(l => {
        if (!groupMap[l.produit_id]) {
            groupMap[l.produit_id] = { produit_id: l.produit_id, nom: l.nom, image: l.image, hasCartouche: l.hasCartouche, carton: null, cartouche: null };
            groups.push(groupMap[l.produit_id]);
        }
        if (l.unite === 'carton') groupMap[l.produit_id].carton = l;
        else groupMap[l.produit_id].cartouche = l;
    });

    if (loading) {
        return (
            <View style={styles.container}>
                <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
                <View style={styles.header}>
                    <View style={styles.headerRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()}>
                            <Ionicons name="arrow-back" size={24} color="#FFF" />
                        </TouchableOpacity>
                        <Text style={styles.headerTitle}>Modifier Vente</Text>
                        <View style={{ width: 24 }} />
                    </View>
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

            <View style={[styles.header, { paddingTop: Math.max(insets.top, 16) }]}>
                <View style={styles.headerRow}>
                    <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                        <Ionicons name="arrow-back" size={20} color={Colors.text} />
                    </TouchableOpacity>
                    <Text style={styles.headerTitle}>Modifier Vente</Text>
                    <View style={{ width: 36 }} />
                </View>
            </View>

            <KeyboardAwareScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false} keyboardShouldPersistTaps="handled">


                {/* Magasin */}
                <View style={styles.section}>
                    <Text style={styles.sectionTitle}>Magasin de vente</Text>
                    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipRow}>
                        {magasins.map(m => (
                            <TouchableOpacity
                                key={m.id}
                                style={[styles.chip, magasinId === m.id && styles.chipActive]}
                                onPress={() => setMagasinId(m.id)}
                            >
                                <Text style={[styles.chipText, magasinId === m.id && styles.chipTextActive]}>{m.nom}</Text>
                            </TouchableOpacity>
                        ))}
                    </ScrollView>
                </View>

                {/* Client */}
                <View style={styles.sessionCard}>
                    <Text style={styles.blockTitle}>Client de cette vente</Text>
                    <View style={styles.searchBar}>
                        <Ionicons name="search" size={18} color={Colors.textLight} />
                        <TextInput
                            style={styles.searchInput}
                            placeholder="Rechercher un client..."
                            placeholderTextColor={Colors.textLight}
                            value={clientSearch}
                            onChangeText={setClientSearch}
                        />
                        {clientSearch ? (
                            <TouchableOpacity onPress={() => setClientSearch('')}>
                                <Ionicons name="close-circle" size={18} color={Colors.textLight} />
                            </TouchableOpacity>
                        ) : null}
                    </View>
                    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipRow} style={styles.prodChipScroll}>
                        <TouchableOpacity
                            style={[styles.chip, !selectedClient && styles.chipActive]}
                            onPress={() => setSelectedClient(null)}
                        >
                            <Text style={[styles.chipText, !selectedClient && styles.chipTextActive]}>Anonyme</Text>
                        </TouchableOpacity>
                        {clients
                            .slice()
                            .sort((a, b) => `${a.nom || ''} ${a.prenom || ''}`.localeCompare(`${b.nom || ''} ${b.prenom || ''}`))
                            .filter(c => !clientSearch || normalizeText(`${c.nom} ${c.prenom || ''}`).includes(normalizeText(clientSearch)))
                            .map(c => {
                                const active = selectedClient === c.id;
                                return (
                                    <TouchableOpacity
                                        key={c.id}
                                        style={[styles.chip, active && styles.chipActive]}
                                        onPress={() => { setSelectedClient(c.id); setClientSearch(''); }}
                                    >
                                        <Text style={[styles.chipText, active && styles.chipTextActive]}>{c.nom} {c.prenom || ''}</Text>
                                    </TouchableOpacity>
                                );
                            })}
                        <TouchableOpacity style={styles.chipNew} onPress={openCreateClient}>
                            <Ionicons name="add" size={16} color={Colors.primary} />
                            <Text style={styles.chipNewText}>Nouveau</Text>
                        </TouchableOpacity>
                    </ScrollView>

                    {/* Produits */}
                    <Text style={[styles.blockTitle, { marginTop: 16 }]}>Choisir les produits</Text>
                    <View style={styles.searchBar}>
                        <Ionicons name="search" size={18} color={Colors.textLight} />
                        <TextInput
                            style={styles.searchInput}
                            placeholder="Rechercher un produit..."
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
                    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipRow} style={styles.prodChipScroll}>
                        {produits
                            .slice()
                            .sort((a, b) => (a.nom || '').localeCompare(b.nom || ''))
                            .filter(p => !search || normalizeText(p.nom).includes(normalizeText(search)))
                            .map(p => {
                                const inCart = lines.some(l => l.produit_id === p.id);
                                return (
                                    <TouchableOpacity
                                        key={p.id}
                                        style={[styles.chip, inCart && styles.chipActive]}
                                        onPress={() => handleAddLine({
                                            produit_id: p.id,
                                            nom: p.nom,
                                            image: p.image,
                                            prix_vente: p.prix_vente_conseille || p.prix || 0,
                                            prix_cartouche: p.a_cartouche ? cartoucheFallback(p) : null,
                                            quantite: 1,
                                            quantite_cartouche: 0,
                                            hasCartouche: !!p.a_cartouche,
                                        })}
                                    >
                                        <Text style={[styles.chipText, inCart && styles.chipTextActive]}>{p.nom}</Text>
                                    </TouchableOpacity>
                                );
                            })}
                    </ScrollView>

                    {/* Articles */}
                    <Text style={[styles.blockTitle, { marginTop: 16 }]}>Articles ({lines.length})</Text>
                    {lines.length === 0 ? (
                        <View style={styles.emptyCart}>
                            <Ionicons name="cart-outline" size={36} color={Colors.border} />
                            <Text style={styles.emptyCartText}>Ajoutez des produits ci-dessus pour composer cette vente.</Text>
                        </View>
                    ) : (
                        groups.map(grp => (
                            <View key={grp.produit_id} style={styles.cartItem}>
                                <View style={styles.cartRowHead}>
                                    {getImageUrl(grp.image) ? (
                                        <Image source={{ uri: getImageUrl(grp.image) }} style={styles.cartThumb} resizeMode="cover" />
                                    ) : (
                                        <View style={styles.cartThumb}><Ionicons name="image-outline" size={18} color={Colors.textLight} /></View>
                                    )}
                                    <Text style={styles.cartItemName}>{grp.nom}</Text>
                                </View>

                                {grp.carton && (
                                    <View style={styles.lineRow}>
                                        <View style={styles.qtyBlock}>
                                            <Text style={styles.qtyLabel}>Cartons</Text>
                                            <View style={styles.stepper}>
                                                <TouchableOpacity style={styles.stepBtn} onPress={() => updateRow(grp.produit_id, 'carton', 'quantite', Math.max(0, grp.carton.quantite - 1))}>
                                                    <Ionicons name="remove" size={16} color={Colors.primary} />
                                                </TouchableOpacity>
                                                <TextInput style={styles.qtyInput} keyboardType="number-pad" value={String(grp.carton.quantite)} onChangeText={(t) => updateRow(grp.produit_id, 'carton', 'quantite', parseInt(t || '0', 10))} />
                                                <TouchableOpacity style={styles.stepBtn} onPress={() => updateRow(grp.produit_id, 'carton', 'quantite', grp.carton.quantite + 1)}>
                                                    <Ionicons name="add" size={16} color={Colors.primary} />
                                                </TouchableOpacity>
                                            </View>
                                        </View>
                                        <View style={styles.priceBlock}>
                                            <Text style={styles.qtyLabel}>Prix carton</Text>
                                            <TextInput style={styles.priceInput} keyboardType="number-pad" value={String(grp.carton.prix_vente)} onChangeText={(t) => updateRow(grp.produit_id, 'carton', 'prix_vente', parseFloat(t || '0'))} />
                                        </View>
                                        <TouchableOpacity onPress={() => removeRow(grp.carton)} style={styles.trashBtn}>
                                            <Ionicons name="trash-outline" size={20} color={Colors.error} />
                                        </TouchableOpacity>
                                    </View>
                                )}

                                {grp.cartouche && (
                                    <View style={styles.lineRow}>
                                        <View style={styles.qtyBlock}>
                                            <Text style={styles.qtyLabel}>Cartouches</Text>
                                            <View style={styles.stepper}>
                                                <TouchableOpacity style={styles.stepBtn} onPress={() => updateRow(grp.produit_id, 'cartouche', 'quantite', Math.max(0, grp.cartouche.quantite - 1))}>
                                                    <Ionicons name="remove" size={16} color={Colors.primary} />
                                                </TouchableOpacity>
                                                <TextInput style={styles.qtyInput} keyboardType="number-pad" value={String(grp.cartouche.quantite)} onChangeText={(t) => updateRow(grp.produit_id, 'cartouche', 'quantite', parseInt(t || '0', 10))} />
                                                <TouchableOpacity style={styles.stepBtn} onPress={() => updateRow(grp.produit_id, 'cartouche', 'quantite', grp.cartouche.quantite + 1)}>
                                                    <Ionicons name="add" size={16} color={Colors.primary} />
                                                </TouchableOpacity>
                                            </View>
                                        </View>
                                        <View style={styles.priceBlock}>
                                            <Text style={styles.qtyLabel}>Prix cartouche</Text>
                                            <TextInput style={styles.priceInput} keyboardType="number-pad" value={String(grp.cartouche.prix_vente)} onChangeText={(t) => updateRow(grp.produit_id, 'cartouche', 'prix_vente', parseFloat(t || '0'))} />
                                        </View>
                                        <TouchableOpacity onPress={() => removeRow(grp.cartouche)} style={styles.trashBtn}>
                                            <Ionicons name="trash-outline" size={20} color={Colors.error} />
                                        </TouchableOpacity>
                                    </View>
                                )}

                                <Text style={styles.lineTotal}>
                                    {formatMoney(
                                        (grp.carton ? lineTotal(grp.carton) : 0) +
                                        (grp.cartouche ? lineTotal(grp.cartouche) : 0)
                                    )}
                                </Text>
                            </View>
                        ))
                    )}

                    {/* Paiement */}
                    {lines.length > 0 && (
                        <View style={styles.summaryCard}>
                            <View style={styles.summaryRow}>
                                <Text style={styles.summaryLabel}>Total Vente :</Text>
                                <Text style={styles.summaryVal}>{formatMoney(total)}</Text>
                            </View>

                            <View style={styles.summaryRow}>
                                <Text style={styles.summaryLabel}>Montant payé :</Text>
                                <Text style={styles.summaryVal}>{formatMoney(aCredit ? (parseFloat(acompte) || 0) : total)}</Text>
                            </View>

                            {selectedClient && (
                                <TouchableOpacity
                                    style={[styles.creditToggle, aCredit && styles.creditToggleActive]}
                                    onPress={() => setACredit(!aCredit)}
                                >
                                    <View style={[styles.checkbox, aCredit && styles.checkboxActive]}>
                                        {aCredit && <Ionicons name="checkmark" size={14} color="#FFF" />}
                                    </View>
                                    <Text style={[styles.creditLabel, aCredit && styles.creditLabelActive]}>À crédit</Text>
                                </TouchableOpacity>
                            )}
                            {aCredit && selectedClient && (
                                <Text style={styles.infoText}>Cette vente sera enregistrée comme une dette client.</Text>
                            )}

                            {!aCredit && (
                                <View style={{ marginTop: 12 }}>
                                    <Text style={styles.label}>Montant remis par le client (FCFA)</Text>
                                    <TextInput
                                        style={styles.input}
                                        keyboardType="number-pad"
                                        placeholder={String(Math.round(total))}
                                        value={montantRemis}
                                        onChangeText={setMontantRemis}
                                    />
                                    {montantRemis && Number(montantRemis) >= total && (
                                        <Text style={styles.monnaieText}>
                                            Monnaie à rendre (Du) : {formatMoney(Number(montantRemis) - total)}
                                        </Text>
                                    )}
                                    {!selectedClient && (
                                        <Text style={[styles.infoText, { color: '#92400e' }]}>Vente anonyme : le montant remis doit couvrir le total.</Text>
                                    )}
                                </View>
                            )}

                            {aCredit && selectedClient && (
                                <View style={{ marginTop: 12 }}>
                                    <Text style={styles.label}>Acompte payé (FCFA) — optionnel</Text>
                                    <TextInput
                                        style={styles.input}
                                        keyboardType="number-pad"
                                        placeholder="0"
                                        value={acompte}
                                        onChangeText={setAcompte}
                                    />
                                    <Text style={styles.infoText}>
                                        Reste à payer : {formatMoney(Math.max(0, total - (parseFloat(acompte) || 0)))}
                                        {'\n'}Le reste sera enregistré comme dette client.
                                    </Text>

                                    <Text style={[styles.label, { marginTop: 10 }]}>Date de règlement souhaitée</Text>
                                    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipRow}>
                                        {ECHEANCE_OPTIONS.map(opt => {
                                            const dt = new Date();
                                            dt.setDate(dt.getDate() + opt.days);
                                            const iso = dt.toISOString().split('T')[0];
                                            const active = dateEcheance === iso;
                                            return (
                                                <TouchableOpacity
                                                    key={opt.key}
                                                    style={[styles.chip, active && styles.chipActive]}
                                                    onPress={() => setDateEcheance(iso)}
                                                >
                                                    <Text style={[styles.chipText, active && styles.chipTextActive]}>{opt.label}</Text>
                                                </TouchableOpacity>
                                            );
                                        })}
                                        <TouchableOpacity
                                            style={[styles.chip, isCustomEcheance && styles.chipActive]}
                                            onPress={() => setShowDatePicker(true)}
                                        >
                                            <Text style={[styles.chipText, isCustomEcheance && styles.chipTextActive]}>
                                                {isCustomEcheance ? formatDateFr(dateEcheance) : 'Personnalisé...'}
                                            </Text>
                                        </TouchableOpacity>
                                    </ScrollView>
                                    {showDatePicker && (
                                        <DateTimePicker
                                            value={dateEcheance ? new Date(dateEcheance) : new Date()}
                                            mode="date"
                                            display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                                            minimumDate={new Date()}
                                            onChange={(e, d) => {
                                                setShowDatePicker(false);
                                                if (d) setDateEcheance(d.toISOString());
                                            }}
                                        />
                                    )}
                                </View>
                            )}

                            <TouchableOpacity style={styles.submitBtn} onPress={handleSubmit} disabled={submitting}>
                                {submitting ? <ActivityIndicator color="#FFF" /> : <Text style={styles.submitBtnText}>Enregistrer les modifications</Text>}
                            </TouchableOpacity>
                        </View>
                    )}
                </View>
            </KeyboardAwareScrollView>


            {/* Modal création client */}
            <Modal visible={showClientModal} transparent animationType="slide" onRequestClose={() => setShowClientModal(false)}>
                <View style={styles.modalOverlay}>
                    <View style={styles.modalContent}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Nouveau client</Text>
                            <TouchableOpacity onPress={() => setShowClientModal(false)}>
                                <Ionicons name="close" size={24} color={Colors.text} />
                            </TouchableOpacity>
                        </View>
                        <Text style={styles.label}>Nom du client *</Text>
                        <TextInput
                            style={styles.input}
                            placeholder="Nom"
                            placeholderTextColor={Colors.textLight}
                            value={newClientNom}
                            onChangeText={setNewClientNom}
                            autoFocus
                        />
                        <Text style={[styles.label, { marginTop: 10 }]}>Téléphone (optionnel)</Text>
                        <TextInput
                            style={styles.input}
                            placeholder="Ex : +229 97000000"
                            placeholderTextColor={Colors.textLight}
                            value={newClientTel}
                            onChangeText={setNewClientTel}
                            keyboardType="phone-pad"
                        />
                        <Text style={[styles.label, { marginTop: 10 }]}>Adresse (optionnel)</Text>
                        <TextInput
                            style={styles.input}
                            placeholder="Ex : Cotonou, Sainte Rita"
                            placeholderTextColor={Colors.textLight}
                            value={newClientAdresse}
                            onChangeText={setNewClientAdresse}
                        />
                        <TouchableOpacity style={styles.submitBtn} onPress={handleCreateClient}>
                            <Text style={styles.submitBtnText}>Créer le client</Text>
                        </TouchableOpacity>
                    </View>
                </View>
            </Modal>
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    header: { backgroundColor: '#FFFFFF', paddingHorizontal: 16, paddingBottom: 12, borderBottomWidth: 1, borderBottomColor: '#E2E8F0' },
    headerRow: { flexDirection: 'row', alignItems: 'center' },
    backBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center' },
    headerTitle: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    scrollContent: { padding: 16, paddingBottom: 40 },
    section: { marginBottom: 18 },
    sectionTitle: { fontSize: 15, fontFamily: 'Poppins_700Bold', color: Colors.primary, marginBottom: 8 },
    valueText: { fontSize: 14, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    sessionCard: { backgroundColor: Colors.surface, borderRadius: 16, padding: 14, marginBottom: 16, borderWidth: 1, borderColor: Colors.border },
    blockTitle: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.text, marginBottom: 8 },
    chipRow: { gap: 8 },
    chip: { paddingHorizontal: 14, paddingVertical: 8, borderRadius: 20, backgroundColor: Colors.background, borderWidth: 1, borderColor: Colors.border },
    chipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    chipText: { fontSize: 13, fontFamily: 'Poppins_500Medium', color: Colors.text },
    chipTextActive: { color: '#FFF', fontFamily: 'Poppins_700Bold' },
    searchBar: {
        flexDirection: 'row', alignItems: 'center', backgroundColor: Colors.background, borderRadius: 12,
        paddingHorizontal: 12, height: 44, marginBottom: 10, borderWidth: 1, borderColor: Colors.border
    },
    prodChipScroll: { marginBottom: 4 },
    chipNew: { flexDirection: 'row', alignItems: 'center', gap: 4, paddingHorizontal: 14, paddingVertical: 8, borderRadius: 20, backgroundColor: '#eef2ff', borderWidth: 1, borderColor: Colors.primary },
    chipNewText: { fontSize: 13, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    searchInput: { flex: 1, marginLeft: 8, fontFamily: 'Poppins_400Regular', fontSize: 13, color: Colors.text },
    prodList: { gap: 8 },
    prodRowWrap: { backgroundColor: Colors.background, borderRadius: 14, borderWidth: 1, borderColor: Colors.border, marginBottom: 8 },
    prodExpand: { padding: 12, paddingTop: 0, gap: 8, borderTopWidth: 1, borderTopColor: Colors.border },
    prodRowItem: {
        flexDirection: 'row', alignItems: 'center', backgroundColor: Colors.background, borderRadius: 14,
        padding: 12, borderWidth: 1, borderColor: Colors.border
    },
    prodRowName: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.text },
    prodRowPrice: { fontSize: 12, fontFamily: 'Poppins_500Medium', color: Colors.secondary, marginTop: 2 },
    addMini: { width: 34, height: 34, borderRadius: 17, backgroundColor: Colors.primary, justifyContent: 'center', alignItems: 'center', marginLeft: 8 },
    createClientBtn: {
        flexDirection: 'row', alignItems: 'center', gap: 8, backgroundColor: Colors.background,
        borderRadius: 14, padding: 12, borderWidth: 1, borderStyle: 'dashed', borderColor: Colors.primary
    },
    createClientText: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    emptyText: { fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.textLight, textAlign: 'center', paddingVertical: 10 },
    emptyCart: { backgroundColor: Colors.background, borderRadius: 14, padding: 20, alignItems: 'center', gap: 8, borderWidth: 1, borderColor: Colors.border },
    emptyCartText: { fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.textLight, textAlign: 'center' },
    cartItem: { backgroundColor: Colors.background, borderRadius: 14, padding: 12, marginBottom: 10, borderWidth: 1, borderColor: Colors.border },
    cartRowHead: { flexDirection: 'row', alignItems: 'center', gap: 10, marginBottom: 8 },
    cartItemName: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.text },
    lineRow: { flexDirection: 'row', alignItems: 'flex-end', gap: 10, marginBottom: 8 },
    qtyBlock: { flex: 1 },
    priceBlock: { flex: 1 },
    qtyLabel: { fontSize: 11, fontFamily: 'Poppins_500Medium', color: Colors.textLight, marginBottom: 4 },
    stepper: { flexDirection: 'row', alignItems: 'center', backgroundColor: Colors.surface, borderRadius: 10, borderWidth: 1, borderColor: Colors.border, paddingHorizontal: 4 },
    stepBtn: { width: 30, height: 34, justifyContent: 'center', alignItems: 'center' },
    qtyInput: { flex: 1, textAlign: 'center', fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.text },
    priceInput: { backgroundColor: Colors.surface, borderRadius: 10, borderWidth: 1, borderColor: Colors.border, paddingHorizontal: 10, paddingVertical: 8, fontSize: 14, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    trashBtn: { padding: 6, justifyContent: 'center' },
    lineTotal: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.primary, textAlign: 'right' },
    summaryCard: { backgroundColor: Colors.surface, borderRadius: 16, padding: 16, marginTop: 4, elevation: 2, borderWidth: 1, borderColor: Colors.border },
    summaryRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingBottom: 10, borderBottomWidth: 1, borderBottomColor: Colors.border },
    summaryLabel: { fontSize: 16, fontFamily: 'Poppins_700Bold', color: Colors.text },
    summaryVal: { fontSize: 22, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    creditToggle: {
        flexDirection: 'row', alignItems: 'center', gap: 10, marginTop: 14,
        paddingVertical: 10, paddingHorizontal: 12, borderRadius: 10,
        backgroundColor: Colors.background, borderWidth: 1, borderColor: Colors.border
    },
    creditToggleActive: { backgroundColor: '#fef9c3', borderColor: '#facc15' },
    checkbox: { width: 22, height: 22, borderRadius: 6, borderWidth: 2, borderColor: Colors.border, backgroundColor: Colors.surface, justifyContent: 'center', alignItems: 'center' },
    checkboxActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    creditLabel: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.text },
    creditLabelActive: { color: '#854d0e' },
    label: { fontSize: 12, fontFamily: 'Poppins_500Medium', color: Colors.textLight, marginBottom: 4 },
    input: { backgroundColor: Colors.background, borderRadius: 10, paddingHorizontal: 12, paddingVertical: 10, fontSize: 14, fontFamily: 'Poppins_400Regular', color: Colors.text, borderWidth: 1, borderColor: Colors.border },
    monnaieText: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.success, marginTop: 6 },
    infoText: { fontSize: 12, fontFamily: 'Poppins_400Regular', color: Colors.textLight, marginTop: 6 },
    submitBtn: { backgroundColor: Colors.secondary, borderRadius: 14, paddingVertical: 16, alignItems: 'center', marginTop: 16 },
    submitBtnText: { color: '#FFF', fontSize: 16, fontFamily: 'Poppins_700Bold' },
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.45)', justifyContent: 'flex-end' },
    modalContent: { backgroundColor: Colors.surface, borderTopLeftRadius: 22, borderTopRightRadius: 22, padding: 20 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
    modalTitle: { fontSize: 17, fontFamily: 'Poppins_700Bold', color: Colors.primary, flex: 1, marginRight: 10 },
    prodThumb: { width: 46, height: 46, borderRadius: 10, backgroundColor: Colors.border, marginRight: 10 },
    cartThumb: { width: 40, height: 40, borderRadius: 8, backgroundColor: Colors.border },
});

export default VenteEditScreen;
