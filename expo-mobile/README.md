# PILOTIX - Application Mobile (Expo) 📱

L'application mobile **PILOTIX** permet de piloter en temps réel l'inventaire, les ventes, les achats et la trésorerie d'une ou plusieurs entreprises, depuis un smartphone.

## 🚀 Fonctionnalités

- **Tableau de bord** : vue synthétique de l'activité (CA, stock, alertes).
- **Gestion de stock** : produits, mouvements, arrivages et alertes de rupture.
- **Ventes & Clients** : création de ventes, fiche client, historique et relances.
- **Dettes** : suivi des créances clients et des dettes de la société.
- **Achats & Logistique** : arrivages, livraisons, dépenses et transferts entre magasins.
- **Trésorerie & Analytique** : suivi de caisse, graphiques et indicateurs de performance.
- **Multi-établissements** : gestion multi-magasins et multi-tenants (plusieurs sociétés).
- **Employés & Administration** : rôles, prestataires, commissions.
- **Multilingue** : interface disponible en plusieurs langues.
- **Hors-ligne** : saisie en mode déconnecté avec synchronisation automatique.
- **Design moderne** : interface fluide, sobre et intuitive (identité vert émeraude PILOTIX).

## 🛠 Installation & Développement

1. Accéder au dossier :
   ```bash
   cd expo-mobile
   ```
2. Installer les dépendances :
   ```bash
   npm install
   ```
3. Lancer l'application (émulateur, device ou web) :
   ```bash
   npx expo start
   ```

## ⚙️ Configuration

- **API** : l'URL de l'API PILOTIX est définie dans `src/api/client.js` (`BASE_URL`, par défaut `https://pilotix.alwaysdata.net`). Pour un serveur local, remplacez par l'adresse IP de votre backend (ex. `http://192.168.1.13:8000`).
- **Identité visuelle** : les couleurs de l'application sont centralisées dans `src/theme/Colors.js` (vert émeraude `#105e49`, ambre `#F59E0B`).
- **Nom de l'app / build** : configuré dans `app.json` (nom `PILOTIX`, package `com.PILOTIX.app`).

## 📦 Build

Générer une version avec EAS Build :

```bash
# Aperçu interne (APK de test)
eas build --platform android --profile preview

# Version de production
eas build --platform android --profile production
```

## 🔐 Permissions

L'application demande l'accès à la caméra et à la galerie (photos de produits) ainsi qu'au stockage, configurables dans `app.json`.

---

Développé avec **React Native & Expo**.
