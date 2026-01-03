<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

   
    public function rules(): array
    {
        return [
            'address' => 'sometimes|string',
            'size' => 'sometimes|integer',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'is_approved' => 'sometimes|boolean',
            'bedrooms' => 'sometimes|integer',
            'bathrooms' => 'sometimes|integer',
            'is_favorite' => 'nullable|in:yes,no',
            'type' => 'sometimes|in:Apartment,House,Studio,Villa',
            'rating' => 'nullable|in:1,2,3,4,5',
            'views' => 'nullable|integer',
            'location' => 'sometimes|string',
            'image_url' => 'nullable|array',
            'image_url.*' => 'nullable|file|image|mimes:jpg,jpeg,png,gif,svg|max:5120',
        ];
    }
}
