<?php

namespace App\Http\Controllers;

class MyItemsController extends Controller
{
    /**
     * Przedmioty przypisane do zalogowanego użytkownika – na razie pusty ekran.
     */
    public function index()
    {
        return view('items.mine');
    }
}
