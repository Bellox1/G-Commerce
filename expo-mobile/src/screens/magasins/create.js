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

const CreateMagasinScreen = ({ navigation, route }) => {
    const insets = useSafeAreaInsets();
    const editing = route?.params?.item;

    const [nom, setNom] = useState(editing?.nom || '');
    const [adresse, setAdresse] = useState(editing?.adresse || '');
    const [loyerMensuel, setLoyerMensuel] = useState(editing?.loyer != null ? String(editing.loyer) : '');
    const [submitting, setSubmitting] = useState(false);

    const handleSubmit = async () => {
        if (!nom) {
            Alert.alert('Erreur', 'Le nom du magasin est obligatoire.');
            return;
        }

        setSubmitting(true);
        try {
            const payload = {
                nom,
                adresse,
                loyer: loyerMensuel ? Number(loyerMensuel) : null
            };

            if (editing) {
                await client.put(`/magasins/${editing.id}`, payload);
                Alert.alert('Succès', 'Magasin modifié avec succès.');
            } else {
                await client.post('/magasins', payload);
                Alert.alert('Succès', 'Magasin créé avec succès.');
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
            <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />

            {/* Top Bar */}
            <View style={[styles.topBar, { paddingTop: Math.max(insets.top, 20) + 8 }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={24} color="#FFF" />
                </TouchableOpacity>
                <Text style={styles.topTitle}>{editing ? 'Modifier Magasin' : 'Nouveau Magasin / Dépôt'}</Text>
                <View style={{ width: 24 }} />
            </View>

            <KeyboardAwareScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>


                <View style={styles.cardSection}>
                    <Text style={styles.fieldLabel}>Nom du magasin / Dépôt *</Text>
                    <TextInput
                        style={styles.input}
                        value={nom}
                        onChangeText={setNom}
                        placeholder="Ex: Dépôt Akpakpa"
                    />

                    <Text style={styles.fieldLabel}>Adresse physique</Text>
                    <TextInput
                        style={styles.input}
                        value={adresse}
                        onChangeText={setAdresse}
                        placeholder="Ex: Cotonou, Rue 45"
                    />

                    <Text style={styles.fieldLabel}>Loyer mensuel (FCFA)</Text>
                    <TextInput
                        style={styles.input}
                        keyboardType="numeric"
                        value={loyerMensuel}
                        onChangeText={setLoyerMensuel}
                        placeholder="Ex: 50000"
                    />
                </View>

                <TouchableOpacity style={styles.submitBtn} onPress={handleSubmit} disabled={submitting}>
                    {submitting ? (
                        <ActivityIndicator color="#FFF" />
                    ) : (
                        <Text style={styles.submitBtnText}>{editing ? 'Enregistrer les modifications' : 'Créer le magasin'}</Text>
                    )}
                </TouchableOpacity>

            </KeyboardAwareScrollView>

        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: Colors.background },
    topBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: Colors.primary, paddingHorizontal: 16, paddingBottom: 12 },
    backBtn: { padding: 4 },
    topTitle: { fontSize: 16, fontFamily: 'Poppins_700Bold', color: '#FFF' },
    scrollContent: { padding: 16, paddingBottom: 40 },
    cardSection: { backgroundColor: '#FFF', borderRadius: 14, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    fieldLabel: { fontSize: 12, fontWeight: '600', color: Colors.text, marginTop: 10, marginBottom: 4 },
    input: { borderWidth: 1, borderColor: Colors.border, borderRadius: 8, padding: 10, fontSize: 14, backgroundColor: '#f8fafc' },
    submitBtn: { backgroundColor: Colors.primary, paddingVertical: 16, borderRadius: 12, alignItems: 'center', marginTop: 10 },
    submitBtnText: { color: '#FFF', fontWeight: '800', fontSize: 15 },
});

export default CreateMagasinScreen;
