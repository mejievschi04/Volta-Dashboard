<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnecKpiOperator extends Model
{
    protected $table = 'onec_kpi_operatori';

    protected $fillable = [
        'onec_kpi_sync_id',
        'operator_id_1c',
        'operator_nume',
        'vanzari_cu_tva',
        'vanzari_fara_tva',
        'profit',
        'nr_comenzi',
    ];

    protected $casts = [
        'vanzari_cu_tva' => 'decimal:2',
        'vanzari_fara_tva' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    public function sync(): BelongsTo
    {
        return $this->belongsTo(OnecKpiSync::class, 'onec_kpi_sync_id');
    }
}
