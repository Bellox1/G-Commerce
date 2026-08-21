import React, { useState, useEffect, useCallback } from 'react';
import {
    View, Text, StyleSheet, TouchableOpacity, ActivityIndicator,
    Alert, ScrollView, TextInput, Modal, KeyboardAvoidingView, Platform, RefreshControl,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import TopHeaderNav from '../../components/TopHeaderNav';
import client from '../../api/client';
import Colors from '../../theme/Colors';
import { todayWAT, formatDateFr } from '../../utils/formatDate';

const SENS_OPTIONS = [
    { key: 'entree',  label: 'Entrée',     color: Colors.success, icon: 'arrow-down-circle' },
    { key: 'sortie',  label: 'Sortie',     color: Colors.error,   icon: 'arrow-up-circle' },
    { key: 'ca_jour', label: 'CA du jour', color: Colors.primary, icon: 'receipt' },
];

const MODES = ['Espèces', 'Mobile Money', 'Chèque'];

const formatMoney = (val) =>
    Number(val || 0).toLocaleString('fr-FR') + ' F';

const TresorerieScreen = ({ navigation }) => {
    const [items, setItems] = useState([]);
    const [totals, setTotals] = useState({ entrees: 0, sorties: 0, ca: 0 });
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [periode, setPeriode] = useState('tout'); // 'tout' | 'aujourd_hui'

    const [modalVisible, setModalVisible] = useState(false);
    const [deleting, setDeleting] = useState(null);

    const [form, setForm] = useState({
        sens: 'entree',
        montant: '',
        libelle: '',
        mode_paiement: '',
        date: todayWAT(),
    });
    const [saving, setSaving] = useState(false);

    const fetchData = useCallback(async () => {
        try {
            const params = periode === 'aujourd_hui' ? { date: todayWAT() } : {};
            const resp = await client.get('/tresoreries', { params });
            const pag = resp.data?.data;
            const list = Array.isArray(pag) ? pag : pag?.data || [];
            setItems(list);
            setTotals({
                entrees: resp.data?.total_entrees || 0,
                sorties: resp.data?.total_sorties || 0,
                ca: resp.data?.total_ca_jour || 0,
            });
        } catch (e) {
            console.log('Erreur chargement tresorerie', e?.response?.data || e.message);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, [periode]);

    useEffect(() => { fetchData(); }, [fetchData]);

    const onRefresh = () => { setRefreshing(true); fetchData(); };

    const openModal = () => {
        setForm({ sens: 'entree', montant: '', libelle: '', mode_paiement: '', date: todayWAT() });
        setModalVisible(true);
    };

    const submit = async () => {
        const montant = Number(form.montant);
        if (!montant || montant <= 0) {
            Alert.alert('Montant requis', 'Veuillez saisir un montant valide.');
            return;
        }
        setSaving(true);
        try {
            await client.post('/tresoreries', {
                ...form,
                montant,
            });
            setModalVisible(false);
            setPeriode('tout');
            fetchData();
        } catch (e) {
            Alert.alert('Erreur', e?.response?.data?.message || 'Enregistrement impossible.');
        } finally {
            setSaving(false);
        }
    };

    const confirmDelete = (item) => {
        Alert.alert(
            'Supprimer le mouvement',
            'Voulez-vous vraiment supprimer ce mouvement de trésorerie ?',
            [
                { text: 'Annuler', style: 'cancel' },
                { text: 'Supprimer', style: 'destructive', onPress: () => remove(item) },
            ]
        );
    };

    const remove = async (item) => {
        setDeleting(item.id);
        try {
            await client.delete(`/tresoreries/${item.id}`);
            setItems((prev) => prev.filter((x) => x.id !== item.id));
            fetchData();
        } catch (e) {
            Alert.alert('Erreur', 'Suppression impossible.');
        } finally {
            setDeleting(null);
        }
    };

    const sensMeta = (key) => SENS_OPTIONS.find((s) => s.key === key) || SENS_OPTIONS[0];

    const SummaryCard = ({ label, value, color }) => (
        <View style={[styles.summaryCard, { borderTopColor: color }]}>
            <Text style={styles.summaryLabel}>{label}</Text>
            <Text style={[styles.summaryValue, { color }]}>{formatMoney(value)}</Text>
        </View>
    );

    return (
        <View style={styles.screen}>
            <TopHeaderNav navigation={navigation} activeCategory="tresorerie" />

            <View style={styles.headerRow}>
                <View>
                    <Text style={styles.title}>Trésorerie</Text>
                    <Text style={styles.subtitle}>CA réel & mouvements d'argent</Text>
                </View>
                <TouchableOpacity style={styles.addBtn} onPress={openModal} activeOpacity={0.85}>
                    <Ionicons name="add" size={18} color="#fff" />
                    <Text style={styles.addBtnText}>Mouvement</Text>
                </TouchableOpacity>
            </View>

            <View style={styles.summaryWrap}>
                <SummaryCard label="Entrées"     value={totals.entrees} color={Colors.success} />
                <SummaryCard label="Sorties"     value={totals.sorties} color={Colors.error} />
                <SummaryCard label="CA du jour"  value={totals.ca}      color={Colors.primary} />
            </View>

            <View style={styles.filterRow}>
                <TouchableOpacity
                    style={[styles.filterChip, periode === 'tout' && styles.filterChipActive]}
                    onPress={() => setPeriode('tout')}
                >
                    <Text style={[styles.filterChipText, periode === 'tout' && styles.filterChipTextActive]}>Tout</Text>
                </TouchableOpacity>
                <TouchableOpacity
                    style={[styles.filterChip, periode === 'aujourd_hui' && styles.filterChipActive]}
                    onPress={() => setPeriode('aujourd_hui')}
                >
                    <Text style={[styles.filterChipText, periode === 'aujourd_hui' && styles.filterChipTextActive]}>Aujourd'hui</Text>
                </TouchableOpacity>
            </View>

            {loading ? (
                <View style={styles.center}><ActivityIndicator size="large" color={Colors.primary} /></View>
            ) : items.length === 0 ? (
                <View style={styles.emptyWrap}>
                    <Ionicons name="cash-outline" size={48} color={Colors.textLight} />
                    <Text style={styles.emptyText}>Aucun mouvement pour cette période.</Text>
                    <TouchableOpacity style={styles.emptyBtn} onPress={openModal}>
                        <Text style={styles.emptyBtnText}>Enregistrer un mouvement</Text>
                    </TouchableOpacity>
                </View>
            ) : (
                <ScrollView
                    style={styles.list}
                    contentContainerStyle={styles.listContent}
                    keyboardShouldPersistTaps="handled"
                    refreshControl={
                        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} tintColor={Colors.primary} />
                    }
                >
                    {items.map((item) => {
                        const meta = sensMeta(item.sens);
                        const isOut = item.sens === 'sortie';
                        return (
                            <TouchableOpacity
                                key={item.id}
                                style={styles.moveCard}
                                onLongPress={() => confirmDelete(item)}
                                activeOpacity={0.9}
                            >
                                <View style={[styles.moveIcon, { backgroundColor: meta.color + '18' }]}>
                                    <Ionicons name={meta.icon} size={22} color={meta.color} />
                                </View>
                                <View style={styles.moveBody}>
                                    <Text style={styles.moveLibelle}>{item.libelle || meta.label}</Text>
                                    <Text style={styles.moveMeta}>
                                        {meta.label} · {formatDateFr(item.date)}
                                        {item.mode_paiement ? ` · ${item.mode_paiement}` : ''}
                                    </Text>
                                </View>
                                <View style={styles.moveRight}>
                                    <Text style={[styles.moveAmount, { color: meta.color }]}>
                                        {isOut ? '- ' : '+ '}{formatMoney(item.montant)}
                                    </Text>
                                    {deleting === item.id && <ActivityIndicator size="small" color={Colors.error} />}
                                </View>
                            </TouchableOpacity>
                        );
                    })}
                </ScrollView>
            )}

            <Modal visible={modalVisible} transparent animationType="slide" onRequestClose={() => setModalVisible(false)}>
                <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={{ flex: 1 }}>
                    <View style={styles.modalOverlay}>
                        <View style={styles.modalCard}>
                            <View style={styles.modalHeader}>
                                <Text style={styles.modalTitle}>Nouveau mouvement</Text>
                                <TouchableOpacity onPress={() => setModalVisible(false)}>
                                    <Ionicons name="close" size={22} color={Colors.text} />
                                </TouchableOpacity>
                            </View>

                            <Text style={styles.fieldLabel}>Type de mouvement</Text>
                            <View style={styles.chipRow}>
                                {SENS_OPTIONS.map((s) => (
                                    <TouchableOpacity
                                        key={s.key}
                                        style={[styles.sensChip, form.sens === s.key && { backgroundColor: s.color, borderColor: s.color }]}
                                        onPress={() => setForm((f) => ({ ...f, sens: s.key }))}
                                    >
                                        <Ionicons name={s.icon} size={16} color={form.sens === s.key ? '#fff' : s.color} />
                                        <Text style={[styles.sensChipText, form.sens === s.key && { color: '#fff' }]}>{s.label}</Text>
                                    </TouchableOpacity>
                                ))}
                            </View>

                            <Text style={styles.fieldLabel}>Montant (F)</Text>
                            <TextInput
                                style={styles.input}
                                keyboardType="numeric"
                                placeholder="Ex : 50000"
                                value={form.montant}
                                onChangeText={(v) => setForm((f) => ({ ...f, montant: v.replace(/[^0-9.]/g, '') }))}
                            />

                            <Text style={styles.fieldLabel}>Libellé</Text>
                            <TextInput
                                style={styles.input}
                                placeholder={form.sens === 'ca_jour' ? 'Chiffre d\'affaires du jour' : 'Ex : Remboursement'}
                                value={form.libelle}
                                onChangeText={(v) => setForm((f) => ({ ...f, libelle: v }))}
                            />

                            <Text style={styles.fieldLabel}>Mode de paiement</Text>
                            <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.modeScroll}>
                                {MODES.map((m) => (
                                    <TouchableOpacity
                                        key={m}
                                        style={[styles.modeChip, form.mode_paiement === m && styles.modeChipActive]}
                                        onPress={() => setForm((f) => ({ ...f, mode_paiement: m }))}
                                    >
                                        <Text style={[styles.modeChipText, form.mode_paiement === m && styles.modeChipTextActive]}>{m}</Text>
                                    </TouchableOpacity>
                                ))}
                            </ScrollView>

                            <View style={styles.modalActions}>
                                <TouchableOpacity style={styles.cancelBtn} onPress={() => setModalVisible(false)}>
                                    <Text style={styles.cancelBtnText}>Annuler</Text>
                                </TouchableOpacity>
                                <TouchableOpacity style={styles.saveBtn} onPress={submit} disabled={saving}>
                                    {saving ? <ActivityIndicator color="#fff" /> : <Text style={styles.saveBtnText}>Enregistrer</Text>}
                                </TouchableOpacity>
                            </View>
                        </View>
                    </View>
                </KeyboardAvoidingView>
            </Modal>
        </View>
    );
};

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: Colors.background },
    headerRow: {
        flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
        paddingHorizontal: 16, paddingTop: 14, paddingBottom: 6,
    },
    title: { fontSize: 22, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text },
    subtitle: { fontSize: 12.5, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 2 },
    addBtn: {
        flexDirection: 'row', alignItems: 'center', gap: 6,
        backgroundColor: Colors.primary, borderRadius: 12, paddingHorizontal: 14, paddingVertical: 9,
    },
    addBtnText: { color: '#fff', fontSize: 13.5, fontFamily: 'PlusJakartaSans_700Bold' },

    summaryWrap: { flexDirection: 'row', gap: 10, paddingHorizontal: 16, marginTop: 8 },
    summaryCard: {
        flex: 1, backgroundColor: '#fff', borderRadius: 14, padding: 12,
        borderTopWidth: 3, borderWidth: 1, borderColor: '#EEF2F6',
        elevation: 2, shadowColor: '#0F172A', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 6,
    },
    summaryLabel: { fontSize: 11.5, fontFamily: 'PlusJakartaSans_500Medium', color: Colors.textLight, textTransform: 'uppercase' },
    summaryValue: { fontSize: 15, fontFamily: 'PlusJakartaSans_700Bold', marginTop: 4 },

    filterRow: { flexDirection: 'row', gap: 8, paddingHorizontal: 16, marginVertical: 12 },
    filterChip: {
        paddingHorizontal: 16, paddingVertical: 7, borderRadius: 18,
        backgroundColor: '#fff', borderWidth: 1, borderColor: '#E2E8F0',
    },
    filterChipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    filterChipText: { fontSize: 12.5, fontFamily: 'PlusJakartaSans_600SemiBold', color: Colors.textLight },
    filterChipTextActive: { color: '#fff' },

    list: { flex: 1 },
    listContent: { paddingHorizontal: 16, paddingBottom: 100, gap: 10 },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },

    moveCard: {
        flexDirection: 'row', alignItems: 'center', gap: 12,
        backgroundColor: '#fff', borderRadius: 14, padding: 12,
        borderWidth: 1, borderColor: '#EEF2F6',
        elevation: 1, shadowColor: '#0F172A', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.04, shadowRadius: 4,
    },
    moveIcon: { width: 42, height: 42, borderRadius: 12, justifyContent: 'center', alignItems: 'center' },
    moveBody: { flex: 1 },
    moveLibelle: { fontSize: 14, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text },
    moveMeta: { fontSize: 11.5, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 2 },
    moveRight: { alignItems: 'flex-end', gap: 4 },
    moveAmount: { fontSize: 14.5, fontFamily: 'PlusJakartaSans_700Bold' },

    emptyWrap: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 30 },
    emptyText: { fontSize: 14, fontFamily: 'PlusJakartaSans_500Medium', color: Colors.textLight, marginTop: 12, textAlign: 'center' },
    emptyBtn: {
        marginTop: 16, backgroundColor: Colors.primary, borderRadius: 12, paddingHorizontal: 18, paddingVertical: 10,
    },
    emptyBtnText: { color: '#fff', fontSize: 13.5, fontFamily: 'PlusJakartaSans_700Bold' },

    modalOverlay: { flex: 1, backgroundColor: 'rgba(15,23,42,0.45)', justifyContent: 'flex-end' },
    modalCard: { backgroundColor: '#fff', borderTopLeftRadius: 22, borderTopRightRadius: 22, padding: 18, paddingBottom: 28 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
    modalTitle: { fontSize: 18, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text },
    fieldLabel: { fontSize: 12.5, fontFamily: 'PlusJakartaSans_600SemiBold', color: Colors.text, marginTop: 12, marginBottom: 6 },
    input: {
        backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 12,
        paddingHorizontal: 14, paddingVertical: 11, fontSize: 14, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.text,
    },
    chipRow: { flexDirection: 'row', gap: 8 },
    sensChip: {
        flexDirection: 'row', alignItems: 'center', gap: 6, flex: 1, justifyContent: 'center',
        paddingVertical: 10, borderRadius: 12, borderWidth: 1, borderColor: '#E2E8F0', backgroundColor: '#fff',
    },
    sensChipText: { fontSize: 12.5, fontFamily: 'PlusJakartaSans_600SemiBold', color: Colors.text },
    modeScroll: { flexGrow: 0 },
    modeChip: {
        paddingHorizontal: 14, paddingVertical: 8, borderRadius: 16,
        backgroundColor: '#F1F5F9', borderWidth: 1, borderColor: '#E2E8F0', marginRight: 8,
    },
    modeChipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
    modeChipText: { fontSize: 12, fontFamily: 'PlusJakartaSans_600SemiBold', color: Colors.text },
    modeChipTextActive: { color: '#fff' },
    modalActions: { flexDirection: 'row', gap: 10, marginTop: 18 },
    cancelBtn: {
        flex: 1, paddingVertical: 13, borderRadius: 12, backgroundColor: '#F1F5F9', alignItems: 'center',
    },
    cancelBtnText: { fontSize: 14, fontFamily: 'PlusJakartaSans_700Bold', color: Colors.text },
    saveBtn: {
        flex: 1, paddingVertical: 13, borderRadius: 12, backgroundColor: Colors.primary, alignItems: 'center', justifyContent: 'center',
    },
    saveBtnText: { fontSize: 14, fontFamily: 'PlusJakartaSans_700Bold', color: '#fff' },
});

export default TresorerieScreen;
