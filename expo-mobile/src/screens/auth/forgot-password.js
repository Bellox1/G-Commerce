import React, { useState } from 'react';
import {
    View, Text, StyleSheet, TextInput, TouchableOpacity,
    KeyboardAvoidingView, Platform, ScrollView,
    ActivityIndicator, Alert, Dimensions, StatusBar, Image
} from 'react-native';
import Colors from '../../theme/Colors';
import { Ionicons } from '@expo/vector-icons';
import client from '../../api/client';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

const { width } = Dimensions.get('window');

const ForgotPasswordScreen = ({ navigation }) => {
    const insets = useSafeAreaInsets();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [loading, setLoading] = useState(false);
    const [step, setStep] = useState(0); // 0: Email, 2: Code, 3: Nouveau Pass
    const [otpArray, setOtpArray] = useState(['', '', '', '', '', '']);
    const [focusedIndex, setFocusedIndex] = useState(0);
    const otpInputs = React.useRef([]);
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);
    const [checkLoading, setCheckLoading] = useState(false);

    const handleSendCode = async () => {
        if (!email || !email.includes('@')) {
            Alert.alert('Erreur', 'Veuillez entrer une adresse email valide');
            return;
        }

        setCheckLoading(true);
        try {
            const res = await client.post('/forgot-password', { email });
            const otp = res.data?.dev_otp;
            if (otp) {
                Alert.alert('Mode dev', `Votre code OTP est : ${otp}`);
            }
            setStep(2);
        } catch (error) {
            const msg = error.response?.data?.message || "Cet email n'est pas reconnu.";
            Alert.alert('Erreur', msg);
        } finally {
            setCheckLoading(false);
        }
    };

    const handleOtpChange = (value, index) => {
        const newOtpArray = [...otpArray];
        newOtpArray[index] = value;
        setOtpArray(newOtpArray);

        if (value && index < 5) {
            otpInputs.current[index + 1]?.focus();
            setFocusedIndex(index + 1);
        }

        const fullOtp = newOtpArray.join('');
        if (fullOtp.length === 6) {
            setStep(3);
        }
    };

    const handleOtpKeyPress = (e, index) => {
        if (e.nativeEvent.key === 'Backspace' && !otpArray[index] && index > 0) {
            otpInputs.current[index - 1]?.focus();
            setFocusedIndex(index - 1);
        }
    };

    const validatePasswordFull = (pass) => {
        return {
            length: pass.length >= 8,
            case: /(?=.*[a-z])(?=.*[A-Z])/.test(pass),
            number: /\d/.test(pass),
            symbol: /[^A-Za-z0-9]/.test(pass),
        };
    };

    const isPasswordValid = (pass) => {
        const v = validatePasswordFull(pass);
        return v.length && v.case && v.number && v.symbol;
    };

    const handleResetPassword = async () => {
        if (!password || !passwordConfirmation) {
            Alert.alert('Erreur', 'Veuillez remplir tous les champs');
            return;
        }

        if (!isPasswordValid(password)) {
            Alert.alert('Mot de passe trop faible', 'Le mot de passe doit respecter tous les critères.');
            return;
        }

        if (password !== passwordConfirmation) {
            Alert.alert('Erreur', 'Les mots de passe ne correspondent pas.');
            return;
        }

        setLoading(true);
        try {
            await client.post('/reset-password', {
                email,
                code: otpArray.join(''),
                password,
                password_confirmation: passwordConfirmation
            });
            Alert.alert('Succès', 'Votre mot de passe a été réinitialisé !', [
                { text: 'Se connecter', onPress: () => navigation.navigate('Login') }
            ]);
        } catch (error) {
            const msg = error.response?.data?.message || "Erreur lors de la réinitialisation";
            Alert.alert('Erreur', msg);
        } finally {
            setLoading(false);
        }
    };

    return (
        <KeyboardAvoidingView
            behavior={Platform.OS === 'ios' ? 'padding' : null}
            style={styles.container}
        >
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            <ScrollView
                contentContainerStyle={[
                    styles.scrollContent,
                    { paddingTop: Math.max(insets.top, 20) + 10 }
                ]}
                keyboardShouldPersistTaps="always"
                showsVerticalScrollIndicator={false}
            >
                <TouchableOpacity style={styles.backBtn} onPress={() => navigation.goBack()}>
                    <Ionicons name="arrow-back" size={24} color={Colors.text} />
                </TouchableOpacity>

                {/* Logo Section */}
                <View style={styles.logoSection}>
                    <Image
                        source={require('../../../assets/pilotix-logo.png')}
                        style={styles.logoImage}
                        resizeMode="contain"
                    />
                </View>

                <View style={styles.header}>
                    <Text style={styles.title}>Réinitialisation</Text>
                    <Text style={styles.subtitle}>
                        {step === 0 && "Entrez votre email pour recevoir un code de vérification"}
                        {step === 2 && "Saisissez le code à 4 chiffres envoyé à " + email}
                        {step === 3 && "Définissez votre nouveau mot de passe sécurisé"}
                    </Text>
                </View>

                {step === 0 && (
                    <View style={styles.form}>
                        <Text style={styles.fieldLabel}>Adresse Email *</Text>
                        <View style={styles.inputGroup}>
                            <Ionicons name="mail-outline" size={20} color={Colors.textLight} style={styles.icon} />
                            <TextInput
                                style={styles.input}
                                placeholder="votre@email.com"
                                placeholderTextColor={Colors.textLight}
                                value={email}
                                onChangeText={setEmail}
                                keyboardType="email-address"
                                autoCapitalize="none"
                                autoCorrect={false}
                                selectionColor={Colors.primary}
                            />
                        </View>
                        <TouchableOpacity
                            style={styles.btn}
                            onPress={handleSendCode}
                            disabled={checkLoading}
                            activeOpacity={0.85}
                        >
                            {checkLoading ? (
                                <ActivityIndicator color="#FFF" />
                            ) : (
                                <Text style={styles.btnText}>Envoyer le code</Text>
                            )}
                        </TouchableOpacity>
                    </View>
                )}

                {step === 2 && (
                    <View style={styles.form}>
                        <View style={styles.otpSplitRow}>
                            {otpArray.map((digit, index) => (
                                <TextInput
                                    key={index}
                                    ref={(ref) => (otpInputs.current[index] = ref)}
                                    style={[
                                        styles.otpBox,
                                        (digit || focusedIndex === index) ? styles.otpBoxActive : null
                                    ]}
                                    value={digit}
                                    onFocus={() => setFocusedIndex(index)}
                                    onChangeText={(val) => handleOtpChange(val, index)}
                                    onKeyPress={(e) => handleOtpKeyPress(e, index)}
                                    keyboardType="number-pad"
                                    maxLength={1}
                                    autoFocus={index === 0}
                                    selectionColor={Colors.primary}
                                />
                            ))}
                        </View>
                        <TouchableOpacity style={styles.cancelBtn} onPress={() => setStep(1)}>
                            <Text style={styles.cancelLink}>Renvoyer un nouveau code</Text>
                        </TouchableOpacity>
                    </View>
                )}

                {step === 3 && (
                    <View style={styles.form}>
                        <Text style={styles.fieldLabel}>Nouveau mot de passe *</Text>
                        <View style={styles.inputGroup}>
                            <Ionicons name="lock-closed-outline" size={20} color={Colors.textLight} style={styles.icon} />
                            <TextInput
                                style={styles.input}
                                placeholder="Nouveau mot de passe"
                                placeholderTextColor={Colors.textLight}
                                value={password}
                                onChangeText={setPassword}
                                secureTextEntry={!showPassword}
                                selectionColor={Colors.primary}
                            />
                            <TouchableOpacity onPress={() => setShowPassword(!showPassword)} style={styles.eyeBtn}>
                                <Ionicons name={showPassword ? 'eye-off-outline' : 'eye-outline'} size={20} color={Colors.textLight} />
                            </TouchableOpacity>
                        </View>

                        {password.length > 0 && (
                            <View style={styles.pwdCriteriaRow}>
                                {Object.entries(validatePasswordFull(password)).map(([key, valid]) => (
                                    <View key={key} style={styles.criteriaItem}>
                                        <Ionicons
                                            name={valid ? "checkmark-circle" : "ellipse-outline"}
                                            size={14}
                                            color={valid ? Colors.success : Colors.textLight}
                                        />
                                        <Text style={[styles.criteriaText, valid && { color: Colors.success }]}>
                                            {key === 'length' ? '8+ car.' :
                                                key === 'case' ? 'Aa' :
                                                    key === 'number' ? '123' : '#$&'}
                                        </Text>
                                    </View>
                                ))}
                            </View>
                        )}

                        <Text style={styles.fieldLabel}>Confirmer le mot de passe *</Text>
                        <View style={styles.inputGroup}>
                            <Ionicons name="lock-closed-outline" size={20} color={Colors.textLight} style={styles.icon} />
                            <TextInput
                                style={styles.input}
                                placeholder="Confirmer le mot de passe"
                                placeholderTextColor={Colors.textLight}
                                value={passwordConfirmation}
                                onChangeText={setPasswordConfirmation}
                                secureTextEntry={!showConfirmPassword}
                                selectionColor={Colors.primary}
                            />
                            <TouchableOpacity onPress={() => setShowConfirmPassword(!showConfirmPassword)} style={styles.eyeBtn}>
                                <Ionicons name={showConfirmPassword ? 'eye-off-outline' : 'eye-outline'} size={20} color={Colors.textLight} />
                            </TouchableOpacity>
                        </View>

                        <TouchableOpacity style={styles.btn} onPress={handleResetPassword} disabled={loading}>
                            {loading ? <ActivityIndicator color="#FFF" /> : <Text style={styles.btnText}>Valider et Réinitialiser</Text>}
                        </TouchableOpacity>
                    </View>
                )}
            </ScrollView>
        </KeyboardAvoidingView>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    scrollContent: { paddingHorizontal: 28, paddingBottom: 40 },
    backBtn: { marginBottom: 16, width: 44, height: 44, borderRadius: 22, backgroundColor: '#F1F5F9', justifyContent: 'center', alignItems: 'center' },
    logoSection: { alignItems: 'center', marginBottom: 10 },
    logoImage: { width: 180, height: 60 },
    header: { alignItems: 'center', marginBottom: 28 },
    title: { fontSize: 26, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text, marginBottom: 6 },
    subtitle: { fontSize: 13.5, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.textLight, textAlign: 'center', lineHeight: 20 },
    form: { width: '100%' },
    fieldLabel: { fontSize: 12, fontFamily: 'PlusJakartaSans_600SemiBold', color: Colors.text, marginBottom: 6, marginLeft: 4 },
    inputGroup: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#FFFFFF',
        borderRadius: 26, // Pill shape
        paddingHorizontal: 20,
        marginBottom: 16,
        height: 52,
        borderWidth: 1,
        borderColor: Colors.border,
    },
    icon: { marginRight: 10 },
    input: { flex: 1, height: '100%', fontSize: 15, fontFamily: 'PlusJakartaSans_400Regular', color: Colors.text, backgroundColor: 'transparent' },
    eyeBtn: { padding: 8 },
    btn: {
        backgroundColor: Colors.primary,
        height: 52,
        borderRadius: 26, // Pill shape
        justifyContent: 'center',
        alignItems: 'center',
        marginTop: 10,
        shadowColor: Colors.primary,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 8,
        elevation: 3,
    },
    btnText: { color: '#FFF', fontSize: 16, fontFamily: 'PlusJakartaSans_700Bold' },
    otpBtn: {
        flexDirection: 'row',
        height: 52,
        borderRadius: 26, // Pill shape
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: 14,
        gap: 12,
    },
    otpBtnText: { color: '#FFF', fontSize: 15, fontFamily: 'PlusJakartaSans_600SemiBold' },
    cancelBtn: { marginTop: 16, alignItems: 'center' },
    cancelLink: { color: Colors.textLight, fontFamily: 'PlusJakartaSans_500Medium', textDecorationLine: 'underline' },
    otpSplitRow: { flexDirection: 'row', justifyContent: 'center', marginVertical: 20, gap: 12 },
    otpBox: {
        width: 56, height: 56, backgroundColor: '#F8FAFC', borderRadius: 16,
        borderWidth: 1.5, borderColor: Colors.border, textAlign: 'center',
        fontSize: 22, fontFamily: 'SpaceGrotesk_700Bold', color: Colors.text,
    },
    otpBoxActive: { borderColor: Colors.primary, backgroundColor: '#FFF' },
    pwdCriteriaRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 16, paddingHorizontal: 4 },
    criteriaItem: { flexDirection: 'row', alignItems: 'center', gap: 4 },
    criteriaText: { fontSize: 11, fontFamily: 'PlusJakartaSans_500Medium', color: Colors.textLight },
});

export default ForgotPasswordScreen;
