import React, { useState } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    TextInput, ActivityIndicator, Alert, StatusBar
} from 'react-native';
import KeyboardAwareScrollView from '../../components/KeyboardAwareScrollView';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

const CreateClientScreen = ({ navigation, route }) => {
    const insets = useSafeAreaInsets();
    const editing = route?.params?.item;

    const [nom, setNom] = useState(editing?.nom || '');
    const [telephone, setTelephone] = useState(editing?.telephone || '');
    const [adresse, setAdresse] = useState(editing?.adresse || '');
    const [limiteCredit, setLimiteCredit] = useState(editing?.limite_credit ? String(editing.limite_credit) : '');
    const [notes, setNotes] = useState(editing?.notes || '');
    const [submitting, setSubmitting] = useState(false);

    const handleSubmit = async () => {
        if (!nom) {
            Alert.alert('Erreur', 'Le nom du client est obligatoire.');
            return;
        }

        setSubmitting(true);
        try {
            const payload = {
                nom,
                telephone,
                adresse,
                limite_credit: limiteCredit ? Number(limiteCredit) : null,
                notes
            };

            if (editing) {
                await client.put(`/clients/${editing.id}`, payload);
                Alert.alert('Succès', 'Client modifié avec succès.');
            } else {
                await client.post('/clients', payload);
                Alert.alert('Succès', 'Nouveau client enregistré avec succès.');
            }

            navigation.goBack();
        } catch (e) {
            const msg = e.response?.data?.message || 'Erreur lors de l\'enregistrement';
            Alert.alert('Erreur', msg);
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            {/* Top Bar */}
            <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 16) }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={20} color={Colors.text} />
                </TouchableOpacity>
                <Text style={styles.topTitle}>{editing ? 'Modifier Client' : 'Nouveau Client'}</Text>
                <View style={{ width: 36 }} />
            </View>

            <KeyboardAwareScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>


                <View style={styles.cardSection}>
                    <Text style={styles.fieldLabel}>Nom complet *</Text>
                    <TextInput
                        style={styles.input}
                        value={nom}
                        onChangeText={setNom}
                        placeholder="Ex: M. Oumar Touré"
                    />

                    <Text style={styles.fieldLabel}>Numéro de Téléphone</Text>
                    <TextInput
                        style={styles.input}
                        keyboardType="phone-pad"
                        value={telephone}
                        onChangeText={setTelephone}
                        placeholder="Ex: +229 97 00 00 00"
                    />

                    <Text style={styles.fieldLabel}>Adresse / Quartier</Text>
                    <TextInput
                        style={styles.input}
                        value={adresse}
                        onChangeText={setAdresse}
                        placeholder="Ex: Akpakpa, Cotonou"
                    />

                    <Text style={styles.fieldLabel}>Plafond de Crédit Autorisations (FCFA)</Text>
                    <TextInput
                        style={styles.input}
                        keyboardType="numeric"
                        value={limiteCredit}
                        onChangeText={setLimiteCredit}
                        placeholder="Ex: 500000"
                    />

                    <Text style={styles.fieldLabel}>Notes / Remarques</Text>
                    <TextInput
                        style={[styles.input, { height: 70 }]}
                        multiline
                        value={notes}
                        onChangeText={setNotes}
                        placeholder="Optionnel..."
                    />
                </View>

                <TouchableOpacity style={styles.submitBtn} onPress={handleSubmit} disabled={submitting}>
                    {submitting ? (
                        <ActivityIndicator color="#FFF" />
                    ) : (
                        <Text style={styles.submitBtnText}>{editing ? 'Enregistrer les modifications' : 'Créer le profil client'}</Text>
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
    scrollContent: { padding: 16, paddingBottom: 40 },
    cardSection: { backgroundColor: '#FFF', borderRadius: 14, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    fieldLabel: { fontSize: 12, fontWeight: '600', color: Colors.text, marginTop: 10, marginBottom: 4 },
    input: { borderWidth: 1, borderColor: Colors.border, borderRadius: 8, padding: 10, fontSize: 14, backgroundColor: '#f8fafc' },
    submitBtn: { backgroundColor: Colors.primary, paddingVertical: 16, borderRadius: 12, alignItems: 'center', marginTop: 10 },
    submitBtnText: { color: '#FFF', fontWeight: '800', fontSize: 15 },
});

export default CreateClientScreen;
