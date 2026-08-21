<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Notifications\AbonnementExpiration;
use Illuminate\Console\Command;

class VerifierAbonnements extends Command
{
    protected $signature = 'abonnements:verifier';
    protected $description = 'Envoie les notifications d’expiration d’abonnement (J-7, J-3, J-1, expiration) aux admins des tenants.';

    public function handle(): int
    {
        $tenants = Tenant::query()
            ->whereNotNull('offre_expires_at')
            ->where('offre_code', '!=', 'locale')
            ->get();

        $total = 0;

        foreach ($tenants as $tenant) {
            $days = now()->diffInDays($tenant->offre_expires_at, false); // négatif si passé
            $sent = $tenant->alertes_envoyees ?? [];

            // Abonnement renouvelé (encore valable > 7j) : on réinitialise le suivi
            if ($days > 7) {
                if (!empty($sent)) {
                    $tenant->alertes_envoyees = [];
                    $tenant->save();
                }
                continue;
            }

            $toSend = [];

            if ($days <= 0 && !isset($sent['expire'])) {
                $toSend['expire'] = $days;
            } elseif ($days <= 1 && $days > 0 && !isset($sent['j1'])) {
                $toSend['j1'] = $days;
            } elseif ($days <= 3 && $days > 1 && !isset($sent['j3'])) {
                $toSend['j3'] = $days;
            } elseif ($days <= 7 && $days > 3 && !isset($sent['j7'])) {
                $toSend['j7'] = $days;
            }

            if (empty($toSend)) {
                continue;
            }

            $admins = $tenant->users()->whereIn('role', ['admin', 'super_admin'])->get();

            foreach ($toSend as $type => $d) {
                foreach ($admins as $admin) {
                    $admin->notify(new AbonnementExpiration($tenant, $type));
                    $total++;
                }
                // En cas d'expiration on conserve uniquement ce drapeau (réinitialisable au renouvellement)
                if ($type === 'expire') {
                    $sent = ['expire' => now()->toDateString()];
                } else {
                    $sent[$type] = now()->toDateString();
                }
            }

            $tenant->alertes_envoyees = $sent;
            $tenant->save();
        }

        $this->info("Notifications d'abonnement envoyées : {$total}");
        return self::SUCCESS;
    }
}
