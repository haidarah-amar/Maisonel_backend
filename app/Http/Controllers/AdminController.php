<?php

namespace App\Http\Controllers;

use App\Models\Appartment;
use Illuminate\Http\Request;
use App\Models\Requestt;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
class AdminController extends Controller
{
    public function allUsers()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function allApartments()
    {
        $apartments = Appartment::all();
        return response()->json($apartments);
    }

    // ========================================================================================

    public function allActiveUsers()
    {
        $users = User::where('is_active', 1)->get();
        return response()->json($users);
    }

    public function allPendingUsers()
    {
        $users = User::where('is_active', 0)->get();
        return response()->json($users);
    }

    public function allRejectedUsers()
    {
        $users = User::where('is_active', -1)->get();
        return response()->json($users);
    }

    public function allApprovedApartments()
    {
        $apartments = Appartment::where('is_active', 1)->get();
        return response()->json($apartments);
    }

    public function allPendingApartments()
    {
        $apartments = Appartment::where('is_active', 0)->get();
        return response()->json($apartments);
    }

    public function allRejectedApartments()
    {
        $apartments = Appartment::where('is_active', -1)->get();
        return response()->json($apartments);
    }

    // =========================================================================================

    public function approveUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (! $user) {
            return response()->json(['error' => 'There is no user, who has this id'], 404);
        }
        
        User::where('id', $id)->update(['is_active' => 1]);
        return response()->json(['message' => 'User approved successfully.'] , 200);
        
    }

        public function rejectUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (! $user) {
            return response()->json(['error' => 'There is no user, who has this id'], 404);
        }
        
        User::where('id', $id)->update(['is_active' => -1]);
        return response()->json(['message' => 'User rejected successfully.'] , 200);

    }

    // ===============================================================================================================


    public function approveApartment(Request $request, $id)
    {
        $apartment = Appartment::findOrFail($id);

        if (!$apartment) {
            return response()->json(['error' => 'There is no apartment with this id'], 404);
        }

        Appartment::where('id', $id)->update(['is_approved' => true]);

            return response()->json(['message' => 'The apartment has been approved successfully.'] , 200);
        }

    public function rejectApartment(Request $request, $id)
    {
        $apartment = Appartment::findOrFail($id);

        if (!$apartment) {
            return response()->json(['error' => 'There is no apartment with this id'], 404);
        }

        Appartment::where('id', $id)->update(['is_approved' => false]);

            return response()->json(['message' => 'The apartment has been rejected successfully.'] , 200);
       
    }
}
