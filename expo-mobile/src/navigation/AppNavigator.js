import React from 'react';
import { View, ActivityIndicator, Platform, StyleSheet } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { useAuth } from '../context/AuthContext';
import Colors from '../theme/Colors';
import { Ionicons } from '@expo/vector-icons';

// ─── Auth Screens ────────────────────────────────────────────────────────────
import WelcomeScreen       from '../screens/auth/welcome';
import LoginScreen         from '../screens/auth/login';
import ForgotPasswordScreen from '../screens/auth/forgot-password';

// ─── Main Screens ─────────────────────────────────────────────────────────────
import HomeScreen          from '../screens/dashboard';
import ProfileScreen       from '../screens/profile';
import AnalytiqueScreen    from '../screens/analytique';
import AlertesScreen        from '../screens/alertes';
import HelpScreen           from '../screens/faq';
import OffreScreen          from '../screens/offre';
import DepensesScreen       from '../screens/depenses';

// Ventes
import VentesScreen        from '../screens/ventes/index';
import VentesCreateScreen  from '../screens/ventes/create';
import VentesShowScreen    from '../screens/ventes/show';

// Stock
import StockScreen         from '../screens/stock/index';
import StockMouvementsScreen from '../screens/stock/mouvements';

// Produits
import ProduitsScreen      from '../screens/produits/index';
import ProduitsCreateScreen from '../screens/produits/create';
import ProduitsShowScreen  from '../screens/produits/show';

// Clients
import ClientsScreen       from '../screens/clients/index';
import ClientsCreateScreen from '../screens/clients/create';
import ClientsShowScreen   from '../screens/clients/show';

// Arrivages
import ArrivagesScreen     from '../screens/arrivages/index';
import ArrivagesCreateScreen from '../screens/arrivages/create';
import ArrivagesShowScreen from '../screens/arrivages/show';

// Dettes
import DettesScreen        from '../screens/dettes/index';
import DettesCreateScreen  from '../screens/dettes/create';
import DettesShowScreen    from '../screens/dettes/show';

// Dettes Société
import DettesSocieteScreen from '../screens/dettes-societe/index';
import DettesSocieteCreateScreen from '../screens/dettes-societe/create';

// Employés
import EmployesScreen      from '../screens/employes/index';
import EmployesCreateScreen from '../screens/employes/create';

// Magasins
import MagasinsScreen      from '../screens/magasins/index';
import MagasinsCreateScreen from '../screens/magasins/create';

// Transferts
import TransfertsScreen    from '../screens/transferts/index';
import TransfertsCreateScreen from '../screens/transferts/create';

// Livraisons
import LivraisonsScreen    from '../screens/livraisons/index';
import LivraisonsShowScreen from '../screens/livraisons/show';

// Dettes Société (show)
import DettesSocieteShowScreen from '../screens/dettes-societe/show';

// Edit screens
import ArrivagesEditScreen  from '../screens/arrivages/edit';
import ClientsEditScreen    from '../screens/clients/edit';
import VentesEditScreen     from '../screens/ventes/edit';
import ProduitsEditScreen   from '../screens/produits/edit';
import EmployesEditScreen   from '../screens/employes/edit';
import TransfertsEditScreen from '../screens/transferts/edit';
import TransfertsShowScreen from '../screens/transferts/show';

// Tenants (Super Admin)
import TenantsScreen        from '../screens/tenants/index';
import TenantsCreateScreen  from '../screens/tenants/create';
import TenantsEditScreen    from '../screens/tenants/edit';
import TenantsShowScreen    from '../screens/tenants/show';

import AdminHomeScreen       from '../screens/admin/index';
import CommissionsScreen     from '../screens/admin/commissions/index';
import PrestatairesScreen    from '../screens/admin/prestataires/index';
import PrestataireShowScreen from '../screens/admin/prestataires/show';

import TresorerieScreen      from '../screens/tresorerie/index';
import OffrePauseScreen      from '../screens/OffrePauseScreen';

import { useCan } from '../utils/permissions';

const Stack = createNativeStackNavigator();
const Tab   = createBottomTabNavigator();

