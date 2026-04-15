<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaportLunarCallCenterInput extends Model
{
    protected $table = 'raport_lunar_call_center_inputs';

    protected $fillable = [
        'ym',
        'operator_nume',
        'chaturi',
        'apeluri',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'chaturi' => 'integer',
        'apeluri' => 'integer',
    ];
}
