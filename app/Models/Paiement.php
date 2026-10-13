<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Paiement extends Model
{
    use Auditable;
    protected $fillable = [
        'lot_id',
        'client_id',
        'montant',
        'reste',
        'date_paiement'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }
}