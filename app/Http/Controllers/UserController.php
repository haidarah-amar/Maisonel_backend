<?php

namespace App\Http\Controllers;

use App\Models\User;
use Date;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Telegram\Bot\Laravel\Facades\Telegram;
class UserController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:50000',
            'id_document' => 'nullable|image|mimes:jpeg,png,jpg|max:50000',
            'phone' => 'required|string|max:15|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Handle photo upload safely
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            if ($file->isValid()) {
                // store under 'photos' directory on the 'public' disk
                $photo_path = $file->store('photos', 'public');
                $validatedData['photo'] = $photo_path;
            } else {
                return response()->json(['message' => 'Invalid photo upload'], 422);
            }
        }

        // Handle id_document upload safely
        if ($request->hasFile('id_document')) {
            $file = $request->file('id_document');
            if ($file->isValid()) {
                $id_path = $file->store('id_documents', 'public');
                $validatedData['id_document'] = $id_path;
            } else {
                return response()->json(['message' => 'Invalid id_document upload'], 422);
            }
        }
        $formattedPhone = User::validateSyrianNumber($request->phone);
        if (!$formattedPhone) {
            return response()->json(['error' => 'Invalid Syrian phone number format.'], 422);
        }

        $verificationCode = Str::random(10); // Code to link Telegram

        // Generate Deep Link
        $botUsername = env('TELEGRAM_BOT_NAME', 'YourBot');
        $link = "https://t.me/$botUsername?start=$verificationCode";


        // Create user (ensure password is hashed)
        $user = User::updateOrCreate([
            'phone' => $formattedPhone,
            'password' => Hash::make($validatedData['password']),
        ], [
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'verification_code' => $verificationCode,
            'birth_date' => $validatedData['birth_date'],
            'photo' => $photo_path,
            'id_document' => $id_path
        ]);
        // $this->sendOtp(request: $request);
        return response()->json([
            'message' => 'Please click the link to verify your Telegram and complete registration.',
            'verification_link' => $link
        ]);
    }
    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'required|string|max:15',
            'password' => 'required|string',
        ]);

        $formattedPhone = User::validateSyrianNumber($request->phone);
        $user = User::where('phone', $formattedPhone)->first();
        if (!Auth::attempt($request->only('phone', 'password'))) {
            return response()->json(['message' => 'Invalid phone or password'], 401);
        }
        if (!$user->phone_verified_at) {
            // Generate OTP
            $otp = rand(100000, 999999);
            $user->update([
                'otp_code' => Hash::make($otp),
                'otp_expires_at' => now()->addMinutes(5)
            ]);

            // Send to Telegram
            try {
                Telegram::sendMessage([
                    'chat_id' => $user->telegram_chat_id,
                    'text' => "🔐 Your Login OTP is: *$otp*\nIt expires in 5 minutes.",
                    'parse_mode' => 'Markdown'
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to send Telegram message.'], 500);
            }

            return response()->json(['message' => 'OTP sent to your Telegram account.']);
        }
        if ($user->is_active == false) {
            return response()->json(['message' => 'Your account is not active. Please wait until an admin activates it.'], 403);
        }
        if (!$user || !$user->telegram_chat_id) {
            return response()->json(['error' => 'User not found or Telegram not linked.'], 404);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'Login successful',
            'User' => $user,
            'Token' => $token
        ], 200);

    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp' => 'required|integer'
        ]);

        $formattedPhone = User::validateSyrianNumber($request->phone);
        $user = User::where('phone', $formattedPhone)->first();

        if (!$user || !$user->otp_code || now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['error' => 'Invalid or expired OTP.'], 401);
        }

        if (Hash::check($request->otp, $user->otp_code)) {
            // Clear OTP
            $user->update([
                'otp_code' => null,
                'otp_expires_at' => null,
                'phone_verified_at' => now()
            ]);
            

            return response()->json([
                'message' => 'Phone verified successfully. waiting for admin activation.',
            ]);
        }

        return response()->json(['error' => 'Invalid OTP'], 401);
    }

    public function logout(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);

    }


    public function sendOtp(Request $request)
    {
        $request->validate(['phone' => 'required']);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !$user->telegram_chat_id) {
            return response()->json([
                'message' => 'يرجى تفعيل البوت أولاً من خلال الرابط: t.me/YourBotName'
            ], 400);
        }

        // توليد الرمز
        $otp = rand(100000, 999999);
        $user->otp_code = $otp;
        $user->save();

        // إرسال الرمز عبر تيليجرام
        $message = "رمز التحقق الخاص بك هو: <b>$otp</b>\nلا تشارك هذا الرمز مع أحد.";

        Http::post("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage", [
            'chat_id' => $user->telegram_chat_id,
            'text' => $message,
            'parse_mode' => 'HTML'
        ]);

        return response()->json(['message' => 'تم إرسال الرمز إلى تيليجرام']);
    }
}
