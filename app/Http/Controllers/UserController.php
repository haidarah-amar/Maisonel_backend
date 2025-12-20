<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\uplodImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use uplodImage;

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date|max:255',
            'type' => 'required|string|in:owner,tenant',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:50000',
            'id_document' => 'required|image|mimes:jpeg,png,jpg|max:50000',
            'phone' => 'required|string|max:15|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($request->hasFile('photo')) {
            $path = $this->uploadImage($request->file('photo'), 'users');
            $validatedData['photo'] = $path;
        }
        if ($request->hasFile('id_document')) {
            $path = $this->uploadImage($request->file('id_document'), 'users/documents');
            $validatedData['id_document'] = $path;
        }

        $user = User::create([
            'phone' => $validatedData['phone'],
            'password' => Hash::make($validatedData['password']),
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'type' => $validatedData['type'],
            'birth_date' => $validatedData['birth_date'],
            'photo' => $validatedData['photo'],
            'id_document' => $validatedData['id_document']
        ]);

        $token = JWTAuth::fromUser($user);
        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        Auth::login($user);
        return response()->json(['message' => 'User registered successfully', 'user' => $user, 'token' => $this->createNewToken($token)], 201);
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => auth()->user(),
        ]);
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'required|string|max:15',
            'password' => 'required|string',
        ]);
        $user = User::where('phone', $validatedData['phone'])->first();

        if ($user && Hash::check($validatedData['password'], $user->password)) {
            $token = JWTAuth::fromUser($user);
            Auth::login($user);
            return response()->json([
                'message' => 'Login successful.',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'data' => $user,
            ]);
        }

    }

    public function logout(Request $request)
    {
        //الحصول على التوكين ر Authorization
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 400);
        }

        try {
            //   محاولة التحقق من التوكين انو هو نشط
            JWTAuth::setToken($token)->invalidate();

            // إرجاع رد بعد إلغاء التوكين
            return response()->json(['message' => 'User successfully signed out'], 200);
        } catch (\Exception $e) {
            // في حال حدوث خطأ في التحقق من التوكين
            return response()->json(['error' => 'Failed to log out', 'message' => $e->getMessage()], 500);
        }
    }

    public function getProfile()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'المستخدم غير مصادق عليه، يرجى إرسال التوكن بشكل صحيح.'
            ], 401);
        }

        return response()->json([
            'message' => 'تم جلب معلومات المستخدم بنجاح.',
            'user' => $user->load('image'),
        ]);
    }

    //تابع يقوم بتعديل معلومات المستخدم
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'المستخدم غير مصادق عليه، الرجاء إرسال التوكن بشكل صحيح.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|between:3,25',
            'phone' => 'sometimes|required|digits:10|unique:users,phone,' . $user->id,
            'password' => 'sometimes|required|string|min:8|confirmed',
            'url' => 'nullable|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'خطأ في التحقق من البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $isUpdated = false;

            if ($request->filled('name') && $request->name !== $user->name) {
                $user->name = $request->name;
                $isUpdated = true;
            }

            if ($request->filled('phone') && $request->phone !== $user->phone) {
                $user->phone = $request->phone;
                $isUpdated = true;
            }

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
                $isUpdated = true;
            }

            if ($request->hasFile('url')) {
                // حذف الصورة القديمة إن وُجدت
                if ($user->image) {
                    $oldPath = public_path('pictures/' . $user->image->url);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                    $user->image()->delete();
                }

                // رفع الصورة الجديدة
                $path = $this->uploadImage($request->file('url'), 'users');
                $user->image()->create(['url' => $path]);
                $isUpdated = true;
            }

            if (!$isUpdated) {
                return response()->json([
                    'message' => 'لم تقم بتعديل أي معلومات.',
                    'user' => $user->load('image'),
                ]);
            }

            $user->save();
            DB::commit();

            return response()->json([
                'message' => 'تم تحديث الملف الشخصي بنجاح.',
                'user' => $user->load('image'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'حدث خطأ أثناء تحديث البيانات.',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }


}
