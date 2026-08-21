import React, { useState, useEffect } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    TextInput, ActivityIndicator, Alert, StatusBar
} from 'react-native';
import KeyboardAwareScrollView from '../../components/KeyboardAwareScrollView';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

const CreateDetteScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const [clients, setClients] = useState([]);
    const [loadingData, setLoadingData] = useState(true);

    const [selectedClient, setSelectedClient] = useState(null);
    const [montant, setMontant] = useState('');
    const [dateEcheance, setDateEcheance] = useState('');
    const [motif, setMotif] = useState('');
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        fetchClients();
    }, []);

    const fetchClients = async () => {
        try {
            const resp = await client.get('/clients');
            const list = resp.data?.data || (Array.isArray(resp.data) ? resp.data : []);
            setClients(list);
            if (list.length > 0) setSelectedClient(list[0].id);
        } catch (e) {
            console.error('Error fetching clients for dette:', e);
        } finally {
            setLoadingData(false);
        }
    };

    const handleSubmit = async () => {
        if (!selectedClient) {
            Alert.alert('Erreur', 'Veuillez sélectionner un client.');
            return;
        }
        if (!montant || Number(montant) <= 0) {
            Alert.alert('Erreur', 'Veuillez saisir un montant de dette valide.');
            return;
        }

        setSubmitting(true);
        try {
            await client.post('/dettes', {
                client_id: selectedClient,
                montant: Number(montant),
                date_echeance: dateEcheance || null,
                motif
            });

            Alert.alert('Succès', 'Dette client créée avec succès !');
            navigation.goBack();
        } catch (e) {
            const msg = e.response?.data?.message || 'Erreur lors de la création de la dette';
            Alert.alert('Erreur', msg);
        } finally {
            setSubmitting(false);
        }
    };

    if (loadingData) {
        return (
            <View style={styles.centerLoader}>
                <ActivityIndicator size="large" color={Colors.primary} />
            </View>
        );
    }

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            {/* Top Bar */}
            <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={20} color={Colors.text} />
                </TouchableOpacity>
                <Text style={styles.topTitle}>Nouvelle Créance Client</Text>
                <View style={{ width: 36 }} />
            </View>

            <KeyboardAwareScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>


                <View style={styles.cardSection}>
                    <Text style={styles.fieldLabel}>Client débiteur *</Text>
                    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipRow}>
                        {clients.map(c => (
                            <TouchableOpacity
                                key={c.id}
                                style={[styles.chip, selectedClient === c.id && styles.chipActive]}
                                onPress={() => setSelectedClient(c.id)}
                            >
                                <Text style={[styles.chipText, selectedClient === c.id && styles.chipTextActive]}>{c.nom}</Text>
                            </TouchableOpacity>
                        ))}
                    </ScrollView>

                    <Text style={styles.fieldLabel}>Montant de la dette (FCFA) *</Text>
                    <TextInput
                        style={styles.input}
                        keyboardType="numeric"
                        value={montant}
                        onChangeText={setMontant}
                        placeholder="Ex: 50000"
                    />

                    <Text style={styles.fieldLabel}>Date d'échéance (AAAA-MM-JJ)</Text>
                    <TextInput
                        style={styles.input}
                        value={dateEcheance}
                        onChangeText={setDateEcheance}
                        placeholder="AAAA-MM-JJ"
                    />

                    <Text style={styles.fieldLabel}>Motif / Note</Text>
                    <TextInput
                        style={styles.input}
                        value={motif}
                        onChangeText={setMotif}
                        placeholder="Optionnel..."
                    />
                </View>

                <TouchableOpacity style={styles.submitBtn} onPress={handleSubmit} disabled={submitting}>
                    {submitting ? (
                        <ActivityIndicator color="#FFF" />
                    ) : (
                        <Text style={styles.submitBtnText}>Enregistrer la dette client</Text>
                    )}
                </TouchableOpacity>

            </KeyboardAwareScrollView>

        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    topBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: '#FFFFFF', paddingHorizontal: 16, paddingBottom: 12, borderBottomWidth: 1, borderBottomColor: '#E2E8F0' },
    backBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center' },
    topTitle: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    centerLoader: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    scrollContent: { padding: 16, paddingBottom: 40 },
    cardSection: { backgroundColor: '#FFF', borderRadius: 14, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    fieldLabel: { fontSize: 12, fontWeight: '600', color: Colors.text, marginTop: 10, marginBottom: 4 },
    input: { borderWidth: 1, borderColor: Colors.border, borderRadius: 8, padding: 10, fontSize: 14, backgroundColor: '#f8fafc' },
    chipRow: { gap: 8, marginVertical: 6 },
    chip: { paddingHorizontal: 14, paddingVertical: 8, borderRadius: 8, backgroundColor: '#f1f5f9' },
    chipActive: { backgroundColor: Colors.primary },
    chipText: { fontSize: 12, fontWeight: '600', color: Colors.text },
    chipTextActive: { color: '#FFF' },
    submitBtn: { backgroundColor: Colors.warning, paddingVertical: 16, borderRadius: 12, alignItems: 'center', marginTop: 10 },
    submitBtnText: { color: '#FFF', fontWeight: '800', fontSize: 15 },
});

export default CreateDetteScreen;
