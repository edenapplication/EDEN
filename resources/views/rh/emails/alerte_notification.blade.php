<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #1e293b; }
        .header { background: linear-gradient(135deg, #7c3aed, #1d4ed8); color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .alert-box { background: #f8fafc; border-left: 4px solid #7c3aed; padding: 15px; margin: 15px 0; }
        .footer { background: #f1f5f9; padding: 15px; text-align: center; font-size: 12px; color: #64748b; }
        .btn { display: inline-block; background: #7c3aed; color: white; padding: 8px 20px; border-radius: 6px; text-decoration: none; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏢 EDEN GROUP - RH</h1>
        <p>Notification automatique</p>
    </div>

    <div class="content">
        <h2>{{ $notification->sujet }}</h2>

        <div class="alert-box">
            <p style="margin:0;">{{ $notification->message }}</p>
        </div>

        @if($alerte && $alerte->lien)
            <p>
                <a href="{{ $alerte->lien }}" class="btn">👁️ Voir le détail</a>
            </p>
        @endif

        @if($alerte && $alerte->employe)
            <p>
                <strong>Employé concerné :</strong>
                {{ $alerte->employe->nom }} {{ $alerte->employe->prenom }}
                ({{ $alerte->employe->matricule }})
            </p>
        @endif

        <p style="font-size:12px;color:#94a3b8;margin-top:20px;">
            Priorité : 
            <span style="font-weight:700;color:{{ $alerte->priorite_color ?? '#94a3b8' }};">
                {{ $alerte->priorite_label ?? 'Normale' }}
            </span>
        </p>
    </div>

    <div class="footer">
        <p>Ce message a été généré automatiquement par le système RH.</p>
        <p>© {{ date('Y') }} Eden Group - Tous droits réservés.</p>
    </div>
</body>
</html>