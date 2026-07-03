<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
            'role'   => $request->input('role'),
            'status' => $request->input('status'),
        ];

        $users = $this->userRepository->paginate(15, array_filter($filters));

        return view('admin.users.index', compact('users', 'filters'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'address'     => ['nullable', 'string', 'max:500'],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],
            'avatar'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'role'        => ['nullable', 'in:admin,customer'],
            'status'      => ['nullable', 'in:active,inactive,banned'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = $validated['role'] ?? User::ROLE_CUSTOMER;
        $validated['status'] = $validated['status'] ?? User::STATUS_ACTIVE;

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = $this->userRepository->create($validated);

        return redirect()->route('admin.users.show', $user)->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $user->load(['orders', 'reviews']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(Request $request, ?User $user = null): View
    {
        $user ??= $request->user();

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, ?User $user = null): RedirectResponse
    {
        $user ??= $request->user();

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone'       => ['nullable', 'string', 'max:50'],
            'address'     => ['nullable', 'string', 'max:500'],
            'password'    => ['nullable', 'string', 'min:8', 'confirmed'],
            'avatar'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'role'        => ['nullable', 'in:admin,customer'],
            'status'      => ['nullable', 'in:active,inactive,banned'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar && file_exists(storage_path('app/public/'.$user->avatar))) {
                unlink(storage_path('app/public/'.$user->avatar));
            }

            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $this->userRepository->update($user, $validated);

        return redirect()->route('admin.users.show', $user)->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $current = $request->user();

        if ($user->id === $current->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $this->userRepository->delete($user);

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
