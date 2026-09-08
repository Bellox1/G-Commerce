import React, { useState, useEffect, useCallback } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    ActivityIndicator, RefreshControl, StatusBar, useWindowDimensions
} from 'react-native';
import { Picker } from '@react-native-picker/picker';
import { LineChart, BarChart, PieChart } from 'react-native-chart-kit';
import { useFocusEffect } from '@react-navigation/native';
import { useAuth } from '../context/AuthContext';
import Colors from '../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import TopHeaderNav from '../components/TopHeaderNav';

const formatMoney = (val) => {
    if (val === null || val === undefined || val === '') return '0 F';
    let n = Math.round(Number(val));
    if (!n || Math.abs(n) === 0) n = 0;
    return n.toLocaleString('fr-FR') + ' F';
};

const fmtY = (v) => {
    const n = Number(v);
    return n >= 1000 ? (n / 1000).toFixed(0) + 'k' : String(n);
};

const MOIS = [
    { v: '01', l: 'Janvier' }, { v: '02', l: 'Février' }, { v: '03', l: 'Mars' },
    { v: '04', l: 'Avril' }, { v: '05', l: 'Mai' }, { v: '06', l: 'Juin' },
    { v: '07', l: 'Juillet' }, { v: '08', l: 'Août' }, { v: '09', l: 'Septembre' },
    { v: '10', l: 'Octobre' }, { v: '11', l: 'Novembre' }, { v: '12', l: 'Décembre' },
];

const chartConfig = {
    backgroundGradientFrom: '#FFFFFF',
    backgroundGradientTo: '#FFFFFF',
    decimalPlaces: 0,
    color: (opacity = 1) => `rgba(16,94,73,${opacity})`,
    labelColor: (opacity = 1) => `rgba(107,114,128,${opacity})`,
    style: { borderRadius: 16 },
    propsForDots: { r: '3', strokeWidth: '2', stroke: '#105e49' },
    propsForBackgroundLines: { stroke: '#F1F5F9', strokeWidth: 1 },
    propsForLabels: { fontSize: 10, fontFamily: 'Poppins_500Medium' },
};

class ChartBoundary extends React.Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false };
    }
    static getDerivedStateFromError() {
        return { hasError: true };
    }
    componentDidCatch(error) {
        console.warn('Chart render error:', error);
    }
    render() {
        if (this.state.hasError) {
            return <Text style={styles.chartError}>Graphique indisponible</Text>;
        }
        return this.props.children;
    }
}

