<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordEntryBatch extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = ['payload' => 'array', 'warnings' => 'array', 'summary' => 'array'];
}
