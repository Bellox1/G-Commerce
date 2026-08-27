@extends('layouts.app')
@section('title', 'Mon Profil')
@section('page-title', 'Mon Profil')

@section('content')
<div class="page-grid page-grid-2">
    {{-- Infos personnelles --}}
    <div class="card">
        <div class="card-header">
            <h3><i class="bi bi-person"></i> Informations personnelles</h3>
            <div style="display:flex; gap:6px; align-items:center;">
                <span class="badge {{ $user->role === 'super_admin' ? 'badge-danger' : ($user->role === 'admin' ? 'badge-warning' : ($user->role === 'vendeur' ? 'badge-success' : 'badge-gray')) }}" style="font-size:.8rem; padding:4px 12px;">
                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                </span>
                @if($user->roles_secondaires)
                    @foreach($user->roles_secondaires as $sr)
                        <span class="badge badge-gray" style="font-size:.8rem;">{{ ucfirst($sr) }}</span>
                    @endforeach
                @endif
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.update') }}" id="profileInfoForm">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    @error('name') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="profileEmailInput" class="form-control" value="{{ old('email', $user->email) }}" required data-original="{{ $user->email }}">
                    @error('email') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $user->telephone) }}">
                    @error('telephone') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>

                <input type="hidden" name="current_password_email" id="hiddenPasswordInput">

                <button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> Enregistrer</button>
            </form>

            <!-- Modal Confirmation Sécurité Changement Email -->
            <div id="emailSecurityModal" class="modal-backdrop" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center;">
                <div style="background:#FFF; width:90%; max-width:440px; border-radius:16px; padding:24px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                        <h4 style="margin:0; font-size:1.1rem; font-weight:700; color:var(--text-dark);">
                            <i class="bi bi-shield-lock-fill" style="color:var(--primary);"></i> Confirmation de sécurité
                        </h4>
                        <button type="button" onclick="closeSecurityModal()" style="background:none; border:none; font-size:1.2rem; cursor:pointer; color:#94a3b8;">&times;</button>
                    </div>
                    <p style="font-size:0.875rem; color:#475569; margin-bottom:16px;">
                        Vous souhaitez modifier votre adresse e-mail vers <strong id="newEmailDisplay" style="color:var(--primary);"></strong>. Veuillez saisir votre mot de passe actuel pour valider.
                    </p>
                    <div class="form-group" style="margin-bottom:16px;">
                        <label class="form-label" style="font-size:0.8rem; font-weight:600;">Mot de passe actuel <span style="color:var(--danger);">*</span></label>
                        <div style="position:relative;">
                            <input type="password" id="modalPasswordInput" class="form-control" placeholder="Entrez votre mot de passe actuel" style="padding-right:40px;">
                            <button type="button" onclick="togglePassVisibility('modalPasswordInput', this)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px;">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        <small style="color:#94a3b8; font-size:0.75rem; display:block; margin-top:4px;">🔒 Limité à 3 tentatives maximum.</small>
                    </div>
                    <div style="display:flex; gap:10px; justify-content:flex-end;">
                        <button type="button" onclick="closeSecurityModal()" class="btn btn-secondary" style="border-radius:8px;">Annuler</button>
                        <button type="button" onclick="submitEmailChangeWithPassword()" class="btn btn-primary" style="border-radius:8px;"><i class="bi bi-check-lg"></i> Confirmer</button>
                    </div>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var form = document.getElementById('profileInfoForm');
                var emailInput = document.getElementById('profileEmailInput');
                var originalEmail = (emailInput ? emailInput.dataset.original : '').trim().toLowerCase();

                form.addEventListener('submit', function(e) {
                    var currentEmail = (emailInput.value || '').trim().toLowerCase();
                    if (currentEmail !== originalEmail && !document.getElementById('hiddenPasswordInput').value) {
                        e.preventDefault();
                        document.getElementById('newEmailDisplay').textContent = emailInput.value;
                        document.getElementById('emailSecurityModal').style.display = 'flex';
                        document.getElementById('modalPasswordInput').focus();
                    }
                });
            });

            function closeSecurityModal() {
                document.getElementById('emailSecurityModal').style.display = 'none';
                document.getElementById('modalPasswordInput').value = '';
            }

            function submitEmailChangeWithPassword() {
                var pwd = document.getElementById('modalPasswordInput').value;
                if (!pwd) {
                    alert('Veuillez entrer votre mot de passe actuel.');
                    return;
                }
                document.getElementById('hiddenPasswordInput').value = pwd;
                document.getElementById('emailSecurityModal').style.display = 'none';
                document.getElementById('profileInfoForm').submit();
            }
            </script>
        </div>
    </div>

    {{-- Mot de passe --}}
    <div class="card">
        <div class="card-header">
            <h3><i class="bi bi-lock"></i> Modifier le mot de passe</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Mot de passe actuel</label>
                    <div style="position:relative;">
                        <input type="password" name="current_password" id="profCurrPass" class="form-control" style="padding-right:40px;" required>
                        <button type="button" onclick="togglePassVisibility('profCurrPass', this)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px;">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    @error('current_password') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Nouveau mot de passe</label>
                    <div style="position:relative;">
                        <input type="password" name="password" id="profNewPass" class="form-control" style="padding-right:40px;" required>
                        <button type="button" onclick="togglePassVisibility('profNewPass', this)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px;">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    @error('password') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <div style="position:relative;">
                        <input type="password" name="password_confirmation" id="profConfPass" class="form-control" style="padding-right:40px;" required>
                        <button type="button" onclick="togglePassVisibility('profConfPass', this)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px;">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> Modifier</button>
            </form>
        </div>
    </div>
</div>

{{-- Zone d'action sensible / suppression discrète --}}
<div style="margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 20px; text-align: center;">
    <button type="button" onclick="document.getElementById('danger-zone-box').style.display = document.getElementById('danger-zone-box').style.display === 'none' ? 'block' : 'none';" style="background:transparent; border:none; color:#94a3b8; font-size:.8rem; cursor:pointer;">
        <i class="bi bi-shield-exclamation"></i> Options de compte avancées...
    </button>

    <div id="danger-zone-box" style="display:none; max-width: 480px; margin: 16px auto 0 auto; background: #fff5f5; border: 1px dashed #feb2b2; border-radius: 12px; padding: 20px; text-align: left;">
        <h4 style="font-size: .9rem; color: #c53030; font-weight: 700; margin-bottom: 8px;">
            <i class="bi bi-exclamation-triangle"></i> Supprimer définitivement mon compte
        </h4>
        <p style="font-size: .8rem; color: #742a2a; margin-bottom: 14px;">
            Cette action supprime l'accès au compte de manière irréversible.
        </p>
        <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Attention : Êtes-vous ABSOLUMENT certain de vouloir supprimer définitivement votre compte ?');">
            @csrf
            @method('DELETE')

            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label" style="font-size: .78rem; color: #742a2a;">Entrez votre mot de passe pour confirmer</label>
                <div style="position:relative;">
                    <input type="password" name="delete_password" id="profDelPass" class="form-control form-control-sm" style="padding-right:40px;" required placeholder="Mot de passe actuel">
                    <button type="button" onclick="togglePassVisibility('profDelPass', this)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px;">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
                @error('delete_password') <small style="color:var(--danger);">{{ $message }}</small> @enderror
            </div>

            <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 8px; font-weight: 700;">
                <i class="bi bi-trash"></i> Confirmer la suppression
            </button>
        </form>
    </div>
</div>

<script>
function togglePassVisibility(inputId, btn) {
    var input = document.getElementById(inputId);
    var icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye-slash';
    }
}
</script>
@endsection
