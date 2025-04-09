<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['red_id', 'name', 'type', 'status'];
    protected $visible = ['id', 'name', 'type', 'status'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
