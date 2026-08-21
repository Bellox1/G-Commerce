@extends('layouts.app')

@section('title', 'Notifications')
@section('subtitle', 'Alertes et rappels importants')

@section('content')
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

    @if($notifications->isEmpty())
        <div class="empty-state">
            <i class="bi bi-bell" style="font-size:2.4rem; color:var(--text-muted);"></i>
            <p>Aucune notification pour le moment.</p>
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
