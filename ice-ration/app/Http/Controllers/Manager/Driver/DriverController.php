<?php

namespace App\Http\Controllers\Manager\Driver;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $drivers = User::query()
            ->where('manager_id', $request->user()->id)
            ->where('role', \App\Models\User::ROLE_TRUCK_DRIVER)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('manager.driver.index', compact('drivers'));
    }

    public function create()
    {
        $trucks = Truck::query()->forManager(auth()->id())->active()->orderBy('plate_number')->get();
        return view('manager.driver.form', ['driver' => new User(), 'trucks' => $trucks]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['password'] = Hash::make($data['password']);
        $data['manager_id'] = $request->user()->id;
        $data['role'] = User::ROLE_TRUCK_DRIVER;

        User::create($data);

        return redirect()->route('manager.drivers.index')
            ->with('status', 'Driver account created successfully.');
    }

    public function edit(User $driver)
    {
        $this->ensureDriverBelongsToManager($driver);

        $trucks = Truck::query()->forManager(auth()->id())->active()->orderBy('plate_number')->get();
        return view('manager.driver.form', compact('driver', 'trucks'));
    }

    public function update(Request $request, User $driver): RedirectResponse
    {
        $this->ensureDriverBelongsToManager($driver);

        $data = $this->validateData($request, $driver);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Ensure the selected truck belongs to the manager if provided
        if (isset($data['truck_id'])) {
            $truck = Truck::where('id', $data['truck_id'])
                ->where('manager_id', $request->user()->id)
                ->first();
            
            if (! $truck) {
                $data['truck_id'] = null;
            }
        }

        $driver->update($data);

        return redirect()->route('manager.drivers.index')
            ->with('status', 'Driver account updated successfully.');
    }

    public function destroy(User $driver): RedirectResponse
    {
        $this->ensureDriverBelongsToManager($driver);

        $driver->update(['is_active' => false]);

        return back()->with('status', 'Driver account deactivated.');
    }

    public function toggle(User $driver): RedirectResponse
    {
        $this->ensureDriverBelongsToManager($driver);

        $driver->update(['is_active' => ! $driver->is_active]);

        return back()->with('status', 'Driver status updated.');
    }

    private function validateData(Request $request, ?User $driver = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'mobile' => ['required', 'string', 'max:20', Rule::unique('users', 'mobile')->ignore($driver)],
            'password' => [$driver ? 'nullable' : 'required', 'string', 'min:6'],
        ];

        $data = $request->validate($rules);

        return $data;
    }

    private function ensureDriverBelongsToManager(User $driver): void
    {
        if ($driver->manager_id !== auth()->id() || $driver->role !== \App\Models\User::ROLE_TRUCK_DRIVER) {
            abort(403, 'Unauthorized action.');
        }
    }
}