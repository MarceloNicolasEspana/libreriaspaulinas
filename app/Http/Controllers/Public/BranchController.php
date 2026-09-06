<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Support\Seo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    public function index(Request $request): Response
    {
        $branches = Branch::query()->active()->ordered()->get();

        return Inertia::render('Branches/Index', [
            'seo' => Seo::page(
                $request,
                'Librerías',
                'Direcciones, horarios y datos de contacto de las librerías Paulinas en Chile.',
                [['name' => 'Inicio', 'href' => '/'], ['name' => 'Librerías', 'href' => '/librerias']],
            ),
            'branches' => BranchResource::collection($branches)->resolve($request),
        ]);
    }

    public function show(Request $request, Branch $branch): Response
    {
        abort_unless($branch->active, 404);

        return Inertia::render('Branches/Show', [
            'seo' => Seo::page(
                $request,
                $branch->name,
                "Dirección, horarios y contacto de {$branch->name}.",
                [
                    ['name' => 'Inicio', 'href' => '/'],
                    ['name' => 'Librerías', 'href' => '/librerias'],
                    ['name' => $branch->name, 'href' => route('branches.show', $branch, absolute: false)],
                ],
                [Seo::localBusiness($branch)],
                type: 'business.business',
            ),
            'branch' => (new BranchResource($branch))->resolve($request),
        ]);
    }
}
