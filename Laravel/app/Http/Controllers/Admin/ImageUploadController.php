<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120' // 5MB
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('uploads/editor', 'public');
            
            return response()->json([
                'success' => true,
                'url' => Storage::url($path)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image uploaded'
        ], 400);
    }
}
