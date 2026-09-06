<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Admin\Catalog;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $stats = [];
        foreach (Catalog::SECTIONS as $section => $definition) {
            $stats[] = ['label' => $definition['label'], 'total' => $definition['model']::count(), 'href' => route('admin.records.index', $section, absolute: false)];
        }

        return Inertia::render('Admin/Dashboard', ['stats' => $stats]);
    }
}
