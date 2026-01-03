<?php

namespace App\Http\Controllers;

use App;
use App\Http\Requests\UpdateApartmentRequest;
use App\Models\Appartment;
use App\uplodImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Requestt;
use App\Http\Requests\AppartmentRequest;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class Appartmentcontroller extends Controller
{
    use uplodImage;

    
    public function index()
    {
    
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => $user,'Unauthorized'], 401);
        }
        $appartments = Appartment::where('owner_id', $user->id)->where('is_approved', true)->get();
        return response()->json($appartments, 200);
    }

    public function store(AppartmentRequest $request)
    {
        // Ensure request is authenticated

        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        $validatedData = $request->validated();


        // Force owner_id to the authenticated user and ignore any client-supplied user_id
        $validatedData['owner_id'] = $user->id;
       

        // Handle multiple image uploads or an array of image URLs/paths
        $images = [];
        // Files uploaded as image_url[]
        if ($request->hasFile('image_url')) {
            $files = $request->file('image_url');
            if (!is_array($files)) {
                $files = [$files];

            }
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $images[] = $file->store('appartment_images', 'public');
                }
            }
        }


        // If client provided image_url as array of strings (URLs or paths), merge them
        if (isset($validatedData['image_url']) && is_array($validatedData['image_url'])) {
            foreach ($validatedData['image_url'] as $val) {
                if (is_string($val) && $val !== '') {
                    $images[] = $val;
                }
            }
        }



        // Merge any remote URLs provided under image_urls
        if (isset($validatedData['image_urls']) && is_array($validatedData['image_urls'])) {
            foreach ($validatedData['image_urls'] as $url) {
                if (is_string($url) && $url !== '') {
                    $images[] = $url;
                }
            }
        }

        // Normalize to null when no images
        $validatedData['image_url'] = count($images) ? $images : null;
          // admin approve
            // Requestt::create([
            //     'user_id' => $user->id,
            //     'requestable_id' => $appartment->id,
            //     'requestable_type' => Appartment::class,
            //     'status' => 'pending',
            // ]);

        $apt = Appartment::create($validatedData);

        return response()->json($apt, 201);
        
    }
    public function update(UpdateApartmentRequest $request, $id){
    $user = Auth::user();
    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $appartment = Appartment::findOrFail($id);

    if ($appartment->owner_id !== $user->id) {
        return response()->json(['message' => 'This apartment does not belong to you'], 403);
    }

    $validatedData = $request->validated();
    // prevent editing owner_id and user_id
    unset( $validatedData['owner_id']);

    // new images array and handling uploads/URLs
    $images = is_array($appartment->image_url) ? $appartment->image_url : [];

    // the uploaded files
    if ($request->hasFile('image_url')) {
        foreach ((array) $request->file('image_url') as $file) {
            if ($file && $file->isValid()) {
                $images[] = $file->store('appartment_images', 'public');
            }
        }
    }

    // new image URLs
    if (isset($validatedData['image_urls']) && is_array($validatedData['image_urls'])) {
        foreach ($validatedData['image_urls'] as $url) {
            if (is_string($url) && $url !== '') {
                $images[] = $url;
            }
        }
    }


    // Remove image_url and image_urls from validated data to avoid conflicts
    unset($validatedData['image_url'], $validatedData['image_urls']);

    // update other fields
    if (!empty($validatedData)) {
        $appartment->update($validatedData);
    }

    // update images if any new ones were added
    if ($request->hasFile('image_url') || $request->has('image_urls')) {
        $appartment->update([
            'image_url' => count($images) ? $images : null
        ]);
    }

    return response()->json([
        'message' => 'Apartment updated successfully',
        'data' => $appartment
    ], 200);
}

    public function show($id)
    {
       $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

            $appartment = Appartment::findOrFail($id);

        if ($appartment->owner_id !== $user->id) {
            return response()->json(['message' => 'This apartment does not belong to you'], 403);
        }
        return response()->json($appartment, 200);
    }

    public function destroy($id)
    {
         $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

            $appartment = Appartment::findOrFail($id);

        if (!$appartment) {
            return response()->json(['message' => 'Apartment does not exist'], 404);
        }
        
        if ($appartment->owner_id !== $user->id) {
            return response()->json(['message' => 'This apartment does not belong to you'], 403);
        }
        $appartment->delete();
        return response()->json(['message' => 'Appartment deleted successfully'], 200);
    }

    public function deleteImage($id, $index)
{
    $user = Auth::user();
    $appartment = Appartment::findOrFail($id);

    if ($appartment->owner_id !== $user->id) {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    $images = $appartment->image_url ?? [];

    if (!isset($images[$index])) {
        return response()->json(['message' => 'Image not found'], 404);
    }

    $path = $images[$index];

    if (!str_starts_with($path, 'http')) {
        Storage::disk('public')->delete($path);
    }

    unset($images[$index]);
    $images = array_values($images);

    $appartment->update([
        'image_url' => count($images) ? $images : null
    ]);

    return response()->json(['message' => 'Image deleted successfully']);
}

public function toggleStatus($id)
{
    $user = Auth::user();
    $appartment = Appartment::findOrFail($id);

    if ($appartment->owner_id !== $user->id) {
        return response()->json(['message' => 'This apartment does not belong to you'], 403);
    }

    $appartment->is_active = !$appartment->is_active;
    $appartment->save();

    return response()->json([
        'message' => $appartment->is_active ? 'Apartment activated' : 'Apartment deactivated',
        'is_active' => $appartment->is_active
    ], 200);
}


    
}
// }
