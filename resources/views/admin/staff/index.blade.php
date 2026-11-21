@extends('admin.layout')

@section('title', 'Staff')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Staff accounts</h1>
            <p>Create individual sign-ins for employees. Staff can access products, inventory, orders, and sales.</p>
        </div>
    </div>

    <section class="panel" style="padding: 18px; margin-bottom: 18px;">
        <h2 class="panel-title">Add a staff member</h2>
        <form method="POST" action="{{ route('admin.staff.store') }}" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap:12px; margin-top:14px;">
            @csrf
            <div>
                <label for="name">Name</label>
                <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="255" style="display:block; width:100%; margin-top:6px; padding:9px; border:1px solid #302a3b; border-radius:6px; background:#14111b; color:#fff;">
                @error('name') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required style="display:block; width:100%; margin-top:6px; padding:9px; border:1px solid #302a3b; border-radius:6px; background:#14111b; color:#fff;">
                @error('email') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password">Temporary password</label>
                <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required style="display:block; width:100%; margin-top:6px; padding:9px; border:1px solid #302a3b; border-radius:6px; background:#14111b; color:#fff;">
                @error('password') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required style="display:block; width:100%; margin-top:6px; padding:9px; border:1px solid #302a3b; border-radius:6px; background:#14111b; color:#fff;">
            </div>
            <div style="grid-column:1 / -1;">
                <button class="primary-button" type="submit">Create staff account</button>
            </div>
        </form>
    </section>

    <section class="panel" style="padding: 18px; margin-bottom: 18px;">
        <h2 class="panel-title">Add an admin account</h2>
        <p style="margin-top:6px;">Admin accounts can manage all store areas and staff accounts.</p>
        <form method="POST" action="{{ route('admin.staff.admin.store') }}" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap:12px; margin-top:14px;">
            @csrf
            <div>
                <label for="admin_name">Name</label>
                <input id="admin_name" name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="255" style="display:block; width:100%; margin-top:6px; padding:9px; border:1px solid #302a3b; border-radius:6px; background:#14111b; color:#fff;">
            </div>
            <div>
                <label for="admin_email">Email</label>
                <input id="admin_email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required style="display:block; width:100%; margin-top:6px; padding:9px; border:1px solid #302a3b; border-radius:6px; background:#14111b; color:#fff;">
            </div>
            <div>
                <label for="admin_password">Temporary password</label>
                <input id="admin_password" type="password" name="password" autocomplete="new-password" minlength="8" required style="display:block; width:100%; margin-top:6px; padding:9px; border:1px solid #302a3b; border-radius:6px; background:#14111b; color:#fff;">
            </div>
            <div>
                <label for="admin_password_confirmation">Confirm password</label>
                <input id="admin_password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required style="display:block; width:100%; margin-top:6px; padding:9px; border:1px solid #302a3b; border-radius:6px; background:#14111b; color:#fff;">
            </div>
            <div style="grid-column:1 / -1;">
                <button class="primary-button" type="submit">Create admin account</button>
            </div>
        </form>
    </section>

    <section class="panel" style="margin-bottom:18px;">
        <div class="table-wrap">
            @if ($adminMembers->isEmpty())
                <div class="empty-state">No admin accounts yet.</div>
            @else
                <table class="data-table">
                    <thead>
                        <tr><th>NAME</th><th>EMAIL</th><th>ACCESS</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($adminMembers as $admin)
                            <tr>
                                <td>{{ $admin->name }}</td>
                                <td>{{ $admin->email }}</td>
                                <td>Administrator / owner</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>

    <section class="panel">
        <div class="table-wrap">
            @if ($staffMembers->isEmpty())
                <div class="empty-state">No staff accounts yet.</div>
            @else
                <table class="data-table">
                    <thead>
                        <tr><th>NAME</th><th>EMAIL</th><th>ACCESS</th><th>ACTION</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($staffMembers as $staff)
                            <tr>
                                <td>{{ $staff->name }}</td>
                                <td>{{ $staff->email }}</td>
                                <td>Products, inventory, orders, sales</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.staff.destroy', $staff) }}" onsubmit="return confirm('Remove this staff account?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="table-action" type="submit">Remove access</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>
@endsection
