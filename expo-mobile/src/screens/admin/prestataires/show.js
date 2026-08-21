import React, { useState, useCallback, useEffect } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    ActivityIndicator, StatusBar, Alert
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useNavigation, DrawerActions } from '@react-navigation/native';
import { useRoute } from '@react-navigation/core';
import { Ionicons } from '@expo/vector-icons';
import Colors from '../../../theme/Colors';
import client from '../../../api/client';
import { Header, Loader, StatusBadge, PRESTATAIRE_STATUS, QUESTION_LABELS } from '../components';

const formatDate = (d) => {
    if (!d) return '—';
    try { return new Date(d).toLocaleString('fr-FR'); } catch { return '—'; }
};

const PrestataireShowScreen = () => {
    const insets = useSafeAreaInsets();
    const navigation = useNavigation();
    const route = useRoute();
    const id = route.params?.id;

    const [item, setItem] = useState(null);
    const [loading, setLoading] = useState(true);
    const [acting, setActing] = useState(false);

    const fetchData = useCallback(async () => {
        try {
            const res = await client.get(`/prestataires/${id}`);
            setItem(res.data?.data || res.data);
        } catch (e) {
            console.error('Error fetching prestataire:', e);
        } finally {
            setLoading(false);
        }
    }, [id]);

    useEffect(() => { fetchData(); }, [fetchData]);

    const actOn = async (action) => {
        setActing(true);
        try {
            await client.post(`/prestataires/${id}/${action}`);
            Alert.alert('Succès', action === 'valider' ? 'Demande approuvée.' : 'Demande rejetée.');
            fetchData();
        } catch (e) {
            Alert.alert('Erreur', e.response?.data?.message || 'Action impossible');
        } finally {
            setActing(false);
        }
    };

    if (loading) {
        return (
            <View style={[styles.container, { paddingTop: insets.top }]}>
                <StatusBar barStyle="dark-content" backgroundColor={Colors.background} />
                <Header title="Demande partenaire" onMenu={() => navigation.dispatch(DrawerActions.openDrawer())} onBack={() => navigation.goBack()} />
                <Loader />
            </View>
        );
    }

    const answers = Array.isArray(item?.questionnaire) ? item.questionnaire : [];
    const pending = item?.statut === 'en_attente';

    return (
        <View style={[styles.container, { paddingTop: insets.top }]}>
            <StatusBar barStyle="dark-content" backgroundColor={Colors.background} />
            <Header title={`${item?.nom || ''} ${item?.prenom || ''}`} onMenu={() => navigation.dispatch(DrawerActions.openDrawer())} onBack={() => navigation.goBack()} />
            <ScrollView contentContainerStyle={styles.content}>
                <View style={styles.card}>
                    <Text style={styles.cardTitle}><Ionicons name="person" size={16} />  Informations du candidat</Text>
                    <Field label="Nom" value={`${item?.nom || ''} ${item?.prenom || ''}`} />
                    <Field label="Email" value={item?.email} />
                    <Field label="Téléphone" value={item?.telephone} />
                    <Field label="Entreprise" value={item?.entreprise} />
                    <Field label="Date" value={formatDate(item?.created_at)} />
                    <View style={styles.fieldRow}>
                        <Text style={styles.fieldLabel}>Statut</Text>
                        <StatusBadge statut={item?.statut} map={PRESTATAIRE_STATUS} />
                    </View>
                </View>

                <View style={styles.card}>
                    <Text style={styles.cardTitle}><Ionicons name="chatbubble-ellipses" size={16} />  Motivation</Text>
                    <Text style={styles.motivation}>{item?.motivation || 'Aucune motivation renseignée.'}</Text>
                </View>

                <View style={styles.card}>
                    <Text style={styles.cardTitle}><Ionicons name="clipboard" size={16} />  Questionnaire ({answers.length} réponses)</Text>
                    {answers.length === 0 ? (
                        <Text style={styles.motivation}>Aucun questionnaire rempli.</Text>
                    ) : (
                        answers.map((ans, i) => (
                            <View key={i} style={[styles.qRow, i > 0 && { borderTopWidth: 1, borderTopColor: Colors.border }]}>
                                <Text style={styles.qLabel}>{i + 1}. {QUESTION_LABELS[i] || 'Question'}</Text>
                                <Text style={styles.qAnswer}>{ans || '—'}</Text>
                            </View>
                        ))
                    )}
                </View>

                {pending && (
                    <View style={styles.actions}>
                        <TouchableOpacity
                            style={[styles.btn, { backgroundColor: Colors.success }]}
                            onPress={() => actOn('valider')}
                            disabled={acting}
                        >
                            <Ionicons name="checkmark" size={18} color="#fff" />
                            <Text style={styles.btnText}>Approuver</Text>
                        </TouchableOpacity>
                        <TouchableOpacity
                            style={[styles.btn, { backgroundColor: Colors.error }]}
                            onPress={() => actOn('rejeter')}
                            disabled={acting}
                        >
                            <Ionicons name="close" size={18} color="#fff" />
                            <Text style={styles.btnText}>Rejeter</Text>
                        </TouchableOpacity>
                    </View>
                )}
            </ScrollView>
        </View>
    );
};

const Field = ({ label, value }) => (
    <View style={styles.fieldRow}>
        <Text style={styles.fieldLabel}>{label}</Text>
        <Text style={styles.fieldValue}>{value || '—'}</Text>
    </View>
);

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: Colors.background },
    content: { padding: 16, gap: 14, paddingBottom: 40 },
    card: { backgroundColor: Colors.cardBg, borderRadius: 16, padding: 16, borderWidth: 1, borderColor: Colors.border },
    cardTitle: { fontSize: 15, fontFamily: 'Poppins_600SemiBold', color: Colors.text, marginBottom: 12 },
    fieldRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 6 },
    fieldLabel: { fontSize: 13, fontFamily: 'Poppins_400Regular', color: Colors.textLight },
    fieldValue: { fontSize: 14, fontFamily: 'Poppins_600SemiBold', color: Colors.text, flexShrink: 1, textAlign: 'right', marginLeft: 12 },
    motivation: { fontSize: 14, fontFamily: 'Poppins_400Regular', color: Colors.text, lineHeight: 22 },
    qRow: { paddingVertical: 10 },
    qLabel: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.primary, marginBottom: 4 },
    qAnswer: { fontSize: 14, fontFamily: 'Poppins_400Regular', color: Colors.text },
    actions: { flexDirection: 'row', gap: 12 },
    btn: { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, paddingVertical: 14, borderRadius: 12 },
    btnText: { color: '#fff', fontSize: 15, fontFamily: 'Poppins_600SemiBold' },
});

export default PrestataireShowScreen;
