import 'react-native-gesture-handler';
import React, { useState, useEffect } from 'react';
import { View, ActivityIndicator, Text } from 'react-native';
import { NavigationContainer } from '@react-navigation/native';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { GestureHandlerRootView } from 'react-native-gesture-handler';
import { AuthProvider } from './src/context/AuthContext';
import AppNavigator from './src/navigation/AppNavigator';
import { StatusBar } from 'expo-status-bar';
import * as SplashScreen from 'expo-splash-screen';
import { RootSiblingParent } from 'react-native-root-siblings';
import ErrorBoundary from './src/components/ErrorBoundary';
import { 
  useFonts, 
  PlusJakartaSans_400Regular, 
  PlusJakartaSans_500Medium, 
  PlusJakartaSans_600SemiBold, 
  PlusJakartaSans_700Bold 
} from '@expo-google-fonts/plus-jakarta-sans';
import { SpaceGrotesk_700Bold } from '@expo-google-fonts/space-grotesk';
import { 
  Poppins_400Regular, 
  Poppins_500Medium, 
  Poppins_600SemiBold, 
  Poppins_700Bold 
} from '@expo-google-fonts/poppins';

import { CartProvider } from './src/context/CartContext';

export default function App() {
  const [fontsLoaded] = useFonts({
    PlusJakartaSans_400Regular,
    PlusJakartaSans_500Medium,
    PlusJakartaSans_600SemiBold,
    PlusJakartaSans_700Bold,
    SpaceGrotesk_700Bold,
    Poppins_400Regular,
    Poppins_500Medium,
    Poppins_600SemiBold,
    Poppins_700Bold,
  });

  // Timeout de sécurité : si fonts pas chargées après 4s, on lance l'app quand même
  const [timedOut, setTimedOut] = useState(false);
  useEffect(() => {
    const t = setTimeout(() => setTimedOut(true), 4000);
    return () => clearTimeout(t);
  }, []);

  // Masquer explicitement le splash screen dès que l'app monte
  useEffect(() => {
    SplashScreen.hideAsync().catch(() => {});
  }, []);

  // Tant que fonts pas chargées ET timeout pas atteint, montrer un loader simple
  if (!fontsLoaded && !timedOut) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: '#FFFFFF' }}>
        <ActivityIndicator size="large" color="#105e49" />
        <Text style={{ marginTop: 12, color: '#64748B', fontSize: 14 }}>Chargement des polices…</Text>
      </View>
    );
  }

  return (
    <ErrorBoundary>
      <GestureHandlerRootView style={{ flex: 1 }}>
        <RootSiblingParent>
          <SafeAreaProvider>
            <AuthProvider>
              <CartProvider>
                <NavigationContainer theme={{
              dark: false,
              colors: {
                primary: '#105e49',
                background: '#FFFFFF',
                card: '#FFFFFF',
                text: '#0F172A',
                border: '#E2E8F0',
                notification: '#F59E0B',
              },
              fonts: {
                regular: { fontFamily: 'PlusJakartaSans_400Regular', fontWeight: '400' },
                medium: { fontFamily: 'PlusJakartaSans_500Medium', fontWeight: '500' },
                bold: { fontFamily: 'PlusJakartaSans_700Bold', fontWeight: '700' },
                heavy: { fontFamily: 'SpaceGrotesk_700Bold', fontWeight: '900' },
              },
            }}>
              <StatusBar style="dark" backgroundColor="#FFFFFF" />
              <AppNavigator />
            </NavigationContainer>
              </CartProvider>
            </AuthProvider>
          </SafeAreaProvider>
        </RootSiblingParent>
      </GestureHandlerRootView>
    </ErrorBoundary>
  );
}
