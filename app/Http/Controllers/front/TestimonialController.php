<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

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
    public function latestTestimonial(Request $request)
    {
        $testimonials = Testimonial::where('status', 1)
            ->take($request->get('limit'))
            ->orderBy('created_at', 'DESC')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $testimonials
        ]);
    }
}
