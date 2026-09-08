import React, { useState, useEffect, useRef } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    ActivityIndicator, Alert, StatusBar, Modal, TextInput,
    KeyboardAvoidingView, Platform
} from 'react-native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { getOfflineVentes } from '../../utils/offlineSync';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useAuth } from '../../context/AuthContext';
import * as Print from 'expo-print';
import * as Sharing from 'expo-sharing';
import * as FileSystem from 'expo-file-system/legacy';
import { formatDateFr, formatDateTimeFr } from '../../utils/formatDate';

const formatMoney = (val) => {
    if (val === null || val === undefined || val === '') return '0 F';
    return Math.round(Number(val)).toLocaleString('fr-FR') + ' FCFA';
};

const MOIS = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

const pad = (n) => String(n).padStart(2, '0');

const formatInvoiceDate = (iso) => {
    if (!iso) return 'N/A';
    const d = new Date(iso);
    return `${pad(d.getDate())} ${MOIS[d.getMonth()]} ${d.getFullYear()} · ${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

const uniteAbbrev = (u) => {
    if (!u) return '';
    const s = ('' + u).toLowerCase();
    if (s.includes('cartouche')) return 'ctc';
    if (s.includes('carton')) return 'ctn';
    if (s.includes('piece') || s.includes('pièce')) return 'pc';
    return u;
};

const escapeHtml = (str) => {
    return ('' + (str || '')).replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
};

const LIVRAISON_STATUTS = [
    { key: 'en_attente', label: 'En attente', color: Colors.warning },
    { key: 'livre', label: 'Livré', color: Colors.success },
    { key: 'probleme', label: 'Problème', color: Colors.error },
];

const getInvoiceLines = (v) => {
    if (!v) return [];
    if (Array.isArray(v.lignes) && v.lignes.length > 0) return v.lignes;
    if (Array.isArray(v.produits) && v.produits.length > 0) return v.produits;
    if (Array.isArray(v.items) && v.items.length > 0) return v.items;
    if (Array.isArray(v.details) && v.details.length > 0) return v.details;
    return [];
};

const ShowVenteScreen = ({ navigation, route }) => {
    const insets = useSafeAreaInsets();
    const { user } = useAuth();
    const companyName = vente?.tenant?.nom || vente?.magasin?.tenant?.nom || user?.tenant?.nom || 'E-STOCK';
    const companyPhone = vente?.tenant?.telephone || vente?.magasin?.tenant?.telephone || user?.tenant?.telephone || user?.telephone || '';
    const { id, openPrint } = route?.params || {};
    const printingRef = useRef(false);
    const [printing, setPrinting] = useState(false);
    const [vente, setVente] = useState(null);
    const [loading, setLoading] = useState(true);
    const [actionLoading, setActionLoading] = useState(false);

    const [showPayModal, setShowPayModal] = useState(false);
    const [payAmount, setPayAmount] = useState('');
    const [showLivModal, setShowLivModal] = useState(false);
    const [livStatut, setLivStatut] = useState(null);
    const [livNote, setLivNote] = useState('');

    useEffect(() => {
        if (id) fetchVente();
    }, [id]);

    const fetchVente = async () => {
        // Si c'est une vente hors-ligne (ID commençant par OFF- ou offline_), la charger depuis le stockage local
        const isOfflineId = typeof id === 'string' && (id.startsWith('OFF-') || id.startsWith('offline_'));
        
        if (isOfflineId) {
            try {
                const offlineVentes = await getOfflineVentes();
                const offlineVente = offlineVentes.find(v => v.id === id);
                if (offlineVente) {
                    setVente(offlineVente);
                    if (openPrint) {
                        setTimeout(() => handlePrint(offlineVente), 600);
                    }
                } else {
                    Alert.alert('Erreur', 'Vente hors-ligne introuvable.');
                }
            } catch (e) {
                console.error('Error fetching offline vente:', e);
                Alert.alert('Erreur', 'Impossible de charger la facture hors-ligne.');
            } finally {
                setLoading(false);
            }
            return;
        }

        try {
            const resp = await client.get(`/ventes/${id}`);
            const data = resp.data?.data || resp.data;
            setVente(data);
            // Auto-trigger print if opened from list with print icon
            if (openPrint && data) {
                setTimeout(() => handlePrint(data), 600);
            }
        } catch (e) {
            console.error('Error fetching vente detail:', e);
            Alert.alert('Erreur', 'Impossible de charger la facture.');
        } finally {
            setLoading(false);
        }
    };

    const openLivModal = () => {
        const isOffline = vente?.isOffline === true || (typeof id === 'string' && (id.startsWith('OFF-') || id.startsWith('offline_')));
        if (isOffline) {
            Alert.alert('Non disponible', 'Les actions de livraison ne sont pas disponibles pour les ventes hors-ligne. Elles seront synchronisées au retour de la connexion.');
            return;
        }
        setLivStatut(vente.statut_livraison || 'en_attente');
        setLivNote('');
        setShowLivModal(true);
    };

    const handleLivraison = async () => {
        if (!livStatut) return;
        try {
            setActionLoading(true);
            await client.put(`/livraisons/${id}/statut`, {
                statut_livraison: livStatut,
                note_livraison: livNote || null,
            });
            Alert.alert('Succès', 'Statut de livraison mis à jour.');
            setShowLivModal(false);
            fetchVente();
        } catch (e) {
            Alert.alert('Erreur', e.response?.data?.message || 'Erreur lors de la mise à jour');
        } finally {
            setActionLoading(false);
        }
    };

    const handlePayerDette = async () => {
        const isOffline = vente?.isOffline === true || (typeof id === 'string' && (id.startsWith('OFF-') || id.startsWith('offline_')));
        if (isOffline) {
            Alert.alert('Non disponible', 'Le paiement de dette n\'est pas disponible pour les ventes hors-ligne. Il sera synchronisé au retour de la connexion.');
            return;
        }
        
        const montant = parseFloat(payAmount);
        const reste = vente.dette?.montant_restant || 0;
        if (!montant || montant <= 0) {
            Alert.alert('Erreur', 'Veuillez saisir un montant valide.');
            return;
        }
        if (montant > reste) {
            Alert.alert('Erreur', `Le montant ne peut pas dépasser le reste dû (${formatMoney(reste)}).`);
            return;
        }
        try {
            setActionLoading(true);
            await client.post(`/dettes/${vente.dette.id}/payer`, { montant });
            Alert.alert('Succès', 'Versement enregistré avec succès.');
            setShowPayModal(false);
            setPayAmount('');
            fetchVente();
        } catch (e) {
            Alert.alert('Erreur', e.response?.data?.message || 'Erreur lors du paiement');
        } finally {
            setActionLoading(false);
        }
    };

    const [masquerSociete, setMasquerSociete] = useState(false);
    const [masquerVendeur, setMasquerVendeur] = useState(false);

    const handlePrint = async (venteData) => {
        const v = venteData || vente;
        if (!v || printingRef.current) return;
        printingRef.current = true;
        setPrinting(true);

        try {
            const rawLines = getInvoiceLines(v);
            const lines = rawLines.map((l) => {
                const nom = l.produit?.nom || l.nom || l.designation || 'Article';
                const px = Number(l.prix_vente || l.prix_unitaire || l.prix || 0);
                const qte = Number(l.quantite || l.qte || 1);
                const tot = Number(l.total_ligne || (px * qte) || 0);
                return `<tr>
                <td style="width:40%;text-align:left;vertical-align:middle;word-break:break-word;">${escapeHtml(nom)}</td>
                <td style="width:20%;text-align:right;vertical-align:middle;">${Math.round(px).toLocaleString('fr-FR')}</td>
                <td style="width:15%;text-align:right;vertical-align:middle;">${qte} ${uniteAbbrev(l.unite)}</td>
                <td style="width:25%;text-align:right;vertical-align:middle;">${Math.round(tot).toLocaleString('fr-FR')}</td>
            </tr>`;
            }).join('');
            const companyHeaderHtml = masquerSociete ? '' : `<h2>${escapeHtml(companyName)}</h2>${companyPhone ? `<div class="sub">Tél: ${escapeHtml(companyPhone)}</div>` : ''}`;
            const vendeurRowHtml = (masquerVendeur || !v.user?.name) ? '' : `<div class="row"><span>Vendeur:</span><span>${escapeHtml(v.user.name)}</span></div>`;
            const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><style>
                @page{size:auto;margin:4mm}*{box-sizing:border-box}
                html,body{width:100%;font-family:Arial,Helvetica,sans-serif;font-size:16px;margin:0;padding:8px;color:#000;font-weight:400}
                h2{text-align:center;margin:0 0 6px;font-size:24px;font-weight:700;color:#000}
                .sub{text-align:center;font-size:15px;margin-bottom:8px;color:#333;font-weight:400}
                .divider{border:none;border-top:2px dashed #555;margin:8px 0}
                .row{display:flex;justify-content:space-between;font-size:16px;margin:4px 0;gap:8px;font-weight:500;color:#000;word-break:break-word}
                table{width:100%;border-collapse:collapse;margin-top:10px;table-layout:fixed;word-wrap:break-word}
                th,td{padding:7px 6px;border-bottom:1px dashed #aaa;font-size:15px;color:#000;font-weight:400;overflow-wrap:anywhere;word-break:break-word}
                th{border-bottom:2px dashed #555;font-size:16px;font-weight:600}
                .total{font-weight:700;margin-top:10px;border-top:2px dashed #555;padding-top:8px;font-size:19px}
                .thanks{text-align:center;margin-top:16px;font-size:15px;font-weight:400;color:#000}
            </style></head><body>
                ${companyHeaderHtml}
                <div class="sub">${escapeHtml(v.magasin?.nom || '')}</div>
                <hr class="divider">
                <div class="row"><span>FACTURE</span><span>${escapeHtml(v.reference)}</span></div>
                <div class="row"><span>${escapeHtml(formatInvoiceDate(v.date_vente))}</span></div>
                <div class="row"><span>Client:</span><span>${escapeHtml(v.client?.nom ? v.client.nom + ' ' + (v.client.prenom || '') : 'Anonyme')}</span></div>
                ${vendeurRowHtml}
                <hr class="divider">
                <table><thead><tr><th style="width:40%;text-align:left;vertical-align:middle;">Article</th><th style="width:20%;text-align:right;vertical-align:middle;">Prix</th><th style="width:15%;text-align:right;vertical-align:middle;">Qté</th><th style="width:25%;text-align:right;vertical-align:middle;">Total</th></tr></thead><tbody>${lines}</tbody></table>
                <hr class="divider">
                <div class="row"><span>Sous-total</span><span>${formatMoney(v.montant_total)}</span></div>
                <div class="row"><span>Payé</span><span>${formatMoney(v.montant_paye)}</span></div>
                ${v.montant_reste > 0 ? `<div class="row" style="color:#cc0000"><span>Reste à payer</span><span>${formatMoney(v.montant_reste)}</span></div>` : ''}
                <div class="row total"><span>NET À PAYER</span><span>${formatMoney(v.montant_total)}</span></div>
                <div class="thanks">Merci pour votre achat !</div>
            </body></html>`;

            await Print.printAsync({ html });
        } catch (e) {
            const msg = (e?.message || '').toLowerCase();
            if (msg.includes('cancel') || msg.includes('did not complete')) return;
            Alert.alert('Impression impossible', e?.message || 'Une erreur est survenue.');
        } finally {
            printingRef.current = false;
            setPrinting(false);
        }
    };

    if (loading) {
        return (
            <View style={styles.centerLoader}>
                <ActivityIndicator size="large" color={Colors.primary} />
            </View>
        );
    }

    if (!vente) {
        return (
            <View style={styles.centerLoader}>
                <Text style={styles.errorText}>Facture introuvable</Text>
            </View>
        );
    }

    const dateFormatted = vente.date_vente
        ? formatInvoiceDate(vente.date_vente)
        : 'N/A';

    const livBadge = LIVRAISON_STATUTS.find(s => s.key === (vente.statut_livraison || 'en_attente')) || LIVRAISON_STATUTS[0];

    const isOffline = vente?.isOffline === true || (typeof id === 'string' && (id.startsWith('OFF-') || id.startsWith('offline_')));

    const handleDeleteVente = () => {
        const isOffline = vente?.isOffline === true || (typeof id === 'string' && (id.startsWith('OFF-') || id.startsWith('offline_')));
        
        if (isOffline) {
            Alert.alert(
                'Supprimer la vente hors-ligne',
                `Êtes-vous sûr de vouloir supprimer cette vente hors-ligne ? Elle ne sera pas synchronisée.`,
                [
                    { text: 'Annuler', style: 'cancel' },
                    {
                        text: 'Supprimer',
                        style: 'destructive',
                        onPress: async () => {
                            try {
                                setActionLoading(true);
                                const { removeOfflineVente } = require('../../utils/offlineSync');
                                await removeOfflineVente(id);
                                Alert.alert('Succès', 'Vente hors-ligne supprimée.');
                                navigation.goBack();
                            } catch (e) {
                                Alert.alert('Erreur', 'Erreur lors de la suppression.');
                            } finally {
                                setActionLoading(false);
                            }
                        },
                    },
                ]
            );
            return;
        }
        
        Alert.alert(
            'Supprimer la vente',
            `Êtes-vous sûr de vouloir supprimer la vente ${vente?.reference} ? Le stock sera réajusté.`,
            [
                { text: 'Annuler', style: 'cancel' },
                {
                    text: 'Supprimer',
                    style: 'destructive',
                    onPress: async () => {
                        try {
                            setActionLoading(true);
                            await client.delete(`/ventes/${id}`);
                            Alert.alert('Succès', 'Vente supprimée avec succès.');
                            navigation.goBack();
                        } catch (e) {
                            Alert.alert('Erreur', e.response?.data?.message || 'Erreur lors de la suppression.');
                        } finally {
                            setActionLoading(false);
                        }
                    },
                },
            ]
        );
    };

    const uiLines = getInvoiceLines(vente);

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            {/* Top Bar */}
            <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={20} color={Colors.text} />
                </TouchableOpacity>
                <Text style={styles.topTitle} numberOfLines={1} ellipsizeMode="tail">Facture N° {vente.reference}</Text>
                <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
                    <TouchableOpacity onPress={handlePrint} style={styles.topActionBtn} disabled={printing}>
                        {printing ? <ActivityIndicator size={16} color={Colors.primary} /> : <Ionicons name="print-outline" size={18} color={Colors.primary} />}
                    </TouchableOpacity>
                    <TouchableOpacity onPress={handleDeleteVente} style={[styles.topActionBtn, { backgroundColor: '#FEE2E2' }]}>
                        <Ionicons name="trash-outline" size={18} color={Colors.error} />
                    </TouchableOpacity>
                    <TouchableOpacity onPress={fetchVente} style={styles.topActionBtn}>
                        <Ionicons name="refresh-outline" size={18} color={Colors.primary} />
                    </TouchableOpacity>
                </View>
            </View>

            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>

                {/* Options rapides masquage impression */}
                <View style={styles.maskChipsRow}>
                    <TouchableOpacity
                        style={[styles.maskChip, masquerSociete && styles.maskChipActive]}
                        onPress={() => setMasquerSociete(!masquerSociete)}
                        activeOpacity={0.7}
                    >
                        <Ionicons
                            name={masquerSociete ? "checkbox" : "square-outline"}
                            size={16}
                            color={masquerSociete ? Colors.primary : Colors.textLight}
                        />
                        <Text style={[styles.maskChipText, masquerSociete && styles.maskChipTextActive]}>
                            Masquer société
                        </Text>
                    </TouchableOpacity>

                    <TouchableOpacity
                        style={[styles.maskChip, masquerVendeur && styles.maskChipActive]}
                        onPress={() => setMasquerVendeur(!masquerVendeur)}
                        activeOpacity={0.7}
                    >
                        <Ionicons
                            name={masquerVendeur ? "checkbox" : "square-outline"}
                            size={16}
                            color={masquerVendeur ? Colors.primary : Colors.textLight}
                        />
                        <Text style={[styles.maskChipText, masquerVendeur && styles.maskChipTextActive]}>
                            Masquer vendeur
                        </Text>
                    </TouchableOpacity>
                </View>

                {/* Carte facture */}
                <View style={styles.invoiceCard}>
                    <View style={styles.invoiceHeader}>
                        <View>
                            <Text style={styles.companyTitle}>{companyName}</Text>
                            <Text style={styles.storeName}>{vente.magasin?.nom || 'Magasin Principal'}</Text>
                        </View>
                        <View style={{ alignItems: 'flex-end' }}>
                            <Text style={styles.factureBadge}>FACTURE</Text>
                            <Text style={styles.factureRef}>{vente.reference}</Text>
                            <Text style={styles.factureDate}>{dateFormatted}</Text>
                        </View>
                    </View>

                    <View style={styles.clientSection}>
                        <Text style={styles.sectionLabel}>CLIENT</Text>
                        <Text style={styles.clientName}>{vente.client?.nom ? `${vente.client.nom} ${vente.client.prenom || ''}` : 'Client Anonyme'}</Text>
                        {vente.client?.telephone && (
                            <Text style={styles.clientSub}>Tél: {vente.client.telephone}</Text>
                        )}
                        {vente.user?.name && (
                            <Text style={styles.clientSub}>Établi par: {vente.user.name}</Text>
                        )}
                    </View>

                    <Text style={styles.sectionLabel}>DÉTAIL DES ARTICLES</Text>
                    <View style={styles.tableHead}>
                    <Text style={[styles.th, { flex: 2 }]}>Article</Text>
                    <Text style={[styles.th, { flex: 1, textAlign: 'right' }]}>Prix</Text>
                    <Text style={[styles.th, { flex: 1, textAlign: 'right' }]}>Qté</Text>
                    <Text style={[styles.th, { flex: 1.2, textAlign: 'right' }]}>Total</Text>
                    </View>

                    {uiLines.length > 0 ? (
                        uiLines.map((l, idx) => {
                            const nom = l.produit?.nom || l.nom || l.designation || 'Article';
                            const px = Number(l.prix_vente || l.prix_unitaire || l.prix || 0);
                            const qte = Number(l.quantite || l.qte || 1);
                            const tot = Number(l.total_ligne || (px * qte) || 0);
                            return (
                                <View key={idx} style={styles.tableRow}>
                                    <View style={[styles.td, { flex: 2, flexDirection: 'row', alignItems: 'center', gap: 6, fontWeight: '600' }]}>
                                        <Text style={{ fontWeight: '600' }}>{nom}</Text>
                                    </View>
                                    <Text style={[styles.td, { flex: 1, textAlign: 'right' }]}>{Math.round(px).toLocaleString('fr-FR')}</Text>
                                    <Text style={[styles.td, { flex: 1, textAlign: 'right' }]}>{qte} {uniteAbbrev(l.unite)}</Text>
                                    <Text style={[styles.td, { flex: 1.2, textAlign: 'right', fontWeight: '700' }]}>{Math.round(tot).toLocaleString('fr-FR')}</Text>
                                </View>
                            );
                        })
                    ) : (
                        <Text style={styles.emptyLignes}>Aucun article répertorié</Text>
                    )}

                    <View style={styles.totauxWrap}>
                        <View style={styles.totalRow}>
                            <Text style={styles.totalLabel}>Total Facture</Text>
                            <Text style={styles.totalValue}>{formatMoney(vente.montant_total)}</Text>
                        </View>
                        <View style={styles.totalRow}>
                            <Text style={styles.totalLabel}>Montant Payé</Text>
                            <Text style={styles.totalValue}>{formatMoney(vente.montant_paye)}</Text>
                        </View>
                        {vente.montant_remis ? (
                            <View style={styles.totalRow}>
                                <Text style={styles.totalLabel}>Montant Remis</Text>
                                <Text style={styles.totalValue}>{formatMoney(vente.montant_remis)}</Text>
                            </View>
                        ) : null}
                        {vente.montant_reste > 0 && (
                            <View style={styles.totalRow}>
                                <Text style={styles.totalLabel}>Reste à Payer</Text>
                                <Text style={styles.totalValue}>{formatMoney(vente.montant_reste)}</Text>
                            </View>
                        )}
                        {vente.montant_remis && vente.montant_remis > vente.montant_total ? (
                            <View style={styles.totalRow}>
                                <Text style={styles.totalLabel}>Monnaie à Rendre</Text>
                                <Text style={styles.totalValue}>{formatMoney(vente.montant_remis - vente.montant_total)}</Text>
                            </View>
                        ) : null}
                        <View style={styles.netBox}>
                            <Text style={styles.netBoxLabel}>NET À PAYER</Text>
                            <Text style={styles.netBoxVal}>{formatMoney(vente.montant_total)}</Text>
                        </View>
                    </View>
                </View>

                {!isOffline && (
                    <>
                        {/* Statut de livraison */}
                        <View style={styles.cardBlock}>
                            <View style={styles.blockHead}>
                                <Text style={styles.blockTitle}>Livraison</Text>
                                <View style={[styles.miniBadge, { backgroundColor: livBadge.color + '18' }]}>
                                    <Text style={[styles.miniBadgeText, { color: livBadge.color }]}>{livBadge.label}</Text>
                                </View>
                            </View>
                            {vente.livreur?.name && (
                                <Text style={styles.blockSub}>Contrôleur : {vente.livreur.name}</Text>
                            )}
                            {vente.date_livraison && (
                                <Text style={styles.blockSub}>Livrée le {formatDateFr(vente.date_livraison)}</Text>
                            )}
                            {vente.note_livraison && (
                                <Text style={styles.blockNote}>Note : {vente.note_livraison}</Text>
                            )}
                            <TouchableOpacity style={styles.btnOutline} onPress={openLivModal}>
                                <Ionicons name="bicycle-outline" size={18} color={Colors.primary} />
                                <Text style={styles.btnOutlineText}>Changer le statut de livraison</Text>
                            </TouchableOpacity>
                        </View>

                        {/* Dette / crédit */}
                        {vente.dette && (
                            <View style={styles.cardBlock}>
                                <Text style={styles.blockTitle}>Créance client</Text>
                                <View style={styles.blockRow}>
                                    <Text style={styles.blockLabel}>Reste dû</Text>
                                    <Text style={[styles.blockVal, { color: Colors.error }]}>{formatMoney(vente.dette.montant_restant)}</Text>
                                </View>
                                {vente.dette.date_echeance && (
                                    <View style={styles.blockRow}>
                                        <Text style={styles.blockLabel}>Échéance</Text>
                                        <Text style={styles.blockVal}>{formatDateFr(vente.dette.date_echeance)}</Text>
                                    </View>
                                )}
                                <TouchableOpacity style={styles.btnPrimary} onPress={() => { setPayAmount(String(vente.dette.montant_restant)); setShowPayModal(true); }}>
                                    <Ionicons name="card-outline" size={18} color="#FFF" />
                                    <Text style={styles.btnActionText}>Ajouter un paiement</Text>
                                </TouchableOpacity>
                            </View>
                        )}

                        {/* Actions */}
                        <View style={styles.actionsGroup}>
                            <TouchableOpacity style={styles.btnEdit} onPress={() => navigation.navigate('VenteEdit', { id: vente.id })}>
                                <Ionicons name="create-outline" size={18} color={Colors.primary} />
                                <Text style={styles.btnEditText}>Modifier la vente</Text>
                            </TouchableOpacity>
                        </View>
                    </>
                )}

                {isOffline && (
                    <View style={styles.cardBlock}>
                        <View style={styles.blockHead}>
                            <Text style={styles.blockTitle}>Mode hors-ligne</Text>
                            <View style={[styles.miniBadge, { backgroundColor: Colors.warning + '18' }]}>
                                <Text style={[styles.miniBadgeText, { color: Colors.warning }]}>Non synchronisé</Text>
                            </View>
                        </View>
                        <Text style={styles.blockSub}>Cette vente a été créée hors-ligne.</Text>
                        <Text style={styles.blockSub}>Elle sera synchronisée automatiquement au retour de la connexion.</Text>
                    </View>
                )}

            </ScrollView>

            {/* Modal paiement dette */}
            <Modal visible={showPayModal} transparent animationType="slide" onRequestClose={() => setShowPayModal(false)}>
                <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={{ flex: 1 }}>
                <View style={styles.modalOverlay}>
                    <View style={styles.modalContent}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Paiement de la créance</Text>
                            <TouchableOpacity onPress={() => setShowPayModal(false)}>
                                <Ionicons name="close" size={24} color={Colors.text} />
                            </TouchableOpacity>
                        </View>
                        <Text style={styles.modalSub}>Reste dû : {formatMoney(vente.dette?.montant_restant)}</Text>
                        <Text style={styles.label}>Montant versé (FCFA)</Text>
                        <TextInput
                            style={styles.input}
                            keyboardType="number-pad"
                            placeholder={String(Math.round(vente.dette?.montant_restant || 0))}
                            value={payAmount}
                            onChangeText={setPayAmount}
                        />
                        <TouchableOpacity style={styles.submitBtn} onPress={handlePayerDette} disabled={actionLoading}>
                            {actionLoading ? <ActivityIndicator color="#FFF" /> : <Text style={styles.submitBtnText}>Valider le versement</Text>}
                        </TouchableOpacity>
                    </View>
                </View>

                </KeyboardAvoidingView>
            </Modal>

            {/* Modal statut livraison */}
            <Modal visible={showLivModal} transparent animationType="slide" onRequestClose={() => setShowLivModal(false)}>
                <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={{ flex: 1 }}>
                <View style={styles.modalOverlay}>
                    <View style={styles.modalContent}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Statut de livraison</Text>
                            <TouchableOpacity onPress={() => setShowLivModal(false)}>
                                <Ionicons name="close" size={24} color={Colors.text} />
                            </TouchableOpacity>
                        </View>
                        {LIVRAISON_STATUTS.map(s => {
                            const locked = s.key === 'en_attente'
                                && (vente.statut_livraison === 'livre' || vente.statut_livraison === 'probleme');
                            return (
                                <TouchableOpacity
                                    key={s.key}
                                    style={[styles.statusOption, livStatut === s.key && styles.statusOptionActive, locked && styles.statusOptionLocked]}
                                    disabled={locked}
                                    onPress={() => setLivStatut(s.key)}
                                >
                                    <View style={[styles.dot, { backgroundColor: s.color }]} />
                                    <Text style={[styles.statusOptionText, livStatut === s.key && styles.statusOptionTextActive]}>{s.label}</Text>
                                    {livStatut === s.key && <Ionicons name="checkmark" size={18} color={Colors.primary} />}
                                </TouchableOpacity>
                            );
                        })}
                        {(vente.statut_livraison === 'livre' || vente.statut_livraison === 'probleme') && (
                            <Text style={styles.statusOptionNote}>Une livraison livrée ou en problème ne peut plus revenir à « En attente ».</Text>
                        )}
                        <Text style={[styles.label, { marginTop: 12 }]}>Note (optionnel)</Text>
                        <TextInput
                            style={styles.input}
                            placeholder="Précisions sur la livraison..."
                            value={livNote}
                            onChangeText={setLivNote}
                        />
                        <TouchableOpacity style={styles.submitBtn} onPress={handleLivraison} disabled={actionLoading}>
                            {actionLoading ? <ActivityIndicator color="#FFF" /> : <Text style={styles.submitBtnText}>Mettre à jour</Text>}
                        </TouchableOpacity>
                    </View>
                </View>
                </KeyboardAvoidingView>
            </Modal>
        </View>
    );
};