// ─── Sleek Bottom Tab Bar ───────────────────────────────────────────────────
const TabNavigator = () => {
    const insets = useSafeAreaInsets();
    const can = useCan();

    return (
        <Tab.Navigator
            screenOptions={({ route }) => ({
                tabBarIcon: ({ focused, color }) => {
                    let iconName;
                    if (route.name === 'Home')         iconName = focused ? 'grid' : 'grid-outline';
                    else if (route.name === 'Stock')   iconName = focused ? 'layers' : 'layers-outline';
                    else if (route.name === 'Ventes')  iconName = focused ? 'cart' : 'cart-outline';
                    else if (route.name === 'Dettes')  iconName = focused ? 'wallet' : 'wallet-outline';
                    else if (route.name === 'Profile') iconName = focused ? 'person' : 'person-outline';

                    return (
                        <View style={styles.tabItemWrap}>
                            {focused && <View style={styles.tabTopBar} />}
                            <Ionicons name={iconName} size={22} color={focused ? Colors.primary : '#64748B'} />
                        </View>
                    );
                },
                tabBarActiveTintColor: Colors.primary,
                tabBarInactiveTintColor: '#64748B',
                tabBarLabelStyle: {
                    fontFamily: 'PlusJakartaSans_700Bold',
                    fontSize: 11,
                    marginBottom: Platform.OS === 'ios' ? 0 : 4,
                },
                tabBarStyle: {
                    height: Platform.OS === 'ios' ? 88 : 68 + (insets?.bottom || 12),
                    paddingBottom: Platform.OS === 'ios' ? 28 : Math.max(insets?.bottom || 14, 14),
                    paddingTop: 8,
                    backgroundColor: '#FFFFFF',
                    borderTopWidth: 1,
                    borderTopColor: '#E2E8F0',
                    elevation: 10,
                    shadowColor: '#0F172A',
                    shadowOffset: { width: 0, height: -4 },
                    shadowOpacity: 0.08,
                    shadowRadius: 8,
                },
                headerShown: false,
            })}
            sceneContainerStyle={{ backgroundColor: '#FFFFFF' }}
        >
            <Tab.Screen name="Home"    component={HomeScreen}    options={{ title: 'Accueil' }} />
            {can('stock') && (
                <Tab.Screen name="Stock"   component={StockScreen}   options={{ title: 'Stock',  tabBarStyle: { display: 'none' } }} />
            )}
            {can('ventes') && (
                <Tab.Screen name="Ventes"  component={VentesScreen}  options={{ title: 'Ventes', tabBarStyle: { display: 'none' } }} />
            )}
            {can('dettes') && (
                <Tab.Screen name="Dettes"  component={DettesScreen}  options={{ title: 'Dette',  tabBarStyle: { display: 'none' } }} />
            )}
            <Tab.Screen name="Profile" component={ProfileScreen} options={{ title: 'Profil', tabBarStyle: { display: 'none' } }} />
        </Tab.Navigator>
    );
};

