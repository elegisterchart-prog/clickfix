@extends('layouts.app')

@section('content')
<div class="card">
    <h2>จัดการสมาชิก</h2>
    <p>กำหนดบทบาทสมาชิก (user / admin)</p>

    <div style="margin-top:12px; overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; min-width:720px;">
            <thead>
                <tr style="text-align:left; border-bottom:1px solid #e2e8f0;">
                    <th>ชื่อ</th>
                    <th>อีเมล</th>
                    <th>บทบาท</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{{ $u->role ?? 'user' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.users.role', ['id' => $u->id]) }}" style="display:inline-flex; gap:8px; align-items:center;">
                            @csrf
                            <select name="role" style="padding:8px 10px; border-radius:8px; border:1px solid #cbd5e1;">
                                <option value="user" {{ ($u->role ?? 'user') === 'user' ? 'selected' : '' }}>user</option>
                                <option value="admin" {{ ($u->role ?? 'user') === 'admin' ? 'selected' : '' }}>admin</option>
                            </select>
                            <button class="button-primary" type="submit">อัปเดต</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:12px;">{{ $users->links() }}</div>
    </div>
</div>
@endsection
