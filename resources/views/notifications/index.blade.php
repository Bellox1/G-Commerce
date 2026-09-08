@extends('layouts.app')

@section('title', 'Notifications')
@section('subtitle', 'Alertes et rappels importants')

@section('content')
@php
    if (!isset($produitsEnAlerte) || empty($produitsEnAlerte)) {
        $produitsEnAlerte = [];
        $tenantId = Auth::user()?->tenant_id;
        if ($tenantId) {
            $prods = \App\Models\Produit::where('tenant_id', $tenantId)
                ->where('actif', true)
                ->get();

            foreach ($prods as $p) {
                $stk = (int) $p->stock;
                $seuil = (int) ($p->seuil_alerte ?? 5);
                if ($stk <= 5 || ($seuil > 0 && $stk <= $seuil)) {
                    $produitsEnAlerte[] = [
                        'id'     => $p->id,
                        'nom'    => $p->nom,
                        'stock'  => $stk,
                        'seuil'  => $seuil,
                    ];
                }
            }
        }
    }
@endphp
<div class="container-py">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 style="margin:0; font-size:1.4rem; font-weight:800;">Notifications</h2>
            <p style="margin:4px 0 0; color:var(--text-muted); font-size:0.9rem;">
                Rappels d'expiration d'abonnement, alertes de stock et autres alertes importantes.
            </p>
        </div>
        @if($notifications->where('read_at', null)->count() > 0)
            <form action="{{ route('notifications.markAll') }}" method="POST" data-no-api="true">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-check2-all"></i> Tout marquer comme lu
                </button>
            </form>
        @endif
    </div>

    @if(count($produitsEnAlerte) > 0)
        <div class="card mb-4" style="border:1px solid #fecaca; background:#ffffff; border-radius:14px; overflow:hidden;">
            <div style="background:#fee2e2; border-bottom:1px solid #fecaca; padding:14px 18px; display:flex; align-items:center; justify-content:space-between;">
                <h3 style="margin:0; font-size:1.05rem; color:#991b1b; display:flex; align-items:center; gap:8px; font-weight:800;">
                    <i class="bi bi-exclamation-triangle-fill" style="color:#dc2626; font-size:1.2rem;"></i>
                    Alertes de Stock &amp; Ruptures ({{ count($produitsEnAlerte) }})
                </h3>
            </div>
            <div class="table-wrap" style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; margin:0;">
                    <thead>
                        <tr style="background:#fef2f2; border-bottom:1px solid #fecaca; color:#991b1b; font-size:0.85rem; text-align:left;">
                            <th style="padding:10px 16px;">Produit</th>
                            <th style="padding:10px 16px; text-align:center;">Seuil Configuré</th>
                            <th style="padding:10px 16px; text-align:center;">Stock Actuel</th>
                            <th style="padding:10px 16px; text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($produitsEnAlerte as $pa)
                            <tr style="border-bottom:1px solid #fee2e2;">
                                <td style="padding:12px 16px; font-weight:700; color:#7f1d1d;">{{ $pa['nom'] }}</td>
                                <td style="padding:12px 16px; text-align:center; color:#991b1b;">{{ $pa['seuil'] }} carton(s)</td>
                                <td style="padding:12px 16px; text-align:center;">
                                    <span style="background:{{ $pa['stock'] <= 0 ? '#dc2626' : '#d97706' }}; color:#fff; font-weight:800; font-size:0.8rem; padding:4px 12px; border-radius:20px; display:inline-block;">
                                        {{ $pa['stock'] }} carton(s) {{ $pa['stock'] <= 0 ? '(Rupture)' : '(Faible)' }}
                                    </span>
                                </td>
                                <td style="padding:12px 16px; text-align:right;">
                                    <a href="{{ route('produits.show', $pa['id']) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($notifications->isEmpty() && count($produitsEnAlerte) == 0)
        <div class="empty-state">
            <i class="bi bi-bell" style="font-size:2.4rem; color:var(--text-muted);"></i>
            <p>Aucune notification ni alerte pour le moment.</p>
        </div>
    @else
        <div class="notif-list">
            @foreach($notifications as $notif)
                @php
                    $data = $notif->data;
                    $unread = is_null($notif->read_at);
                    $categorie = $data['categorie'] ?? '';
                    $icon = match(true) {
                        $categorie === 'stock' => 'bi-box-seam',
                        ($data['alert_type'] ?? '') === 'j7' => 'bi-calendar-week',
                        ($data['alert_type'] ?? '') === 'j3' => 'bi-calendar-event',
                        ($data['alert_type'] ?? '') === 'j1' => 'bi-exclamation-circle',
                        ($data['alert_type'] ?? '') === 'expire' => 'bi-x-circle-fill',
                        default => 'bi-info-circle',
                    };
                    $color = match(true) {
                        $categorie === 'stock' => 'var(--danger)',
                        ($data['alert_type'] ?? '') === 'expire' => 'var(--danger)',
                        ($data['alert_type'] ?? '') === 'j1' => '#d97706',
                        default => 'var(--primary)',
                    };
                @endphp
                <div class="notif-card {{ $unread ? 'unread' : '' }}">
                    <div class="notif-icon" style="color:{{ $color }};">
                        <i class="bi {{ $icon }}"></i>
                    </div>
                    <div class="notif-body">
                        <div class="notif-title">{{ $data['titre'] ?? 'Notification' }}</div>
                        <div class="notif-text">{!! nl2br(e($data['message'] ?? '')) !!}</div>
                        <div class="notif-date">{{ $notif->created_at->format('d/m/Y à H:i') }}</div>
                    </div>
                    @if($unread)
                        <span class="notif-dot"></span>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    @endif
</div>

<style>
    .container-py { max-width: 820px; margin: 0 auto; padding: 24px 16px; }
    .notif-list { display: flex; flex-direction: column; gap: 12px; }
    .notif-card {
        display: flex; gap: 14px; align-items: flex-start;
        background: #fff; border: 1px solid var(--border); border-radius: 14px;
        padding: 16px; position: relative;
    }
    .notif-card.unread { border-color: var(--primary); background: #f6fbf9; }
    .notif-icon { font-size: 1.5rem; line-height: 1; margin-top: 2px; }
    .notif-body { flex: 1; }
    .notif-title { font-weight: 700; font-size: 0.98rem; color: var(--text); margin-bottom: 4px; }
    .notif-text { font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; }
    .notif-date { font-size: 0.78rem; color: #94a3b8; margin-top: 6px; }
    .notif-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--primary); position: absolute; top: 16px; right: 16px; }
    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state p { margin-top: 12px; }
</style>
@endsection
