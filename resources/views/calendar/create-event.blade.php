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
        @elseif(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if(isset($events) && count($events))
            @php
                // Ambil semua mapping untuk guru yang aktif sebagai collection dengan key berdasarkan teacher_event_id
                $mappings = \App\Models\EventMapping::where('teacher_id', $teacher->id)
                              ->get()
                              ->keyBy('teacher_event_id');
            @endphp
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
                            <td>{{ $event->summary ?? '-' }}</td>
                            <td>
                                {{ $event->formatted_start ?? '-' }}
                            </td>
                            <td>
                                {{ $event->formatted_end ?? '-' }}
                            </td>
                            <td>
                                @if(isset($mappings[$event->id]) && $mappings[$event->id]->student_id == auth()->user()->id)
                                    <form action="{{ route('student-teacher.deleteEvent', $mappings[$event->id]->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus event ini?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                @else
                                    <span class="text-muted">Tidak dapat dihapus</span>
                                @endif
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