const AnalytiqueScreen = ({ navigation }) => {
    const { user } = useAuth();
    const insets = useSafeAreaInsets();
    const { width: windowWidth } = useWindowDimensions();
    const chartWidth = Math.max(260, windowWidth - 64);

    const currentYear = new Date().getFullYear();
    const years = [currentYear - 3, currentYear - 2, currentYear - 1, currentYear];
    const [annee, setAnnee] = useState(currentYear);
    // null = pas de filtre mois (vue annuelle), sinon '01'..'12'
    const [mois, setMois] = useState(null);
    const [raw, setRaw] = useState(null);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const fetchAnalytique = useCallback(async () => {
        try {
            const params = { annee };
            if (mois) params.mois = mois;
            const resp = await client.get('/analytique', { params });
            const body = resp.data;
            const payload = body && body.data !== undefined ? body.data : body;
            setRaw(payload || {});
        } catch (e) {
            console.error('Error fetching analytique:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, [annee, mois]);

    useEffect(() => { fetchAnalytique(); }, [fetchAnalytique]);

    const onRefresh = () => { setRefreshing(true); fetchAnalytique(); };

    // Nombre de mois écoulés dans l'année sélectionnée
    // (si année en cours → mois courant, si année passée → 12)
    const moisEcoules = annee === currentYear ? new Date().getMonth() + 1 : 12;

    const totalVentesAn = raw?.moisData ? raw.moisData.reduce((a, b) => a + b, 0) : 0;
    const totalDepensesAn = raw?.depensesData ? raw.depensesData.reduce((a, b) => a + b, 0) : 0;
    // Loyer : on multiplie par les mois réellement écoulés, pas 12 fixes
    const totalLoyerAn = raw?.loyersCumules ?? (raw?.loyerMensuel || 0) * (mois ? 1 : moisEcoules);
    const totalNetAn = totalVentesAn - totalDepensesAn - totalLoyerAn;
    const nbVentesAn = raw?.nbVentesData ? raw.nbVentesData.reduce((a, b) => a + b, 0) : 0;

    const moisLabel = mois ? (MOIS.find(m => m.v === mois)?.l ?? mois) : null;

    const maxTop = raw?.topProduits?.length
        ? Math.max(...raw.topProduits.map(p => p.total_vendu || 0), 1)
        : 1;

    const ChartCard = ({ icon, title, sub, children }) => (
        <View style={styles.chartCard}>
            <View style={styles.chartCardHead}>
                <Ionicons name={icon} size={18} color={Colors.primary} />
                <Text style={styles.chartCardTitle}>{title}</Text>
            </View>
            <Text style={styles.chartCardSub}>{sub}</Text>
            {children}
        </View>
    );

    return (
        <View style={styles.container}>
            {/* Top Navigation Header replacing Drawer */}
            <TopHeaderNav navigation={navigation} activeCategory="analytique" />

            {/* View Title Header */}
            <View style={styles.viewHeaderRow}>
                <Text style={styles.headerTitle}>Analyse Avancée</Text>
                <Text style={styles.headerSub}>Graphiques et indicateurs — {user?.tenant?.nom || ''}</Text>
            </View>

            {loading ? (
                <View style={styles.centerLoader}>
                    <ActivityIndicator size="large" color={Colors.primary} />
                    <Text style={styles.loaderText}>Calcul des analyses en cours...</Text>
                </View>
            ) : (
                <ScrollView
                    contentContainerStyle={styles.scrollContent}
                    showsVerticalScrollIndicator={false}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
                >
                    {/* Sélecteur Année / Mois */}
                    <View style={styles.filterCard}>
                        {/* --- Ligne Année --- */}
                        <View style={styles.filterSection}>
                            <View style={styles.filterLabelRow}>
                                <Ionicons name="calendar-outline" size={13} color="#64748B" />
                                <Text style={styles.filterLabel}>Année</Text>
                            </View>
                            <View style={styles.yearChips}>
                                {years.map(y => (
                                    <TouchableOpacity
                                        key={y}
                                        style={[styles.yearChip, annee === y && styles.yearChipActive]}
                                        onPress={() => setAnnee(y)}
                                    >
                                        <Text style={[styles.yearChipText, annee === y && styles.yearChipTextActive]}>{y}</Text>
                                    </TouchableOpacity>
                                ))}
                            </View>
                        </View>

                        {/* --- Ligne Mois (optionnel) --- */}
                        <View style={[styles.filterSection, { borderTopWidth: 1, borderTopColor: '#EEF2F6', paddingTop: 12, marginTop: 4 }]}>
                            <View style={styles.filterLabelRow}>
                                <Ionicons name="filter-outline" size={13} color="#64748B" />
                                <Text style={styles.filterLabel}>Mois <Text style={styles.filterOptional}>(optionnel)</Text></Text>
                                {mois && (
                                    <TouchableOpacity onPress={() => setMois(null)} style={styles.clearBtn}>
                                        <Ionicons name="close-circle" size={15} color="#94A3B8" />
                                        <Text style={styles.clearBtnText}>Tout l'an</Text>
                                    </TouchableOpacity>
                                )}
                            </View>
                            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ gap: 6 }}>
                                {MOIS.map(m => (
                                    <TouchableOpacity
                                        key={m.v}
                                        style={[styles.moisChip, mois === m.v && styles.moisChipActive]}
                                        onPress={() => setMois(prev => prev === m.v ? null : m.v)}
                                    >
                                        <Text style={[styles.moisChipText, mois === m.v && styles.moisChipTextActive]}>{m.l.slice(0, 3)}</Text>
                                    </TouchableOpacity>
                                ))}
                            </ScrollView>
                        </View>
                    </View>

                    {/* 1. Ventes mensuelles */}
                    {raw?.moisLabels?.length > 0 && (
                        <ChartCard icon="trending-up" title="Ventes mensuelles" sub="Évolution des encaissements par mois (FCFA)">
                            <ChartBoundary>
                                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ paddingRight: 10 }}>
                                    <LineChart
                                        data={{
                                            labels: raw.moisLabels,
                                            datasets: [{ data: (raw.moisData || []).map(v => Number(v) || 0) }]
                                        }}
                                        width={Math.max(chartWidth, (raw.moisLabels?.length || 0) * 50)}
                                        height={230}
                                        chartConfig={chartConfig}
                                        bezier={(raw.moisLabels?.length || 0) > 1}
                                        formatYLabel={fmtY}
                                        style={styles.chart}
                                    />
                                </ScrollView>
                            </ChartBoundary>
                        </ChartCard>
                    )}

                    {/* 2. Revenu net mensuel (comparatif 3 courbes) */}
                    {raw?.moisLabels?.length > 0 && (
                        <ChartCard icon="bar-chart" title="Revenu net mensuel" sub="Ventes (vert) − Dépenses (rouge) = Revenu net (foncé)">
                            <ChartBoundary>
                                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ paddingRight: 10 }}>
                                    <LineChart
                                        data={{
                                            labels: raw.moisLabels,
                                            datasets: [
                                                { data: (raw.moisData || []).map(v => Number(v) || 0), color: (o = 1) => `rgba(22,163,74,${o})`, strokeWidth: 2 },
                                                { data: (raw.depensesData || []).map(v => Number(v) || 0), color: (o = 1) => `rgba(220,38,38,${o})`, strokeWidth: 2 },
                                                { data: (raw.revenuNetData || []).map(v => Number(v) || 0), color: (o = 1) => `rgba(16,94,73,${o})`, strokeWidth: 3 },
                                            ],
                                        }}
                                        width={Math.max(chartWidth, (raw.moisLabels?.length || 0) * 50)}
                                        height={230}
                                        chartConfig={chartConfig}
                                        bezier={(raw.moisLabels?.length || 0) > 1}
                                        formatYLabel={fmtY}
                                        style={styles.chart}
                                    />
                                </ScrollView>
                            </ChartBoundary>
                            <View style={styles.legendRow}>
                                <View style={styles.legendItem}><View style={[styles.legendDot, { backgroundColor: '#16a34a' }]} /><Text style={styles.legendText}>Ventes</Text></View>
                                <View style={styles.legendItem}><View style={[styles.legendDot, { backgroundColor: '#dc2626' }]} /><Text style={styles.legendText}>Dépenses</Text></View>
                                <View style={styles.legendItem}><View style={[styles.legendDot, { backgroundColor: '#105e49' }]} /><Text style={styles.legendText}>Revenu net</Text></View>
                            </View>
                        </ChartCard>
                    )}

                    {/* 3. Ventes quotidiennes */}
                    {raw?.joursLabels?.length > 0 && (
                        <ChartCard icon="calendar" title="Ventes quotidiennes" sub="Encaissements par jour du mois sélectionné (FCFA)">
                            <ChartBoundary>
                                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ paddingRight: 10 }}>
                                    <LineChart
                                        data={{
                                            labels: raw.joursLabels,
                                            datasets: [{ data: (raw.ventesJourData || []).map(v => Number(v) || 0) }]
                                        }}
                                        width={Math.max(chartWidth, (raw.joursLabels?.length || 0) * 36)}
                                        height={230}
                                        chartConfig={{
                                            ...chartConfig,
                                            color: (opacity = 1) => `rgba(37,99,235,${opacity})`,
                                            propsForDots: { r: '2', strokeWidth: '1', stroke: '#2563eb' }
                                        }}
                                        bezier={(raw.joursLabels?.length || 0) > 1}
                                        formatYLabel={fmtY}
                                        style={styles.chart}
                                    />
                                </ScrollView>
                            </ChartBoundary>
                        </ChartCard>
                    )}

                    {/* 4. Classement des produits (barres horizontales) */}
                    {raw?.topProduits?.length > 0 && (
                        <ChartCard icon="trophy" title="Classement des produits les plus vendus" sub="Les produits les plus vendus (quantité)">
                            {raw.topProduits.map((p, i) => (
                                <View key={i} style={styles.topItem}>
                                    <View style={styles.topRow}>
                                        <Text style={styles.topNom} numberOfLines={1}>{i + 1}. {p.nom}</Text>
                                        <Text style={styles.topVal}>{p.total_vendu || 0} u</Text>
                                    </View>
                                    <View style={styles.topBarBg}>
                                        <View style={[styles.topBarFill, { width: `${((p.total_vendu || 0) / maxTop) * 100}%` }]} />
                                    </View>
                                </View>
                            ))}
                        </ChartCard>
                    )}

                    {/* 5. Répartition des ventes (donut) */}
                    {raw?.statutLabels?.length > 0 && (
                        <ChartCard icon="pie-chart" title="Répartition des ventes" sub="Selon le statut de paiement">
                            <ChartBoundary>
                                <PieChart
                                    data={raw.statutLabels.map((l, i) => ({
                                        name: l,
                                        population: Number(raw.statutData?.[i]) || 0,
                                        color: raw.statutColors?.[i] || Colors.primary,
                                        legendFontColor: '#334155',
                                        legendFontSize: 11,
                                    }))}
                                    accessor="population"
                                    width={chartWidth}
                                    height={200}
                                    hasLegend={false}
                                    backgroundColor="transparent"
                                    paddingLeft={String(Math.round(chartWidth / 4))}
                                    chartConfig={chartConfig}
                                    style={[styles.chart, { alignSelf: 'center' }]}
                                />
                            </ChartBoundary>
                            <View style={styles.legendRow}>
                                {raw.statutLabels.map((l, i) => (
                                    <View key={i} style={styles.legendItem}>
                                        <View style={[styles.legendDot, { backgroundColor: raw.statutColors?.[i] || Colors.primary }]} />
                                        <Text style={styles.legendText}>{l} ({raw.statutData?.[i] || 0})</Text>
                                    </View>
                                ))}
                            </View>
                        </ChartCard>
                    )}

                    {/* 6. Ventes par vendeur */}
                    {raw?.ventesParVendeur?.length > 0 && (
                        <ChartCard icon="people" title="Ventes par vendeur" sub="Total encaissé par collaborateur (FCFA)">
                            <ChartBoundary>
                                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ paddingRight: 10 }}>
                                    <BarChart
                                        data={{
                                            labels: (raw.ventesParVendeur || []).map(v => (v.user?.name || v.name || 'N/A').split(' ')[0]),
                                            datasets: [{ data: (raw.ventesParVendeur || []).map(v => Number(v.total) || 0) }],
                                        }}
                                        width={Math.max(chartWidth, (raw.ventesParVendeur?.length || 0) * 75)}
                                        height={230}
                                        chartConfig={{
                                            ...chartConfig,
                                            color: (opacity = 1) => `rgba(124,58,237,${opacity})`,
                                        }}
                                        fromZero
                                        formatYLabel={fmtY}
                                        style={styles.chart}
                                        showValuesOnTopOfBars={true}
                                    />
                                </ScrollView>
                            </ChartBoundary>
                        </ChartCard>
                    )}

                    {/* 7. Nombre de ventes par mois */}
                    {raw?.moisLabels?.length > 0 && (
                        <ChartCard icon="receipt" title="Nombre de ventes par mois" sub="Volume de transactions mensuel">
                            <ChartBoundary>
                                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ paddingRight: 10 }}>
                                    <BarChart
                                        data={{
                                            labels: raw.moisLabels,
                                            datasets: [{ data: (raw.nbVentesData || []).map(v => Number(v) || 0) }]
                                        }}
                                        width={Math.max(chartWidth, (raw.moisLabels?.length || 0) * 48)}
                                        height={230}
                                        chartConfig={{
                                            ...chartConfig,
                                            color: (opacity = 1) => `rgba(8,145,178,${opacity})`,
                                        }}
                                        fromZero
                                        style={styles.chart}
                                    />
                                </ScrollView>
                            </ChartBoundary>
                        </ChartCard>
                    )}

                    {/* 8. Dettes créées par mois */}
                    {raw?.moisLabels?.length > 0 && (
                        <ChartCard icon="card" title="Dettes créées par mois" sub="Montant total des nouvelles dettes (FCFA)">
                            <ChartBoundary>
                                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ paddingRight: 10 }}>
                                    <LineChart
                                        data={{
                                            labels: raw.moisLabels,
                                            datasets: [{
                                                data: (raw.dettesData || []).map(v => Number(v) || 0),
                                                color: (o = 1) => `rgba(220,38,38,${o})`
                                            }]
                                        }}
                                        width={Math.max(chartWidth, (raw.moisLabels?.length || 0) * 48)}
                                        height={230}
                                        chartConfig={{
                                            ...chartConfig,
                                            color: (opacity = 1) => `rgba(220,38,38,${opacity})`,
                                            propsForDots: { r: '3', strokeWidth: '2', stroke: '#dc2626' }
                                        }}
                                        bezier={(raw.moisLabels?.length || 0) > 1}
                                        formatYLabel={fmtY}
                                        style={styles.chart}
                                    />
                                </ScrollView>
                            </ChartBoundary>
                        </ChartCard>
                    )}

                    {/* 9. Alertes stock */}
                    <ChartCard icon="warning" title="Produits en alerte stock" sub="Stock sous le seuil d'alerte">
                        {raw?.stockAlertes?.length > 0 ? (
                            raw.stockAlertes.map((a, i) => (
                                <View key={i} style={styles.alerteItem}>
                                    <Text style={styles.alerteNom}>{a.nom}</Text>
                                    <View style={[styles.alerteBadge, { backgroundColor: a.stock <= 0 ? Colors.error : Colors.warning }]}>
                                        <Text style={styles.alerteBadgeText}>{a.stock} carton(s)</Text>
                                    </View>
                                </View>
                            ))
                        ) : (
                            <Text style={styles.emptyText}>Aucun produit en alerte</Text>
                        )}
                    </ChartCard>

                    {/* 10. Résumé */}
                    <ChartCard
                        icon="calculator"
                        title={moisLabel ? `Résumé — ${moisLabel} ${annee}` : `Résumé ${annee}`}
                        sub={moisLabel
                            ? `Chiffres clés pour ${moisLabel} ${annee}`
                            : `Chiffres clés — ${moisEcoules} mois écoulés sur ${annee}`
                        }
                    >
                        <View style={styles.summaryGrid}>
                            <View style={[styles.summaryCell, { backgroundColor: '#f8f9fa' }]}>
                                <Text style={styles.summaryVal}>{formatMoney(totalVentesAn)}</Text>
                                <Text style={styles.summaryLbl}>{moisLabel ? 'Ventes du mois' : 'Total ventes'}</Text>
                            </View>
                            <View style={[styles.summaryCell, { backgroundColor: '#fef2f2' }]}>
                                <Text style={[styles.summaryVal, { color: Colors.error }]}>{formatMoney(totalDepensesAn)}</Text>
                                <Text style={styles.summaryLbl}>{moisLabel ? 'Dépenses du mois' : 'Total dépenses'}</Text>
                            </View>
                            <View style={[styles.summaryCell, { backgroundColor: '#fef2f2' }]}>
                                <Text style={[styles.summaryVal, { color: Colors.error }]}>{formatMoney(totalLoyerAn)}</Text>
                                <Text style={styles.summaryLbl}>
                                    {moisLabel
                                        ? 'Loyer du mois'
                                        : `Loyers (${moisEcoules} mois)`
                                    }
                                </Text>
                            </View>
                            <View style={[styles.summaryCell, { backgroundColor: '#f8f9fa' }]}>
                                <Text style={styles.summaryVal}>{nbVentesAn}</Text>
                                <Text style={styles.summaryLbl}>Nb de ventes</Text>
                            </View>
                            <View style={[styles.summaryCellFull, { borderColor: '#1f2937' }]}>
                                <Text style={styles.summaryNet}>{formatMoney(totalNetAn)}</Text>
                                <Text style={styles.summaryLbl}>
                                    {moisLabel ? 'Revenu net du mois' : `Revenu net (${moisEcoules} mois)`}
                                </Text>
                            </View>
                        </View>
                    </ChartCard>

                </ScrollView>
            )}
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    viewHeaderRow: {
        paddingHorizontal: 16,
        paddingTop: 14,
        paddingBottom: 8,
        backgroundColor: '#FFFFFF',
    },
    headerTitle: { fontSize: 20, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    headerSub: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 2 },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center', gap: 10 },
    loaderText: { fontSize: 14, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight },
    scrollContent: { padding: 16, paddingBottom: 32 },
    filterCard: {
        backgroundColor: Colors.surface,
        borderRadius: 16, padding: 14, marginBottom: 16, elevation: 1,
        borderWidth: 1, borderColor: '#EEF2F6',
    },
    filterSection: { marginBottom: 2 },
    filterLabelRow: { flexDirection: 'row', alignItems: 'center', gap: 5, marginBottom: 8 },
    filterLabel: { fontSize: 12, fontFamily: 'Poppins_600SemiBold', color: Colors.textLight, flex: 1 },
    filterOptional: { fontWeight: '400', color: '#94A3B8', fontSize: 11 },
    clearBtn: { flexDirection: 'row', alignItems: 'center', gap: 3 },
    clearBtnText: { fontSize: 11, color: '#94A3B8', fontFamily: 'Poppins_500Medium' },
    yearChips: { flexDirection: 'row', gap: 8 },
    yearChip: {
        flex: 1, paddingVertical: 8, borderRadius: 10,
        backgroundColor: '#F1F5F9', alignItems: 'center',
        borderWidth: 1, borderColor: 'transparent',
    },
    yearChipActive: { backgroundColor: '#0F172A', borderColor: '#0F172A' },
    yearChipText: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: '#64748B' },
    yearChipTextActive: { color: '#fff' },
    moisChips: { flexDirection: 'row', flexWrap: 'wrap', gap: 6 },
    moisChip: {
        paddingHorizontal: 10, paddingVertical: 6, borderRadius: 8,
        backgroundColor: '#F1F5F9',
        borderWidth: 1, borderColor: 'transparent',
    },
    moisChipActive: { backgroundColor: '#0F172A', borderColor: '#0F172A' },
    moisChipText: { fontSize: 12, fontFamily: 'Poppins_500Medium', color: '#64748B' },
    moisChipTextActive: { color: '#fff' },
    chartCard: {
        backgroundColor: Colors.surface, borderRadius: 16, padding: 16,
        marginBottom: 16, elevation: 1, overflow: 'hidden',
    },
    chartCardHead: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    chartCardTitle: { fontSize: 15, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    chartCardSub: { fontSize: 12, fontFamily: 'Poppins_400Regular', color: Colors.textLight, marginBottom: 12, marginTop: 2 },
    chart: { borderRadius: 12, marginTop: 4 },
    chartError: { fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.textLight, textAlign: 'center', paddingVertical: 24 },
    legendRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 14, marginTop: 12, justifyContent: 'center' },
    legendItem: { flexDirection: 'row', alignItems: 'center', gap: 6 },
    legendDot: { width: 10, height: 10, borderRadius: 5 },
    legendText: { fontSize: 12, fontFamily: 'Poppins_500Medium', color: Colors.text },
    topItem: { marginBottom: 12 },
    topRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 },
    topNom: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text, flex: 1, marginRight: 8 },
    topVal: { fontSize: 12, fontFamily: 'Poppins_700Bold', color: Colors.secondary },
    topBarBg: { height: 8, backgroundColor: '#f1f5f9', borderRadius: 4, overflow: 'hidden' },
    topBarFill: { height: 8, backgroundColor: '#d97706', borderRadius: 4 },
    alerteItem: {
        flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
        paddingVertical: 8, paddingHorizontal: 12, backgroundColor: '#fff7ed',
        borderRadius: 6, marginBottom: 8,
    },
    alerteNom: { fontSize: 13, fontFamily: 'Poppins_500Medium', color: Colors.text },
    alerteBadge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12 },
    alerteBadgeText: { fontSize: 12, fontFamily: 'Poppins_700Bold', color: '#FFF' },
    emptyText: { fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.textLight, textAlign: 'center', paddingVertical: 16 },
    summaryGrid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between', gap: 10, marginTop: 4 },
    summaryCell: {
        width: '48%', borderRadius: 8, padding: 14, alignItems: 'center',
    },
    summaryCellFull: {
        width: '100%', borderRadius: 8, padding: 18, alignItems: 'center',
        backgroundColor: '#f8f9fa', borderWidth: 2,
    },
    summaryVal: { fontSize: 18, fontFamily: 'Poppins_800ExtraBold', color: '#1f2937' },
    summaryNet: { fontSize: 22, fontFamily: 'Poppins_900Black', color: '#000' },
    summaryLbl: { fontSize: 11, fontFamily: 'Poppins_400Regular', color: Colors.textLight, textTransform: 'uppercase', marginTop: 2 },
});

export default AnalytiqueScreen;
