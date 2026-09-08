import React, { useState, useEffect } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    ActivityIndicator, Alert, StatusBar, TextInput
} from 'react-native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { formatDateFr, formatDateTimeFr } from '../../utils/formatDate';

const formatMoney = (val) => {
    if (val === null || val === undefined || val === '') return '0 FCFA';
    let n = Math.round(Number(val));
    if (!n || Math.abs(n) === 0) n = 0;
    return n.toLocaleString('fr-FR') + ' FCFA';
};

const formatNaira = (val) => {
    if (!val && val !== 0) return '0 ₦';
    let n = Math.round(Number(val));
    if (!n || Math.abs(n) === 0) n = 0;
    return n.toLocaleString('fr-FR') + ' ₦';
};

const DEVISE_SYM = { NGN: '₦', EUR: '€', USD: '$', CNY: '¥', XOF: 'FCFA', AUTRE: '' };
const getDeviseSymbole = (d) => DEVISE_SYM[d] || '₦';
const formatOrigine = (val, devise) => {
    if (!val && val !== 0) return '0';
    let n = Math.round(Number(val));
    if (!n || Math.abs(n) === 0) n = 0;
    return n.toLocaleString('fr-FR') + ' ' + getDeviseSymbole(devise);
};

const computeRevenu = (arr) => {
    const produits = arr?.produits || [];
    return produits.reduce((s, p) => {
        const prixV = Number(p.prix_vente_suggere || p.produit?.prix_vente_suggere || 0);
        const qte = Number(p.pivot?.quantite || p.quantite || 0);
        return s + prixV * qte;
    }, 0);
};

const computeBenefice = (arr) => {
    return computeRevenu(arr) - (Number(arr?.total_cout_reel) || 0);
};

const getStatutBadge = (statut) => {
    switch (statut) {
        case 'receptionne': return { label: 'Réceptionné', color: Colors.success, bg: Colors.success + '18' };
        case 'valide': return { label: 'Validé', color: Colors.success, bg: Colors.success + '18' };
        case 'en_cours': return { label: 'En attente', color: Colors.warning, bg: Colors.warning + '18' };
        case 'annule': return { label: 'Annulé', color: Colors.error, bg: Colors.error + '18' };
        default: return { label: statut || 'En attente', color: Colors.textLight, bg: Colors.border };
    }
};

const getFournisseurs = (arr) => {
    const noms = (arr?.produits || [])
        .map((p) => p.fournisseur?.nom || p.produit?.fournisseur?.nom)
        .filter(Boolean);
    const uniques = [...new Set(noms)];
    if (uniques.length > 0) return uniques.join(', ');
    return arr?.fournisseur?.nom || '—';
};

const plurielUnite = (qte, unite) => {
    if (!unite) return '';
    return Number(qte) > 1 ? `${unite}s` : unite;
};

const arrondirPrix = (prix) => {
    const p = Number(prix || 0);
    if (p <= 0) return 0;
    if (p < 30000) return Math.ceil(p / 100) * 100;
    if (p < 50000) return Math.ceil(p / 500) * 500;
    return Math.ceil(p / 1000) * 1000;
};

