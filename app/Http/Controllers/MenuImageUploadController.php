<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuImageUploadController extends Controller
{
    /**
     * Handle the image upload for menu items
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
            ]);

            $image = $request->file('image');
            
            // Generate a unique filename
            $timestamp = now()->timestamp;
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $filename = $timestamp . '_' . Str::slug($originalName) . '.' . $extension;
            
            // Define the storage path
            $storagePath = 'menu-images';
            
            // Store the image in the public disk
            $path = $image->storeAs($storagePath, $filename, 'public');
            
            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
                'filename' => $filename
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Menu image upload failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete an uploaded menu image
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImage(Request $request)
    {
        try {
            $request->validate([
                'path' => 'required|string'
            ]);
            
            $path = $request->input('path');
            
            // Security check: make sure the path is within our menu-images directory
            if (!str_starts_with($path, 'menu-images/')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file path'
                ], 400);
            }
            
            // Delete the file if it exists
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Image deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }
            
        } catch (\Exception $e) {
            \Log::error('Menu image deletion failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Deletion failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
