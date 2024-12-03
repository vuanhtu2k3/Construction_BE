<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function index()
    {
        $member = Member::where('status', 1)
            ->orderBy('created_at', 'DESC')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $member
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'job_title' => 'required',
            'linkedin_url' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
        $member = new Member();
        $member->name = $request->name;
        $member->job_title = $request->job_title;
        $member->linkedin_url = $request->linkedin_url;
        $member->status = $request->status;
        $member->save();
        // Save Temp Image Here
        if ($request->imageId > 0) {
            $tempImage = TempImage::find($request->imageId);
            if ($tempImage != null) {
                //142233.png
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $fileName = strtotime('now') . $member->id . '.' . $ext;
                //Create small thumail here
                $sourcePath = public_path('uploads/temps' . $tempImage->name);
                $destPath = public_path('uploads/members/small' . $fileName);
                $manager = new ImageManager(Driver::class);
                $img = $manager->read($sourcePath);
                $img->coverDown(500, 600);
                $img->save($destPath);
                //Create large thumail here
                $destPath = public_path('uploads/members/large' . $fileName);
                $manager = new ImageManager(Driver::class);
                $img = $manager->read($sourcePath);
                $img->scaleDown(1200);
                $img->save($destPath);

                $member->image = $fileName;
                $member->save();
            }
        }
        return response()->json([
            'status' => true,
            'message' => 'Member Added Successfully',
        ]);
    }
    public function update(Request $request, $id)
    {
        $member = Member::find($id);
        if (empty($member)) {
            return response()->json([
                'status' => false,
                'message' => 'Member not found',
            ]);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'job_title' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ]);
        } else {
            $member->name = $request->name;
            $member->job_title = $request->job_title;
            $member->linkedin_url = $request->linkedin_url;
            $member->status = $request->status;
            $member->save();

            // Save Temp Image Here
            if ($request->imageId > 0) {
                $oldImage = $member->image;
                $tempImage = TempImage::find($request->imageId);
                if ($tempImage != null) {
                    //142233.png
                    $extArray = explode('.', $tempImage->name);
                    $ext = last($extArray);

                    $fileName = strtotime('now') . $member->id . '.' . $ext;
                    //Create small thumail here
                    $sourcePath = public_path('uploads/temps' . $tempImage->name);
                    $destPath = public_path('uploads/members/small' . $fileName);
                    $manager = new ImageManager(Driver::class);
                    $img = $manager->read($sourcePath);
                    $img->coverDown(500, 600);
                    $img->save($destPath);
                    //Create large thumail here
                    $destPath = public_path('uploads/members/large' . $fileName);
                    $manager = new ImageManager(Driver::class);
                    $img = $manager->read($sourcePath);
                    $img->scaleDown(1200);
                    $img->save($destPath);
                    $member->image = $fileName;
                    $member->save();
                    if ($oldImage != '') {
                        File::delete(public_path('uploads/members/large' . $oldImage));
                        File::delete(public_path('uploads/members/small' . $oldImage));
                    }
                }
            }



            return response()->json([
                'status' => true,
                'message' => 'Member updated successfully',

            ]);
        }
    }
    public function show(Request $request, $id)
    {
        $member = Member::find($id);
        if (empty($member)) {
            return response()->json([
                'status' => false,
                'message' => 'Member not found',
            ]);
        }
        return response()->json([
            'status' => true,
            'data' => $member
        ]);
    }
    public function destroy($id)
    {
        $member = Member::find($id);
        if (empty($member)) {
            return response()->json([
                'status' => false,
                'message' => 'Member not found',
            ]);
        }
        File::delete(public_path('uploads/members/large' . $member->image));
        File::delete(public_path('uploads/members/small' . $member->image));

        $member->delete();
        return response()->json([
            'status' => true,
            'message' => 'Member deleted successfully',
        ]);
    }
}
