<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function index(Request $request)
    {
        $products = Products::with(['gallery'])->latest()->get();

        return view('page.frontend.index', compact('products'));
    }

    public function details(Request $request, $slug)
    {
        return view('page.frontend.details');
    }

    public function cart(Request $request)
    {
        return view('page.frontend.cart');
    }

    public function success(Request $request)
    {
        return view('page.frontend.success');
    }
}
