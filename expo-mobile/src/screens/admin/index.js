import React, { useState, useCallback } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    TextInput, StatusBar, ActivityIndicator, RefreshControl, Alert
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useNavigation, useFocusEffect, DrawerActions } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import Colors from '../../theme/Colors';
import client from '../../api/client';
import { StatusBadge } from './components';

const fmtMoney = (val) => `${(Number(val || 0)).toLocaleString('fr-FR')} F`;
const fmtNb = (val) => (Number(val || 0)).toLocaleString('fr-FR');

const AdminHomeScreen = () => {
    const insets = useSafeAreaInsets();
    const navigation = useNavigation();

    const [tenants, setTenants] = useState([]);
    const [stats, setStats] = useState(null);
    const [expired, setExpired] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [query, setQuery] = useState('');

    const fetchData = useCallback(async () => {
        try {
            const res = await client.get('/tenants');
            const pag = res.data?.data;
            const list = Array.isArray(pag) ? pag : (pag?.data || []);
            setTenants(list);
            setStats(res.data?.stats || null);
            setExpired(res.data?.societesExpirees || []);
        } catch (e) {
            console.error('Error fetching tenants:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useFocusEffect(useCallback(() => { fetchData(); }, [fetchData]));

    const removeTenant = async (t) => {
        Alert.alert(
            'Supprimer la société',
            `Êtes-vous sûr de vouloir supprimer « ${t.nom} » ? Toutes ses données associées seront indisponibles.`,
            [
                { text: 'Annuler', style: 'cancel' },
                {
                    text: 'Supprimer', style: 'destructive',
                    onPress: async () => {
                        try {
                            await client.delete(`/tenants/${t.id}`);
                            fetchData();
                        } catch (e) {
                            Alert.alert('Erreur', e.response?.data?.message || 'Suppression impossible');
                        }
                    },
                },
            ]
        );
    };

    const filtered = tenants.filter((t) => {
        if (!query) return true;
        const q = query.toLowerCase();
        return [t.nom, t.marque, t.activite, t.ville, t.pays, t.email]
            .filter(Boolean)
            .some((f) => f.toLowerCase().includes(q));
    });

    const kpis = stats ? [
        { key: 'partenaires', label: 'Partenaires', value: fmtNb(stats.nbPartenaires), icon: 'people', color: Colors.warning },
        { key: 'ventes', label: 'Offres Vendues', value: fmtNb(stats.nbVentes), icon: 'receipt', color: Colors.info },
        { key: 'total', label: 'Total Ventes (Offres)', value: fmtMoney(stats.totalPrixVente), icon: 'cash', color: Colors.success },
        { key: 'apayer', label: 'À payer aux Partenaires', value: fmtMoney(stats.totalAPayer), icon: 'hourglass', color: Colors.error },
        { key: 'regle', label: 'Déjà Réglé', value: fmtMoney(stats.totalRegle), icon: 'wallet', color: Colors.info },
        { key: 'net', label: 'Revenu Net (Plateforme)', value: fmtMoney(stats.revenuNet), icon: 'pie-chart', color: Colors.success },
    ] : [];

    if (loading) {
        return (
            <View style={[styles.container, { paddingTop: insets.top }]}>
                <StatusBar barStyle="dark-content" backgroundColor={Colors.background} />
                <View style={styles.header}>
                    <TouchableOpacity onPress={() => navigation.dispatch(DrawerActions.openDrawer())} style={styles.menuBtn}>
                        <Ionicons name="apps" size={26} color={Colors.text} />
                    </TouchableOpacity>
                    <View>
                        <Text style={styles.headerTitle}>Gestion des Sociétés</Text>
                        <Text style={styles.headerSubtitle}>Pilotix</Text>
                    </View>
                </View>
                <View style={styles.center}><ActivityIndicator size="large" color={Colors.primary} /></View>
            </View>
        );
    }

    return (
        <View style={[styles.container, { paddingTop: insets.top }]}>
            <StatusBar barStyle="dark-content" backgroundColor={Colors.background} />
            <View style={styles.header}>
                <TouchableOpacity onPress={() => navigation.dispatch(DrawerActions.openDrawer())} style={styles.menuBtn}>
                    <Ionicons name="apps" size={26} color={Colors.text} />
                </TouchableOpacity>
                <View>
                    <Text style={styles.headerTitle}>Gestion des Sociétés</Text>
                    <Text style={styles.headerSubtitle}>Pilotix</Text>
                </View>
            </View>

            <ScrollView
                contentContainerStyle={styles.content}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={fetchData} colors={[Colors.primary]} />}
            >
                {/* Avertissement commissions */}
                <View style={styles.warnBox}>
                    <Ionicons name="warning" size={22} color={Colors.warning} />
                    <Text style={styles.warnText}>
                        <Text style={{ fontFamily: 'Poppins_700Bold' }}>Attention : </Text>
                        Ne créez pas de société par ici si vous voulez des commissions. Toute société créée depuis cette page ne sera pas liée à un partenaire et ne générera aucune commission. Pour générer des commissions, utilisez l'espace prestataire.
                    </Text>
                </View>

                {/* KPIs */}
                {stats ? (
                    <View style={styles.kpiGrid}>
                        {kpis.map((k) => (
                            <View key={k.key} style={[styles.kpiCard, { borderLeftColor: k.color }]}>
                                <Ionicons name={k.icon} size={22} color={k.color} />
                                <Text style={styles.kpiVal}>{k.value}</Text>
                                <Text style={styles.kpiLbl}>{k.label}</Text>
                            </View>
                        ))}
                    </View>
                ) : null}

                {/* Offres expirées */}
                {expired.length > 0 ? (
                    <View style={styles.expiredBox}>
                        <View style={styles.expiredHead}>
                            <Ionicons name="alert-circle" size={22} color={Colors.error} />
                            <Text style={styles.expiredTitle}>Offres expirées — Renouvellement requis</Text>
                        </View>
                        <Text style={styles.expiredHint}>
                            Les sociétés suivantes ont une offre expirée. Leurs utilisateurs sont en lecture seule.
                        </Text>
                        {expired.map((s) => (
                            <View key={s.id} style={styles.expiredItem}>
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.expiredName}>{s.nom}</Text>
                                    <Text style={styles.expiredSub}>
                                        Offre : {s.offre_code || 'N/A'}
                                        {s.partenaire ? ` — Partenaire : ${s.partenaire.name}` : ''}
                                    </Text>
                                </View>
                                <TouchableOpacity style={styles.renewBtn} onPress={() => navigation.navigate('TenantEdit', { id: s.id, item: s })}>
                                    <Text style={styles.renewBtnText}>Renouveler</Text>
                                </TouchableOpacity>
                            </View>
                        ))}
                    </View>
                ) : null}

                {/* Liste des sociétés */}
                <View style={styles.sectionHead}>
                    <Text style={styles.sectionTitle}>
                        <Ionicons name="business" size={16} color={Colors.primary} />  Liste des Sociétés clientes
                        <Text style={styles.countBadge}> {tenants.length}</Text>
                    </Text>
                    <TouchableOpacity style={styles.addBtn} onPress={() => navigation.navigate('TenantCreate')}>
                        <Ionicons name="add" size={18} color="#fff" />
                        <Text style={styles.addBtnText}>Nouvelle Société</Text>
                    </TouchableOpacity>
                </View>

                <View style={styles.searchWrap}>
                    <Ionicons name="search" size={18} color={Colors.textLight} />
                    <TextInput
                        style={styles.searchInput}
                        placeholder="Rechercher une société..."
                        placeholderTextColor={Colors.textLight}
                        value={query}
                        onChangeText={setQuery}
                    />
                </View>

                {filtered.length === 0 ? (
                    <View style={styles.empty}>
                        <Ionicons name="business-outline" size={44} color={Colors.textLight} />
                        <Text style={styles.emptyText}>Aucune société trouvée.</Text>
                    </View>
                ) : (
                    filtered.map((t) => (
                        <View key={t.id} style={styles.card}>
                            <View style={styles.cardTop}>
                                <Text style={styles.cardName}>{t.nom}</Text>
                                <StatusBadge
                                    statut={t.actif ? 'actif' : 'inactif'}
                                    map={{
                                        actif: { label: 'Actif', color: Colors.success, bg: Colors.success + '20' },
                                        inactif: { label: 'Inactif', color: Colors.error, bg: Colors.error + '20' },
                                    }}
                                />
                            </View>
                            <Text style={styles.cardSub}>{t.marque || '-'}{t.activite ? `  •  ${t.activite}` : ''}</Text>
                            <Text style={styles.cardSub}>
                                {[t.ville, t.pays].filter(Boolean).join(', ') || '-'}  •  {t.telephone || '-'}
                            </Text>
                            <Text style={styles.cardSub}>{t.email || '-'}</Text>
                            <View style={styles.cardMeta}>
                                <Text style={styles.metaText}>Magasins : <Text style={styles.metaStrong}>{t.magasins_count ?? 0}</Text></Text>
                                <Text style={styles.metaText}>Utilisateurs : <Text style={styles.metaStrong}>{t.users_count ?? 0}</Text></Text>
                            </View>
                            <View style={styles.cardActions}>
                                <TouchableOpacity style={[styles.actBtn, { flex: 1 }]} onPress={() => navigation.navigate('TenantShow', { id: t.id })}>
                                    <Ionicons name="eye" size={16} color={Colors.primary} />
                                    <Text style={[styles.actText, { color: Colors.primary }]}>Voir</Text>
                                </TouchableOpacity>
                                <TouchableOpacity style={[styles.actBtn, { flex: 1 }]} onPress={() => navigation.navigate('TenantEdit', { id: t.id, item: t })}>
                                    <Ionicons name="pencil" size={16} color={Colors.text} />
                                    <Text style={styles.actText}>Modifier</Text>
                                </TouchableOpacity>
                                <TouchableOpacity style={[styles.actBtn, { flex: 1 }]} onPress={() => removeTenant(t)}>
                                    <Ionicons name="trash" size={16} color={Colors.error} />
                                    <Text style={[styles.actText, { color: Colors.error }]}>Supprimer</Text>
                                </TouchableOpacity>
                            </View>
                        </View>
                    ))
                )}
            </ScrollView>
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: Colors.background },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    header: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 20, paddingVertical: 16, borderBottomWidth: 1, borderBottomColor: Colors.border },
    menuBtn: { marginRight: 14 },
    headerTitle: { fontSize: 20, fontFamily: 'Poppins_700Bold', color: Colors.text },
    headerSubtitle: { fontSize: 13, fontFamily: 'Poppins_500Medium', color: Colors.textLight, marginTop: 2 },
    content: { padding: 16, gap: 16 },
    warnBox: { flexDirection: 'row', gap: 10, backgroundColor: '#FFF7ED', borderWidth: 1, borderColor: '#FED7AA', borderRadius: 12, padding: 14 },
    warnText: { flex: 1, fontSize: 12.5, fontFamily: 'Poppins_400Regular', color: '#9A3412', lineHeight: 18 },
    kpiGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
    kpiCard: { width: '47.5%', backgroundColor: Colors.cardBg, borderRadius: 14, padding: 14, borderLeftWidth: 4, borderWidth: 1, borderColor: Colors.border },
    kpiVal: { fontSize: 18, fontFamily: 'Poppins_700Bold', color: Colors.text, marginTop: 8 },
    kpiLbl: { fontSize: 11.5, fontFamily: 'Poppins_500Medium', color: Colors.textLight, marginTop: 2 },
    expiredBox: { backgroundColor: '#FEF2F2', borderWidth: 1, borderColor: '#FECACA', borderRadius: 12, padding: 16 },
    expiredHead: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    expiredTitle: { fontSize: 15, fontFamily: 'Poppins_700Bold', color: '#991B1B' },
    expiredHint: { fontSize: 12, fontFamily: 'Poppins_400Regular', color: '#991B1B', marginTop: 6, marginBottom: 10 },
    expiredItem: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#fff', borderWidth: 1, borderColor: '#FECACA', borderRadius: 10, padding: 12, marginTop: 8 },
    expiredName: { fontSize: 14, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    expiredSub: { fontSize: 12, fontFamily: 'Poppins_400Regular', color: Colors.textLight, marginTop: 2 },
    renewBtn: { backgroundColor: Colors.primary, paddingHorizontal: 14, paddingVertical: 8, borderRadius: 8 },
    renewBtnText: { color: '#fff', fontSize: 12.5, fontFamily: 'Poppins_600SemiBold' },
    sectionHead: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginTop: 6 },
    sectionTitle: { fontSize: 16, fontFamily: 'Poppins_700Bold', color: Colors.text },
    countBadge: { fontSize: 12, backgroundColor: '#F1F5F9', color: Colors.textLight, borderRadius: 20, paddingHorizontal: 8, paddingVertical: 2, fontFamily: 'Poppins_600SemiBold' },
    addBtn: { flexDirection: 'row', alignItems: 'center', gap: 4, backgroundColor: Colors.primary, paddingHorizontal: 12, paddingVertical: 8, borderRadius: 10 },
    addBtnText: { color: '#fff', fontSize: 12.5, fontFamily: 'Poppins_600SemiBold' },
    searchWrap: { flexDirection: 'row', alignItems: 'center', gap: 8, backgroundColor: Colors.cardBg, borderWidth: 1, borderColor: Colors.border, borderRadius: 12, paddingHorizontal: 12, paddingVertical: 10 },
    searchInput: { flex: 1, fontSize: 14, fontFamily: 'Poppins_400Regular', color: Colors.text },
    empty: { alignItems: 'center', paddingVertical: 40 },
    emptyText: { marginTop: 10, fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.textLight },
    card: { backgroundColor: Colors.cardBg, borderRadius: 14, padding: 14, borderWidth: 1, borderColor: Colors.border },
    cardTop: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
    cardName: { fontSize: 16, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    cardSub: { fontSize: 12.5, fontFamily: 'Poppins_400Regular', color: Colors.textLight, marginTop: 4 },
    cardMeta: { flexDirection: 'row', gap: 16, marginTop: 10 },
    metaText: { fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.text },
    metaStrong: { fontFamily: 'Poppins_700Bold', color: Colors.text },
    cardActions: { flexDirection: 'row', gap: 8, marginTop: 12 },
    actBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6, backgroundColor: Colors.background, borderWidth: 1, borderColor: Colors.border, borderRadius: 10, paddingVertical: 8 },
    actText: { fontSize: 12.5, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
});

export default AdminHomeScreen;
