<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Course;

class CourseController extends Controller
{
    //
    public function index()
    {
        $courses = Course::all();
        return Inertia::render('Courses', ['courses' => $courses]);
    }
}
