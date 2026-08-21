import React, { useState, useEffect } from 'react';
import {
    View, Text, StyleSheet, ScrollView, TouchableOpacity, StatusBar
} from 'react-native';
import Colors from '../../theme/Colors';
import { DrawerActions } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { Header, Loader, Empty, StatusBadge } from '../../components/ui';

const statusMap = {
    actif: { label: 'Actif', color: Colors.success, bg: '#dcfce7' },
    inactif: { label: 'Inactif', color: Colors.error, bg: '#fee2e2' },
};

const ShowTenantScreen = ({ navigation, route }) => {
    const { id } = route?.params || {};
    const [tenant, setTenant] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (id) fetchTenant();
    }, [id]);

    const fetchTenant = async () => {
        try {
            const resp = await client.get(`/tenants/${id}`);
            setTenant(resp.data?.data || resp.data);
        } catch (e) {
            console.error('Error fetching tenant detail:', e);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <View style={styles.container}>
                <Header title="Société" onMenu={() => navigation.dispatch(DrawerActions.openDrawer())} onBack={() => navigation.goBack()} />
                <Loader />
            </View>
        );
    }

    if (!tenant) {
        return (
            <View style={styles.container}>
                <Header title="Société" onMenu={() => navigation.dispatch(DrawerActions.openDrawer())} onBack={() => navigation.goBack()} />
                <Empty text="Société introuvable" />
            </View>
        );
    }

    const magasins = tenant.magasins || [];
    const users = tenant.users || [];

    return (
        <View style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor={Colors.background} />

            <Header
                title={tenant.nom}
                onMenu={() => navigation.dispatch(DrawerActions.openDrawer())} onBack={() => navigation.goBack()}
                right={
                    <TouchableOpacity onPress={() => navigation.navigate('TenantEdit', { id: tenant.id, item: tenant })}>
                        <Ionicons name="create-outline" size={24} color={Colors.primary} />
                    </TouchableOpacity>
                }
            />

            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>

                {/* Fiche d'identité */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}><Ionicons name="information-circle-outline" size={16} color={Colors.primary} /> Fiche d'identité</Text>

                    <View style={styles.row}>
                        <Text style={styles.label}>Nom / Raison Sociale :</Text>
                        <Text style={styles.val}>{tenant.nom}</Text>
                    </View>

                    {tenant.marque ? (
                        <View style={styles.row}>
                            <Text style={styles.label}>Marque :</Text>
                            <Text style={[styles.val, { color: Colors.primary }]}>{tenant.marque}</Text>
                        </View>
                    ) : null}

                    {tenant.activite ? (
                        <View style={styles.row}>
                            <Text style={styles.label}>Secteur d'activité :</Text>
                            <Text style={styles.val}>{tenant.activite}</Text>
                        </View>
                    ) : null}

                    <View style={styles.row}>
                        <Text style={styles.label}>Pays & Ville :</Text>
                        <Text style={styles.val}>{tenant.ville || 'Non renseignée'} ({tenant.pays})</Text>
                    </View>

                    {tenant.telephone ? (
                        <View style={styles.row}>
                            <Text style={styles.label}>Téléphone principal :</Text>
                            <Text style={styles.val}>{tenant.telephone}</Text>
                        </View>
                    ) : null}

                    {tenant.email ? (
                        <View style={styles.row}>
                            <Text style={styles.label}>E-mail principal :</Text>
                            <Text style={styles.val}>{tenant.email}</Text>
                        </View>
                    ) : null}

                    <View style={styles.row}>
                        <Text style={styles.label}>Statut :</Text>
                        <StatusBadge statut={tenant.actif ? 'actif' : 'inactif'} map={statusMap} />
                    </View>
                </View>

                {/* Magasins */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}><Ionicons name="storefront-outline" size={16} color={Colors.primary} /> Magasins / Dépôts associés</Text>
                    {magasins.length > 0 ? (
                        magasins.map((m, idx) => (
                            <View key={idx} style={styles.listRow}>
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.listName}>{m.nom}</Text>
                                    {m.adresse ? <Text style={styles.listSub}>{m.adresse}</Text> : null}
                                </View>
                            </View>
                        ))
                    ) : (
                        <Text style={styles.emptyText}>Aucun magasin créé pour le moment.</Text>
                    )}
                </View>

                {/* Utilisateurs */}
                <View style={styles.cardSection}>
                    <Text style={styles.cardTitle}><Ionicons name="people-outline" size={16} color={Colors.primary} /> Personnel & Rôles</Text>
                    {users.length > 0 ? (
                        users.map((u, idx) => (
                            <View key={idx} style={styles.listRow}>
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.listName}>{u.name}</Text>
                                    <Text style={styles.listSub}>{u.email}</Text>
                                </View>
                                <View style={styles.roleTag}>
                                    <Text style={styles.roleTagText}>{u.role}</Text>
                                </View>
                            </View>
                        ))
                    ) : (
                        <Text style={styles.emptyText}>Aucun utilisateur créé.</Text>
                    )}
                </View>

            </ScrollView>
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: Colors.background },
    scrollContent: { padding: 16, paddingBottom: 30 },
    cardSection: { backgroundColor: Colors.surface, borderRadius: 14, padding: 16, borderWidth: 1, borderColor: Colors.border, marginBottom: 16 },
    cardTitle: { fontSize: 14, fontFamily: 'Poppins_700Bold', color: Colors.primary, marginBottom: 12 },
    row: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    label: { fontSize: 13, fontFamily: 'Poppins_500Medium', color: Colors.textLight },
    val: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text, flexShrink: 1, textAlign: 'right', marginLeft: 10 },
    listRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    listName: { fontSize: 13, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    listSub: { fontSize: 11, color: Colors.textLight, marginTop: 2 },
    roleTag: { backgroundColor: '#f1f5f9', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6 },
    roleTagText: { fontSize: 11, fontFamily: 'Poppins_600SemiBold', color: Colors.text },
    emptyText: { textAlign: 'center', color: Colors.textLight, fontSize: 12, paddingVertical: 12, fontFamily: 'Poppins_400Regular' },
});

export default ShowTenantScreen;
