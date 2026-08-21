import React, { useState, useEffect } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity, Image,
    TextInput, ActivityIndicator, Alert, Modal, StatusBar, Platform
} from 'react-native';
import KeyboardAwareScrollView from '../../components/KeyboardAwareScrollView';
import AsyncStorage from '@react-native-async-storage/async-storage';
import DateTimePicker from '@react-native-community/datetimepicker';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { getImageUrl } from '../../utils/image';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { formatDateFr, formatDateTimeFr } from '../../utils/formatDate';


const DRAFT_KEY = 'vente_draft';

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

const newSession = () => ({
    id: uid(),
    clientId: null,
    aCredit: false,
    montantRemis: '',
    acompte: '',
    dateEcheance: null,
    lines: [],
});

const ECHEANCE_OPTIONS = [
    { key: 'today', label: "Aujourd'hui", days: 0 },
    { key: 'tomorrow', label: 'Demain', days: 1 },
    { key: 'week', label: 'Dans 1 semaine', days: 7 },
    { key: 'month', label: 'Dans 1 mois', days: 30 },
];

const normalizeSession = (s) => ({
    id: s.id || uid(),
    clientId: s.clientId ?? null,
    aCredit: !!s.aCredit,
    montantRemis: s.montantRemis || '',
    acompte: s.acompte || '',
    dateEcheance: s.dateEcheance || null,
    lines: Array.isArray(s.lines) ? s.lines : [],
});

const cartoucheFallback = (produit) => {
    const conseille = produit.prix_vente_conseille || produit.prix || 0;
    const cartPar = produit.cartouche_par_carton || 1;
    return produit.prix_cartouche || Math.ceil((conseille / cartPar) / 100) * 100;
};

const lineTotal = (l) => (l.prix_vente * l.quantite) + ((l.prix_cartouche || 0) * l.quantite_cartouche);
const sessionTotal = (s) => s.lines.reduce((sum, l) => sum + lineTotal(l), 0);

const buildVentePayload = (s) => ({
    client_id: s.clientId || null,
    a_credit: s.aCredit,
    montant_paye: s.aCredit ? (parseFloat(s.acompte) || 0) : null,
    montant_remis: !s.aCredit
        ? (s.montantRemis ? parseFloat(s.montantRemis) : sessionTotal(s))
        : null,
    date_echeance: s.aCredit && s.dateEcheance
        ? new Date(s.dateEcheance).toISOString().split('T')[0] : null,
    lignes: s.lines.map(l => ({
        produit_id: l.produit_id,
        quantite: l.quantite,
        quantite_cartouche: l.hasCartouche ? l.quantite_cartouche : 0,
        prix_vente: l.prix_vente,
        prix_cartouche: l.hasCartouche ? (l.prix_cartouche || null) : null
    }))
});

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
                    <Text style={styles.addMiniPrice}>{formatMoney(produit.prix_vente_conseille || produit.prix)}</Text>
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
                            <TextInput style={styles.priceInput} keyboardType="number-pad" value={String(prixC)} onChangeText={(t) => setPrixC(parseInt(t || '0', 10))} />
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
                                <TextInput style={styles.priceInput} keyboardType="number-pad" value={String(prixK)} onChangeText={(t) => setPrixK(parseInt(t || '0', 10))} />
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

