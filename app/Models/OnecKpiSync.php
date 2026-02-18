<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnecKpiSync extends Model
{
    protected $table = 'onec_kpi_syncs';

    protected $fillable = [
        'period_start',
        'period_end',
        'company',
        'currency',
        'vanzari_cu_tva',
        'vanzari_fara_tva',
        'profit',
        'nr_comenzi',
        'generated_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'generated_at' => 'datetime',
        'vanzari_cu_tva' => 'decimal:2',
        'vanzari_fara_tva' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    public function operatori(): HasMany
    {
        return $this->hasMany(OnecKpiOperator::class, 'onec_kpi_sync_id');
    }
}
