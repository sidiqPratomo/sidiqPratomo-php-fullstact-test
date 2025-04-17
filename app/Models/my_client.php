<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class my_client extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'my_client';
    
    protected $fillable = [
        'name', 'slug', 'is_project', 'self_capture', 
        'client_prefix', 'client_logo', 'address', 
        'phone_number', 'city'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
