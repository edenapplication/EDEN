<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RH\Notification;
use App\Models\RH\Alerte;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\AlerteNotificationMail;

class EnvoyerNotifications extends Command
{
    protected $signature = 'rh:envoyer-notifications {--canal=interface}';
    protected $description = 'Envoie les notifications en attente';

    public function handle()
    {
        $canal = $this->option('canal');

        $this->info('📨 Envoi des notifications en cours...');

        $notifications = Notification::where('envoyee', false)
            ->where('canal', $canal)
            ->get();

        if ($notifications->isEmpty()) {
            $this->info('✅ Aucune notification en attente.');
            return 0;
        }

        $envoyees = 0;
        $erreurs = 0;

        foreach ($notifications as $notification) {
            try {
                if ($canal === 'email') {
                    $this->envoyerEmail($notification);
                } elseif ($canal === 'interface') {
                    $this->marquerInterface($notification);
                }

                $notification->marquerEnvoyee();
                $envoyees++;
            } catch (\Exception $e) {
                $notification->enregistrerErreur($e->getMessage());
                $erreurs++;
                $this->error("❌ Erreur: " . $e->getMessage());
            }
        }

        $this->info("✅ {$envoyees} notification(s) envoyée(s), {$erreurs} erreur(s).");

        return 0;
    }

    private function envoyerEmail(Notification $notification)
    {
        $user = User::find($notification->user_id);
        if (!$user || !$user->email) {
            throw new \Exception("Utilisateur sans email: {$notification->user_id}");
        }

        Mail::to($user->email)->send(new AlerteNotificationMail($notification));
    }

    private function marquerInterface(Notification $notification)
    {
        // Les notifications d'interface sont marquées comme envoyées
        // car elles sont déjà dans la base de données
        return true;
    }
}