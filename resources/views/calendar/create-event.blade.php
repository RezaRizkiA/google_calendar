@extends('layouts.app')

@section('content')
<div class="row">
    <!-- Bagian Tabel Daftar Event dari Kalender Guru -->
    <div class="col-12">
        <h1>Daftar Event Guru</h1>
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(isset($events) && count($events))
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama Event</th>
                        <th>Waktu Mulai</th>
                        <th>Waktu Selesai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                        <tr>
                            <!-- Gunakan properti summary dari event, dan fallback '-' jika tidak ada -->
                            <td>{{ $event->summary ?? '-' }}</td>
                            <!-- Periksa apakah ada start.dateTime, jika tidak, gunakan start.date -->
                            <td>
                                {{ isset($event->start->dateTime) ? $event->start->dateTime : (isset($event->start->date) ? $event->start->date : '-') }}
                            </td>
                            <td>
                                {{ isset($event->end->dateTime) ? $event->end->dateTime : (isset($event->end->date) ? $event->end->date : '-') }}
                            </td>
                            <td>
                                {{-- Contoh tombol aksi; sesuaikan route dan aksi jika diperlukan --}}
                                <a href="#" class="btn btn-sm btn-primary">Edit</a>
                                <a href="#" class="btn btn-sm btn-danger">Delete</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Tidak ada event tersimpan di kalender guru.</p>
        @endif
    </div>

    <!-- Bagian Form Tambah Event Baru -->
    <div class="col-12 mt-4">
        <h2>Tambah Event Baru</h2>
        <form action="{{ route('student-teacher.createEvent') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="summary" class="form-label">Nama Event</label>
                <input type="text" name="summary" class="form-control" id="summary" value="{{ old('summary') }}" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea name="description" id="description" class="form-control">{{ old('description') }}</textarea>
            </div>

            <div class="mb-3">
                <label for="start" class="form-label">Waktu Mulai (DateTime)</label>
                <input type="datetime-local" name="start" class="form-control" id="start" required>
            </div>

            <div class="mb-3">
                <label for="end" class="form-label">Waktu Selesai (DateTime)</label>
                <input type="datetime-local" name="end" class="form-control" id="end" required>
            </div>

            <button type="submit" class="btn btn-success">Simpan</button>
        </form>
    </div>
</div>
@endsection

{{-- @extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @elseif(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h4>Form Event untuk Murid & Pengajar</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('student-teacher.createEvent') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="start" class="form-label">Start</label>
                        <input type="datetime-local" id="start" name="start" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="end" class="form-label">End</label>
                        <input type="datetime-local" id="end" name="end" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="summary" class="form-label">Summary</label>
                        <input type="text" id="summary" name="summary" class="form-control" placeholder="Judul Event">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3" placeholder="Deskripsi Event"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Buat Event di Murid & Pengajar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection --}}
