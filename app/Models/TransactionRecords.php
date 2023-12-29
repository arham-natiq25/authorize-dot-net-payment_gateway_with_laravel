<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionRecords extends Model
{
    use HasFactory;

    protected $fillable =[
        'user_id',
        'payment',
        'trx_id'
    ];
}
