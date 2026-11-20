<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MyItemsController extends Controller
{
    public function index(Request $request)
    {
        $assignments = $request->user()
            ->itemAssignments()
            ->open()
            ->with('item.company')
            ->orderByDesc('assigned_at')
            ->get();

        return view('items.mine', compact('assignments'));
    }
}
