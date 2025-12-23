<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow authenticated users to make this request.
        // If you need additional authorization logic (e.g. only owners),
        // implement checks here like: return auth()->check() && auth()->user()->role === 'owner';
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address' => 'required|string',
            'size' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            // is_approved stored as boolean in DB, but allow it to be optional during creation
            'is_approved' => 'sometimes|boolean',
            'bedrooms' => 'required|integer',
            'bathrooms' => 'required|integer',
            // is_favorite is stored as enum('yes','no') in DB — validate accordingly
            'is_favorite' => 'nullable|in:yes,no',
            'type' => 'required|in:apartment,house,studio,villa',
            'rating' => 'nullable|in:1,2,3,4,5',
            'views' => 'nullable|integer',
            'location' => 'required|string',
            // allow multiple images as array of files or array of URLs; each file max 5MB
            'image_url' => 'nullable|array',
            'image_url.*' => 'nullable|file|image|mimes:jpg,jpeg,png,gif,svg|max:5120',
        ];
    }
}
