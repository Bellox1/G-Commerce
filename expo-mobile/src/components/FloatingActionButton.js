import React from 'react';
import { TouchableOpacity, StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import Colors from '../theme/Colors';

const FloatingActionButton = ({ onPress, icon = 'add', label = null }) => {
    return (
        <TouchableOpacity
            style={[styles.fab, label ? styles.fabExtended : styles.fabCircle]}
            onPress={onPress}
            activeOpacity={0.88}
        >
            <Ionicons name={icon} size={26} color="#FFFFFF" />
            {label ? <Text style={styles.fabLabel}>{label}</Text> : null}
        </TouchableOpacity>
    );
};

const styles = StyleSheet.create({
    fab: {
        position: 'absolute',
        bottom: 90, // Positioned right above the floating bottom navbar
        right: 20,
        backgroundColor: Colors.primary,
        justifyContent: 'center',
        alignItems: 'center',
        elevation: 8,
        shadowColor: Colors.primary,
        shadowOffset: { width: 0, height: 6 },
        shadowOpacity: 0.35,
        shadowRadius: 10,
        borderWidth: 1,
        borderColor: 'rgba(255, 255, 255, 0.3)',
        zIndex: 999,
    },
    fabCircle: {
        width: 56,
        height: 56,
        borderRadius: 28,
    },
    fabExtended: {
        flexDirection: 'row',
        height: 50,
        borderRadius: 25,
        paddingHorizontal: 18,
        gap: 8,
    },
    fabLabel: {
        color: '#FFFFFF',
        fontSize: 14,
        fontFamily: 'PlusJakartaSans_700Bold',
    },
});

export default FloatingActionButton;
