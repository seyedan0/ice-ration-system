<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->whereIn('role', [User::ROLE_STATION_AGENT, User::ROLE_TRUCK_DRIVER])
            ->with('station')
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $stations = Station::query()->active()->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'stations'));
    }

    public function create()
    {
        $stations = Station::query()->active()->orderBy('name')->get();

        return view('admin.users.form', ['user' => new User(), 'stations' => $stations]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('admin.users.index')->with('status', 'Staff member created successfully.');
    }

    public function edit(User $user)
    {
        $stations = Station::query()->active()->orderBy('name')->get();

        return view('admin.users.form', compact('user', 'stations'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validateData($request, $user);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('status', 'Staff member updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->update(['is_active' => false]);

        return redirect()->route('admin.users.index')->with('status', 'Staff member deactivated.');
    }

    public function toggle(User $user): RedirectResponse
    {
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', 'Status updated.');
    }

    private function validateData(Request $request, ?User $user = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'mobile' => ['required', 'string', 'max:20', Rule::unique('users', 'mobile')->ignore($user)],
            'role' => ['required', Rule::in([User::ROLE_STATION_AGENT, User::ROLE_TRUCK_DRIVER])],
            'station_id' => ['nullable', 'exists:stations,id', 'required_if:role,' . User::ROLE_STATION_AGENT],
            'is_active' => ['sometimes', 'boolean'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6'],
        ];

        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($data['role'] === User::ROLE_TRUCK_DRIVER) {
            $data['station_id'] = null;
        }

        return $data;
    }
}
