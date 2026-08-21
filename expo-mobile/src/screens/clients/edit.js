import React, { useState } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    TextInput, ActivityIndicator, Alert
} from 'react-native';
import KeyboardAwareScrollView from '../../components/KeyboardAwareScrollView';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useRoute } from '@react-navigation/core';
import { Header } from '../../components/ui';

const ClientEditScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const route = useRoute();
    const { item, id } = route.params || {};

    const [nom, setNom] = useState(item?.nom || '');
    const [telephone, setTelephone] = useState(item?.telephone || '');
    const [adresse, setAdresse] = useState(item?.adresse || '');
    const [limiteCredit, setLimiteCredit] = useState(item?.limite_credit ? String(item.limite_credit) : '');
    const [notes, setNotes] = useState(item?.notes || '');
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

            await client.put(`/clients/${id || item?.id}`, payload);
            Alert.alert('Succès', 'Client modifié avec succès.');
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
            <Header title="Modifier Client" onBack={() => navigation.goBack()} />

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

            </KeyboardAwareScrollView>


            <View style={[styles.footer, { paddingBottom: 16 + insets.bottom }]}>
                <TouchableOpacity style={styles.submitBtn} onPress={handleSubmit} disabled={submitting}>
                    {submitting ? (
                        <ActivityIndicator color="#FFF" />
                    ) : (
                        <Text style={styles.submitBtnText}>Enregistrer les modifications</Text>
                    )}
                </TouchableOpacity>
            </View>

        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: Colors.background },
    scrollContent: { padding: 16, paddingBottom: 40 },
    cardSection: { backgroundColor: '#FFF', borderRadius: 14, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    fieldLabel: { fontSize: 12, fontWeight: '600', color: Colors.text, marginTop: 10, marginBottom: 4 },
    input: { borderWidth: 1, borderColor: Colors.border, borderRadius: 8, padding: 10, fontSize: 14, backgroundColor: '#f8fafc' },
    footer: { padding: 16, backgroundColor: Colors.surface, borderTopWidth: 1, borderTopColor: Colors.border },
    submitBtn: { backgroundColor: Colors.primary, paddingVertical: 16, borderRadius: 12, alignItems: 'center' },
    submitBtnText: { color: '#FFF', fontWeight: '800', fontSize: 15 },
});

export default ClientEditScreen;
