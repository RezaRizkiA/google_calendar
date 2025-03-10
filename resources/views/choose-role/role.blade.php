@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Pilih Role Anda</h3>
            </div>
            <div class="card-body">
                <h5>Halo, {{ $user->name }}</h5>
                <p class="text-muted">Email: {{ $user->email }}</p>
                
                @if(session('info'))
                    <div class="alert alert-info">
                        {{ session('info') }}
                    </div>
                @endif

                <form action="{{ route('role.processRoleForm') }}" method="POST">
                    @csrf
                    <div class="form-check">
                        <input type="radio" id="role-student" name="role" value="student" class="form-check-input" required>
                        <label class="form-check-label" for="role-student">Saya Murid</label>
                    </div>

                    <div class="form-check">
                        <input type="radio" id="role-teacher" name="role" value="teacher" class="form-check-input">
                        <label class="form-check-label" for="role-teacher">Saya Pengajar</label>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">
                        Simpan Role
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
