<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MovilidadAccesoController extends Controller
{
    public function create()
    {
        return view('movilidad.login');
    }

    public function store(Request $r)
    {
        $data = $r->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (! Auth::attempt($data)) {
            throw ValidationException::withMessages(['email' => 'Credenciales incorrectas.']);
        }$r->session()->regenerate();

        return redirect()->intended(route('vehiculos.index'));
    }

    public function destroy(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect()->route('movilidad.login');
    }
}
