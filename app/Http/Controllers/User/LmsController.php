<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LmsMaterial;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LmsController extends Controller
{
    public function index(Request $request): Response
    {
        $materials = LmsMaterial::with(['category.parent'])
            ->where('is_active', true)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('user/Lms/Index', [
            'materials' => $materials,
        ]);
    }

    public function show(LmsMaterial $lms_material): Response
    {
        if (! $lms_material->is_active) {
            abort(404);
        }

        $lms_material->load(['category.parent']);

        return Inertia::render('user/Lms/Show', [
            'material' => $lms_material,
        ]);
    }
}
