<?php
namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseLocationController extends Controller
{
    public function index(Warehouse $warehouse)
    {
        $locations = $warehouse->locations()
            ->with('parent', 'children')
            ->orderBy('code')
            ->paginate(20);

        $parentLocations = $warehouse->locations()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $types = [
            'zone' => 'Zone',
            'aisle' => 'Aisle',
            'rack' => 'Rack',
            'shelf' => 'Shelf',
            'bin' => 'Bin',
        ];

        return view('warehouses.locations.index', compact(
            'warehouse',
            'locations',
            'parentLocations',
            'types'
        ));
    }

    public function store(Request $request, Warehouse $warehouse)
    {
        $data = $request->validate([
            'parent_id' => [
                'nullable',
                Rule::exists('warehouse_locations', 'id')
                    ->where('warehouse_id', $warehouse->id),
            ],
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('warehouse_locations', 'code')
                    ->where('warehouse_id', $warehouse->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in([
                'zone', 'aisle', 'rack', 'shelf', 'bin',
            ])],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($request->filled('parent_id')) {
            $parent = WarehouseLocation::findOrFail($data['parent_id']);

            $levels = [
                'zone' => 0,
                'aisle' => 1,
                'rack' => 2,
                'shelf' => 3,
                'bin' => 4,
            ];

            if (
                $levels[$data['type']] !==
                $levels[$parent->type] + 1
            ) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'parent_id' => 'Choose a parent one level above this location type.',
                    ]);
            }
        } elseif ($data['type'] !== 'zone') {
            return back()
                ->withInput()
                ->withErrors([
                    'parent_id' => 'Only a Zone can be created without a parent.',
                ]);
        }

        $warehouse->locations()->create($data);

        return back()->with('success', 'Location created successfully.');
    }

    public function update(Request $request, WarehouseLocation $location)
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('warehouse_locations', 'code')
                    ->where('warehouse_id', $location->warehouse_id)
                    ->ignore($location->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $location->update($data);

        return back()->with('success', 'Location updated successfully.');
    }

    public function destroy(WarehouseLocation $location)
    {
        if ($location->children()->exists()) {
            return back()->with(
                'error',
                'This location contains child locations. Remove or reassign them first.'
            );
        }

        $location->delete();

        return back()->with('success', 'Location deleted successfully.');
    }
}
