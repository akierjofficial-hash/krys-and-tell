<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'base_price',
        'allow_custom_price',
        'description',
        'color',
    ];

    protected $casts = [
        'allow_custom_price' => 'boolean',
        'base_price' => 'decimal:2',
    ];

    public function visitProcedures()
    {
        return $this->hasMany(VisitProcedure::class);
    }
}
