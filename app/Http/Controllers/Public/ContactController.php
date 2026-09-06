<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Institutional/Contact', [
            'seo' => Seo::page(
                $request,
                'Contacto',
                'Escríbenos para consultas sobre libros, distribución, librerías y recursos Paulinas.',
                [['name' => 'Inicio', 'href' => '/'], ['name' => 'Contacto', 'href' => '/contacto']],
            ),
            'formAction' => route('contact.store', absolute: false),
        ]);
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        ContactMessage::create([
            ...$request->safe()->only(['name', 'email', 'phone', 'subject', 'message']),
            'ip_address' => (string) $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500),
        ]);

        return back()->with('success', 'Recibimos tu mensaje. Te responderemos a la brevedad.');
    }
}