const ShowArrivageScreen = ({ navigation, route }) => {
    const insets = useSafeAreaInsets();
    const { id } = route?.params || {};
    const [arrivageData, setArrivageData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [validating, setValidating] = useState(false);
    const [prixEdits, setPrixEdits] = useState({});
    const [conserverMap, setConserverMap] = useState({});
    const [savingPrixId, setSavingPrixId] = useState(null);

    useEffect(() => {
        if (id) fetchArrivageDetail();
    }, [id]);

    const fetchArrivageDetail = async () => {
        try {
            const resp = await client.get(`/arrivages/${id}`);
            setArrivageData(resp.data?.data || resp.data);
        } catch (e) {
            console.error('Error fetching arrivage detail:', e);
            Alert.alert('Erreur', 'Impossible de charger l\'arrivage.');
        } finally {
            setLoading(false);
        }
    };

    const handleValiderArrivage = async () => {
        Alert.alert(
            'Valider l\'arrivage',
            'Confirmez-vous la réception de ces stocks en magasin ? Le stock sera augmenté automatiquement.',
            [
                { text: 'Annuler', style: 'cancel' },
                {
                    text: 'Valider et intégrer',
                    onPress: async () => {
                        setValidating(true);
                        try {
                            await client.post(`/arrivages/${id}/valider`);
                            Alert.alert('Succès', 'Arrivage validé ! Le stock des magasins a été mis à jour.');
                            fetchArrivageDetail();
                        } catch (e) {
                            Alert.alert('Erreur', e.response?.data?.message || 'Erreur lors de la validation');
                        } finally {
                            setValidating(false);
                        }
                    }
                }
            ]
        );
    };

    const handleSavePrixSuggere = async (ligneId) => {
        const val = prixEdits[ligneId];
        if (val === undefined || val === '') return;
        try {
            setSavingPrixId(ligneId);
            await client.put(`/arrivages/produit/${ligneId}/prix-suggere`, {
                prix_vente_suggere: Math.round(Number(val)),
            });
            setArrivageData((prev) => {
                const arr = prev?.arrivage || prev;
                const prods = (arr?.produits || []).map((p) =>
                    p.id === ligneId ? { ...p, prix_vente_suggere: Math.round(Number(val)) } : p
                );
                const updated = { ...arr, produits: prods };
                return prev?.arrivage ? { ...prev, arrivage: updated } : updated;
            });
            Alert.alert('Succès', 'Prix de vente suggéré mis à jour.');
        } catch (e) {
            Alert.alert('Erreur', e.response?.data?.message || 'Erreur lors de la mise à jour');
        } finally {
            setSavingPrixId(null);
        }
    };

    const toggleConserverPrix = (p) => {
        const ancien = Math.round(Number(p.produit?.prix_vente_conseille ?? 0));
        const coutRevU = Number(p.cout_unitaire_reel || p.pivot?.cout_unitaire_reel || 0);
        const calcSug = Math.round(arrondirPrix(coutRevU));

        const rawPvSug = Math.round(Number(p.prix_vente_suggere || p.produit?.prix_vente_suggere || 0));
        const pvSug = (rawPvSug > 0 && rawPvSug !== ancien) ? rawPvSug : (calcSug > 0 ? calcSug : ancien);

        const isConserving = conserverMap[p.id] ?? (rawPvSug === ancien && ancien > 0);
        const newVal = !isConserving;
        setConserverMap((prev) => ({ ...prev, [p.id]: newVal }));
        if (newVal) {
            setPrixEdits((prev) => ({ ...prev, [p.id]: String(ancien) }));
        } else {
            setPrixEdits((prev) => ({ ...prev, [p.id]: String(pvSug) }));
        }
    };

    if (loading) {
        return (
            <View style={styles.centerLoader}>
                <ActivityIndicator size="large" color={Colors.primary} />
            </View>
        );
    }

    const arrivage = arrivageData?.arrivage || arrivageData;
    const produits = arrivageData?.produits || arrivage?.produits || [];
    const totalQte = produits.reduce((s, p) => s + Number(p.pivot?.quantite || p.quantite || 0), 0);

    if (!arrivage) {
        return (
            <View style={styles.centerLoader}>
                <Text style={styles.errorText}>Arrivage introuvable</Text>
            </View>
        );
    }

    const isValidated = arrivage.statut === 'receptionne' || arrivage.statut === 'valide' || arrivage.valide;
    const statutBadge = getStatutBadge(arrivage.statut);

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            {/* Top Bar */}
            <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={20} color={Colors.text} />
                </TouchableOpacity>
                <Text style={styles.topTitle}>Détail Arrivage #{arrivage.id}</Text>
                <TouchableOpacity
                    onPress={() => navigation.navigate('ArrivageEdit', { id: arrivage.id })}
                    style={styles.topActionBtn}
                >
                    <Ionicons name="pencil-outline" size={18} color={Colors.primary} />
                </TouchableOpacity>
            </View>

            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>

                {/* Hero Header */}
                <View style={styles.heroCard}>
                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                        <Text style={styles.heroRef}>{arrivage.reference || `ARR-${arrivage.id}`}</Text>
                        <View style={[styles.badge, { backgroundColor: statutBadge.bg }]}>
                            <Text style={[styles.badgeText, { color: statutBadge.color }]}>
                                {statutBadge.label}
                            </Text>
                        </View>
                    </View>

                    <Text style={styles.heroSub}>
                        Créé le {arrivage.created_at ? formatDateTimeFr(arrivage.created_at) : formatDateFr(arrivage.date_arrivage)} par {arrivage.user?.name || 'Utilisateur'}
                    </Text>
                </View>

                {/* Bouton de validation si non validé */}
                {!isValidated && (
                    <TouchableOpacity
                        style={styles.validerBtn}
                        onPress={handleValiderArrivage}
                        disabled={validating}
                    >
                        {validating ? (
                            <ActivityIndicator color="#FFF" />
                        ) : (
                            <>
                                <Ionicons name="checkmark-circle-outline" size={20} color="#FFF" />
                                <Text style={styles.validerBtnText}>Valider et Intégrer au Stock</Text>
                            </>
                        )}
                    </TouchableOpacity>
                )}

                {/* Informations Générales */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Informations Générales</Text>
                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Fournisseur(s)</Text>
                        <Text style={styles.infoValue}>{getFournisseurs(arrivage)}</Text>
                    </View>
                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Nombre d'articles</Text>
                        <Text style={styles.infoValue}>{produits.length} produit(s)</Text>
                    </View>
                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Quantité totale</Text>
                        <Text style={styles.infoValue}>{totalQte} unité(s)</Text>
                    </View>
                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Magasin de stockage</Text>
                        <Text style={styles.infoValue}>{arrivage.magasin?.nom || '—'}</Text>
                    </View>
                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Taux Devise → FCFA</Text>
                        <Text style={styles.infoValue}>
                            {Number(arrivage.taux_change) > 0
                                 ? `1 ${getDeviseSymbole(arrivage.devise_origine)} = ${Number(arrivage.taux_change).toLocaleString('fr-FR', { maximumFractionDigits: 0 })} FCFA`
                                : '—'}
                        </Text>
                    </View>
                </View>

                {/* Liste des Articles & Répartition du Coût */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Liste des Articles & Répartition du Coût</Text>
                    <Text style={styles.cardSub}>{produits.length} article(s) · Quantité totale : {totalQte} unité(s)</Text>

                    {produits.length > 0 ? (
                        produits.map((p, idx) => {
                            const qte = Number(p.pivot?.quantite || p.quantite || 0);
                            const prixAchat = Number(p.prix_unitaire_origine || p.pivot?.prix_unitaire_origine || 0);
                            const achatFcfa = prixAchat * Number(arrivage.taux_change || 0);
                            const fraisProrata = qte > 0 ? Number(p.part_frais || p.pivot?.part_frais || 0) / qte : 0;
                            const coutRevU = Number(p.cout_unitaire_reel || p.pivot?.cout_unitaire_reel || 0);
                            const calcSug = Math.round(arrondirPrix(coutRevU));
                            const rawPvSug = Math.round(Number(p.prix_vente_suggere || p.produit?.prix_vente_suggere || 0));
                            const ancienPrix = Math.round(Number(p.produit?.prix_vente_conseille ?? 0));
                            const pvSug = (rawPvSug > 0 && rawPvSug !== ancienPrix) ? rawPvSug : (calcSug > 0 ? calcSug : ancienPrix);
                            const conserving = conserverMap[p.id] ?? (rawPvSug === ancienPrix && ancienPrix > 0);
                            const unite = p.produit?.unite || p.unite || 'Carton';
                            const deviseSym = getDeviseSymbole(arrivage.devise_origine);
                            const currentInputVal = conserving
                                ? String(ancienPrix)
                                : (prixEdits[p.id] !== undefined ? String(prixEdits[p.id]) : String(pvSug));
                            return (
                                <View key={idx} style={styles.prodCard}>
                                    <Text style={styles.prodName}>{p.nom || p.produit?.nom || 'Article'}</Text>
                                    <Text style={styles.prodFournisseur}>{p.fournisseur?.nom || p.produit?.fournisseur?.nom || '—'}</Text>
                                    <View style={styles.prodGrid}>
                                        <View style={styles.prodCell}>
                                            <Text style={styles.prodCellLabel}>Quantité</Text>
                                            <Text style={styles.prodCellValue}>{qte} {plurielUnite(qte, unite)}</Text>
                                        </View>
                                        <View style={styles.prodCell}>
                                            <Text style={styles.prodCellLabel}>Prix Achat ({deviseSym})</Text>
                                            <Text style={styles.prodCellValue}>{formatOrigine(prixAchat, arrivage.devise_origine)}</Text>
                                        </View>
                                        <View style={styles.prodCell}>
                                            <Text style={styles.prodCellLabel}>Achat</Text>
                                            <Text style={styles.prodCellValue}>{formatMoney(achatFcfa)}</Text>
                                        </View>
                                        <View style={styles.prodCell}>
                                            <Text style={styles.prodCellLabel}>Frais prorata</Text>
                                            <Text style={styles.prodCellValue}>{formatMoney(fraisProrata)} / u.</Text>
                                        </View>
                                        <View style={styles.prodCell}>
                                            <Text style={styles.prodCellLabel}>Coût Rev. U.</Text>
                                            <Text style={[styles.prodCellValue, { color: Colors.success, fontWeight: '700' }]}>{formatMoney(coutRevU)}</Text>
                                        </View>
                                    </View>

                                    <Text style={[styles.pvSugEditLabel, { marginTop: 4 }]}>Ancien prix : {formatMoney(ancienPrix)}</Text>
                                    <View style={styles.pvSugEditRow}>
                                        <Text style={styles.pvSugEditLabel}>Prix Vente Suggéré</Text>
                                        <View style={styles.pvSugEditInputWrap}>
                                            <TextInput
                                                style={[styles.pvSugEditInput, conserving && { backgroundColor: '#F1F5F9' }]}
                                                value={currentInputVal}
                                                onChangeText={(t) => setPrixEdits((prev) => ({ ...prev, [p.id]: t }))}
                                                keyboardType="numeric"
                                                placeholder="0"
                                                editable={!conserving}
                                            />
                                            <TouchableOpacity
                                                style={styles.pvSugEditBtn}
                                                onPress={() => handleSavePrixSuggere(p.id)}
                                                disabled={savingPrixId === p.id}
                                            >
                                                {savingPrixId === p.id
                                                    ? <ActivityIndicator size="small" color="#FFF" />
                                                    : <Ionicons name="checkmark" size={18} color="#FFF" />}
                                            </TouchableOpacity>
                                        </View>
                                    </View>
                                    <TouchableOpacity
                                        onPress={() => toggleConserverPrix(p)}
                                        style={{ flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: 6 }}
                                    >
                                        <Ionicons name={conserving ? 'checkmark-circle' : 'ellipse-outline'} size={20} color={conserving ? Colors.success : Colors.textLight} />
                                        <Text style={{ fontSize: 13, color: conserving ? Colors.success : Colors.textLight }}>Conserver le prix actuel</Text>
                                    </TouchableOpacity>
                                </View>
                            );
                        })
                    ) : (
                        <Text style={styles.emptyText}>Aucun produit enregistré</Text>
                    )}
                </View>

                {/* Charges Importation */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Charges Importation</Text>
                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Valeur marchandise ({getDeviseSymbole(arrivage.devise_origine) || 'Origine'})</Text>
                        <Text style={styles.infoValue}>{formatOrigine(arrivage.total_valeur_origine, arrivage.devise_origine)}</Text>
                    </View>
                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Valeur marchandise (CFA)</Text>
                        <Text style={styles.infoValue}>{formatMoney(arrivage.total_valeur_fcfa)}</Text>
                    </View>
                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Frais de transport</Text>
                        <Text style={styles.infoValue}>{formatMoney(arrivage.frais_transport)}</Text>
                    </View>
                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Droits douaniers</Text>
                        <Text style={styles.infoValue}>{formatMoney(arrivage.frais_douane)}</Text>
                    </View>
                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Frais de manutention</Text>
                        <Text style={styles.infoValue}>{formatMoney(arrivage.frais_manutention)}</Text>
                    </View>
                    <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Autres frais divers</Text>
                        <Text style={styles.infoValue}>{formatMoney(arrivage.frais_divers)}</Text>
                    </View>
                    <View style={[styles.infoRow, { borderTopWidth: 1, borderTopColor: Colors.border, paddingTop: 8, marginTop: 4 }]}>
                        <Text style={[styles.infoLabel, { fontWeight: '700' }]}>Total charges annexes</Text>
                        <Text style={[styles.infoValue, { fontWeight: '700' }]}>{formatMoney(arrivage.total_frais)}</Text>
                    </View>
                    <View style={styles.infoRow}>
                        <Text style={[styles.infoLabel, { fontWeight: '700' }]}>COÛT TOTAL</Text>
                        <Text style={[styles.infoValue, { fontWeight: '700', color: Colors.primary }]}>{formatMoney(arrivage.total_cout_reel)}</Text>
                    </View>
                </View>

                {/* Bénéfice Prévisionnel */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Bénéfice Prévisionnel</Text>
                    <View style={styles.totalRow}>
                        <Text style={styles.totalLabel}>Revenu attendu (prix suggéré)</Text>
                        <Text style={styles.totalValue}>{formatMoney(computeRevenu(arrivage))}</Text>
                    </View>
                    <View style={styles.totalRow}>
                        <Text style={styles.totalLabel}>Coût total réel</Text>
                        <Text style={styles.totalValue}>{formatMoney(arrivage.total_cout_reel)}</Text>
                    </View>
                    <View style={[styles.totalRow, { borderTopWidth: 1, borderTopColor: Colors.border, paddingTop: 8, marginTop: 4 }]}>
                        <Text style={[styles.totalLabel, { fontWeight: '700', color: computeBenefice(arrivage) >= 0 ? Colors.success : Colors.error }]}>
                            {computeBenefice(arrivage) >= 0 ? 'BÉNÉFICE' : 'PERTE'}
                        </Text>
                        <Text style={[styles.totalValue, { fontWeight: '700', color: computeBenefice(arrivage) >= 0 ? Colors.success : Colors.error }]}>
                            {formatMoney(computeBenefice(arrivage))}
                        </Text>
                    </View>
                </View>

                {/* Règle d'arrondi du prix suggéré (en bas) */}
                <View style={[styles.cardSection, { backgroundColor: '#f8fafc' }]}>
                    <Text style={styles.rulesTitle}><Ionicons name="information-circle-outline" size={15} color={Colors.textLight} /> Règle d'arrondi du prix suggéré</Text>
                    <Text style={styles.rulesText}>Le coût de revient est arrondi à la hausse selon ces paliers :</Text>
                    <View style={styles.rulesList}>
                        <Text style={styles.rulesItem}>• <Text style={styles.rulesBold}>Moins de 30 000 FCFA</Text> → arrondi aux <Text style={styles.rulesBold}>100 FCFA</Text> près (pas de 25, 50, 75)</Text>
                        <Text style={styles.rulesItem}>• <Text style={styles.rulesBold}>30 000 à 49 999 FCFA</Text> → arrondi aux <Text style={styles.rulesBold}>500 FCFA</Text> près</Text>
                        <Text style={styles.rulesItem}>• <Text style={styles.rulesBold}>50 000 FCFA et plus</Text> → arrondi aux <Text style={styles.rulesBold}>1 000 FCFA</Text> près</Text>
                    </View>
                    <Text style={[styles.rulesText, { fontStyle: 'italic', marginTop: 6 }]}>Le prix suggéré peut être modifié manuellement à tout moment.</Text>
                </View>

            </ScrollView>
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    topBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: '#FFFFFF', paddingHorizontal: 16, paddingBottom: 12, borderBottomWidth: 1, borderBottomColor: '#E2E8F0' },
    backBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center' },
    topActionBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center' },
    topTitle: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    errorText: { color: Colors.error, fontSize: 14, fontFamily: 'Poppins_500Medium' },
    scrollContent: { padding: 16, paddingBottom: 30 },
    heroCard: { backgroundColor: '#FFF', borderRadius: 16, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    heroRef: { fontSize: 18, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    badge: { paddingHorizontal: 8, paddingVertical: 4, borderRadius: 8 },
    badgeText: { fontSize: 11, fontWeight: '800' },
    heroSub: { fontSize: 12, color: Colors.textLight, marginTop: 4 },
    validerBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, backgroundColor: Colors.success, paddingVertical: 14, borderRadius: 12, marginBottom: 16 },
    validerBtnText: { color: '#FFF', fontWeight: '800', fontSize: 14 },
    cardSection: { backgroundColor: '#FFF', borderRadius: 14, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    cardTitle: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.primary, marginBottom: 4 },
    cardSub: { fontSize: 12, color: Colors.textLight, marginBottom: 12 },
    totalRow: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 6 },
    totalLabel: { fontSize: 13, fontWeight: '600', color: Colors.text },
    totalValue: { fontSize: 13, fontWeight: '700', color: Colors.text },
    infoRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 7 },
    infoLabel: { fontSize: 13, fontWeight: '500', color: Colors.textLight },
    infoValue: { fontSize: 13, fontWeight: '600', color: Colors.text, textAlign: 'right', flexShrink: 1, marginLeft: 8 },
    tableHead: { flexDirection: 'row', backgroundColor: '#f1f5f9', paddingVertical: 8, paddingHorizontal: 10, borderRadius: 6, marginBottom: 6 },
    th: { fontSize: 11, fontWeight: '700', color: Colors.textLight },
    tableRow: { flexDirection: 'row', paddingVertical: 10, paddingHorizontal: 10, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    td: { fontSize: 12, color: Colors.text },
    emptyText: { textAlign: 'center', color: Colors.textLight, fontSize: 12, paddingVertical: 12 },
    prodCard: { backgroundColor: '#f8fafc', borderRadius: 10, padding: 12, marginBottom: 10, borderWidth: 1, borderColor: Colors.border },
    pvSugEditRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginTop: 10, paddingTop: 10, borderTopWidth: 1, borderTopColor: Colors.border },
    pvSugEditLabel: { fontSize: 12, fontWeight: '700', color: Colors.primary },
    pvSugEditInputWrap: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    pvSugEditInput: { backgroundColor: '#FFF', borderWidth: 1, borderColor: Colors.border, borderRadius: 8, paddingHorizontal: 10, paddingVertical: 6, fontSize: 13, fontWeight: '700', color: Colors.primary, textAlign: 'right', width: 120 },
    pvSugEditBtn: { backgroundColor: Colors.primary, borderRadius: 8, paddingVertical: 8, paddingHorizontal: 10, alignItems: 'center', justifyContent: 'center' },
    prodName: { fontSize: 14, fontWeight: '700', color: Colors.text },
    prodFournisseur: { fontSize: 11.5, color: Colors.textLight, marginTop: 2, marginBottom: 8 },
    prodGrid: { flexDirection: 'row', flexWrap: 'wrap' },
    prodCell: { width: '33.33%', paddingVertical: 4 },
    prodCellLabel: { fontSize: 10.5, color: Colors.textLight, marginBottom: 2 },
    prodCellValue: { fontSize: 12, fontWeight: '600', color: Colors.text },
    rulesTitle: { fontSize: 13, fontWeight: '700', color: Colors.text, marginBottom: 8 },
    rulesText: { fontSize: 11.5, color: Colors.textLight, lineHeight: 18 },
    rulesList: { marginLeft: 4, marginTop: 4 },
    rulesItem: { fontSize: 11.5, color: Colors.textLight, lineHeight: 18 },
    rulesBold: { fontWeight: '700', color: Colors.text },
});

export default ShowArrivageScreen;
