<?php

namespace Chronos\Http\Controllers\Content;

use App\Http\Controllers\Controller;

use Chronos\Models\Content;
use Chronos\Models\ContentFieldData;
use Chronos\Models\ContentType;
use Chronos\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContentController extends Controller
{

    public function index(ContentType $type)
    {
        if (!Auth::user()->hasPermission('view_content_type_' . $type->id)) {
            abort(403);
        }

        return view('chronos::content.manage.index')->with([
            'languages' => Language::active()->get(),
            'type' => $type
        ]);
    }

    public function create(ContentType $type)
    {
        if (!Auth::user()->hasPermission('add_content_type_' . $type->id)) {
            abort(403);
        }

        return view('chronos::content.manage.create')->with('type', $type);
    }

    public function edit(ContentType $type, Content $content)
    {
        if (!Auth::user()->hasPermission('edit_content_type_' . $type->id)) {
            abort(403);
        }

        return view('chronos::content.manage.edit')->with([
            'content' => $content,
            'languages' => Language::active()->get(),
            'type' => $type
        ]);
    }

    public function fieldsets(ContentType $type, Content $content)
    {
        return view('chronos::content.fieldsets.edit')->with([
            'parent' => $content,
            'type' => $type
        ]);
    }

}
