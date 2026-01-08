<?php

namespace App\Http\Controllers\Admin\Sales\POS;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function create()
    {
        if (request()->has('customer')) {
            $customer = Customer::where('company_id', request()->company->id)->where('uuid', request()->customer)->first();

            return view('theme.adminlte.pos.orders.step-2', compact('customer'));

        }

        return view('theme.adminlte.pos.orders.create');
    }

    public function loadCustomers(Request $request)
    {
        $customers = Customer::where('company_id', $request->company->id)
            ->get();

        $data['customers'] = $customers;

        $response['view'] = view('theme.adminlte.components.customer-cards', $data)->render();

        return response()->json($response);
    }
}
