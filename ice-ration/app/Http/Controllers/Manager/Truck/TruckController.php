<?php

namespace App\Http\Controllers\Manager\Truck;

use App\Http\Controllers\Controller;
use App\Models\Truck;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TruckController extends Controller
{
    public function index(Request $request)
    {
        $trucks = Truck::query()
            ->forManager($request->user()->id)
            ->orderBy('plate_number')
            ->get();

        return view('manager.trucks.index', compact('trucks'));
    }

    public function create()
    {
        return view('manager.trucks.form', ['truck' => new Truck()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plate_number' => ['required', 'string', 'max:50', 'unique:trucks,plate_number'],
            'capacity' => ['required', 'integer', 'min:100', 'max:10000'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $data['manager_id'] = $request->user()->id;
        $data['is_active'] = $request->boolean('is_active', true);

        Truck::create($data);

        return redirect()->route('manager.trucks.index')->with('status', 'Truck added successfully.');
    }

    public function edit(Truck $truck)
    {
        $this->authorize('owns', $truck);

        return view('manager.trucks.form', compact('truck'));
    }

    public function update(Request $request, Truck $truck): RedirectResponse
    {
        $this->authorize('owns', $truck);

        $data = $request->validate([
            'plate_number' => ['required', 'string', 'max:50', 'unique:trucks,plate_number,'.$truck->id],
            'capacity' => ['required', 'integer', 'min:100', 'max:10000'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $truck->update($data);

        return redirect()->route('manager.trucks.index')->with('status', 'Truck updated successfully.');
    }

    public function destroy(Truck $truck): RedirectResponse
    {
        $this->authorize('owns', $truck);

        $truck->update(['is_active' => false]);

        return redirect()->route('manager.trucks.index')->with('status', 'Truck deactivated.');
    }
}
