<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    public function index(Request $request): Response
    {
        $branches = Branch::query()->active()->ordered()->get();

        return Inertia::render('Branches/Index', [
            'seo' => [
                'title' => 'Librerías',
                'description' => 'Direcciones, horarios y datos de contacto de las librerías Paulinas en Chile.',
            ],
            'branches' => BranchResource::collection($branches)->resolve($request),
        ]);
    }

    public function show(Request $request, Branch $branch): Response
    {
        abort_unless($branch->active, 404);

        return Inertia::render('Branches/Show', [
            'seo' => [
                'title' => $branch->name,
                'description' => "Dirección, horarios y contacto de {$branch->name}.",
            ],
            'branch' => (new BranchResource($branch))->resolve($request),
        ]);
    }
}
