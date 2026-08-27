const MOIS = [
    'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
    'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre',
];

const pad = (n) => String(n).padStart(2, '0');

export const parseToLocalDateObj = (dateStr) => {
    if (!dateStr) return new Date();
    if (dateStr instanceof Date) return isNaN(dateStr.getTime()) ? new Date() : dateStr;
    const clean = String(dateStr).trim().split('T')[0];
    const parts = clean.split('-');
    if (parts.length === 3) {
        const y = parseInt(parts[0], 10);
        const m = parseInt(parts[1], 10) - 1;
        const d = parseInt(parts[2], 10);
        const obj = new Date(y, m, d);
        if (!isNaN(obj.getTime())) return obj;
    }
    const fallback = new Date(dateStr);
    return isNaN(fallback.getTime()) ? new Date() : fallback;
};

export const formatDateFr = (iso) => {
    if (!iso) return '—';
    const d = parseToLocalDateObj(iso);
    if (isNaN(d.getTime())) return '—';
    return `${pad(d.getDate())} ${MOIS[d.getMonth()]} ${d.getFullYear()}`;
};

export const formatDateTimeFr = (iso) => {
    if (!iso) return '—';
    const d = parseToLocalDateObj(iso);
    if (isNaN(d.getTime())) return '—';
    return `${pad(d.getDate())} ${MOIS[d.getMonth()]} ${d.getFullYear()} à ${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

// Date du jour en WAT (UTC+1), indépendamment du fuseau du téléphone.
export const todayWAT = () => {
    const d = new Date(Date.now() + 3600000);
    return `${d.getUTCFullYear()}-${pad(d.getUTCMonth() + 1)}-${pad(d.getUTCDate())}`;
};
