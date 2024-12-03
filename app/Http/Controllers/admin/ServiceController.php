<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('created_at', 'DESC')->get();
        return response()->json([
            'status' => true,
            'data' => $services
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'short_desc' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ]);
        }
        $service = new Service();
        $service->title = $request->title;
        $service->short_desc = $request->short_desc;
        $service->slug = Str::slug($request->slug);
        $service->content = $request->content;
        $service->status = $request->status;
        $service->save();

        // Save Temp Image Here
        if ($request->imageId > 0) {
            $tempImage = TempImage::find($request->imageId);
            if ($tempImage != null) {
                //142233.png
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $fileName = strtotime('now') . $service->id . '.' . $ext;
                //Create small thumail here
                $sourcePath = public_path('uploads/temps' . $tempImage->name);
                $destPath = public_path('uploads/services/small' . $fileName);
                $manager = new ImageManager(Driver::class);
                $img = $manager->read($sourcePath);
                $img->coverDown(500, 600);
                $img->save($destPath);
                //Create large thumail here
                $destPath = public_path('uploads/services/large' . $fileName);
                $manager = new ImageManager(Driver::class);
                $img = $manager->read($sourcePath);
                $img->scaleDown(1200);
                $img->save($destPath);

                $service->image = $fileName;
                $service->save();
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Service created successfully',
        ]);
    }

    public function show(Request $request, $id)
    {
        $services = Service::find($id);
        if (empty($services)) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found',
            ]);
        }
        return response()->json([
            'status' => true,
            'data' => $services
        ]);
    }

    public function update(Request $request, $id)
    {
        $services = Service::find($id);
        if (empty($services)) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found',
            ]);
        }
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'short_desc' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ]);
        } else {
            $services->title = $request->title;
            $services->short_desc = $request->short_desc;
            $services->slug = Str::slug($request->slug);
            $services->content = $request->content;
            $services->status = $request->status;
            $services->save();

            // Save Temp Image Here
            if ($request->imageId > 0) {
                $oldImage = $services->image;
                $tempImage = TempImage::find($request->imageId);
                if ($tempImage != null) {
                    //142233.png
                    $extArray = explode('.', $tempImage->name);
                    $ext = last($extArray);

                    $fileName = strtotime('now') . $services->id . '.' . $ext;
                    //Create small thumail here
                    $sourcePath = public_path('uploads/temps' . $tempImage->name);
                    $destPath = public_path('uploads/services/small' . $fileName);
                    $manager = new ImageManager(Driver::class);
                    $img = $manager->read($sourcePath);
                    $img->coverDown(500, 600);
                    $img->save($destPath);
                    //Create large thumail here
                    $destPath = public_path('uploads/services/large' . $fileName);
                    $manager = new ImageManager(Driver::class);
                    $img = $manager->read($sourcePath);
                    $img->scaleDown(1200);
                    $img->save($destPath);
                    $services->image = $fileName;
                    $services->save();
                    if ($oldImage != '') {
                        File::delete(public_path('uploads/services/large' . $oldImage));
                        File::delete(public_path('uploads/services/small' . $oldImage));
                    }
                }
            }



            return response()->json([
                'status' => true,
                'message' => 'Service updated successfully',

            ]);
        }
    }

    public function destroy($id)
    {
        $service = Service::find($id);
        if (empty($service)) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found',
            ]);
        }
        File::delete(public_path('uploads/services/large' . $service->image));
        File::delete(public_path('uploads/services/small' . $service->image));
        $service->delete();
        return response()->json([
            'status' => true,
            'message' => 'Service deleted successfully',
        ]);
    }
}
