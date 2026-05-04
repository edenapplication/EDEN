<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LotHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'lot_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
    ];

    // 🔗 Historique appartient à un lot
    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }
}
