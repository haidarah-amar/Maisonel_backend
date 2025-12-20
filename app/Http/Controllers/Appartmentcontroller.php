<?php

namespace App\Http\Controllers;

use App;
use App\Models\Appartment;
use App\uplodImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Requestt;
use App\Http\Requests\AppartmentRequest;
class Appartmentcontroller extends Controller
{
    use uplodImage;
    public function index()
    {
        $appartment = Appartment::where('is_avilable', true)->get();
        return response()->json($appartment, 200);
    }

    public function store(AppartmentRequest $request)
    {
        //user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        if ($user->type !== 'owner') {
            return response()->json(['message' => 'Only owners can create appartments'], 403);
        }

        // check
        // $validatedData = $request->validate([
        //     'title' => 'required|string|max:255',
        //     'description' => 'required|string',
        //     'price' => 'required|numeric',
        //     'location' => 'required|string|max:255',
        //     'image_url' => 'nullable|url',
        // ]);

        //start
        try {
            DB::beginTransaction();
            //create
            $appartment = Appartment::create([
                'user_id' => $user->id,
                'title' => $request['title'],
                'description' => $request['description'],
                'price' => $request['price'],
                'location' => $request['location'],
            ]);
            //img
            if ($request->hasFile('image_url')) {
                $path = $this->uploadImage($request->file('image_url'), 'appartment');
                $appartment->image_url = $path;
                $appartment->save();
            }

            // admin approve
            Requestt::create([
                'user_id' => $user->id,
                'requestable_id' => $appartment->id,
                'requestable_type' => Appartment::class,
                'status' => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'the appartment is created wait to be approved by the admin !',
                'data' => $appartment,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'error while creating the appartment',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        if (!$user->type == 'owner') {
            return response()->json(['message' => 'Only owners can create appartments'], 403);
        }
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|text',
            'price' => 'required|numeric',
            'image_url' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
        ]);
        try {
            DB::beginTransaction();
            $appartment=Appartment::findOrFail($id);
            if(!$appartment){
                return response()->json(['masseg:'=>'not found'],404);
            }
            $appartment->update([
                'user_id' => 1,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'price' => $request->input('price'),
                'image_url' => $request->input('image_url'),
            ]);
            if ($request->hasFile('image_url')) {
                $path = $this->uploadImage($request->file('url'), 'property');
                $appartment->images()->create(['url' => $path]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'error while creating the appartment',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }
    public function show($id)
    {
        $appartment = Appartment::find($id);
        if (!$appartment) {
            return response()->json(['message' => 'Appartment not found'], 404);
        }
        return response()->json($appartment, 200);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        if (!$user->type == 'owner') {
            return response()->json(['message' => 'Only owners can create appartments'], 403);
        }
        $appartment = Appartment::find($id);
        if (!$appartment) {
            return response()->json(['message' => 'Appartment not found'], 404);
        }
        $appartment->delete();
        return response()->json(['message' => 'Appartment deleted successfully'], 204);
    }

    public function getAppartmentUser($id)
    {
        $user = Appartment::findOrFail($id)->user;

        return response()->json($user, 200);
    }
}
// }