const VenteSessionCard = ({ session, index, clients, produits, magasinId, submitting, onUpdate, onRemove, onSubmit, onCreateClient }) => {
    const [search, setSearch] = useState('');
    const [clientSearch, setClientSearch] = useState('');
    const [showDatePicker, setShowDatePicker] = useState(false);
    const [showClientModal, setShowClientModal] = useState(false);
    const [newClientNom, setNewClientNom] = useState('');
    const [newClientTel, setNewClientTel] = useState('');
    const [newClientAdresse, setNewClientAdresse] = useState('');

    const canSearchProduct = search.trim().length >= 2;
    const filteredProduits = canSearchProduct
        ? produits.filter(p => normalizeText(p.nom).includes(normalizeText(search)))
        : [];

    const canSearchClient = clientSearch.trim().length >= 2;
    const filteredClients = canSearchClient
        ? clients.filter(c => normalizeText(`${c.nom} ${c.prenom || ''}`).includes(normalizeText(clientSearch)))
        : [];

    const isCustomEcheance = !!session.dateEcheance && !ECHEANCE_OPTIONS.some(o => {
        const d = new Date();
        d.setDate(d.getDate() + o.days);
        return session.dateEcheance === d.toISOString().split('T')[0];
    });

    const handleAddLine = (line) => {
        const lines = [...session.lines];
        const existing = lines.find(c => c.produit_id === line.produit_id);
        if (existing) {
            existing.quantite += line.quantite;
            if (line.hasCartouche) existing.quantite_cartouche += line.quantite_cartouche;
        } else {
            lines.push(line);
        }
        onUpdate({ lines });
    };

    const removeLine = (produitId) => onUpdate({ lines: session.lines.filter(c => c.produit_id !== produitId) });

    const updateLine = (produitId, key, value) => {
        onUpdate({ lines: session.lines.map(c => c.produit_id === produitId ? { ...c, [key]: value } : c) });
    };

    const openCreateClient = () => {
        setNewClientNom(clientSearch.trim());
        setNewClientTel('');
        setNewClientAdresse('');
        setShowClientModal(true);
    };

    const handleCreateClient = async () => {
        const ok = await onCreateClient({
            nom: newClientNom,
            telephone: newClientTel,
            adresse: newClientAdresse,
        });
        if (ok) {
            setShowClientModal(false);
            setClientSearch('');
            setNewClientNom('');
            setNewClientTel('');
            setNewClientAdresse('');
        }
    };

    return (
        <View style={styles.sessionCard}>
            <View style={styles.cardHeader}>
                <Text style={styles.cardHeaderTitle}>Vente {index + 1}</Text>
                <View style={styles.cardHeaderRight}>
                    <View style={styles.cardTotalBadge}>
                        <Text style={styles.cardTotalText}>{formatMoney(sessionTotal(session))}</Text>
                    </View>
                    <TouchableOpacity style={styles.cardDeleteBtn} onPress={onRemove}>
                        <Ionicons name="trash-outline" size={18} color={Colors.error} />
                    </TouchableOpacity>
                </View>
            </View>

            {/* Client */}
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
                    style={[styles.chip, !session.clientId && styles.chipActive]}
                    onPress={() => onUpdate({ clientId: null })}
                >
                    <Text style={[styles.chipText, !session.clientId && styles.chipTextActive]}>Anonyme</Text>
                </TouchableOpacity>
                {clients
                    .slice()
                    .sort((a, b) => `${a.nom || ''} ${a.prenom || ''}`.localeCompare(`${b.nom || ''} ${b.prenom || ''}`))
                    .filter(c => !clientSearch || normalizeText(`${c.nom} ${c.prenom || ''}`).includes(normalizeText(clientSearch)))
                    .map(c => {
                        const active = session.clientId === c.id;
                        return (
                            <TouchableOpacity
                                key={c.id}
                                style={[styles.chip, active && styles.chipActive]}
                                onPress={() => { onUpdate({ clientId: c.id }); setClientSearch(''); }}
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
                        const inCart = session.lines.some(c => c.produit_id === p.id);
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
                                    <Text style={[styles.chipText, inCart && styles.chipTextActive]} numberOfLines={1}>{p.nom}</Text>
                                </TouchableOpacity>
                        );
                    })}
            </ScrollView>

            {/* Lignes */}
            <Text style={[styles.blockTitle, { marginTop: 16 }]}>Articles ({session.lines.length})</Text>
            {session.lines.length === 0 ? (
                <View style={styles.emptyCart}>
                    <Ionicons name="cart-outline" size={36} color={Colors.border} />
                    <Text style={styles.emptyCartText}>Ajoutez des produits ci-dessus pour composer cette vente.</Text>
                </View>
            ) : (
                session.lines.map(item => (
                    <View key={item.produit_id} style={styles.cartItem}>
                        <View style={styles.cartRowHead}>
                            {getImageUrl(item.image) ? (
                                <Image source={{ uri: getImageUrl(item.image) }} style={styles.cartThumb} resizeMode="cover" />
                            ) : (
                                <View style={styles.cartThumb}><Ionicons name="image-outline" size={18} color={Colors.textLight} /></View>
                            )}
                            <Text style={styles.cartItemName}>{item.nom}</Text>
                        </View>
                        <View style={styles.lineRow}>
                            <View style={styles.qtyBlock}>
                                <Text style={styles.qtyLabel}>Cartons</Text>
                                <View style={styles.stepper}>
                                    <TouchableOpacity style={styles.stepBtn} onPress={() => updateLine(item.produit_id, 'quantite', Math.max(0, item.quantite - 1))}>
                                        <Ionicons name="remove" size={16} color={Colors.primary} />
                                    </TouchableOpacity>
                                    <TextInput
                                        style={styles.qtyInput}
                                        keyboardType="number-pad"
                                        value={String(item.quantite)}
                                        onChangeText={(t) => updateLine(item.produit_id, 'quantite', parseInt(t || '0', 10))}
                                    />
                                    <TouchableOpacity style={styles.stepBtn} onPress={() => updateLine(item.produit_id, 'quantite', item.quantite + 1)}>
                                        <Ionicons name="add" size={16} color={Colors.primary} />
                                    </TouchableOpacity>
                                </View>
                            </View>
                            <View style={styles.priceBlock}>
                                <Text style={styles.qtyLabel}>Prix carton</Text>
                                <TextInput
                                    style={styles.priceInput}
                                    keyboardType="number-pad"
                                    value={String(item.prix_vente)}
                                    onChangeText={(t) => updateLine(item.produit_id, 'prix_vente', parseInt(t || '0', 10))}
                                />
                            </View>
                            <TouchableOpacity onPress={() => removeLine(item.produit_id)} style={styles.trashBtn}>
                                <Ionicons name="trash-outline" size={20} color={Colors.error} />
                            </TouchableOpacity>
                        </View>

                        {item.hasCartouche && (
                            <View style={styles.lineRow}>
                                <View style={styles.qtyBlock}>
                                    <Text style={styles.qtyLabel}>Cartouches</Text>
                                    <View style={styles.stepper}>
                                        <TouchableOpacity style={styles.stepBtn} onPress={() => updateLine(item.produit_id, 'quantite_cartouche', Math.max(0, item.quantite_cartouche - 1))}>
                                            <Ionicons name="remove" size={16} color={Colors.primary} />
                                        </TouchableOpacity>
                                        <TextInput
                                            style={styles.qtyInput}
                                            keyboardType="number-pad"
                                            value={String(item.quantite_cartouche)}
                                            onChangeText={(t) => updateLine(item.produit_id, 'quantite_cartouche', parseInt(t || '0', 10))}
                                        />
                                        <TouchableOpacity style={styles.stepBtn} onPress={() => updateLine(item.produit_id, 'quantite_cartouche', item.quantite_cartouche + 1)}>
                                            <Ionicons name="add" size={16} color={Colors.primary} />
                                        </TouchableOpacity>
                                    </View>
                                </View>
                                <View style={styles.priceBlock}>
                                    <Text style={styles.qtyLabel}>Prix cartouche</Text>
                                        <TextInput
                                            style={styles.priceInput}
                                            keyboardType="number-pad"
                                            value={String(item.prix_cartouche || 0)}
                                            onChangeText={(t) => updateLine(item.produit_id, 'prix_cartouche', parseInt(t || '0', 10))}
                                        />
                                </View>
                            </View>
                        )}

                        <Text style={styles.lineTotal}>{formatMoney(lineTotal(item))}</Text>
                    </View>
                ))
            )}

            {/* Paiement */}
            {session.lines.length > 0 && (
                <View style={styles.summaryCard}>
                    <View style={styles.summaryRow}>
                        <Text style={styles.summaryLabel}>Total Vente :</Text>
                        <Text style={styles.summaryVal}>{formatMoney(sessionTotal(session))}</Text>
                    </View>

                    <View style={styles.summaryRow}>
                        <Text style={styles.summaryLabel}>Montant payé :</Text>
                        <Text style={styles.summaryVal}>{formatMoney(session.aCredit ? (parseFloat(session.acompte) || 0) : sessionTotal(session))}</Text>
                    </View>

                    {session.clientId && (
                        <TouchableOpacity
                            style={[styles.creditToggle, session.aCredit && styles.creditToggleActive]}
                            onPress={() => onUpdate({ aCredit: !session.aCredit })}
                        >
                            <View style={[styles.checkbox, session.aCredit && styles.checkboxActive]}>
                                {session.aCredit && <Ionicons name="checkmark" size={14} color="#FFF" />}
                            </View>
                            <Text style={[styles.creditLabel, session.aCredit && styles.creditLabelActive]}>À crédit</Text>
                        </TouchableOpacity>
                    )}
                    {session.aCredit && session.clientId && (
                        <Text style={styles.infoText}>Cette vente sera enregistrée comme une dette client.</Text>
                    )}

                    {!session.aCredit && (
                        <View style={{ marginTop: 12 }}>
                            <Text style={styles.label}>Montant remis par le client (FCFA)</Text>
                            <TextInput
                                style={styles.input}
                                keyboardType="number-pad"
                                placeholder={String(Math.round(sessionTotal(session)))}
                                value={session.montantRemis}
                                onChangeText={(t) => onUpdate({ montantRemis: t })}
                            />
                            {session.montantRemis && Number(session.montantRemis) >= sessionTotal(session) && (
                                <Text style={styles.monnaieText}>
                                    Monnaie à rendre (Du) : {formatMoney(Number(session.montantRemis) - sessionTotal(session))}
                                </Text>
                            )}
                            {!session.clientId && (
                                <Text style={[styles.infoText, { color: '#92400e' }]}>Vente anonyme : le montant remis doit couvrir le total.</Text>
                            )}
                        </View>
                    )}

                    {session.aCredit && session.clientId && (
                        <View style={{ marginTop: 12 }}>
                            <Text style={styles.label}>Acompte payé (FCFA) — optionnel</Text>
                            <TextInput
                                style={styles.input}
                                keyboardType="number-pad"
                                placeholder="0"
                                value={session.acompte}
                                onChangeText={(t) => onUpdate({ acompte: t })}
                            />
                            <Text style={styles.infoText}>
                                Reste à payer : {formatMoney(Math.max(0, sessionTotal(session) - (parseFloat(session.acompte) || 0)))}
                                {'\n'}Le reste sera enregistré comme dette client.
                            </Text>

                            <Text style={[styles.label, { marginTop: 10 }]}>Date de règlement souhaitée</Text>
                            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipRow}>
                                {ECHEANCE_OPTIONS.map(opt => {
                                    const d = new Date();
                                    d.setDate(d.getDate() + opt.days);
                                    const iso = d.toISOString().split('T')[0];
                                    const active = session.dateEcheance === iso;
                                    return (
                                        <TouchableOpacity
                                            key={opt.key}
                                            style={[styles.chip, active && styles.chipActive]}
                                            onPress={() => onUpdate({ dateEcheance: iso })}
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
                                        {isCustomEcheance ? formatDateFr(session.dateEcheance) : 'Personnalisé...'}
                                    </Text>
                                </TouchableOpacity>
                            </ScrollView>
                            {showDatePicker && (
                                <DateTimePicker
                                    value={session.dateEcheance ? new Date(session.dateEcheance) : new Date()}
                                    mode="date"
                                    display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                                    minimumDate={new Date()}
                                    onChange={(e, d) => {
                                        setShowDatePicker(false);
                                        if (d) onUpdate({ dateEcheance: d.toISOString() });
                                    }}
                                />
                            )}
                        </View>
                    )}

                    <View style={{ marginTop: 14, marginBottom: 10 }}>
                        <Text style={styles.label}>Notes &amp; Remarques</Text>
                        <TextInput
                            style={[styles.input, { height: 80, textAlignVertical: 'top' }]}
                            value={session.notes || ''}
                            onChangeText={(t) => onUpdate({ notes: t })}
                            placeholder="Écrivez vos remarques sur la vente..."
                            multiline
                        />
                    </View>

                    <TouchableOpacity style={styles.submitBtn} onPress={onSubmit} disabled={submitting}>
                        {submitting ? <ActivityIndicator color="#FFF" /> : <Text style={styles.submitBtnText}>Valider cette vente</Text>}
                    </TouchableOpacity>
                </View>
            )}

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

const VenteCreateScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const [clients, setClients] = useState([]);
    const [magasins, setMagasins] = useState([]);
    const [produits, setProduits] = useState([]);
    const [loading, setLoading] = useState(true);
    const [draft, setDraft] = useState({ magasinId: null, activeSessionId: null, sessions: [] });
    const [loaded, setLoaded] = useState(false);
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        (async () => {
            try {
                const saved = await AsyncStorage.getItem(DRAFT_KEY);
                if (saved) {
                    const d = JSON.parse(saved);
                    if (d && Array.isArray(d.sessions)) {
                        setDraft({
                            magasinId: d.magasinId ?? null,
                            activeSessionId: d.activeSessionId ?? null,
                            sessions: d.sessions.map(normalizeSession),
                        });
                    }
                }
            } catch (e) {
                console.error('Failed to load vente draft', e);
            } finally {
                setLoaded(true);
            }
        })();
    }, []);

    useEffect(() => {
        if (loaded) AsyncStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
    }, [draft, loaded]);

    useEffect(() => {
        if (!loaded) return;
        if (draft.sessions.length === 0) {
            const s = newSession();
            setDraft(d => ({ ...d, sessions: [s], activeSessionId: s.id }));
        } else if (!draft.sessions.find(s => s.id === draft.activeSessionId)) {
            setDraft(d => ({ ...d, activeSessionId: d.sessions[0].id }));
        }
    }, [loaded, draft.sessions, draft.activeSessionId]);

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
            setDraft(d => {
                if (d.magasinId) return d;
                return { ...d, magasinId: magList.length > 0 ? magList[0].id : null };
            });

            setProduits(pRes.data?.data?.data || pRes.data?.data || (Array.isArray(pRes.data) ? pRes.data : []));
        } catch (e) {
            console.error('Error fetching initial data for sale:', e);
        } finally {
            setLoading(false);
        }
    };

    const updateSession = (id, patch) => {
        setDraft(d => ({
            ...d,
            sessions: d.sessions.map(s => s.id === id ? { ...s, ...patch } : s)
        }));
    };

    const setMagasin = (id) => setDraft(d => ({ ...d, magasinId: id }));

    const addSession = () => {
        const s = newSession();
        setDraft(d => ({ ...d, sessions: [...d.sessions, s], activeSessionId: s.id }));
    };

    const removeSession = (id) => {
        setDraft(d => {
            if (d.sessions.length <= 1) {
                const s = newSession();
                return { ...d, sessions: [s], activeSessionId: s.id };
            }
            const sessions = d.sessions.filter(s => s.id !== id);
            const activeSessionId = d.activeSessionId === id ? sessions[0].id : d.activeSessionId;
            return { ...d, sessions, activeSessionId };
        });
    };

    const createClientForSession = async (sessionId, data) => {
        const name = (data?.nom || '').trim();
        if (!name) {
            Alert.alert('Erreur', 'Le nom du client est requis.');
            return false;
        }
        try {
            const resp = await client.post('/clients', {
                nom: name,
                telephone: tel,
                adresse: (data?.adresse || '').trim(),
            });
            const created = resp.data?.client || null;
            let id = null;
            if (created && created.id) {
                id = created.id;
                setClients(prev => prev.some(c => c.id === created.id) ? prev : [...prev, created]);
            } else {
                const r = await client.get('/clients');
                const list = r.data?.data || (Array.isArray(r.data) ? r.data : []);
                setClients(list);
                const found = list.find(c => (c.nom || '').toLowerCase() === name.toLowerCase());
                id = found ? found.id : null;
            }
            if (id) updateSession(sessionId, { clientId: id });
            return true;
        } catch (e) {
            Alert.alert('Erreur', e.response?.data?.message || 'Impossible de créer le client.');
            return false;
        }
    };

    const submitVentes = async (payload, onSuccess) => {
        setSubmitting(true);
        try {
            const resp = await client.post('/ventes', payload);
            if (resp.data?.credit_warning) {
                Alert.alert('Attention crédit client', resp.data.message, [
                    { text: 'Annuler', style: 'cancel' },
                    {
                        text: 'Continuer quand même',
                        onPress: async () => {
                            try {
                                await client.post('/ventes', { ...payload, ignore_credit_warning: true });
                                onSuccess();
                            } catch (err) {
                                Alert.alert('Erreur', err.response?.data?.message || 'Erreur lors de l\'enregistrement de la vente');
                            } finally {
                                setSubmitting(false);
                            }
                        }
                    }
                ]);
                return;
            }
            onSuccess();
        } catch (e) {
            const msg = e.response?.data?.message || 'Erreur lors de l\'enregistrement de la vente';
            Alert.alert('Erreur', msg);
        } finally {
            setSubmitting(false);
        }
    };

    const submitSession = async (session) => {
        if (session.lines.length === 0) {
            Alert.alert('Erreur', 'Veuillez ajouter au moins un produit à cette vente.');
            return;
        }
        if (!draft.magasinId) {
            Alert.alert('Erreur', 'Veuillez sélectionner un magasin.');
            return;
        }
        if (session.aCredit && !session.clientId) {
            Alert.alert('Erreur', 'Un crédit nécessite de sélectionner un client.');
            return;
        }
        if (!session.clientId && !session.aCredit) {
            const remis = session.montantRemis ? parseFloat(session.montantRemis) : null;
            const total = sessionTotal(session);
            if (remis === null || remis < total) {
                Alert.alert('Erreur', `Vente anonyme : le montant remis doit couvrir le total (${formatMoney(total)}).`);
                return;
            }
        }
        if (session.aCredit && session.dateEcheance
            && new Date(session.dateEcheance) < new Date(new Date().toDateString())) {
            Alert.alert('Erreur', 'La date d\'échéance ne peut pas être dans le passé.');
            return;
        }

        const wasLast = draft.sessions.length === 1;
        const payload = { magasin_id: draft.magasinId, ventes: [buildVentePayload(session)] };

        submitVentes(payload, () => {
            Alert.alert('Succès', 'Vente enregistrée avec succès !');
            if (wasLast) {
                AsyncStorage.removeItem(DRAFT_KEY);
                navigation.goBack();
                return;
            }
            removeSession(session.id);
        });
    };

    const submitAll = async () => {
        const valid = draft.sessions.filter(s => s.lines.length > 0);
        if (valid.length === 0) {
            Alert.alert('Erreur', 'Aucune vente à enregistrer.');
            return;
        }
        if (!draft.magasinId) {
            Alert.alert('Erreur', 'Veuillez sélectionner un magasin.');
            return;
        }
        for (const s of valid) {
            if (s.aCredit && !s.clientId) {
                Alert.alert('Erreur', 'Chaque crédit nécessite un client.');
                return;
            }
        }
        const payload = { magasin_id: draft.magasinId, ventes: valid.map(buildVentePayload) };
        submitVentes(payload, () => {
            Alert.alert('Succès', `${valid.length} vente(s) enregistrée(s) avec succès !`);
            AsyncStorage.removeItem(DRAFT_KEY);
            navigation.goBack();
        });
    };

    if (loading) {
        return (
            <View style={styles.container}>
                <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />
                <View style={[styles.header, { paddingTop: Math.max(insets.top, 16) }]}>
                    <View style={styles.headerRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                            <Ionicons name="arrow-back" size={20} color={Colors.text} />
                        </TouchableOpacity>
                        <Text style={styles.headerTitle}>Nouvelle Vente</Text>
                        <View style={{ width: 36 }} />
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
                    <View style={{ flex: 1, marginLeft: 12 }}>
                        <Text style={styles.headerTitle}>Nouvelle Vente</Text>
                        {draft.sessions.filter(s => s.lines.length > 0).length > 1 && (
                            <Text style={styles.headerSub}>{draft.sessions.filter(s => s.lines.length > 0).length} ventes en brouillon</Text>
                        )}
                    </View>
                </View>
            </View>

            <KeyboardAwareScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false} keyboardShouldPersistTaps="handled">


                {/* Magasin */}
                {magasins.length > 0 && (
                    <View style={styles.section}>
                        <Text style={styles.sectionTitle}>Magasin de vente</Text>
                        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipRow}>
                            {magasins.map(m => (
                                <TouchableOpacity
                                    key={m.id}
                                    style={[styles.chip, draft.magasinId === m.id ? styles.chipActive : {}]}
                                    onPress={() => setMagasin(m.id)}
                                >
                                    <Text style={[styles.chipText, draft.magasinId === m.id && styles.chipTextActive]}>{m.nom}</Text>
                                </TouchableOpacity>
                            ))}
                        </ScrollView>
                    </View>
                )}

                {/* Cartes de vente empilées */}
                {draft.sessions.map((s, i) => (
                    <VenteSessionCard
                        key={s.id}
                        session={s}
                        index={i}
                        clients={clients}
                        produits={produits}
                        magasinId={draft.magasinId}
                        submitting={submitting}
                        onUpdate={(patch) => updateSession(s.id, patch)}
                        onRemove={() => removeSession(s.id)}
                        onSubmit={() => submitSession(s)}
                        onCreateClient={(data) => createClientForSession(s.id, data)}
                    />
                ))}

                <TouchableOpacity style={styles.newVenteBtnBottom} onPress={addSession}>
                    <Ionicons name="add" size={20} color={Colors.primary} />
                    <Text style={styles.newVenteTextBottom}>Nouvelle vente</Text>
                </TouchableOpacity>

                {draft.sessions.filter(s => s.lines.length > 0).length > 1 && (
                    <TouchableOpacity style={styles.submitAllBtn} onPress={submitAll} disabled={submitting}>
                        <Text style={styles.submitAllText}>Tout enregistrer ({draft.sessions.filter(s => s.lines.length > 0).length} ventes)</Text>
                    </TouchableOpacity>
                )}

            </KeyboardAwareScrollView>

        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    header: { backgroundColor: '#FFFFFF', paddingHorizontal: 16, paddingBottom: 12, borderBottomWidth: 1, borderBottomColor: '#E2E8F0' },
    headerRow: { flexDirection: 'row', alignItems: 'center' },
    backBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center' },
    headerTitle: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    headerSub: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 2 },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    scrollContent: { padding: 16, paddingBottom: 40 },
    section: { marginBottom: 18 },
    sectionTitle: { fontSize: 15, fontFamily: 'Poppins_700Bold', color: Colors.primary, marginBottom: 8 },
    blockTitle: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.text, marginBottom: 8 },
    chipRow: { gap: 8 },
    chip: { paddingHorizontal: 14, paddingVertical: 6, borderRadius: 20, backgroundColor: Colors.surface, borderWidth: 1, borderColor: Colors.border, alignItems: 'center', minWidth: 96 },
    chipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    chipText: { fontSize: 13, fontFamily: 'Poppins_500Medium', color: Colors.text },
    chipTextActive: { color: '#FFF', fontFamily: 'Poppins_700Bold' },
    sessionCard: { backgroundColor: Colors.surface, borderRadius: 16, padding: 14, marginBottom: 16, borderWidth: 1, borderColor: Colors.border },
    cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12, paddingBottom: 10, borderBottomWidth: 1, borderBottomColor: Colors.border },
    cardHeaderTitle: { fontSize: 16, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    cardHeaderRight: { flexDirection: 'row', alignItems: 'center', gap: 10 },
    cardTotalBadge: { backgroundColor: Colors.background, paddingHorizontal: 12, paddingVertical: 4, borderRadius: 20 },
    cardTotalText: { fontSize: 13, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    cardDeleteBtn: { padding: 4 },
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
    addMini: { flexDirection: 'row', alignItems: 'center', gap: 4, backgroundColor: Colors.primary, paddingHorizontal: 10, paddingVertical: 6, borderRadius: 16, marginLeft: 8 },
    addMiniPrice: { color: '#FFF', fontWeight: '700', fontSize: 12 },
    createClientBtn: {
        flexDirection: 'row', alignItems: 'center', gap: 8, backgroundColor: Colors.background,
        borderRadius: 14, padding: 12, borderWidth: 1, borderStyle: 'dashed', borderColor: Colors.primary
    },
    createClientText: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    emptyText: { fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.textLight, textAlign: 'center', paddingVertical: 10 },
    emptyCart: { backgroundColor: Colors.background, borderRadius: 14, padding: 20, alignItems: 'center', gap: 8, borderWidth: 1, borderColor: Colors.border },
    emptyCartText: { fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.textLight, textAlign: 'center' },
    cartItem: { backgroundColor: Colors.background, borderRadius: 14, padding: 12, marginBottom: 10, borderWidth: 1, borderColor: Colors.border },
    cartItemName: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.text, marginBottom: 8 },
    lineRow: { flexDirection: 'row', alignItems: 'flex-end', gap: 10, marginBottom: 8 },
    qtyBlock: { flex: 1 },
    priceBlock: { flex: 1 },
    qtyLabel: { fontSize: 11, fontFamily: 'Poppins_500Medium', color: Colors.textLight, marginBottom: 4 },
    stepper: { flexDirection: 'row', alignItems: 'center', backgroundColor: Colors.surface, borderRadius: 10, borderWidth: 1, borderColor: Colors.border, paddingHorizontal: 4 },
    stepBtn: { width: 30, height: 34, justifyContent: 'center', alignItems: 'center' },
    qtyInput: { flex: 1, textAlign: 'center', fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.text },
    priceInput: { backgroundColor: Colors.surface, borderRadius: 10, borderWidth: 1, borderColor: Colors.border, paddingHorizontal: 10, paddingVertical: 8, fontSize: 14, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    priceStatic: { fontSize: 14, fontWeight: '700', color: Colors.text, marginTop: 2 },
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
    newVenteBtnBottom: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, paddingVertical: 16, borderRadius: 14, borderWidth: 1, borderStyle: 'dashed', borderColor: Colors.primary, marginBottom: 10 },
    newVenteTextBottom: { fontSize: 15, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    submitAllBtn: { backgroundColor: Colors.primary, borderRadius: 14, paddingVertical: 16, alignItems: 'center', marginBottom: 10 },
    submitAllText: { color: '#FFF', fontSize: 16, fontFamily: 'Poppins_700Bold' },
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.45)', justifyContent: 'flex-end' },
    modalContent: { backgroundColor: Colors.surface, borderTopLeftRadius: 22, borderTopRightRadius: 22, padding: 20 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
    modalTitle: { fontSize: 17, fontFamily: 'Poppins_700Bold', color: Colors.primary, flex: 1, marginRight: 10 },
    modalPreview: { fontSize: 15, fontFamily: 'Poppins_700Bold', color: Colors.text, textAlign: 'right', marginTop: 10, marginBottom: 4 },
    prodThumb: { width: 46, height: 46, borderRadius: 10, backgroundColor: Colors.border, marginRight: 10, justifyContent: 'center', alignItems: 'center' },
    cartRowHead: { flexDirection: 'row', alignItems: 'center', gap: 10, marginBottom: 8 },
    cartThumb: { width: 40, height: 40, borderRadius: 8, backgroundColor: Colors.border, justifyContent: 'center', alignItems: 'center' },
    modalThumb: { width: 64, height: 64, borderRadius: 12, backgroundColor: Colors.border, marginBottom: 10 },
});

export default VenteCreateScreen;
