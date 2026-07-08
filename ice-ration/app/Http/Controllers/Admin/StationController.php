<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StationController extends Controller
{
    public function index(Request $request)
    {
        $stations = Station::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.stations.index', compact('stations'));
    }

    public function create()
    {
        return view('admin.stations.form', ['station' => new Station()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        Station::create($data);

        return redirect()->route('admin.stations.index')->with('status', 'Station created successfully.');
    }

    public function edit(Station $station)
    {
        return view('admin.stations.form', compact('station'));
    }

    public function update(Request $request, Station $station): RedirectResponse
    {
        $data = $this->validateData($request);

        $station->update($data);

        return redirect()->route('admin.stations.index')->with('status', 'Station updated successfully.');
    }

    public function destroy(Station $station): RedirectResponse
    {
        $station->update(['is_active' => false]);

        return redirect()->route('admin.stations.index')->with('status', 'Station deactivated.');
    }

    public function toggle(Station $station): RedirectResponse
    {
        $station->update(['is_active' => ! $station->is_active]);

        return back()->with('status', 'Station status updated.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
            'current_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
