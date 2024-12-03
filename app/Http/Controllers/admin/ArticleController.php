<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::where('status', 1)
            ->orderBy('created_at', 'DESC')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $articles,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
        $article = new Article();
        $article->title = $request->title;
        $article->slug = Str::slug($request->slug);
        $article->author = $request->author;
        $article->content = $request->content;
        $article->status = $request->status;
        $article->save();
        // Save Temp Image Here
        if ($request->imageId > 0) {
            $oldImage = $article->image;
            $tempImage = TempImage::find($request->imageId);
            if ($tempImage != null) {
                //142233.png
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $fileName = strtotime('now') . $article->id . '.' . $ext;
                //Create small thumail here
                $sourcePath = public_path('uploads/temps' . $tempImage->name);
                $destPath = public_path('uploads/articles/small' . $fileName);
                $manager = new ImageManager(Driver::class);
                $img = $manager->read($sourcePath);
                $img->coverDown(500, 600);
                $img->save($destPath);
                //Create large thumail here
                $destPath = public_path('uploads/articles/large' . $fileName);
                $manager = new ImageManager(Driver::class);
                $img = $manager->read($sourcePath);
                $img->scaleDown(1200);
                $img->save($destPath);
                $article->image = $fileName;
                $article->save();
                if ($oldImage != '') {
                    File::delete(public_path('uploads/articles/large' . $oldImage));
                    File::delete(public_path('uploads/articles/small' . $oldImage));
                }
            }
        }
        return response()->json([
            'status' => true,
            'message' => 'Article created successfully',
        ]);
    }
    public function update(Request $request, $id)
    {
        $article = Article::find($id);
        if (empty($article)) {
            return response()->json([
                'status' => false,
                'message' => 'Article not found',
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
            $article->title = $request->title;
            $article->slug = Str::slug($request->slug);
            $article->author = $request->author;
            $article->content = $request->content;
            $article->status = $request->status;

            // Save Temp Image Here
            if ($request->imageId > 0) {
                $oldImage = $article->image;
                $tempImage = TempImage::find($request->imageId);
                if ($tempImage != null) {
                    //142233.png
                    $extArray = explode('.', $tempImage->name);
                    $ext = last($extArray);

                    $fileName = strtotime('now') . $article->id . '.' . $ext;
                    //Create small thumail here
                    $sourcePath = public_path('uploads/temps' . $tempImage->name);
                    $destPath = public_path('uploads/articles/small' . $fileName);
                    $manager = new ImageManager(Driver::class);
                    $img = $manager->read($sourcePath);
                    $img->coverDown(500, 600);
                    $img->save($destPath);
                    //Create large thumail here
                    $destPath = public_path('uploads/articles/large' . $fileName);
                    $manager = new ImageManager(Driver::class);
                    $img = $manager->read($sourcePath);
                    $img->scaleDown(1200);
                    $img->save($destPath);
                    $article->image = $fileName;
                    $article->save();
                    if ($oldImage != '') {
                        File::delete(public_path('uploads/articles/large' . $oldImage));
                        File::delete(public_path('uploads/articles/small' . $oldImage));
                    }
                }
            }



            return response()->json([
                'status' => true,
                'message' => 'Article updated successfully',

            ]);
        }
    }
    public function show(Request $request, $id)
    {
        $article = Article::find($id);
        if (empty($article)) {
            return response()->json([
                'status' => false,
                'message' => 'Article not found',
            ]);
        }
        return response()->json([
            'status' => true,
            'data' => $article
        ]);
    }
    public function destroy($id)
    {
        $article = Article::find($id);
        if (empty($project)) {
            return response()->json([
                'status' => false,
                'message' => 'Article not found',
            ]);
        }
        File::delete(public_path('uploads/articles/large' . $article->image));
        File::delete(public_path('uploads/articles/small' . $article->image));

        $project->delete();
        return response()->json([
            'status' => true,
            'message' => 'Article deleted successfully',
        ]);
    }
}
