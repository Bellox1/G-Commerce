import React, { useState, useEffect, useCallback } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    ActivityIndicator, RefreshControl, Dimensions, StatusBar
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

const { width } = Dimensions.get('window');
const chartWidth = width - 64;

const formatMoney = (val) => {
    if (!val && val !== 0) return '0 F';
    return Number(val).toLocaleString('fr-FR') + ' F';
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
    propsForBackgroundLines: { stroke: '#E5E7EB', strokeWidth: 1 },
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
    const currentYear = new Date().getFullYear();
    const years = [currentYear - 3, currentYear - 2, currentYear - 1, currentYear];
    const [annee, setAnnee] = useState(currentYear);
    const [mois, setMois] = useState(String(new Date().getMonth() + 1).padStart(2, '0'));
    const [raw, setRaw] = useState(null);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const fetchAnalytique = useCallback(async () => {
        try {
            const resp = await client.get('/analytique', { params: { annee, mois } });
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

    const totalVentesAn = raw?.moisData ? raw.moisData.reduce((a, b) => a + b, 0) : 0;
    const totalDepensesAn = raw?.depensesData ? raw.depensesData.reduce((a, b) => a + b, 0) : 0;
    const totalLoyerAn = raw?.loyersCumules ?? (raw?.loyerMensuel || 0) * 12;
    const totalNetAn = totalVentesAn - totalDepensesAn - totalLoyerAn;
    const nbVentesAn = raw?.nbVentesData ? raw.nbVentesData.reduce((a, b) => a + b, 0) : 0;

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
                        <View style={styles.filterCol}>
                            <Text style={styles.filterLabel}>Année</Text>
                            <View style={styles.pickerWrap}>
                                <Picker selectedValue={annee} onValueChange={setAnnee} mode="dropdown">
                                    {years.map(y => <Picker.Item key={y} label={String(y)} value={y} />)}
                                </Picker>
                            </View>
                        </View>
                        <View style={styles.filterCol}>
                            <Text style={styles.filterLabel}>Mois</Text>
                            <View style={styles.pickerWrap}>
                                <Picker selectedValue={mois} onValueChange={setMois} mode="dropdown">
                                    {MOIS.map(m => <Picker.Item key={m.v} label={m.l} value={m.v} />)}
                                </Picker>
                            </View>
                        </View>
                    </View>

                    {/* 1. Ventes mensuelles */}
                    {raw?.moisLabels?.length > 0 && (
                        <ChartCard icon="trending-up" title="Ventes mensuelles" sub="Évolution des encaissements par mois (FCFA)">
                            <ChartBoundary>
                                <LineChart
                                    data={{ labels: raw.moisLabels, datasets: [{ data: raw.moisData || [] }] }}
                                    width={chartWidth} height={240} chartConfig={chartConfig}
                                    bezier formatYLabel={fmtY} style={styles.chart}
                                />
                            </ChartBoundary>
                        </ChartCard>
                    )}

                    {/* 2. Revenu net mensuel (groupé) */}
                    {raw?.moisLabels?.length > 0 && (
                        <ChartCard icon="bar-chart" title="Revenu net mensuel" sub="Ventes − Dépenses − Loyers = Revenu net (FCFA)">
                            <ChartBoundary>
                                <BarChart
                                    data={{
                                        labels: raw.moisLabels,
                                        datasets: [
                                            { data: raw.moisData || [], color: (o) => `rgba(22,163,74,${o})` },
                                            { data: raw.depensesData || [], color: (o) => `rgba(220,38,38,${o})` },
                                            { data: raw.revenuNetData || [], color: (o) => `rgba(16,94,73,${o})` },
                                        ],
                                    }}
                                    width={chartWidth} height={240} chartConfig={chartConfig}
                                    fromZero formatYLabel={fmtY} style={styles.chart}
                                />
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
                                <LineChart
                                    data={{ labels: raw.joursLabels, datasets: [{ data: raw.ventesJourData || [] }] }}
                                    width={chartWidth} height={240} chartConfig={chartConfig}
                                    bezier formatYLabel={fmtY} style={styles.chart}
                                />
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
                                        population: raw.statutData?.[i] || 0,
                                        color: raw.statutColors?.[i] || Colors.primary,
                                        legend: l,
                                    }))}
                                    accessor="population"
                                    width={chartWidth}
                                    height={240}
                                    backgroundColor="transparent"
                                    paddingLeft="0"
                                    chartConfig={chartConfig}
                                    style={[styles.chart, { alignSelf: 'center' }]}
                                />
                            </ChartBoundary>
                            <View style={styles.legendRow}>
                                {raw.statutLabels.map((l, i) => (
                                    <View key={i} style={styles.legendItem}>
                                        <View style={[styles.legendDot, { backgroundColor: raw.statutColors?.[i] || Colors.primary }]} />
                                        <Text style={styles.legendText}>{l}</Text>
                                    </View>
                                ))}
                            </View>
                        </ChartCard>
                    )}

                    {/* 6. Ventes par vendeur */}
                    {raw?.ventesParVendeur?.length > 0 && (
                        <ChartCard icon="people" title="Ventes par vendeur" sub="Total encaissé par collaborateur (FCFA)">
                            <ChartBoundary>
                                <BarChart
                                    data={{
                                        labels: (raw.ventesParVendeur || []).map(v => v.user?.name || v.name || 'N/A'),
                                        datasets: [{ data: (raw.ventesParVendeur || []).map(v => v.total) }],
                                    }}
                                    width={chartWidth} height={240} chartConfig={chartConfig}
                                    fromZero formatYLabel={fmtY} style={styles.chart}
                                />
                            </ChartBoundary>
                        </ChartCard>
                    )}

                    {/* 7. Nombre de ventes par mois */}
                    {raw?.moisLabels?.length > 0 && (
                        <ChartCard icon="receipt" title="Nombre de ventes par mois" sub="Volume de transactions mensuel">
                            <ChartBoundary>
                                <BarChart
                                    data={{ labels: raw.moisLabels, datasets: [{ data: raw.nbVentesData || [] }] }}
                                    width={chartWidth} height={240} chartConfig={chartConfig}
                                    fromZero style={styles.chart}
                                />
                            </ChartBoundary>
                        </ChartCard>
                    )}

                    {/* 8. Dettes créées par mois */}
                    {raw?.moisLabels?.length > 0 && (
                        <ChartCard icon="card" title="Dettes créées par mois" sub="Montant total des nouvelles dettes (FCFA)">
                            <ChartBoundary>
                                <LineChart
                                    data={{ labels: raw.moisLabels, datasets: [{ data: raw.dettesData || [], color: (o) => `rgba(220,38,38,${o})` }] }}
                                    width={chartWidth} height={240} chartConfig={{ ...chartConfig, color: (o) => `rgba(220,38,38,${o})` }}
                                    bezier formatYLabel={fmtY} style={styles.chart}
                                />
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

                    {/* 10. Résumé annuel */}
                    <ChartCard icon="calculator" title={`Résumé ${raw?.annee || annee}`} sub="Chiffres clés annuels">
                        <View style={styles.summaryGrid}>
                            <View style={[styles.summaryCell, { backgroundColor: '#f8f9fa' }]}>
                                <Text style={styles.summaryVal}>{formatMoney(totalVentesAn)}</Text>
                                <Text style={styles.summaryLbl}>Total ventes</Text>
                            </View>
                            <View style={[styles.summaryCell, { backgroundColor: '#fef2f2' }]}>
                                <Text style={[styles.summaryVal, { color: Colors.error }]}>{formatMoney(totalDepensesAn)}</Text>
                                <Text style={styles.summaryLbl}>Total dépenses</Text>
                            </View>
                            <View style={[styles.summaryCell, { backgroundColor: '#fef2f2' }]}>
                                <Text style={[styles.summaryVal, { color: Colors.error }]}>{formatMoney(totalLoyerAn)}</Text>
                                <Text style={styles.summaryLbl}>Loyers annuels</Text>
                            </View>
                            <View style={[styles.summaryCell, { backgroundColor: '#f8f9fa' }]}>
                                <Text style={styles.summaryVal}>{nbVentesAn}</Text>
                                <Text style={styles.summaryLbl}>Nombre de ventes</Text>
                            </View>
                            <View style={[styles.summaryCellFull, { borderColor: '#1f2937' }]}>
                                <Text style={styles.summaryNet}>{formatMoney(totalNetAn)}</Text>
                                <Text style={styles.summaryLbl}>Revenu net annuel</Text>
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
        flexDirection: 'row', gap: 12, backgroundColor: Colors.surface,
        borderRadius: 16, padding: 12, marginBottom: 16, elevation: 1,
    },
    filterCol: { flex: 1 },
    filterLabel: { fontSize: 12, fontFamily: 'Poppins_600SemiBold', color: Colors.textLight, marginBottom: 2 },
    pickerWrap: {
        backgroundColor: Colors.background, borderRadius: 10,
        borderWidth: 1, borderColor: Colors.border, overflow: 'hidden',
    },
    chartCard: {
        backgroundColor: Colors.surface, borderRadius: 16, padding: 16,
        marginBottom: 16, elevation: 1,
    },
    chartCardHead: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    chartCardTitle: { fontSize: 15, fontFamily: 'Poppins_700Bold', color: Colors.primary },
    chartCardSub: { fontSize: 12, fontFamily: 'Poppins_400Regular', color: Colors.textLight, marginBottom: 12, marginTop: 2 },
    chart: { borderRadius: 12, marginTop: 4 },
    chartError: { fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.textLight, textAlign: 'center', paddingVertical: 24 },
    legendRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 14, marginTop: 12 },
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
    summaryGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12, marginTop: 4 },
    summaryCell: {
        width: (width - 64 - 16 - 12) / 2, borderRadius: 8, padding: 14, alignItems: 'center',
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
