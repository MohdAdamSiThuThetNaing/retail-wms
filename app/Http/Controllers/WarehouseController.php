<?php
namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::query()
            ->withCount(['stocks', 'locations'])
            ->withSum('stocks', 'quantity');

        if ($request->filled('q')) {
            $search = $request->q;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $warehouses = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Warehouse::count(),
            'active' => Warehouse::where('is_active', true)->count(),
            'inactive' => Warehouse::where('is_active', false)->count(),
            'stock_units' => Warehouse::withSum('stocks', 'quantity')
                ->get()
                ->sum('stocks_sum_quantity'),
        ];

        return view('warehouses.index', compact('warehouses', 'stats'));
    }

    public function create()
    {
        return view('warehouses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:32', 'unique:warehouses,code'],
            'name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:150'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        Warehouse::create($data);

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load([
            'stocks.product',
            'locations' => function ($query) {
                $query->with('children')->orderBy('code');
            },
        ]);

        $stockSummary = [
            'product_types' => $warehouse->stocks()->count(),
            'stock_units' => $warehouse->stocks()->sum('quantity'),
            'low_stock' => $warehouse->stocks()
                ->whereColumn('quantity', '<=', 'reorder_level')
                ->count(),
            'locations' => $warehouse->locations()->count(),
        ];

        return view('warehouses.show', compact(
            'warehouse',
            'stockSummary'
        ));
    }

    public function edit(Warehouse $warehouse)
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('warehouses', 'code')->ignore($warehouse->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:150'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $warehouse->update($data);

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse)
    {
        if (
            $warehouse->stocks()->exists() ||
            $warehouse->movements()->exists() ||
            $warehouse->locations()->exists()
        ) {
            return back()->with(
                'error',
                'This warehouse has inventory, movements, or locations. Deactivate it instead of deleting it.'
            );
        }

        $warehouse->delete();

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }
}
