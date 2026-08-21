import React, { useState } from 'react';
import {
    View, Text, StyleSheet, TextInput, TouchableOpacity,
    KeyboardAvoidingView, Platform, ScrollView,
    ActivityIndicator, Alert, StatusBar, Image
} from 'react-native';
import { useAuth } from '../../context/AuthContext';
import { apiErrorMessage } from '../../api/client';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

const LoginScreen = ({ navigation }) => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [loading, setLoading] = useState(false);
    const [showPassword, setShowPassword] = useState(false);
    const [emailFocused, setEmailFocused] = useState(false);
    const [passFocused, setPassFocused] = useState(false);
    const { login } = useAuth();
    const insets = useSafeAreaInsets();

    const handleLogin = async () => {
        if (!email || !password) {
            Alert.alert('Erreur', 'Veuillez remplir votre adresse email et votre mot de passe.');
            return;
        }
        setLoading(true);
        try {
            await login(email.trim(), password);
        } catch (error) {
            const message = error?.userMessage || apiErrorMessage(error, { isLogin: true });
            if (__DEV__) console.log('[Login] échec:', message);
            Alert.alert('Échec de connexion', message);
        } finally {
            setLoading(false);
        }
    };

    return (
        <KeyboardAvoidingView
            behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
            style={styles.root}
        >
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            <ScrollView
                contentContainerStyle={[
                    styles.scrollContent,
                    { paddingTop: Math.max(insets.top + 10, 30), paddingBottom: Math.max(insets.bottom + 20, 30) }
                ]}
                keyboardShouldPersistTaps="always"
                showsVerticalScrollIndicator={false}
            >
                {/* Brand Header */}
                <View style={styles.logoSection}>
                    <Image
                        source={require('../../../assets/pilotix.png')}
                        style={styles.logoImage}
                        resizeMode="contain"
                    />
                </View>

                {/* Title Section (Matching Image 2) */}
                <View style={styles.titleSection}>
                    <Text style={styles.title}>Connexion</Text>
                    <Text style={styles.subtitle}>Connectez-vous pour piloter votre entreprise</Text>
                </View>

                {/* Pill Form Inputs (Matching Image 2) */}
                <View style={styles.form}>
                    <View style={[styles.inputPill, emailFocused && styles.inputPillFocused]}>
                        <Ionicons name="mail-outline" size={18} color={emailFocused ? Colors.primary : Colors.textLight} style={styles.inputIcon} />
                        <TextInput
                            style={styles.input}
                            placeholder="Adresse email"
                            placeholderTextColor={Colors.textMuted}
                            value={email}
                            onChangeText={setEmail}
                            keyboardType="email-address"
                            autoCapitalize="none"
                            autoCorrect={false}
                            onFocus={() => setEmailFocused(true)}
                            onBlur={() => setEmailFocused(false)}
                            selectionColor={Colors.primary}
                        />
                    </View>

                    <View style={[styles.inputPill, passFocused && styles.inputPillFocused]}>
                        <Ionicons name="lock-closed-outline" size={18} color={passFocused ? Colors.primary : Colors.textLight} style={styles.inputIcon} />
                        <TextInput
                            style={styles.input}
                            placeholder="Mot de passe"
                            placeholderTextColor={Colors.textMuted}
                            value={password}
                            onChangeText={setPassword}
                            secureTextEntry={!showPassword}
                            autoCapitalize="none"
                            onFocus={() => setPassFocused(true)}
                            onBlur={() => setPassFocused(false)}
                            selectionColor={Colors.primary}
                        />
                        <TouchableOpacity
                            onPress={() => setShowPassword(!showPassword)}
                            style={styles.eyeBtn}
                        >
                            <Ionicons
                                name={showPassword ? 'eye-off-outline' : 'eye-outline'}
                                size={18}
                                color={Colors.textLight}
                            />
                        </TouchableOpacity>
                    </View>

                    <TouchableOpacity
                        style={styles.forgotBtn}
                        onPress={() => navigation.navigate('ForgotPassword')}
                        activeOpacity={0.7}
                    >
                        <Text style={styles.forgotText}>Mot de passe oublié ?</Text>
                    </TouchableOpacity>

                    {/* Main Pill Button */}
                    <TouchableOpacity
                        style={styles.btnPrimary}
                        onPress={handleLogin}
                        disabled={loading}
                        activeOpacity={0.88}
                    >
                        {loading ? (
                            <ActivityIndicator color="#FFFFFF" size="small" />
                        ) : (
                            <Text style={styles.btnPrimaryText}>Se Connecter</Text>
                        )}
                    </TouchableOpacity>

                    {/* Divider */}
                    <View style={styles.dividerRow}>
                        <View style={styles.dividerLine} />
                        <Text style={styles.dividerText}>ou</Text>
                        <View style={styles.dividerLine} />
                    </View>

                    {/* Secondary Option Pill Buttons (Image 2 style) */}
                    <TouchableOpacity
                        style={styles.optionPill}
                        onPress={() => navigation.navigate('Welcome')}
                        activeOpacity={0.7}
                    >
                        <Ionicons name="information-circle-outline" size={18} color={Colors.text} />
                        <Text style={styles.optionPillText}>Découvrir les fonctionnalités</Text>
                    </TouchableOpacity>

                    <View style={styles.footerPrompt}>
                        <Text style={styles.footerPromptText}>Problème de connexion ? </Text>
                        <TouchableOpacity onPress={() => navigation.navigate('ForgotPassword')}>
                            <Text style={styles.footerLink}>Obtenir de l'aide</Text>
                        </TouchableOpacity>
                    </View>
                </View>
            </ScrollView>
        </KeyboardAvoidingView>
    );
};

