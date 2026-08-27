import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../context/AuthContext';
import Colors from '../theme/Colors';

const OffrePauseScreen = () => {
    const { user, logout } = useAuth();
    const nom = user?.tenant?.offre_statut?.libelle || 'En pause';

    return (
        <ScrollView contentContainerStyle={styles.container} style={{ backgroundColor: Colors.background }}>
            <View style={styles.iconWrap}>
                <Ionicons name="pause-circle" size={64} color={Colors.secondary || '#f59e0b'} />
            </View>

            <Text style={styles.title}>Offre en pause</Text>
            <Text style={styles.subtitle}>
                L'offre de votre société est actuellement en pause. Toutes les fonctionnalités liées
                (ventes, stock, clients, dépenses…) sont suspendues jusqu'à la reprise de l'offre.
            </Text>

            <View style={styles.badge}>
                <Text style={styles.badgeText}>Statut : {nom}</Text>
            </View>

            <TouchableOpacity style={styles.button} onPress={logout} activeOpacity={0.85}>
                <Ionicons name="log-out-outline" size={20} color="#fff" />
                <Text style={styles.buttonText}>Se déconnecter</Text>
            </TouchableOpacity>

            <Text style={styles.help}>
                Contactez votre administrateur ou partenaire PILOTIX pour reprendre l'offre.
            </Text>
        </ScrollView>
    );
};

const styles = StyleSheet.create({
    container: {
        flex: 1,
        alignItems: 'center',
        justifyContent: 'center',
        padding: 28,
    },
    iconWrap: {
        width: 110,
        height: 110,
        borderRadius: 55,
        backgroundColor: 'rgba(245, 158, 11, 0.12)',
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: 24,
    },
    title: {
        fontFamily: 'SpaceGrotesk_700Bold',
        fontSize: 24,
        color: Colors.text || '#0f172a',
        textAlign: 'center',
        marginBottom: 12,
    },
    subtitle: {
        fontFamily: 'PlusJakartaSans_400Regular',
        fontSize: 15,
        color: '#64748b',
        textAlign: 'center',
        lineHeight: 22,
        marginBottom: 18,
    },
    badge: {
        backgroundColor: '#fef3c7',
        paddingVertical: 8,
        paddingHorizontal: 16,
        borderRadius: 50,
        marginBottom: 28,
    },
    badgeText: {
        fontFamily: 'PlusJakartaSans_700Bold',
        fontSize: 13,
        color: '#b45309',
    },
    button: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 8,
        backgroundColor: Colors.primary,
        paddingVertical: 14,
        paddingHorizontal: 28,
        borderRadius: 12,
        elevation: 3,
    },
    buttonText: {
        fontFamily: 'PlusJakartaSans_700Bold',
        fontSize: 15,
        color: '#fff',
    },
    help: {
        fontFamily: 'PlusJakartaSans_400Regular',
        fontSize: 12,
        color: '#94a3b8',
        textAlign: 'center',
        marginTop: 22,
    },
});

export default OffrePauseScreen;
