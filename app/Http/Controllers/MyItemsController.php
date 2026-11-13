<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MyItemsController extends Controller
{
    /**
     * Przedmioty przypisane do zalogowanego użytkownika
     */
    public function index(Request $request)
    {
        $items = $request->user()
            ->items()
            ->with('company')
            ->get();

        return view('items.mine', compact('items'));
    }
}
