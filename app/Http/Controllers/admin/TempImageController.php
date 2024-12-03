<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class TempImageController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => true,
                'message' => 'Invalid Image',
                'errors' => $validator->errors(),
            ]);
        }
        // Logic Image
        $image = $request->image;
        $imageName = time() . '.' . $image->getClientOriginalExtension();

        // Save in Database
        $model = new TempImage();
        $model->name = $imageName;
        $model->save();

        //Save image in uploads/temps
        $image->move(public_path('uploads/temps'), $imageName);

        //Create small thumail here
        $sourcePath = public_path('uploads/temps' . $imageName);
        $destPath = public_path('uploads/temps/thumb/' . $imageName);
        $manager = new ImageManager(Driver::class);
        $img = $manager->read($sourcePath);
        $img->coverDown(300, 300);
        $img->save($destPath);

        return response()->json([
            'status' => true,
            'data' => $model,
            'message' => 'Image uploaded successfully',
        ]);
    }
}
