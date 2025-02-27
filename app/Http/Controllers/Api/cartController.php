<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class cartController extends Controller implements HasMiddleware
{
    private $colums = [
        'id_cart',
        'status',
    ];

    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum', except: ['']),
        ];
    }

    public function allByCustomers(Request $request)
    {
        $userCompany = $request->user()->companies()->first();
        $carts       = $userCompany->carts()->get($this->colums)->load('products');

        return Controller::response(200, false, $message = 'Cart list', $carts);
    }

    public function current(Request $request)
    {
        $userCompany = $request->user()->companies()->first();
        $currentCart = $userCompany->carts()->where('status', 1)->first();

        if (! $currentCart) {
            return Controller::response(404, true, $message = 'Cart not found');
        }

        $cart = $currentCart->load('products');

        return Controller::response(200, false, $message = 'Current cart', $cart);
    }

    public function addProduct(Request $request)
    {
        $userCompany = $request->user()->companies()->first();
        $currentCart = $userCompany->carts()->where('status', 1)->first();
    
        $validatedData = $request->validate([
            'id_product' => 'required|int|exists:products_stores,id_product',
            'quantity'   => 'required|int|min:1',
        ]);
    
        if (!$currentCart) {
            $currentCart = $userCompany->carts()->create([
                'status'     => 1,
                'created_at' => now(),
            ]);
        }

        $product = $currentCart->products()
            ->where('products_stores.id_product', $validatedData['id_product'])
            ->first();
    
        if ($product) {
            $currentCart->products()->updateExistingPivot($validatedData['id_product'], [
                'quantity' => $product->pivot->quantity + $validatedData['quantity']
            ]);
        } else {
            $currentCart->products()->attach($validatedData['id_product'], [
                'quantity' => $validatedData['quantity'],
            ]);
        }
    
        return Controller::response(201, false, $message = 'Product added to cart');
    }
    

    public function deleteProduct(Request $request)
    {
        $userCompany = $request->user()->companies()->first();
    
        $currentCart = $userCompany->carts()->where('status', 1)->first();
    
        $validated = (object) $request->validate([
            'id_product' => 'required|int|exists:products_stores,id_product',
        ]);
    
        if (! $currentCart) {
            return Controller::response(404, true, 'Cart not found');
        }
    
        // Especificar la tabla en el WHERE para evitar ambigüedad
        $product = $currentCart->products()
            ->where('products_carts.id_product', $validated->id_product)
            ->first();
    
        if (! $product) {
            return Controller::response(404, true, 'Product not found in cart');
        }
    
        $currentCart->products()->detach($validated->id_product);
    
        return Controller::response(200, false, 'Product removed from cart');
    }
    
}
