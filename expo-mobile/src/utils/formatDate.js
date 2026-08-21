const MOIS = [
    'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
    'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre',
];

const pad = (n) => String(n).padStart(2, '0');

export const formatDateFr = (iso) => {
    if (!iso) return '—';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return '—';
    return `${pad(d.getDate())} ${MOIS[d.getMonth()]} ${d.getFullYear()}`;
};

export const formatDateTimeFr = (iso) => {
    if (!iso) return '—';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return '—';
    return `${pad(d.getDate())} ${MOIS[d.getMonth()]} ${d.getFullYear()} à ${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

// Date du jour en WAT (UTC+1), indépendamment du fuseau du téléphone.
// On calcule à partir de l'UTC + 1h pour garantir l'heure du Bénin même si
// l'appareil est réglé sur un autre fuseau (ex: voyage à l'étranger).
export const todayWAT = () => {
    const d = new Date(Date.now() + 3600000);
    return `${d.getUTCFullYear()}-${pad(d.getUTCMonth() + 1)}-${pad(d.getUTCDate())}`;
};