const styles = StyleSheet.create({
    maskChipsRow: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 10,
        marginBottom: 12,
    },
    maskChip: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 6,
        paddingHorizontal: 12,
        paddingVertical: 7,
        borderRadius: 20,
        backgroundColor: '#F1F5F9',
        borderWidth: 1,
        borderColor: '#E2E8F0',
    },
    maskChipActive: {
        backgroundColor: '#EFF6FF',
        borderColor: Colors.primary,
    },
    maskChipText: {
        fontSize: 12,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.textLight,
    },
    maskChipTextActive: {
        color: Colors.primary,
        fontFamily: 'PlusJakartaSans_700Bold',
    },
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    topBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: '#FFFFFF', paddingHorizontal: 16, paddingBottom: 12, borderBottomWidth: 1, borderBottomColor: '#E2E8F0' },
    backBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center' },
    topActionBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center' },
    topTitle: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text, flex: 1, textAlign: 'center', marginHorizontal: 8 },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    errorText: { color: Colors.error, fontSize: 14, fontFamily: 'Poppins_500Medium' },
    scrollContent: { padding: 16, paddingBottom: 30 },
    invoiceCard: { backgroundColor: '#FFF', borderRadius: 16, padding: 20, borderWidth: 1, borderColor: Colors.border, elevation: 2 },
    invoiceHeader: { flexDirection: 'row', justifyContent: 'space-between', borderBottomWidth: 2, borderBottomColor: Colors.border, paddingBottom: 16, marginBottom: 16 },
    companyTitle: { fontSize: 20, fontFamily: 'SpaceGrotesk_700Bold', color: '#1f2937' },
    storeName: { fontSize: 11, color: '#6b7280', marginTop: 2 },
    factureBadge: { fontSize: 12, fontWeight: '800', color: '#1f2937' },
    factureRef: { fontSize: 13, fontWeight: '700', color: '#1f2937', marginTop: 2 },
    factureDate: { fontSize: 11, color: '#6b7280', marginTop: 2 },
    clientSection: { backgroundColor: '#f8fafc', padding: 12, borderRadius: 8, marginBottom: 16 },
    sectionLabel: { fontSize: 10, fontWeight: '800', color: Colors.textLight, letterSpacing: 0.5, marginBottom: 6 },
    clientName: { fontSize: 14, fontWeight: '700', color: Colors.text },
    clientSub: { fontSize: 12, color: Colors.textLight, marginTop: 2 },
    tableHead: { flexDirection: 'row', backgroundColor: '#f1f5f9', paddingVertical: 8, paddingHorizontal: 10, borderRadius: 6, marginBottom: 6 },
    th: { fontSize: 11, fontWeight: '700', color: Colors.textLight, paddingHorizontal: 6 },
    tableRow: { flexDirection: 'row', paddingVertical: 10, paddingHorizontal: 10, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    td: { fontSize: 12, color: Colors.text, paddingHorizontal: 6 },
    emptyLignes: { textAlign: 'center', color: Colors.textLight, paddingVertical: 16, fontSize: 12 },
    totauxWrap: { marginTop: 20, paddingTop: 12, borderTopWidth: 1, borderTopColor: Colors.border },
    totalRow: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 6 },
    totalLabel: { fontSize: 13, fontWeight: '600', color: Colors.text },
    totalValue: { fontSize: 13, fontWeight: '700', color: Colors.text },
    netBox: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', borderTopWidth: 2, borderTopColor: '#1f2937', padding: 14, marginTop: 10 },
    netBoxLabel: { color: '#1f2937', fontWeight: '800', fontSize: 13 },
    netBoxVal: { color: '#1f2937', fontWeight: '800', fontSize: 16 },
    cardBlock: { backgroundColor: Colors.surface, borderRadius: 16, padding: 16, marginTop: 14, borderWidth: 1, borderColor: Colors.border },
    blockHead: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
    blockTitle: { fontSize: 15, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    miniBadge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8 },
    miniBadgeText: { fontSize: 11, fontFamily: 'Poppins_700Bold' },
    blockSub: { fontSize: 12, color: Colors.textLight, marginTop: 2 },
    blockNote: { fontSize: 12, color: Colors.textLight, marginTop: 2, fontStyle: 'italic' },
    blockRow: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 4 },
    blockLabel: { fontSize: 13, color: Colors.textLight },
    blockVal: { fontSize: 13, fontWeight: '700', color: Colors.text },
    btnOutline: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, borderWidth: 1, borderColor: Colors.primary, paddingVertical: 12, borderRadius: 12, marginTop: 10 },
    btnOutlineText: { color: Colors.primary, fontWeight: '700', fontSize: 14 },
    btnPrimary: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, backgroundColor: Colors.primary, paddingVertical: 14, borderRadius: 12, marginTop: 12 },
    btnActionText: { color: '#FFF', fontWeight: '700', fontSize: 14 },
    btnEdit: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, borderWidth: 1, borderColor: Colors.primary, paddingVertical: 14, borderRadius: 12, marginBottom: 10 },
    btnEditText: { color: Colors.primary, fontWeight: '700', fontSize: 14 },
    actionsGroup: { marginTop: 16 },
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.45)', justifyContent: 'flex-end' },
    modalContent: { backgroundColor: Colors.surface, borderTopLeftRadius: 22, borderTopRightRadius: 22, padding: 20 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 14 },
    modalTitle: { fontSize: 17, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    modalSub: { fontSize: 13, color: Colors.textLight, marginBottom: 12 },
    label: { fontSize: 12, fontFamily: 'Poppins_500Medium', color: Colors.textLight, marginBottom: 4 },
    input: { backgroundColor: Colors.background, borderRadius: 10, paddingHorizontal: 12, paddingVertical: 10, fontSize: 14, fontFamily: 'Poppins_400Regular', color: Colors.text, borderWidth: 1, borderColor: Colors.border },
    submitBtn: { backgroundColor: Colors.secondary, borderRadius: 14, paddingVertical: 16, alignItems: 'center', marginTop: 16 },
    submitBtnText: { color: '#FFF', fontSize: 16, fontFamily: 'Poppins_700Bold' },
    statusOption: { flexDirection: 'row', alignItems: 'center', gap: 10, paddingVertical: 12, paddingHorizontal: 12, borderRadius: 10, borderWidth: 1, borderColor: Colors.border, marginBottom: 8 },
    statusOptionActive: { borderColor: Colors.primary, backgroundColor: Colors.primary + '0A' },
    statusOptionLocked: { opacity: 0.4 },
    dot: { width: 12, height: 12, borderRadius: 6 },
    statusOptionText: { flex: 1, fontSize: 14, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    statusOptionTextActive: { color: Colors.primary },
    statusOptionNote: { fontSize: 12, fontFamily: 'Poppins_400Regular', color: Colors.textLight, marginTop: 2, marginBottom: 4 },
    lineThumb: { width: 30, height: 30, borderRadius: 6, backgroundColor: Colors.border },
});

export default ShowVenteScreen;
