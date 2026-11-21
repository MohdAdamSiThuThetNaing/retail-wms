<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('q')) {
            $search = $request->q;

            $query->where(function ($q) use ($search) {
                $q->where('customer_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('customer_type', $request->type);
        }

        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $customers = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Customer::count(),
            'active' => Customer::where('is_active', true)->count(),
            'inactive' => Customer::where('is_active', false)->count(),
            'new_this_month' => Customer::whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])->count(),
        ];

        return view('customers.index', compact('customers', 'stats'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_code' => [
                'required', 'string', 'max:30',
                'unique:customers,customer_code',
            ],
            'customer_type' => [
                'required',
                Rule::in(['individual', 'retail', 'wholesale', 'corporate']),
            ],
            'name' => ['required', 'string', 'max:180'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:50'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'credit_limit' => ['required', 'numeric', 'min:0'],
            'payment_terms' => [
                'required',
                Rule::in(['cash', 'net_15', 'net_30', 'net_60']),
            ],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        Customer::create($data);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'customer_code' => [
                'required', 'string', 'max:30',
                Rule::unique('customers', 'customer_code')
                    ->ignore($customer->id),
            ],
            'customer_type' => [
                'required',
                Rule::in(['individual', 'retail', 'wholesale', 'corporate']),
            ],
            'name' => ['required', 'string', 'max:180'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:50'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'credit_limit' => ['required', 'numeric', 'min:0'],
            'payment_terms' => [
                'required',
                Rule::in(['cash', 'net_15', 'net_30', 'net_60']),
            ],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $customer->update($data);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->update(['is_active' => false]);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deactivated successfully.');
    }
}
