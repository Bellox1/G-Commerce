import React from 'react';
import { View, Text, TouchableOpacity, StatusBar } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import Colors from '../theme/Colors';
import { Ionicons } from '@expo/vector-icons';

const ScreenHeader = ({ title, subtitle, navigation, onBack }) => {
    const nav = navigation || useNavigation();
    return (
        <View style={styles.bar}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />
            <TouchableOpacity
                style={styles.backBtn}
                onPress={onBack || (() => nav.goBack())}
                activeOpacity={0.6}
            >
                <Ionicons name="arrow-back" size={22} color={Colors.text} />
            </TouchableOpacity>
            <View style={styles.barText}>
                <Text style={styles.barTitle} numberOfLines={1}>{title}</Text>
                {subtitle ? <Text style={styles.barSub} numberOfLines={1}>{subtitle}</Text> : null}
            </View>
        </View>
    );
};

const styles = {
    bar: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#FFFFFF',
        paddingHorizontal: 12,
        paddingTop: StatusBar.currentHeight ? StatusBar.currentHeight + 8 : 24,
        paddingBottom: 10,
        borderBottomWidth: 1,
        borderBottomColor: '#F1F5F9',
    },
    backBtn: {
        width: 38, height: 38, borderRadius: 19,
        backgroundColor: '#F8FAFC', justifyContent: 'center', alignItems: 'center',
        borderWidth: 1, borderColor: '#E2E8F0',
    },
    barText: { flex: 1, marginLeft: 12 },
    barTitle: { fontSize: 18, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text },
    barSub: { fontSize: 12, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, marginTop: 1 },
};

export default ScreenHeader;
