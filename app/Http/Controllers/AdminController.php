<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Requestt;
use Illuminate\Support\Facades\Auth;
class AdminController extends Controller
{
    public function index()
    {
        $requests = Requestt::where('status', 'pending')->with('requestable')->get();
        return response()->json($requests);
    }

    // قبول الطلب (تغيير الحالة إلى approved)
    public function approveAppartment(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->type !== 'admin') {
            return response()->json(['error' => 'غير مصرح لك بالوصول إلى هذا المورد.'], 403);
        }

        $request = Requestt::findOrFail($id);

        if (!$request) {
            return response()->json(['error' => 'الطلب غير موجود.'], 404);
        }

        $request->status = 'accepted';  // تأكد أنها نصية
        $request->save();

        // تفعيل العقار المرتبط عند الموافقة
        $appartment = $request->requestable;
        if ($appartment) {
            $appartment->is_avilable = true;
            $appartment->save();
            return response()->json(['message' => 'تم قبول الطلب وتفعيل العقار.']);
        }

    }


    // رفض الطلب (تغيير الحالة إلى rejected)
    public function rejectAppartment(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->type !== 'admin') {
            return response()->json(['error' => 'غير مصرح لك بالوصول إلى هذا المورد.'], 403);
        }
        
        $requestt = Requestt::findOrFail($id);
        $requestt->status = 'rejected';
        $requestt->save();

        return response()->json(['message' => 'تم رفض الطلب.']);
    }
}
