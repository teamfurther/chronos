<?php

namespace Chronos\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Chronos\Models\ImageStyle;

class ImageStylesController extends Controller
{

    public function index()
    {
        return view('chronos::settings.image_styles.index');
    }

    public function create()
    {
        return view('chronos::settings.image_styles.create');
    }

    public function edit(ImageStyle $style)
    {
        return view('chronos::settings.image_styles.edit')->with('style', $style);
    }

}
