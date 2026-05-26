<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        $field = $request->hasFile('image') ? 'image' : 'upload';

        if (! $request->hasFile($field)) {
            return response()->json([
                'success' => false,
                'message' => 'No image uploaded'
            ], 400);
        }

        $validator = Validator::make([$field => $request->file($field)], [
            $field => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120' // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first($field)
            ], 422);
        }

        $image = $request->file($field);
        $path = $image->store('uploads/editor', 'public');
        $url = Storage::url($path);

        return response()->json([
            'success' => true,
            'uploaded' => true,
            'url' => $url
        ]);
    }
}
