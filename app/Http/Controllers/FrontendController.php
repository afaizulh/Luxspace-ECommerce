<?php

namespace App\Http\Controllers;

use Exception;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Carts;
use App\Models\Products;
use App\Models\Transactions;
use Illuminate\Http\Request;
use App\Models\TransactionItems;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CheckoutRequest;

class FrontendController extends Controller
{
    public function index(Request $request)
    {
        $products = Products::with(['gallery'])->latest()->get();

        return view('page.frontend.index', compact('products'));
    }

    public function details(Request $request, $slug)
    {
        $product = Products::with(['gallery'])->where('slug', $slug)->firstOrFail();
        $recommendations = Products::with(['gallery'])->inRandomOrder()->limit(4)->get();

        return view('page.frontend.details', compact('product', 'recommendations'));
    }

    public function cartAdd(Request $request, $id)
    {
        Carts::create([
            'user_id' => Auth::user()->id,
            'product_id' => $id
        ]);

        return redirect('cart');
    }

    public function cartDelete(Request $request, $id)
    {
        $item = Carts::findOrFail($id);

        $item->delete();

        return redirect('cart');
    }

    public function cart(Request $request)
    {
        $carts = Carts::with(['product.gallery'])->where('user_id', Auth::user()->id)->get();

        return view('page.frontend.cart', compact('carts'));
    }

    public function checkout(CheckoutRequest $request)
    {
        $data = $request->all();

        // Get Carts Data
        $carts = Carts::with(['product'])->where('id', Auth::user()->id)->get();

        // Add To Transactions Data
        $data['user_id'] = Auth::user()->id;
        $data['total_price'] = $carts->sum('product.price');

        // Create Transactions
        $transaction = Transactions::create($data);


        // Create Transaction Items
        foreach ($carts as $cart) {
            $items[] = TransactionItems::create([
                'transaction_id' => $transaction->id,
                'user_id' => $cart->user_id,
                'product_id' => $cart->product_id
            ]);
        }

        // Delete Carts After Transactions
        Carts::where('user_id', Auth::user()->id)->delete();

        // Midtrans Configuration
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        // SetUp Midtrans Variable
        $midtrans = [
            'transaction_details' => [
                'order_id' => 'LUX-' . $transaction->id,
                'gross_amount' => (int) $transaction->total_price
            ],

            'customer_details' => [
                'first_name' => $transaction->name,
                'email' => $transaction->email
            ],

            'enabled_payments' => ['gopay', 'bank_transfer'],

            'vtweb' => []
        ];

        // Payment Process
        try {
            // Get Snap Payment Page URL
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;

            $transaction->payment_url = $paymentUrl;
            $transaction->save();

            // Redirect to Snap Payment Page
            return redirect($paymentUrl);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function success(Request $request)
    {
        return view('page.frontend.success');
    }
}