const styles = StyleSheet.create({
    root: {
        flex: 1,
        backgroundColor: '#FFFFFF',
    },
    scrollContent: {
        paddingHorizontal: 28,
        flexGrow: 1,
        justifyContent: 'center',
    },
    logoSection: {
        alignItems: 'center',
        marginBottom: 24,
    },
    logoImage: {
        width: 170,
        height: 55,
    },
    titleSection: {
        alignItems: 'center',
        marginBottom: 30,
    },
    title: {
        fontSize: 28,
        fontFamily: 'SpaceGrotesk_700Bold',
        color: Colors.text,
        marginBottom: 6,
    },
    subtitle: {
        fontSize: 14,
        fontFamily: 'PlusJakartaSans_400Regular',
        color: Colors.textLight,
        textAlign: 'center',
    },
    form: {
        width: '100%',
    },
    inputPill: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#FFFFFF',
        borderRadius: 26, // Rounded Pill Shape
        paddingHorizontal: 20,
        marginBottom: 14,
        height: 52,
        borderWidth: 1,
        borderColor: '#E2E8F0',
    },
    inputPillFocused: {
        borderColor: Colors.primary,
        backgroundColor: '#F8FAFC',
    },
    inputIcon: {
        marginRight: 10,
    },
    input: {
        flex: 1,
        height: 52,
        fontSize: 15,
        fontFamily: 'PlusJakartaSans_400Regular',
        color: Colors.text,
    },
    eyeBtn: {
        padding: 6,
    },
    forgotBtn: {
        alignSelf: 'center',
        marginTop: 4,
        marginBottom: 24,
    },
    forgotText: {
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_500Medium',
        color: Colors.textLight,
        textDecorationLine: 'underline',
    },
    btnPrimary: {
        backgroundColor: Colors.primary,
        borderRadius: 26, // Pill Button
        height: 54,
        justifyContent: 'center',
        alignItems: 'center',
        elevation: 3,
        shadowColor: Colors.primary,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.25,
        shadowRadius: 8,
    },
    btnPrimaryText: {
        color: '#FFFFFF',
        fontSize: 16,
        fontFamily: 'PlusJakartaSans_700Bold',
    },
    dividerRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginVertical: 24,
    },
    dividerLine: {
        flex: 1,
        height: 1,
        backgroundColor: '#E2E8F0',
    },
    dividerText: {
        marginHorizontal: 16,
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_400Regular',
        color: Colors.textMuted,
    },
    optionPill: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: '#F1F5F9',
        borderRadius: 26,
        height: 50,
        gap: 8,
        marginBottom: 20,
    },
    optionPillText: {
        fontSize: 14,
        fontFamily: 'PlusJakartaSans_600SemiBold',
        color: Colors.text,
    },
    footerPrompt: {
        flexDirection: 'row',
        justifyContent: 'center',
        alignItems: 'center',
        marginTop: 8,
    },
    footerPromptText: {
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_400Regular',
        color: Colors.textLight,
    },
    footerLink: {
        fontSize: 13,
        fontFamily: 'PlusJakartaSans_700Bold',
        color: Colors.primary,
    },
});

export default LoginScreen;
