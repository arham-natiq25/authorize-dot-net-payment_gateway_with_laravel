<?php

namespace App\Http\Controllers;

use App\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GetCardsOfCustomerController extends Controller
{
    function getCards() {
        $cards = CustomerProfile::where('user_id',Auth::user()->id)->get();

        return response()->json($cards);

    }
}
