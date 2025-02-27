<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class cartController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum', except: ['']),
        ];
    }

    public function allByCustomers(Request $request)
    {
        $userCompany = $request->user()->companies()->first();
        $carts       = $userCompany->carts()->get()->where('status', 2)->load('products');

        return Controller::response(200, false, $message = 'Bought carts', $carts);
    }

    public function getCart(Request $request, int $id)
    {
        $userCompany = $request->user()->companies()->first();
        $currentCart = $userCompany->carts()->where('id_cart', $id)->first();

        if (! $currentCart) {
            return Controller::response(404, true, $message = 'Cart not found');
        }

        $cart = $currentCart->load('products');
        return Controller::response(200, false, $message = 'Current cart', $cart);
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

        if (! $currentCart) {
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
                'quantity' => $product->pivot->quantity + $validatedData['quantity'],
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
    public function buy(Request $request)
    {
        $userCompany = $request->user()->companies()->first();

        $currentCart = $userCompany->carts()->where('status', 1)->first();

        if (! $currentCart) {
            return Controller::response(404, true, 'Cart not found');
        }

        $products = $currentCart->products;

        if ($products->isEmpty()) {
            return Controller::response(400, true, 'Cart is empty');
        }

        // Iniciar una transacción para asegurar consistencia en la BD
        DB::beginTransaction();

        try {
            $totalPrice = 0;

            foreach ($products as $product) {
                $quantity   = $product->pivot->quantity;
                $finalPrice = $product->pivot->final_price ?? $product->price;
                $totalPrice += $finalPrice * $quantity;

                // Verificar si hay suficiente stock
                if ($product->stock < $quantity) {
                    DB::rollBack();
                    return Controller::response(400, true, "Not enough stock for product {$product->product_name}, available: {$product->stock}");
                }

                // Reducir el stock disponible
                $product->decrement('stock', $quantity);

                // Actualizar precio final del producto en products_carts
                $currentCart->products()
                    ->updateExistingPivot($product->id_product, ['final_price' => $finalPrice]);
            }

            // Actualizar estado del carrito a 2 (comprado) y registrar el total
            $currentCart->update([
                'status'      => 2,
                'total_price' => $totalPrice,
                'total_items' => $products->sum('pivot.quantity'),
                'bought_at'   => now(),
            ]);

            DB::commit();

            return Controller::response(201, false, 'Purchase completed successfully', [
                'id_cart'     => $currentCart->id_cart,
                'total_price' => $totalPrice,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return Controller::response(500, true, 'An error occurred while processing the purchase');
        }
    }

}
