<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramAuthController extends Controller
{
    public function handleWebhook()
    {
        $update = Telegram::commandsHandler(true);

        // Check if it's a message
        if ($update->getMessage()) {
            $message = $update->getMessage();
            $chatId = $message->getChat()->getId();
            $text = $message->getText();

            // Handle /start command with payload (e.g., /start vCode123)
            if (str_starts_with($text, '/start ')) {
                $code = explode(' ', $text)[1];
                $this->linkUser($chatId, $code);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function linkUser($chatId, $code)
    {
        $user = User::where('verification_code', $code)->first();

        if ($user) {
            $user->update([
                'telegram_chat_id' => $chatId,
                'verification_code' => null, // Clear code after usage
                'email_verified_at' => now(), // Mark as "verified"
            ]);

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Phone number linked successfully! You can now receive OTPs here."
            ]);
        } else {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "⚠️ Invalid or expired verification link."
            ]);
        }
    }
}
