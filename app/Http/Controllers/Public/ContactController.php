<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Institutional/Contact', [
            'seo' => [
                'title' => 'Contacto',
                'description' => 'Escríbenos para consultas sobre libros, distribución, librerías y recursos Paulinas.',
            ],
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
