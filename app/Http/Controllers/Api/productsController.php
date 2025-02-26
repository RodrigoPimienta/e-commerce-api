<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductStore;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class productsController extends Controller implements HasMiddleware
{

    private $columsStore = [
        'id_store',
        'id_company',
        'name',
        'address',
        'status',
    ];

    private $colums = [
        'id_product',
        'id_store',
        'name',
        'description',
        'price',
        'stock',
        'status',
    ];
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum', except: ['']),
        ];
    }

    public function allBySeller(Request $request): object
    {
        $userCompany = $request->user()->companies()->first();
        $stores      = $userCompany->load('stores')->load('stores.products');
        return Controller::response(200, false, $message = 'Product list', $stores);
    }

    public function allByStore(Request $request, int $id): object
    {
        $userCompany = $request->user()->companies()->first();

        $store = Store::where([
            'id_store'   => $id,
            'id_company' => $userCompany->id_company,
        ])->first($this->columsStore);

        if (! $store) {
            return Controller::response(404, true, $message = 'Store not found');
        }

        $store->load('products');
        return Controller::response(200, false, $message = 'Product list', $store);
    }

    public function store(Request $request): object
    {
        $userCompany = $request->user()->companies()->first();
        $request     = (object) $request->validate([
            'id_store'    => 'required|int|exists:stores,id_store',
            'name'        => 'required|string',
            'description' => 'required|string',
            'price'       => 'required|int|min:0',
            'stock'       => 'required|int',
        ]);

        // check if store belongs to the company
        $store = Store::where([
            'id_store'   => $request->id_store,
            'id_company' => $userCompany->id_company,
        ])->first();

        if (! $store) {
            return Controller::response(404, true, $message = 'Store not found');
        }

        $product = ProductStore::create([
            'id_store'    => $request->id_store,
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'stock'       => $request->stock,
        ]);

        if (! $product) {
            return Controller::response(500, true, $message = 'Product not created');
        }

        return Controller::response(201, false, $message = 'Product created', $product);
    }

    public function show(Request $request, int $id): object
    {
        $userCompany = $request->user()->companies()->first();
        $product = ProductStore::find($id,$this->colums);

        if (! $product) {
            return Controller::response(404, true, $message = 'Product not found');
        }
        // check if store belongs to the company
        $store = Store::where([
            'id_store'   => $product->id_store,
            'id_company' => $userCompany->id_company,
        ])->first();

        if (! $store) {
            return Controller::response(404, true, $message = 'Store not found');
        }

        return Controller::response(200,false, $message = 'Product', $product);
    }

    public function update(Request $request, $id): object
    {

        $userCompany = $request->user()->companies()->first();
        $request     = (object) $request->validate([
            'name'        => 'required|string',
            'description' => 'required|string',
            'price'       => 'required|int|min:0',
            'stock'       => 'required|int',
        ]);

        $product = ProductStore::find($id,$this->colums);

        if (! $product) {
            return Controller::response(404, true, $message = 'Product not found');
        }
        // check if store belongs to the company
        $store = Store::where([
            'id_store'   => $product->id_store,
            'id_company' => $userCompany->id_company,
        ])->first();

        if (! $store) {
            return Controller::response(404, true, $message = 'Store not found');
        }

        $product->name        = $request->name;
        $product->description = $request->description;
        $product->price       = $request->price;
        $product->stock       = $request->stock;
        $product->save();

        return Controller::response(200, true, $message = 'Product updated', $product);
    }

    public function updateStatus(Request $request, $id): object
    {
        $userCompany = $request->user()->companies()->first();
        $request     = (object) $request->validate([
            'status' => 'required|integer|in:0,1',
        ]);

        $product = ProductStore::find($id, $this->colums);

        if (! $product) {
            return Controller::response(404, true, $message = 'Product not found');
        }
        // check if store belongs to the company
        $store = Store::where([
            'id_store'   => $product->id_store,
            'id_company' => $userCompany->id_company,
        ])->first();

        if (! $store) {
            return Controller::response(404, true, $message = 'Store not found');
        }

        $product->status = $request->status;
        $product->save();

        return Controller::response(200, true, $message = 'Product status updated', $product);
    }
}
