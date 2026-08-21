import React, { useState, useCallback } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    ActivityIndicator, RefreshControl, Modal, TextInput, Alert
} from 'react-native';
import { useAuth } from '../context/AuthContext';
import { useFocusEffect } from '@react-navigation/native';
import Colors from '../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client, { apiErrorMessage } from '../api/client';
import TopHeaderNav from '../components/TopHeaderNav';
import FloatingActionButton from '../components/FloatingActionButton';

import StimulateurCA from '../components/StimulateurCA';
import { formatDateFr } from '../utils/formatDate';

const formatMoney = (val) => {
    if (val === null || val === undefined || val === '') return '0 F';
    return Number(val).toLocaleString('fr-FR') + ' F';
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    const pad = (n) => String(n).padStart(2, '0');
    return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()}`;
};

const DashboardScreen = ({ navigation }) => {
    const { user, hasCapability } = useAuth();

    const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
    const [periode, setPeriode] = useState('jour');
    const [dashboard, setDashboard] = useState(null);
    const [loading, setLoading] = useState(true);
    const [errorMsg, setErrorMsg] = useState('');
    const [refreshing, setRefreshing] = useState(false);

    // Modals
    const [dateModalVisible, setDateModalVisible] = useState(false);
    const [tempDate, setTempDate] = useState(selectedDate);
    const [depenseModalVisible, setDepenseModalVisible] = useState(false);
    const [depenseMontant, setDepenseMontant] = useState('');
    const [depenseDesc, setDepenseDesc] = useState('');
    const [submittingDepense, setSubmittingDepense] = useState(false);

    const isDG = user?.role === 'super_admin' || user?.role === 'admin' || user?.role === 'superviseur' || (user?.roles_secondaires || []).includes('superviseur');

    const fetchDashboard = useCallback(async (dateParam = selectedDate, periodeParam = periode) => {
        try {
            const resp = await client.get('/dashboard', { params: { date: dateParam, periode: periodeParam } });
            const data = resp.data?.data || resp.data;
            setDashboard(data);
            setErrorMsg('');
        } catch (e) {
            if (__DEV__) console.log('[Dashboard] échec:', e?.userMessage || apiErrorMessage(e));
            setErrorMsg(apiErrorMessage(e));
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, [selectedDate, periode]);

    useFocusEffect(
        useCallback(() => {
            fetchDashboard(selectedDate);
        }, [fetchDashboard, selectedDate])
    );

    const onRefresh = () => {
        setRefreshing(true);
        fetchDashboard(selectedDate);
    };

    const handleApplyDate = (dateVal) => {
        setSelectedDate(dateVal);
        setLoading(true);
        fetchDashboard(dateVal, periode);
    };

    const resetDateToToday = () => {
        const todayStr = new Date().toISOString().split('T')[0];
        setSelectedDate(todayStr);
        setLoading(true);
        fetchDashboard(todayStr, periode);
    };

    const shiftPeriod = (delta) => {
        const d = new Date(selectedDate);
        if (periode === 'jour') d.setDate(d.getDate() + delta);
        else if (periode === 'semaine') d.setDate(d.getDate() + delta * 7);
        else if (periode === 'mois') d.setMonth(d.getMonth() + delta);
        else if (periode === 'annee') d.setFullYear(d.getFullYear() + delta);
        const iso = d.toISOString().split('T')[0];
        setSelectedDate(iso);
        setLoading(true);
        fetchDashboard(iso, periode);
    };

    const submitDepense = async () => {
        const montant = parseFloat(depenseMontant);
        if (!montant || montant <= 0) {
            Alert.alert('Montant requis', 'Veuillez saisir un montant valide.');
            return;
        }
        setSubmittingDepense(true);
        try {
            await client.post('/dashboard/depense', {
                montant,
                description: depenseDesc,
                date: selectedDate,
            });
            setDepenseModalVisible(false);
            setDepenseMontant('');
            setDepenseDesc('');
            fetchDashboard(selectedDate);
        } catch (e) {
            Alert.alert('Erreur', 'Impossible d\'enregistrer la dépense.');
        } finally {
            setSubmittingDepense(false);
        }
    };

    const isToday = selectedDate === new Date().toISOString().split('T')[0];
    const tenant = user?.tenant;

    const periodeLabel = dashboard?.periodeLabel ?? '';
    const encaissePeriode = dashboard?.encaissePeriode ?? 0;
    const caPeriode = dashboard?.caPeriode ?? 0;
    const creancesPeriode = dashboard?.creancesPeriode ?? 0;
    const depensePeriode = dashboard?.depensePeriode ?? 0;

    const periph = { jour: 'du jour', semaine: 'de la semaine', mois: 'du mois', annee: "de l'année" }[periode] || 'du jour';
    const PERIODES = [
        { key: 'jour', label: 'Jour' },
        { key: 'semaine', label: 'Semaine' },
        { key: 'mois', label: 'Mois' },
        { key: 'annee', label: 'Année' },
    ];

    const revenuNet = dashboard?.revenuNetMois ?? 0;
    const ventesJour = dashboard?.ventesJour ?? 0;
    const caJour = dashboard?.caJour ?? 0;
    const creancesJour = dashboard?.creancesJour ?? 0;
    const depenseJour = dashboard?.depenseJour ?? 0;
    const dettePaiementsJour = dashboard?.dettePaiementsJour ?? 0;
    const totalDettes = dashboard?.totalDettes ?? 0;
    const dettesEnRetard = dashboard?.dettesEnRetard ?? 0;
    const dettesEnRetardListe = dashboard?.dettesEnRetardListe || [];
    const totalDettesSociete = dashboard?.totalDettesSociete ?? 0;
    const nbVentesJour = dashboard?.nbVentesJour ?? 0;
    const stockAlertes = dashboard?.stockAlertes || [];
    const meilleuresVentes = dashboard?.dernieresVentes || [];
    const topProduits = dashboard?.topProduits || [];
    const employes = dashboard?.employes || [];
    const statsParPersonne = dashboard?.statsParPersonne || [];
    const depensesDuJour = dashboard?.depensesDuJour || [];

    return (
        <View style={styles.container}>
            {/* Top Navigation Header replacing Drawer on all views */}
            <TopHeaderNav
                navigation={navigation}
                activeCategory="dashboard"
            />

            {/* Period Selector Bar (remplace la saisie manuelle de date) */}
            <View style={styles.dateFilterStrip}>
                <View style={styles.periodePills}>
                    {PERIODES.map((p) => (
                        <TouchableOpacity
                            key={p.key}
                            style={[styles.periodePill, periode === p.key && styles.periodePillActive]}
                            onPress={() => { setPeriode(p.key); setLoading(true); fetchDashboard(selectedDate, p.key); }}
                        >
                            <Text style={[styles.periodePillText, periode === p.key && styles.periodePillTextActive]}>{p.label}</Text>
                        </TouchableOpacity>
                    ))}
                </View>

                <View style={styles.periodeNavRow}>
                    <TouchableOpacity onPress={() => shiftPeriod(-1)} hitSlop={8}>
                        <Ionicons name="chevron-back" size={20} color={Colors.primary} />
                    </TouchableOpacity>
                    <TouchableOpacity
                        style={styles.periodeLabelWrap}
                        onPress={() => { if (periode === 'jour') { setTempDate(selectedDate); setDateModalVisible(true); } }}
                    >
                        <Text style={styles.periodeLabelText}>{periodeLabel || formatDate(selectedDate)}</Text>
                    </TouchableOpacity>
                    <TouchableOpacity onPress={() => shiftPeriod(1)} hitSlop={8}>
                        <Ionicons name="chevron-forward" size={20} color={Colors.primary} />
                    </TouchableOpacity>
                    {!isToday && (
                        <TouchableOpacity style={styles.resetDateBtn} onPress={resetDateToToday}>
                            <Ionicons name="refresh-outline" size={14} color={Colors.primary} />
                            <Text style={styles.resetDateText}>Aujourd'hui</Text>
                        </TouchableOpacity>
                    )}
                </View>

                {isDG && (
                    <TouchableOpacity style={styles.depenseHeaderBtn} onPress={() => setDepenseModalVisible(true)}>
                        <Ionicons name="add-circle-outline" size={14} color="#FFF" />
                        <Text style={styles.depenseBtnText}>Dépense</Text>
                    </TouchableOpacity>
                )}
            </View>

            {/* Dashboard Content */}
            <ScrollView
                contentContainerStyle={styles.scrollContent}
                showsVerticalScrollIndicator={false}
                refreshControl={
                    <RefreshControl
                        refreshing={refreshing}
                        onRefresh={onRefresh}
                        colors={[Colors.primary]}
                        tintColor={Colors.primary}
                    />
                }
            >
                {/* Company Tenant Banner */}
                <View style={styles.tenantBanner}>
                    <Text style={styles.tenantName}>{tenant?.nom || 'Ma Société'}</Text>
                    <View style={styles.badgeOffre}>
                        <View style={styles.dotActive} />
                        <Text style={styles.badgeOffreText}>Offre {tenant?.offre_code || 'Professionnel'}</Text>
                    </View>
                </View>

                {loading ? (
                    <View style={styles.loader}>
                        <ActivityIndicator size="large" color={Colors.primary} />
                        <Text style={styles.loaderText}>Chargement des indicateurs...</Text>
                    </View>
                ) : errorMsg && !dashboard ? (
                    <View style={[styles.loader, { marginTop: 30 }]}>
                        <Ionicons name="cloud-offline-outline" size={42} color="#DC2626" />
                        <Text style={[styles.loaderText, { color: '#DC2626', marginTop: 10, textAlign: 'center', paddingHorizontal: 20 }]}>{errorMsg}</Text>
                    </View>
                ) : (
                    <>
                        {/* 1. Stat Boxes Grid (selon la période sélectionnée) */}
                        <View style={styles.statsGrid}>
                            <View style={styles.statBox}>
                                <View style={styles.boxIconWrap}>
                                    <Ionicons name="cash-outline" size={18} color={Colors.primary} />
                                </View>
                                <Text style={styles.statValue}>{formatMoney(encaissePeriode)}</Text>
                                <Text style={styles.statLabel}>Encaissé {periph}</Text>
                            </View>

                            <View style={styles.statBox}>
                                <View style={styles.boxIconWrap}>
                                    <Ionicons name="stats-chart-outline" size={18} color={Colors.primary} />
                                </View>
                                <Text style={styles.statValue}>{formatMoney(caPeriode)}</Text>
                                <Text style={styles.statLabel}>C.A. {periph}</Text>
                            </View>

                            <View style={styles.statBox}>
                                <View style={styles.boxIconWrap}>
                                    <Ionicons name="alert-circle-outline" size={18} color={Colors.warning} />
                                </View>
                                <Text style={styles.statValue}>{formatMoney(creancesPeriode)}</Text>
                                <Text style={styles.statLabel}>Créances {periph}</Text>
                            </View>
                        </View>

                        <View style={styles.statsGrid}>
                            <View style={styles.statBox}>
                                <View style={styles.boxIconWrap}>
                                    <Ionicons name="receipt-outline" size={18} color={Colors.error} />
                                </View>
                                <Text style={[styles.statValue, { color: Colors.error }]}>-{formatMoney(depensePeriode)}</Text>
                                <Text style={styles.statLabel}>Dépenses {periph}</Text>
                            </View>

                            <View style={styles.statBox}>
                                <View style={styles.boxIconWrap}>
                                    <Ionicons name="wallet-outline" size={18} color={Colors.warning} />
                                </View>
                                <Text style={styles.statValue}>{formatMoney(dettePaiementsJour)}</Text>
                                <Text style={styles.statLabel}>Dettes encaissées</Text>
                            </View>
                        </View>

                        {/* 3. Debts Stats Row */}
                        <View style={styles.statsGrid}>
                            <TouchableOpacity style={styles.statBox} onPress={() => navigation.navigate('DrawerDettes')}>
                                <View style={styles.boxIconWrap}>
                                    <Ionicons name="card-outline" size={18} color={Colors.error} />
                                </View>
                                <Text style={[styles.statValue, { color: Colors.error }]}>{formatMoney(totalDettes)}</Text>
                                <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4 }}>
                                    <Text style={styles.statLabel}>Dettes Clients</Text>
                                    {dettesEnRetard > 0 && (
                                        <View style={styles.badgeRetard}>
                                            <Text style={styles.badgeRetardText}>{dettesEnRetard} retard</Text>
                                        </View>
                                    )}
                                </View>
                            </TouchableOpacity>

                            <TouchableOpacity style={styles.statBox} onPress={() => navigation.navigate('DrawerDettesSociete')}>
                                <View style={styles.boxIconWrap}>
                                    <Ionicons name="business-outline" size={18} color={Colors.primary} />
                                </View>
                                <Text style={[styles.statValue, { color: Colors.primary }]}>-{formatMoney(totalDettesSociete)}</Text>
                                <Text style={styles.statLabel}>Nos dettes</Text>
                            </TouchableOpacity>
                        </View>

                        {/* 4. Dernières Ventes du Jour */}
                        <View style={styles.cardSection}>
                            <View style={styles.cardSectionHeader}>
                                <Text style={styles.cardSectionTitle}>
                                    Ventes du jour ({nbVentesJour})
                                </Text>
                                <TouchableOpacity onPress={() => navigation.navigate('VenteCreate')}>
                                    <Text style={styles.btnNewAction}>+ Nouvelle vente</Text>
                                </TouchableOpacity>
                            </View>

                            {meilleuresVentes.length > 0 ? (
                                meilleuresVentes.map((v, i) => (
                                    <TouchableOpacity
                                        key={i}
                                        style={styles.saleRow}
                                        onPress={() => navigation.navigate('VenteShow', { id: v.id })}
                                        activeOpacity={0.7}
                                    >
                                        <View style={{ flex: 1 }}>
                                            <Text style={styles.saleRef}>{v.reference || `Vente #${v.id}`}</Text>
                                            <Text style={styles.saleClient}>{v.client?.nom ? `${v.client.nom} ${v.client.prenom || ''}` : 'Client Anonyme'}</Text>
                                        </View>
                                        <View style={{ alignItems: 'flex-end' }}>
                                            <Text style={styles.saleAmount}>{formatMoney(v.montant_total)}</Text>
                                            <View style={[
                                                styles.statusTag,
                                                { backgroundColor: v.statut_paiement === 'paye' ? Colors.primaryLight : '#FEE2E2' }
                                            ]}>
                                                <Text style={[
                                                    styles.statusTagText,
                                                    { color: v.statut_paiement === 'paye' ? Colors.primary : Colors.error }
                                                ]}>
                                                    {v.statut_paiement === 'paye' ? 'Payé' : v.statut_paiement === 'partiel' ? 'Partiel' : 'Impayé'}
                                                </Text>
                                            </View>
                                        </View>
                                    </TouchableOpacity>
                                ))
                            ) : (
                                <Text style={styles.emptyText}>Aucune vente enregistrée ce jour</Text>
                            )}

                            <TouchableOpacity style={styles.viewAllBtn} onPress={() => navigation.navigate('Ventes')}>
                                <Text style={styles.viewAllBtnText}>Voir toutes les ventes →</Text>
                            </TouchableOpacity>
                        </View>

                        {/* 5. Créances en Retard */}
                        {dettesEnRetardListe.length > 0 && (
                            <View style={[styles.cardSection, { borderColor: '#FEE2E2' }]}>
                                <View style={styles.cardSectionHeader}>
                                    <Text style={[styles.cardSectionTitle, { color: Colors.error }]}>
                                        ⚠️ Créances en retard ({dettesEnRetard})
                                    </Text>
                                </View>

                                {dettesEnRetardListe.map((d, i) => (
                                    <View key={i} style={styles.retardRow}>
                                        <View style={{ flex: 1 }}>
                                            <Text style={styles.retardClient}>{d.client?.nom ? `${d.client.nom} ${d.client.prenom || ''}` : `Dette #${d.id}`}</Text>
                                            <Text style={styles.retardSub}>Échéance : {formatDateFr(d.date_echeance)}</Text>
                                        </View>
                                        <View style={{ alignItems: 'flex-end', gap: 4 }}>
                                            <Text style={styles.retardAmount}>{formatMoney(d.reste_a_payer)}</Text>
                                            <TouchableOpacity
                                                style={styles.btnEncaisser}
                                                onPress={() => navigation.navigate('DetteShow', { id: d.id })}
                                            >
                                                <Ionicons name="cash-outline" size={12} color="#FFF" />
                                                <Text style={styles.btnEncaisserText}>Encaisser</Text>
                                            </TouchableOpacity>
                                        </View>
                                    </View>
                                ))}
                            </View>
                        )}

                        {/* 6. Ruptures & Alertes de Stock */}
                        {stockAlertes.length > 0 && (
                            <View style={styles.cardSection}>
                                <View style={styles.cardSectionHeader}>
                                    <Text style={[styles.cardSectionTitle, { color: Colors.error }]}>
                                        📦 Alertes de Stock ({stockAlertes.length})
                                    </Text>
                                </View>

                                {stockAlertes.map((st, i) => (
                                    <View key={i} style={styles.stockAlertRow}>
                                        <Text style={styles.stockAlertName}>{st.produit?.nom || 'Produit'}</Text>
                                        <View style={styles.stockBadgeDanger}>
                                            <Text style={styles.stockBadgeDangerText}>{st.quantite} dispo (seuil {st.seuil_alerte})</Text>
                                        </View>
                                    </View>
                                ))}

                                {hasCapability('import') && (
                                <TouchableOpacity style={styles.btnCommanderArrivage} onPress={() => navigation.navigate('ArrivageCreate')}>
                                    <Ionicons name="cart-outline" size={16} color="#FFFFFF" />
                                    <Text style={styles.btnCommanderText}>Créer un Arrivage</Text>
                                </TouchableOpacity>
                                )}
                            </View>
                        )}

                        {/* 7. Top Produits Vendus */}
                        {topProduits.length > 0 && (
                            <View style={styles.cardSection}>
                                <View style={styles.cardSectionHeader}>
                                    <Text style={styles.cardSectionTitle}>🏆 Top Produits vendus</Text>
                                </View>
                                {topProduits.map((p, i) => (
                                    <View key={i} style={styles.topProdRow}>
                                        <View style={styles.rankCircle}>
                                            <Text style={styles.rankText}>{i + 1}</Text>
                                        </View>
                                        <Text style={styles.topProdName}>{p.nom}</Text>
                                        <Text style={styles.topProdQty}>{p.total_vendu || 0} vendus</Text>
                                    </View>
                                ))}
                            </View>
                        )}

                        {/* 7b. Stimulateur CA + Trésorerie (après le Top Produits) */}
                        <View style={{ flexDirection: 'row', paddingHorizontal: 16, paddingTop: 14, gap: 10, alignItems: 'center' }}>
                            <StimulateurCA />
                            <TouchableOpacity style={styles.tresoPillDash} onPress={() => navigation.navigate('Tresorerie')} activeOpacity={0.88}>
                                <Ionicons name="cash" size={18} color="#FFFFFF" />
                                <Text style={styles.tresoPillDashText}>Trésorerie</Text>
                            </TouchableOpacity>
                        </View>

                        {/* 8. Performances par Vendeur */}
                        {statsParPersonne.length > 0 && (
                            <View style={styles.cardSection}>
                                <View style={styles.cardSectionHeader}>
                                    <Text style={styles.cardSectionTitle}>💼 Ventes par Vendeur</Text>
                                </View>
                                {statsParPersonne.map((st, i) => (
                                    <View key={i} style={styles.sellerRow}>
                                        <Text style={styles.sellerName}>{st.name || 'Vendeur'}</Text>
                                        <Text style={styles.sellerTotal}>{formatMoney(st.total_ca)}</Text>
                                    </View>
                                ))}
                            </View>
                        )}


                        {/* 10. Dépenses du Jour */}
                        <View style={styles.cardSection}>
                            <View style={styles.cardSectionHeader}>
                                <Text style={styles.cardSectionTitle}>🧾 Dépenses du Jour ({depensesDuJour.length})</Text>
                                <TouchableOpacity onPress={() => navigation.navigate('Depenses')}>
                                    <Text style={{ fontSize: 12, color: Colors.primary, fontWeight: '600' }}>Voir tout</Text>
                                </TouchableOpacity>
                            </View>
                            {depensesDuJour.length > 0 ? (
                                depensesDuJour.map((d, i) => (
                                    <View key={i} style={styles.depenseRow}>
                                        <View style={{ flex: 1 }}>
                                            <Text style={styles.depenseDesc}>{d.description || 'Dépense'}</Text>
                                            <Text style={styles.depenseUser}>{d.user?.name || ''}</Text>
                                        </View>

                                        <Text style={styles.depenseAmount}>-{formatMoney(d.montant)}</Text>
                                    </View>
                                ))
                            ) : (
                                <Text style={styles.emptyText}>Aucune dépense enregistrée aujourd'hui</Text>
                            )}
                        </View>
                    </>
                )}
            </ScrollView>

            {/* Modal Sélection Date */}
            <Modal visible={dateModalVisible} transparent animationType="fade">
                <View style={styles.modalOverlay}>
                    <View style={styles.modalContent}>
                        <Text style={styles.modalTitle}>Filtrer par date</Text>
                        <Text style={styles.inputLabel}>Saisir une date (AAAA-MM-JJ)</Text>
                        <TextInput
                            style={styles.modalInput}
                            value={tempDate}
                            onChangeText={setTempDate}
                            placeholder="ex: 2026-08-18"
                        />
                        <View style={styles.modalActionsRow}>
                            <TouchableOpacity style={styles.modalBtnCancel} onPress={() => setDateModalVisible(false)}>
                                <Text style={styles.modalBtnCancelText}>Annuler</Text>
                            </TouchableOpacity>
                            <TouchableOpacity style={styles.modalBtnSubmit} onPress={() => { setDateModalVisible(false); handleApplyDate(tempDate); }}>
                                <Text style={styles.modalBtnSubmitText}>Appliquer</Text>
                            </TouchableOpacity>
                        </View>
                    </View>
                </View>
            </Modal>

            {/* Modal Saisie Dépense */}
            <Modal visible={depenseModalVisible} transparent animationType="fade">
                <View style={styles.modalOverlay}>
                    <View style={styles.modalContent}>
                        <Text style={styles.modalTitle}>Enregistrer une dépense</Text>

                        <Text style={styles.inputLabel}>Montant (FCFA) *</Text>
                        <TextInput
                            style={styles.modalInput}
                            value={depenseMontant}
                            onChangeText={setDepenseMontant}
                            placeholder="ex: 5000"
                            keyboardType="numeric"
                        />

                        <Text style={styles.inputLabel}>Motif / Description</Text>
                        <TextInput
                            style={[styles.modalInput, { height: 70, textAlignVertical: 'top' }]}
                            value={depenseDesc}
                            onChangeText={setDepenseDesc}
                            placeholder="ex: Achat d'emballages"
                            multiline
                        />

                        <View style={styles.modalActionsRow}>
                            <TouchableOpacity style={styles.modalBtnCancel} onPress={() => setDepenseModalVisible(false)}>
                                <Text style={styles.modalBtnCancelText}>Annuler</Text>
                            </TouchableOpacity>
                            <TouchableOpacity style={styles.modalBtnSubmit} onPress={submitDepense} disabled={submittingDepense}>
                                {submittingDepense ? (
                                    <ActivityIndicator color="#FFF" size="small" />
                                ) : (
                                    <Text style={styles.modalBtnSubmitText}>Enregistrer</Text>
                                )}
                            </TouchableOpacity>
                        </View>
                    </View>
                </View>
            </Modal>
        </View>
    );
};

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#FFFFFF',
    },
    // Date Filter Strip
    dateFilterStrip: {
        flexDirection: 'column',
        alignItems: 'stretch',
        gap: 8,
        paddingHorizontal: 16,
        paddingVertical: 10,
        backgroundColor: '#FAFAFA',
        borderBottomWidth: 1,
        borderBottomColor: '#F1F5F9',
    },
    periodePills: {
        flexDirection: 'row',
        gap: 6,
    },
    periodePill: {
        flex: 1,
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: '#FFFFFF',
        paddingVertical: 8,
        borderRadius: 10,
        borderWidth: 1,
        borderColor: '#E2E8F0',
    },
    periodePillActive: {
        backgroundColor: Colors.primary,
        borderColor: Colors.primary,
    },
    periodePillText: {
        fontSize: 12.5,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.text,
    },
    periodePillTextActive: {
        color: '#FFFFFF',
    },
    periodeNavRow: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 10,
    },
    periodeLabelWrap: {
        flex: 1,
        alignItems: 'center',
    },
    periodeLabelText: {
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: Colors.text,
        textAlign: 'center',
    },
    dateSelectorPill: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 6,
        backgroundColor: '#FFFFFF',
        paddingHorizontal: 12,
        paddingVertical: 6,
        borderRadius: 16,
        borderWidth: 1,
        borderColor: '#E2E8F0',
    },
    dateSelectorText: {
        fontSize: 12,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.text,
    },
    resetDateBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 4,
        backgroundColor: Colors.primaryLight,
        paddingHorizontal: 10,
        paddingVertical: 6,
        borderRadius: 16,
    },
    resetDateText: {
        fontSize: 12,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.primary,
    },
    depenseHeaderBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 4,
        backgroundColor: Colors.error,
        paddingHorizontal: 10,
        paddingVertical: 6,
        borderRadius: 16,
        alignSelf: 'center',
    },
    depenseBtnText: {
        fontSize: 12,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: '#FFFFFF',
    },

    scrollContent: {
        padding: 16,
        paddingBottom: 36,
    },
    tenantBanner: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        marginBottom: 14,
    },
    tenantName: {
        fontSize: 19,
        fontFamily: 'SpaceGrotesk_700Bold',
        color: Colors.text,
    },
    badgeOffre: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 6,
        backgroundColor: Colors.primaryLight,
        paddingHorizontal: 10,
        paddingVertical: 4,
        borderRadius: 12,
    },
    dotActive: {
        width: 6,
        height: 6,
        borderRadius: 3,
        backgroundColor: Colors.primary,
    },
    badgeOffreText: {
        fontSize: 11,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: Colors.primary,
    },

    loader: {
        paddingVertical: 40,
        alignItems: 'center',
    },
    loaderText: {
        marginTop: 10,
        color: Colors.textLight,
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_400Regular',
    },

    // Net Revenue Hero Card
    netRevenueCard: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: Colors.primaryLight,
        borderRadius: 18,
        padding: 18,
        marginBottom: 14,
        borderWidth: 1,
        borderColor: '#A7F3D0',
    },
    netRevenueIconWrap: {
        width: 44,
        height: 44,
        borderRadius: 22,
        backgroundColor: '#FFFFFF',
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: 14,
    },
    netRevenueValue: {
        fontSize: 22,
        fontFamily: 'SpaceGrotesk_700Bold',
        color: Colors.primary,
    },
    netRevenueLabel: {
        fontSize: 12,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.primary,
        marginTop: 2,
    },

    // Stats Grid
    statsGrid: {
        flexDirection: 'row',
        gap: 10,
        marginBottom: 10,
    },
    statBox: {
        flex: 1,
        backgroundColor: '#FFFFFF',
        borderRadius: 16,
        padding: 14,
        borderWidth: 1,
        borderColor: '#E2E8F0',
    },
    boxIconWrap: {
        width: 34,
        height: 34,
        borderRadius: 10,
        backgroundColor: '#F8FAFC',
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 8,
    },
    tresoPillDash: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 6,
        backgroundColor: '#111111',
        paddingHorizontal: 14,
        paddingVertical: 8,
        borderRadius: 20,
        elevation: 2,
        shadowColor: '#000000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.25,
        shadowRadius: 4,
    },
    tresoPillDashText: {
        color: '#FFFFFF',
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_700Bold',
    },
    statValue: {
        fontSize: 15,
        fontFamily: 'SpaceGrotesk_700Bold',
        color: Colors.text,
    },
    statLabel: {
        fontSize: 11,
        fontFamily: 'PlusJakartaSans_400Regular',
        color: Colors.textLight,
        marginTop: 2,
    },
    badgeRetard: {
        backgroundColor: '#FEE2E2',
        paddingHorizontal: 6,
        paddingVertical: 2,
        borderRadius: 6,
    },
    badgeRetardText: {
        fontSize: 10,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: Colors.error,
    },

    // Card Section
    cardSection: {
        backgroundColor: '#FFFFFF',
        borderRadius: 18,
        padding: 16,
        marginBottom: 14,
        borderWidth: 1,
        borderColor: '#E2E8F0',
    },
    cardSectionHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 12,
    },
    cardSectionTitle: {
        fontSize: 14.5,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: Colors.text,
    },
    btnNewAction: {
        fontSize: 12,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: Colors.primary,
        backgroundColor: Colors.primaryLight,
        paddingHorizontal: 10,
        paddingVertical: 4,
        borderRadius: 12,
    },
    saleRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingVertical: 10,
        borderBottomWidth: 1,
        borderBottomColor: '#F1F5F9',
    },
    saleRef: {
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: Colors.primary,
    },
    saleClient: {
        fontSize: 11.5,
        fontFamily: 'PlusJakartaSans_400Regular',
        color: Colors.textLight,
        marginTop: 2,
    },
    saleAmount: {
        fontSize: 13.5,
        fontFamily: 'SpaceGrotesk_700Bold',
        color: Colors.text,
    },
    statusTag: {
        paddingHorizontal: 8,
        paddingVertical: 3,
        borderRadius: 8,
        marginTop: 2,
    },
    statusTagText: {
        fontSize: 10,
        fontFamily: 'PlusJakartaSans_700Bold',
    },
    emptyText: {
        textAlign: 'center',
        color: Colors.textLight,
        fontSize: 12.5,
        paddingVertical: 16,
        fontFamily: 'PlusJakartaSans_400Regular',
    },
    viewAllBtn: {
        marginTop: 10,
        alignItems: 'center',
        backgroundColor: '#F8FAFC',
        paddingVertical: 10,
        borderRadius: 12,
    },
    viewAllBtnText: {
        color: Colors.primary,
        fontSize: 12.5,
        fontFamily: 'PlusJakartaSans_700Bold',
    },

    retardRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingVertical: 10,
        borderBottomWidth: 1,
        borderBottomColor: '#FEE2E2',
    },
    retardClient: {
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: Colors.text,
    },
    retardSub: {
        fontSize: 11,
        fontFamily: 'PlusJakartaSans_400Regular',
        color: Colors.textLight,
    },
    retardAmount: {
        fontSize: 13.5,
        fontFamily: 'SpaceGrotesk_700Bold',
        color: Colors.error,
    },
    btnEncaisser: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 4,
        backgroundColor: Colors.warning,
        paddingHorizontal: 10,
        paddingVertical: 4,
        borderRadius: 12,
    },
    btnEncaisserText: {
        color: '#FFFFFF',
        fontSize: 11,
        fontFamily: 'PlusJakartaSans_700Bold',
    },

    stockAlertRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingVertical: 10,
        borderBottomWidth: 1,
        borderBottomColor: '#F1F5F9',
    },
    stockAlertName: {
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.text,
    },
    stockBadgeDanger: {
        backgroundColor: '#FEE2E2',
        paddingHorizontal: 8,
        paddingVertical: 4,
        borderRadius: 8,
    },
    stockBadgeDangerText: {
        color: Colors.error,
        fontSize: 11,
        fontFamily: 'PlusJakartaSans_700Bold',
    },
    btnCommanderArrivage: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 6,
        backgroundColor: Colors.primary,
        paddingVertical: 12,
        borderRadius: 22,
        marginTop: 14,
    },
    btnCommanderText: {
        color: '#FFFFFF',
        fontFamily: 'PlusJakartaSans_700Bold',
        fontSize: 13.5,
    },

    topProdRow: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 10,
        paddingVertical: 10,
        borderBottomWidth: 1,
        borderBottomColor: '#F1F5F9',
    },
    rankCircle: {
        width: 22,
        height: 22,
        borderRadius: 11,
        backgroundColor: Colors.primary,
        justifyContent: 'center',
        alignItems: 'center',
    },
    rankText: {
        color: '#FFFFFF',
        fontSize: 11,
        fontFamily: 'PlusJakartaSans_700Bold',
    },
    topProdName: {
        flex: 1,
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.text,
    },
    topProdQty: {
        fontSize: 12.5,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: Colors.primary,
    },

    sellerRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        paddingVertical: 10,
        borderBottomWidth: 1,
        borderBottomColor: '#F1F5F9',
    },
    sellerName: {
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.text,
    },
    sellerTotal: {
        fontSize: 13.5,
        fontFamily: 'SpaceGrotesk_700Bold',
        color: Colors.primary,
    },

    empRow: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 10,
        paddingVertical: 10,
        borderBottomWidth: 1,
        borderBottomColor: '#F1F5F9',
    },
    empAvatarWrap: {
        position: 'relative',
    },
    empAvatar: {
        width: 32,
        height: 32,
        borderRadius: 16,
        backgroundColor: '#E2E8F0',
        justifyContent: 'center',
        alignItems: 'center',
    },
    empAvatarText: {
        fontSize: 11,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: Colors.text,
    },
    onlineDot: {
        position: 'absolute',
        bottom: 0,
        right: 0,
        width: 8,
        height: 8,
        borderRadius: 4,
        borderWidth: 1.5,
        borderColor: '#FFFFFF',
    },
    empName: {
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.text,
    },
    empRole: {
        fontSize: 11,
        color: Colors.textLight,
        textTransform: 'capitalize',
        fontFamily: 'PlusJakartaSans_400Regular',
    },
    empStatusText: {
        fontSize: 11,
        fontFamily: 'PlusJakartaSans_600SemiBold',
    },

    depenseRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingVertical: 10,
        borderBottomWidth: 1,
        borderBottomColor: '#F1F5F9',
    },
    depenseDesc: {
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.text,
    },
    depenseUser: {
        fontSize: 11,
        color: Colors.textLight,
        fontFamily: 'PlusJakartaSans_400Regular',
    },
    depenseAmount: {
        fontSize: 13.5,
        fontFamily: 'SpaceGrotesk_700Bold',
        color: Colors.error,
    },
    modalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(15,23,42,0.4)',
        justifyContent: 'center',
        alignItems: 'center',
        padding: 20,
    },
    modalContent: {
        width: '100%',
        backgroundColor: '#FFFFFF',
        borderRadius: 20,
        padding: 20,
    },
    modalTitle: {
        fontSize: 17,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: Colors.text,
        marginBottom: 8,
    },
    inputLabel: {
        fontSize: 12,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.text,
        marginBottom: 6,
        marginTop: 8,
    },
    modalInput: {
        borderWidth: 1,
        borderColor: '#E2E8F0',
        borderRadius: 14,
        padding: 12,
        fontSize: 14,
        backgroundColor: '#F8FAFC',
        fontFamily: 'PlusJakartaSans_400Regular',
    },
    modalActionsRow: {
        flexDirection: 'row',
        gap: 10,
        marginTop: 18,
    },
    modalBtnCancel: {
        flex: 1,
        paddingVertical: 12,
        borderRadius: 20,
        backgroundColor: '#F1F5F9',
        alignItems: 'center',
    },
    modalBtnCancelText: {
        fontSize: 13.5,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.text,
    },
    modalBtnSubmit: {
        flex: 1,
        paddingVertical: 12,
        borderRadius: 20,
        backgroundColor: Colors.primary,
        alignItems: 'center',
    },
    modalBtnSubmitText: {
        fontSize: 13.5,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: '#FFFFFF',
    },
});

export default DashboardScreen;
