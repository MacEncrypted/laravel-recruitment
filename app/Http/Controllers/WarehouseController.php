<?php

namespace App\Http\Controllers;

use App\Models\Company;

class WarehouseController extends Controller
{
    /**
     * Magazyn firmy – na razie pusty ekran (do zaimplementowania).
     */
    public function index(Company $company)
    {
        return view('companies.warehouse.index', compact('company'));
    }
}
