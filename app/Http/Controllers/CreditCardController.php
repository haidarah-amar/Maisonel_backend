<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CreditCard;

class CreditCardController extends Controller
{

    //
    public function index(Request $request){

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $creditCards = $user->creditCards;
        return response()->json(['credit_cards' => $creditCards], 200);
    }

    public function store(Request $request){
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validate([
            'card_holder_name' => 'required|string|max:255',
            'card_number' => 'required|string|size:16|unique:credit_cards,card_number',
            'expiration_date' => 'required|date_format:m/y|after:today',
            'cvv' => 'required|string|size:3',
        ]);
        $number = $validatedData['card_number'];
        $sum = 0;
        $alt = false;
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $digit = ord($number[$i]) - 48;
            if ($alt) {
                $digit = ($digit << 1);
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
            $alt = !$alt;
        }

        if (($sum % 10) !== 0) {
            return response()->json(['message' => 'Invalid credit card number'], 400);
        }

        $creditCard = CreditCard::create([
            'user_id' => $user->id,
            'card_holder_name' => $validatedData['card_holder_name'],
            'card_number' => $validatedData['card_number'],
            'expiration_date' => \DateTime::createFromFormat('m/y', $validatedData['expiration_date'])->format('Y-m-d'),
            'cvv' => $validatedData['cvv'],
        ]);

        return response()->json(['message' => 'Credit card added successfully', 'credit_card' => $creditCard], 201);
    }
    
    public function delete(){
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $creditCard = CreditCard::where('user_id', $user->id)->first();
        if (!$creditCard) {
            return response()->json(['message' => 'Credit card not found'], 404);
        }
        $creditCard->delete();
        return response()->json(['message' => 'Credit card deleted successfully'], 200);
    }

    public function destroy($id){
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $creditCard = CreditCard::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$creditCard) {
            return response()->json(['message' => 'Credit card not found'], 404);
        }

        $creditCard->delete();

        return response()->json(['message' => 'Credit card deleted successfully'], 200);
    }
}
