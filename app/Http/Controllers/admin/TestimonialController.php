<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::where('status', 1)
            ->orderBy('created_at', 'DESC')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $testimonials
        ]);
    }

    public function store(Request $request)
    {
        $validator  = Validator::make($request->all(), [
            'testimonial' => 'required',
            'citation' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
        $testimonial = new Testimonial();
        $testimonial->testimonial = $request->testimonial;
        $testimonial->citation = $request->citation;
        $testimonial->designation = $request->designation;
        $testimonial->status = $request->status;
        $testimonial->save();
        // Save Temp Image Here
        if ($request->imageId > 0) {
            $tempImage = TempImage::find($request->imageId);
            if ($tempImage != null) {
                //142233.png
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $fileName = strtotime('now') . $testimonial->id . '.' . $ext;
                //Create small thumail here
                $sourcePath = public_path('uploads/temps' . $tempImage->name);
                $destPath = public_path('uploads/testimonials/small' . $fileName);
                $manager = new ImageManager(Driver::class);
                $img = $manager->read($sourcePath);
                $img->coverDown(500, 600);
                $img->save($destPath);
                //Create large thumail here
                $destPath = public_path('uploads/testimonials/large' . $fileName);
                $manager = new ImageManager(Driver::class);
                $img = $manager->read($sourcePath);
                $img->scaleDown(1200);
                $img->save($destPath);

                $testimonial->image = $fileName;
                $testimonial->save();
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Testimonial Added Successfully'
        ]);
    }
    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::find($id);
        if (empty($testimonial)) {
            return response()->json([
                'status' => false,
                'message' => 'Testimonial not found',
            ]);
        }
        $validator  = Validator::make($request->all(), [
            'testimonial' => 'required',
            'citation' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
        $testimonial = new Testimonial();
        $testimonial->testimonial = $request->testimonial;
        $testimonial->citation = $request->citation;
        $testimonial->designation = $request->designation;
        $testimonial->status = $request->status;
        $testimonial->save();
        // Save Temp Image Here
        if ($request->imageId > 0) {
            $tempImage = TempImage::find($request->imageId);
            if ($tempImage != null) {
                //142233.png
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $fileName = strtotime('now') . $testimonial->id . '.' . $ext;
                //Create small thumail here
                $sourcePath = public_path('uploads/temps' . $tempImage->name);
                $destPath = public_path('uploads/testimonials/small' . $fileName);
                $manager = new ImageManager(Driver::class);
                $img = $manager->read($sourcePath);
                $img->coverDown(500, 600);
                $img->save($destPath);
                //Create large thumail here
                $destPath = public_path('uploads/testimonials/large' . $fileName);
                $manager = new ImageManager(Driver::class);
                $img = $manager->read($sourcePath);
                $img->scaleDown(1200);
                $img->save($destPath);

                $testimonial->image = $fileName;
                $testimonial->save();
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Testimonial updated Successfully'
        ]);
    }
    public function show(Request $request, $id)
    {
        $testimonials = Testimonial::find($id);
        if (empty($testimonials)) {
            return response()->json([
                'status' => false,
                'message' => 'Testimonials not found',
            ]);
        }
        return response()->json([
            'status' => true,
            'data' => $testimonials
        ]);
    }
    public function destroy($id)
    {
        $testimonial = Testimonial::find($id);
        if (empty($testimonial)) {
            return response()->json([
                'status' => false,
                'message' => 'Testimonials not found',
            ]);
        }
        File::delete(public_path('uploads/testimonials/large' . $testimonial->image));
        File::delete(public_path('uploads/testimonials/small' . $testimonial->image));

        $testimonial->delete();
        return response()->json([
            'status' => true,
            'message' => 'Testimonials deleted successfully',
        ]);
    }
}
