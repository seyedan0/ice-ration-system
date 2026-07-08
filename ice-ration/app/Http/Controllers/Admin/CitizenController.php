<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Citizen;
use App\Models\Station;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CitizenController extends Controller
{
    public function index(Request $request)
    {
        $citizens = Citizen::query()
            ->with('preferredStation')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($q2) use ($search) {
                    $q2->where('full_name', 'like', "%{$search}%")
                        ->orWhere('national_id', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('qr_code', $search);
                });
            })
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.citizens.index', compact('citizens'));
    }

    public function create()
    {
        $stations = Station::query()->active()->orderBy('name')->get();

        return view('admin.citizens.form', ['citizen' => new Citizen(), 'stations' => $stations]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['qr_code'] = (string) Str::uuid();

        $citizen = Citizen::create($data);

        return redirect()->route('admin.citizens.card', $citizen)->with('status', 'Citizen registered successfully.');
    }

    public function edit(Citizen $citizen)
    {
        $stations = Station::query()->active()->orderBy('name')->get();

        return view('admin.citizens.form', compact('citizen', 'stations'));
    }

    public function update(Request $request, Citizen $citizen): RedirectResponse
    {
        $data = $this->validateData($request, $citizen);

        $citizen->update($data);

        return redirect()->route('admin.citizens.index')->with('status', 'Citizen updated successfully.');
    }

    public function destroy(Citizen $citizen): RedirectResponse
    {
        $citizen->update(['is_active' => false]);

        return redirect()->route('admin.citizens.index')->with('status', 'Citizen deactivated.');
    }

    public function toggle(Citizen $citizen): RedirectResponse
    {
        $citizen->update(['is_active' => ! $citizen->is_active]);

        return back()->with('status', 'Status updated.');
    }

    public function card(Citizen $citizen)
    {
        return view('admin.citizens.card', compact('citizen'));
    }

    public function qr(Citizen $citizen): Response
    {
        $svg = QrCode::format('svg')->size(280)->margin(1)->generate($citizen->qr_code);

        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }

    private function validateData(Request $request, ?Citizen $citizen = null): array
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:200'],
            'national_id' => ['required', 'string', 'max:20', Rule::unique('citizens', 'national_id')->ignore($citizen)],
            'mobile' => ['required', 'string', 'max:20', Rule::unique('citizens', 'mobile')->ignore($citizen)],
            'daily_ration' => ['required', 'integer', 'min:1', 'max:50'],
            'preferred_station_id' => ['required', 'exists:stations,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
