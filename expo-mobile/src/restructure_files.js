const fs = require('fs');
const path = require('path');

const srcDir = '/home/bellox/Busness/E-Stock/expo-mobile/src/screens';

const mappings = [
    { from: 'HomeScreen.js', to: 'dashboard.js' },
    { from: 'AnalytiqueScreen.js', to: 'analytique.js' },
    { from: 'ProfileScreen.js', to: 'profile.js' },
    { from: 'VentesScreen.js', to: 'ventes/index.js' },
    { from: 'VenteCreateScreen.js', to: 'ventes/create.js' },
    { from: 'ProduitsScreen.js', to: 'produits/index.js' },
    { from: 'ClientsScreen.js', to: 'clients/index.js' },
    { from: 'StockScreen.js', to: 'stock/index.js' },
    { from: 'ArrivagesScreen.js', to: 'arrivages/index.js' },
    { from: 'DettesScreen.js', to: 'dettes/index.js' },
    { from: 'DettesSocieteScreen.js', to: 'dettes-societe/index.js' },
    { from: 'EmployesScreen.js', to: 'employes/index.js' },
    { from: 'MagasinsScreen.js', to: 'magasins/index.js' },
    { from: 'TransfertsScreen.js', to: 'transferts/index.js' },
    { from: 'LivraisonsScreen.js', to: 'livraisons/index.js' },
    { from: 'LoginScreen.js', to: 'auth/login.js' },
    { from: 'ForgotPasswordScreen.js', to: 'auth/forgot-password.js' },
    { from: 'RegisterScreen.js', to: 'auth/register.js' },
    { from: 'WelcomeScreen.js', to: 'auth/welcome.js' }
];

mappings.forEach(mapping => {
    const fromPath = path.join(srcDir, mapping.from);
    const toPath = path.join(srcDir, mapping.to);

    if (fs.existsSync(fromPath)) {
        let content = fs.readFileSync(fromPath, 'utf8');

        // Update imports if the file is moved to a subdirectory
        const isSubdir = mapping.to.includes('/');
        if (isSubdir) {
            content = content.replace(/from '..\//g, "from '../../");
            content = content.replace(/from "..\//g, 'from "../../');
            content = content.replace(/require\('..\//g, "require('../../");
            content = content.replace(/require\("..\//g, 'require("../../');
        }

        fs.writeFileSync(toPath, content, 'utf8');
        fs.unlinkSync(fromPath);
        console.log(`Moved ${mapping.from} to ${mapping.to}`);
    } else {
        console.log(`File ${mapping.from} not found.`);
    }
});
