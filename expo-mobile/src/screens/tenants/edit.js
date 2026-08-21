import React, { useState } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity,
    TextInput, ActivityIndicator, Alert, StatusBar
} from 'react-native';
import KeyboardAwareScrollView from '../../components/KeyboardAwareScrollView';
import { Picker } from '@react-native-picker/picker';
import { DrawerActions } from '@react-navigation/native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { Header } from '../../components/ui';

const PAYS = [
    { code: 'BJ', label: 'Bénin (BJ)' },
    { code: 'NG', label: 'Nigeria (NG)' },
    { code: 'TG', label: 'Togo (TG)' },
    { code: 'CI', label: 'Côte d\'Ivoire (CI)' },
    { code: 'GH', label: 'Ghana (GH)' },
];

const EditTenantScreen = ({ navigation, route }) => {
    const item = route?.params?.item;
    const id = route?.params?.id || item?.id;

    const [nom, setNom] = useState(item?.nom || '');
    const [marque, setMarque] = useState(item?.marque || '');
    const [activite, setActivite] = useState(item?.activite || '');
    const [pays, setPays] = useState(item?.pays || 'BJ');
    const [ville, setVille] = useState(item?.ville || '');
    const [telephone, setTelephone] = useState(item?.telephone || '');
    const [email, setEmail] = useState(item?.email || '');
    const [actif, setActif] = useState(item?.actif ? '1' : '0');
    const [submitting, setSubmitting] = useState(false);

    const handleSubmit = async () => {
        if (!nom) {
            Alert.alert('Erreur', 'Le nom de la société est obligatoire.');
            return;
        }

        setSubmitting(true);
        try {
            const payload = {
                nom,
                marque,
                activite,
                pays,
                ville,
                telephone,
                email,
                actif: actif === '1',
            };

            await client.put(`/tenants/${id}`, payload);
            Alert.alert('Succès', 'Société mise à jour avec succès.');
            navigation.goBack();
        } catch (e) {
            const msg = e.response?.data?.message || 'Erreur lors de la mise à jour de la société';
            Alert.alert('Erreur', msg);
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor={Colors.background} />

            <Header
                title="Modifier Société"
                onMenu={() => navigation.dispatch(DrawerActions.openDrawer())} onBack={() => navigation.goBack()}
            />

            <KeyboardAwareScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>


                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Fiche d'identité</Text>

                    <Text style={styles.fieldLabel}>Nom / Raison Sociale *</Text>
                    <TextInput style={styles.input} value={nom} onChangeText={setNom} placeholder="Ex : SAÏMOUS" />

                    <Text style={styles.fieldLabel}>Marque Commerciale</Text>
                    <TextInput style={styles.input} value={marque} onChangeText={setMarque} placeholder="Ex : RICCI" />

                    <Text style={styles.fieldLabel}>Secteur d'activité</Text>
                    <TextInput style={styles.input} value={activite} onChangeText={setActivite} placeholder="Ex : Importation et vente" />
                </View>

                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}>Localisation & Statut</Text>

                    <Text style={styles.fieldLabel}>Pays *</Text>
                    <View style={styles.pickerWrap}>
                        <Picker selectedValue={pays} onValueChange={setPays} style={styles.picker}>
                            {PAYS.map((p) => (
                                <Picker.Item key={p.code} label={p.label} value={p.code} />
                            ))}
                        </Picker>
                    </View>

                    <Text style={styles.fieldLabel}>Ville</Text>
                    <TextInput style={styles.input} value={ville} onChangeText={setVille} placeholder="Ex : Cotonou" />

                    <View style={styles.row2}>
                        <View style={styles.col}>
                            <Text style={styles.fieldLabel}>Téléphone</Text>
                            <TextInput style={styles.input} keyboardType="phone-pad" value={telephone} onChangeText={setTelephone} placeholder="Ex : +229 97 00 00 00" />
                        </View>
                        <View style={styles.col}>
                            <Text style={styles.fieldLabel}>Email société</Text>
                            <TextInput style={styles.input} keyboardType="email-address" value={email} onChangeText={setEmail} placeholder="Ex : contact@societe.com" />
                        </View>
                    </View>

                    <Text style={styles.fieldLabel}>Statut du compte</Text>
                    <View style={styles.pickerWrap}>
                        <Picker selectedValue={actif} onValueChange={setActif} style={styles.picker}>
                            <Picker.Item label="Actif (Autorisé à se connecter)" value="1" />
                            <Picker.Item label="Inactif (Connexion bloquée)" value="0" />
                        </Picker>
                    </View>
                </View>

                <TouchableOpacity style={styles.submitBtn} onPress={handleSubmit} disabled={submitting}>
                    {submitting ? (
                        <ActivityIndicator color="#FFF" />
                    ) : (
                        <Text style={styles.submitBtnText}>Enregistrer les modifications</Text>
                    )}
                </TouchableOpacity>

            </KeyboardAwareScrollView>

        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: Colors.background },
    scrollContent: { padding: 16, paddingBottom: 40 },
    cardSection: { backgroundColor: Colors.surface, borderRadius: 14, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    cardTitle: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.primary, marginBottom: 12 },
    fieldLabel: { fontSize: 12, fontFamily: 'Poppins_500Medium', color: Colors.textLight, marginTop: 10, marginBottom: 4 },
    input: { borderWidth: 1, borderColor: Colors.border, borderRadius: 8, padding: 10, fontSize: 14, fontFamily: 'Poppins_400Regular', color: Colors.text, backgroundColor: Colors.background },
    row2: { flexDirection: 'row', gap: 10 },
    col: { flex: 1 },
    pickerWrap: { borderWidth: 1, borderColor: Colors.border, borderRadius: 8, backgroundColor: Colors.background, overflow: 'hidden' },
    picker: { height: 50, color: Colors.text },
    submitBtn: { backgroundColor: Colors.primary, paddingVertical: 16, borderRadius: 12, alignItems: 'center', marginTop: 10 },
    submitBtnText: { color: '#FFF', fontSize: 15, fontFamily: 'Poppins_700Bold' },
});

export default EditTenantScreen;
