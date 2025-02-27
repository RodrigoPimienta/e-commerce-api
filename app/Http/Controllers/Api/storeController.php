<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class storeController extends Controller implements HasMiddleware
{

    private $colums = [
        'id_store',
        'id_company',
        'name',
        'address',
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
        $stores      = $userCompany->load('stores');
        return Controller::response(200, false, $message = 'Store list', $stores);
    }

    public function allSellsBySeller(Request $request)
    {
        $userCompany = $request->user()->companies()->first();
    
        $stores = $userCompany->stores()->get();
    
        $stores = $stores->map(function ($store) {
            $store->productsCarts = $store->productsCarts($store->id_store);
            
            $store->sells = $store->formatSells();
            $store->makeHidden('productsCarts');
            return $store;
        });
    
        return Controller::response(200, false, $message = 'Stores sells', $stores);
    }
    

    public function allSellsByStore(Request $request, int $id)
    {
        $userCompany = $request->user()->companies()->first();
    
        $store = Store::where([
            'id_store'   => $id,
            'id_company' => $userCompany->id_company,
        ])->first($this->colums);
    
        if (! $store) {
            return Controller::response(404, true, $message = 'Store not found');
        }
    
        // Obtener los productos del carrito y luego formatearlos
        $store->productsCarts = $store->productsCarts($id);
        $store->sells = $store->formatSells();
        $store->makeHidden('productsCarts');    
        return Controller::response(200, false, $message = 'Store sells', $store);
    }
    

    public function store(Request $request): object
    {
        $userCompany = $request->user()->companies()->first();
        $request     = (object) $request->validate([
            'name'    => 'required|string',
            'address' => 'required|string',
        ]);

        $store = Store::create([
            'name'       => $request->name,
            'address'    => $request->address,
            'id_company' => $userCompany->id_company,
        ]);

        if (! $store) {
            return Controller::response(500, true, $message = 'Store not created');
        }

        return Controller::response(201, false, $message = 'Store created', $store);
    }

    public function show(Request $request, int $id): object
    {

        $userCompany = $request->user()->companies()->first();

        $store = Store::where([
            'id_store'   => $id,
            'id_company' => $userCompany->id_company,
        ])->first($this->colums);

        if (! $store) {
            return Controller::response(404, true, $message = 'Store not found');
        }

        $store->load('products');

        return Controller::response(200, false, $message = 'Store', $store);
    }

    public function update(Request $request, int $id): object
    {
        $userCompany = $request->user()->companies()->first();

        $store = Store::where([
            'id_store'   => $id,
            'id_company' => $userCompany->id_company,
        ])->first($this->colums);

        if (! $store) {
            return Controller::response(404, true, $message = 'Store not found');
        }

        $request = (object) $request->validate([
            'name'    => 'required|string',
            'address' => 'required|string',
        ]);

        $store->name    = $request->name;
        $store->address = $request->address;

        $store->save();

        return Controller::response(200, true, $message = 'Store updated', $store);
    }

    public function updateStatus(Request $request, int $id): object
    {
        $userCompany = $request->user()->companies()->first();
        $store       = Store::where([
            'id_store'   => $id,
            'id_company' => $userCompany->id_company,
        ])->first($this->colums);

        if (! $store) {
            return Controller::response(404, true, $message = 'Store not found');
        }

        $request = (object) $request->validate([
            'status' => 'required|integer|in:0,1',
        ]);

        $store->status = $request->status;

        $store->save();

        return Controller::response(200, true, $message = 'Store status updated', $store);
    }
}
