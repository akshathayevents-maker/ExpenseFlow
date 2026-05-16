@php
    $role = auth()->user()->role ?? 'employee';
    return redirect()->route($role . '.dashboard');
@endphp
