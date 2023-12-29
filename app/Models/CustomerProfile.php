<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerProfile extends Model
{
    use HasFactory;
    protected $fillable =[
      'user_id',
      'last_four_digit',
      'card_type',
      'customer_profile_id',
      'payment_profile_id'
    ];
}
