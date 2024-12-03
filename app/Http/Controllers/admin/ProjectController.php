<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::where('status', 1)
            ->orderBy('created_at', 'DESC')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $projects
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'slug' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
        $project = new Project();
        $project->title = $request->title;
        $project->slug = Str::slug($request->slug);
        $project->short_desc = $request->short_desc;
        $project->content = $request->content;
        $project->construction_type = $request->construction_type;
        $project->sector = $request->sector;
        $project->status = $request->status;
        $project->location = $request->location;
        $project->save();
        // Save Temp Image Here
        if ($request->imageId > 0) {
            $tempImage = TempImage::find($request->imageId);
            if ($tempImage != null) {
                //142233.png
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $fileName = strtotime('now') . $project->id . '.' . $ext;
                //Create small thumail here
                $sourcePath = public_path('uploads/temps' . $tempImage->name);
                $destPath = public_path('uploads/projects/small' . $fileName);
                $manager = new ImageManager(Driver::class);
                $img = $manager->read($sourcePath);
                $img->coverDown(500, 600);
                $img->save($destPath);
                //Create large thumail here
                $destPath = public_path('uploads/projects/large' . $fileName);
                $manager = new ImageManager(Driver::class);
                $img = $manager->read($sourcePath);
                $img->scaleDown(1200);
                $img->save($destPath);

                $project->image = $fileName;
                $project->save();
            }
        }
        return response()->json([
            'status' => true,
            'message' => 'Project Added Successfully',
        ]);
    }

    public function update(Request $request, $id)
    {
        $project = Project::find($id);
        if (empty($project)) {
            return response()->json([
                'status' => false,
                'message' => 'Project not found',
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
            $project->title = $request->title;
            $project->slug = Str::slug($request->slug);
            $project->short_desc = $request->short_desc;
            $project->content = $request->content;
            $project->construction_type = $request->construction_type;
            $project->sector = $request->sector;
            $project->status = $request->status;
            $project->location = $request->location;

            // Save Temp Image Here
            if ($request->imageId > 0) {
                $oldImage = $project->image;
                $tempImage = TempImage::find($request->imageId);
                if ($tempImage != null) {
                    //142233.png
                    $extArray = explode('.', $tempImage->name);
                    $ext = last($extArray);

                    $fileName = strtotime('now') . $project->id . '.' . $ext;
                    //Create small thumail here
                    $sourcePath = public_path('uploads/temps' . $tempImage->name);
                    $destPath = public_path('uploads/projects/small' . $fileName);
                    $manager = new ImageManager(Driver::class);
                    $img = $manager->read($sourcePath);
                    $img->coverDown(500, 600);
                    $img->save($destPath);
                    //Create large thumail here
                    $destPath = public_path('uploads/projects/large' . $fileName);
                    $manager = new ImageManager(Driver::class);
                    $img = $manager->read($sourcePath);
                    $img->scaleDown(1200);
                    $img->save($destPath);
                    $project->image = $fileName;
                    $project->save();
                    if ($oldImage != '') {
                        File::delete(public_path('uploads/projects/large' . $oldImage));
                        File::delete(public_path('uploads/projects/small' . $oldImage));
                    }
                }
            }



            return response()->json([
                'status' => true,
                'message' => 'Project updated successfully',

            ]);
        }
    }
    public function show(Request $request, $id)
    {
        $projects = Project::find($id);
        if (empty($projects)) {
            return response()->json([
                'status' => false,
                'message' => 'Project not found',
            ]);
        }
        return response()->json([
            'status' => true,
            'data' => $projects
        ]);
    }
    public function destroy($id)
    {
        $project = Project::find($id);
        if (empty($project)) {
            return response()->json([
                'status' => false,
                'message' => 'Project not found',
            ]);
        }
        File::delete(public_path('uploads/projects/large' . $project->image));
        File::delete(public_path('uploads/projects/small' . $project->image));

        $project->delete();
        return response()->json([
            'status' => true,
            'message' => 'Project deleted successfully',
        ]);
    }
}