const AppNavigator = () => {
    const { user, loading, redirectTo } = useAuth();

    if (loading) {
        return (
            <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: Colors.background }}>
                <ActivityIndicator size="large" color={Colors.primary} />
            </View>
        );
    }

    // Offre en pause (utilisateurs rattachés à une société) : aucune fonctionnalité accessible.
    if (user?.tenant?.offre_en_pause) {
        return (
            <Stack.Navigator screenOptions={{ headerShown: false }}>
                <Stack.Screen name="OffrePause" component={OffrePauseScreen} />
            </Stack.Navigator>
        );
    }

    return (
        <Stack.Navigator
            screenOptions={{
                headerShown: false,
                headerBackTitleVisible: false,
                headerBackTitle: '',
                gestureEnabled: true,
                contentStyle: { backgroundColor: Colors.background },
            }}
        >
            {user ? (
                <>
                    {redirectTo === 'tenants' && (
                        <>
                            <Stack.Screen name="AdminHome"             component={AdminHomeScreen}        options={{ headerShown: false }} />
                            <Stack.Screen name="Tenants"               component={TenantsScreen}          options={{ headerShown: false }} />
                            <Stack.Screen name="admin-prestataires"    component={PrestatairesScreen}     options={{ headerShown: false }} />
                            <Stack.Screen name="admin-commissions"     component={CommissionsScreen}      options={{ headerShown: false }} />
                            <Stack.Screen name="admin-prestataire-show" component={PrestataireShowScreen} options={{ headerShown: false }} />
                            <Stack.Screen name="TenantCreate"          component={TenantsCreateScreen}    options={{ headerShown: false }} />
                            <Stack.Screen name="TenantsCreate"         component={TenantsCreateScreen}    options={{ headerShown: false }} />
                            <Stack.Screen name="TenantEdit"            component={TenantsEditScreen}      options={{ headerShown: false }} />
                            <Stack.Screen name="TenantsEdit"           component={TenantsEditScreen}      options={{ headerShown: false }} />
                            <Stack.Screen name="TenantShow"            component={TenantsShowScreen}      options={{ headerShown: false }} />
                            <Stack.Screen name="TenantsShow"           component={TenantsShowScreen}      options={{ headerShown: false }} />
                            <Stack.Screen name="Profile"               component={ProfileScreen}          options={{ headerShown: false }} />
                        </>
                    )}

                    {redirectTo === 'dashboard' && (
                        <>
                            <Stack.Screen name="MainTabs" component={TabNavigator} options={{ headerShown: false }} />

                            <Stack.Screen name="Ventes"              component={VentesScreen}        options={{ headerShown: false }} />
                            <Stack.Screen name="Stock"               component={StockScreen}         options={{ headerShown: false }} />
                            <Stack.Screen name="Offre"               component={OffreScreen}         options={{ headerShown: false }} />
                            <Stack.Screen name="Depenses"            component={DepensesScreen}      options={{ headerShown: false }} />

                            <Stack.Screen name="DrawerProduits"      component={ProduitsScreen}      options={{ headerShown: false }} />
                            <Stack.Screen name="DrawerArrivages"     component={ArrivagesScreen}     options={{ headerShown: false }} />
                            <Stack.Screen name="DrawerClients"       component={ClientsScreen}       options={{ headerShown: false }} />
                            <Stack.Screen name="DrawerDettes"        component={DettesScreen}        options={{ headerShown: false }} />
                            <Stack.Screen name="DrawerTransferts"    component={TransfertsScreen}    options={{ headerShown: false }} />
                            <Stack.Screen name="DrawerDettesSociete"  component={DettesSocieteScreen} options={{ headerShown: false }} />
                            <Stack.Screen name="DrawerLivraisons"    component={LivraisonsScreen}    options={{ headerShown: false }} />
                            <Stack.Screen name="DrawerEmployes"      component={EmployesScreen}      options={{ headerShown: false }} />
                            <Stack.Screen name="DrawerMagasins"      component={MagasinsScreen}      options={{ headerShown: false }} />
                            <Stack.Screen name="DrawerAnalytique"    component={AnalytiqueScreen}    options={{ headerShown: false }} />
                            <Stack.Screen name="Alertes"            component={AlertesScreen}       options={{ headerShown: false }} />
                            <Stack.Screen name="FAQ"                 component={HelpScreen}          options={{ headerShown: false }} />

                            <Stack.Screen name="VenteCreate"   component={VentesCreateScreen}  options={{ headerShown: false }} />
                            <Stack.Screen name="VentesCreate"  component={VentesCreateScreen}  options={{ headerShown: false }} />
                            <Stack.Screen name="VenteShow"     component={VentesShowScreen}    options={{ headerShown: false }} />
                            <Stack.Screen name="VentesShow"    component={VentesShowScreen}    options={{ headerShown: false }} />

                            <Stack.Screen name="StockMouvements" component={StockMouvementsScreen} options={{ headerShown: false }} />

                            <Stack.Screen name="ProduitCreate"  component={ProduitsCreateScreen} options={{ headerShown: false }} />
                            <Stack.Screen name="ProduitsCreate" component={ProduitsCreateScreen} options={{ headerShown: false }} />
                            <Stack.Screen name="ProduitShow"    component={ProduitsShowScreen}   options={{ headerShown: false }} />
                            <Stack.Screen name="ProduitsShow"   component={ProduitsShowScreen}   options={{ headerShown: false }} />

                            <Stack.Screen name="ClientCreate"   component={ClientsCreateScreen}  options={{ headerShown: false }} />
                            <Stack.Screen name="ClientsCreate"  component={ClientsCreateScreen}  options={{ headerShown: false }} />
                            <Stack.Screen name="ClientShow"     component={ClientsShowScreen}    options={{ headerShown: false }} />
                            <Stack.Screen name="ClientsShow"    component={ClientsShowScreen}    options={{ headerShown: false }} />

                            <Stack.Screen name="ArrivageCreate"  component={ArrivagesCreateScreen} options={{ headerShown: false }} />
                            <Stack.Screen name="ArrivagesCreate" component={ArrivagesCreateScreen} options={{ headerShown: false }} />
                            <Stack.Screen name="ArrivageShow"    component={ArrivagesShowScreen}   options={{ headerShown: false }} />
                            <Stack.Screen name="ArrivagesShow"   component={ArrivagesShowScreen}   options={{ headerShown: false }} />

                            <Stack.Screen name="DetteCreate"    component={DettesCreateScreen}   options={{ headerShown: false }} />
                            <Stack.Screen name="DettesCreate"   component={DettesCreateScreen}   options={{ headerShown: false }} />
                            <Stack.Screen name="DetteShow"      component={DettesShowScreen}     options={{ headerShown: false }} />
                            <Stack.Screen name="DettesShow"     component={DettesShowScreen}     options={{ headerShown: false }} />

                            <Stack.Screen name="DetteSocieteCreate"  component={DettesSocieteCreateScreen} options={{ headerShown: false }} />
                            <Stack.Screen name="DettesSocieteCreate" component={DettesSocieteCreateScreen} options={{ headerShown: false }} />

                            <Stack.Screen name="EmployeCreate"  component={EmployesCreateScreen} options={{ headerShown: false }} />
                            <Stack.Screen name="EmployesCreate" component={EmployesCreateScreen} options={{ headerShown: false }} />

                            <Stack.Screen name="MagasinCreate"  component={MagasinsCreateScreen} options={{ headerShown: false }} />
                            <Stack.Screen name="MagasinsCreate" component={MagasinsCreateScreen} options={{ headerShown: false }} />

                            <Stack.Screen name="TransfertCreate"  component={TransfertsCreateScreen} options={{ headerShown: false }} />
                            <Stack.Screen name="TransfertsCreate" component={TransfertsCreateScreen} options={{ headerShown: false }} />
                            <Stack.Screen name="TransfertShow"    component={TransfertsShowScreen}   options={{ headerShown: false }} />
                            <Stack.Screen name="TransfertsShow"   component={TransfertsShowScreen}   options={{ headerShown: false }} />
                            <Stack.Screen name="TransfertEdit"    component={TransfertsEditScreen}   options={{ headerShown: false }} />

                            <Stack.Screen name="LivraisonShow"    component={LivraisonsShowScreen}   options={{ headerShown: false }} />
                            <Stack.Screen name="LivraisonsShow"   component={LivraisonsShowScreen}   options={{ headerShown: false }} />

                            <Stack.Screen name="DetteSocieteShow"  component={DettesSocieteShowScreen} options={{ headerShown: false }} />
                            <Stack.Screen name="DettesSocieteShow" component={DettesSocieteShowScreen} options={{ headerShown: false }} />

                            <Stack.Screen name="Tresorerie"       component={TresorerieScreen}      options={{ headerShown: false }} />

                            <Stack.Screen name="ArrivageEdit"    component={ArrivagesEditScreen}  options={{ headerShown: false }} />
                            <Stack.Screen name="ArrivagesEdit"   component={ArrivagesEditScreen}  options={{ headerShown: false }} />
                            <Stack.Screen name="ClientEdit"      component={ClientsEditScreen}    options={{ headerShown: false }} />
                            <Stack.Screen name="ClientsEdit"     component={ClientsEditScreen}    options={{ headerShown: false }} />
                            <Stack.Screen name="VenteEdit"       component={VentesEditScreen}     options={{ headerShown: false }} />
                            <Stack.Screen name="VentesEdit"      component={VentesEditScreen}     options={{ headerShown: false }} />
                            <Stack.Screen name="ProduitEdit"     component={ProduitsEditScreen}   options={{ headerShown: false }} />
                            <Stack.Screen name="ProduitsEdit"    component={ProduitsEditScreen}   options={{ headerShown: false }} />
                            <Stack.Screen name="EmployeEdit"     component={EmployesEditScreen}   options={{ headerShown: false }} />
                            <Stack.Screen name="EmployesEdit"    component={EmployesEditScreen}   options={{ headerShown: false }} />

                            <Stack.Screen name="Profile"          component={ProfileScreen}         options={{ headerShown: false }} />
                        </>
                    )}
                </>
            ) : (
                <>
                    <Stack.Screen name="Welcome"        component={WelcomeScreen}        options={{ headerShown: false }} />
                    <Stack.Screen name="Login"          component={LoginScreen}          options={{ headerShown: false }} />
                    <Stack.Screen name="ForgotPassword" component={ForgotPasswordScreen} />
                </>
            )}
        </Stack.Navigator>
    );
};

const styles = StyleSheet.create({
    tabItemWrap: {
        alignItems: 'center',
        justifyContent: 'center',
        position: 'relative',
        width: 56,
        paddingTop: 4,
    },
    tabTopBar: {
        position: 'absolute',
        top: 0,
        left: 0,
        right: 0,
        height: 3,
        backgroundColor: Colors.primary,
    },
});

export default AppNavigator;
