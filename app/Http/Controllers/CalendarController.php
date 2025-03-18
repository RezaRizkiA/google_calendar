<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GoogleCalendarApi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{
    /**
     * Menampilkan form pembuatan event dan daftar event dari kalender guru.
     */
    public function showCreateForm()
    {
        // Pastikan user sudah login
        $student = Auth::user();
        if (! $student) {
            return redirect()->route('google.login')
                ->with('error', 'Harus login dulu dengan Google!');
        }

        // Ambil event dari kalender guru (pengajar)
        $teacher = User::where('role', 'teacher')->first();
        $events  = collect(); // default: koleksi kosong
        if ($teacher && $teacher->google_token) {
            try {
                $teacherService      = GoogleCalendarApi::getCalendarService($teacher);
                $teacherEventsResult = $teacherService->events->listEvents('primary');
                $events              = $teacherEventsResult->getItems();

                // Untuk setiap event, tambahkan properti formatted_start dan formatted_end
                foreach ($events as $event) {
                    if (isset($event->start->dateTime)) {
                        $event->formatted_start = \Carbon\Carbon::parse($event->start->dateTime)
                            ->format('d M Y, H:i');
                    } elseif (isset($event->start->date)) {
                        $event->formatted_start = \Carbon\Carbon::parse($event->start->date)
                            ->format('d M Y');
                    } else {
                        $event->formatted_start = '-';
                    }
                    if (isset($event->end->dateTime)) {
                        $event->formatted_end = \Carbon\Carbon::parse($event->end->dateTime)
                            ->format('d M Y, H:i');
                    } elseif (isset($event->end->date)) {
                        $event->formatted_end = \Carbon\Carbon::parse($event->end->date)
                            ->format('d M Y');
                    } else {
                        $event->formatted_end = '-';
                    }
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Gagal mengambil event: ' . $e->getMessage());
            }
        }

        // Kirim $teacher dan $events yang sudah diformat ke view
        return view('calendar.create-event', compact('events', 'teacher'));
    }

    /**
     * Membuat event di Google Calendar murid dan juga di kalender guru.
     * Setelah event dibuat, menyimpan mapping ID event ke database.
     */
    public function createEventForStudentAndTeacher(Request $request)
    {
        // Pastikan user (murid) sudah login
        $student = auth()->user();
        if (! $student) {
            return redirect()->route('google.login')->with('error', 'Harus login dulu!');
        }

        // Validasi input event
        $request->validate([
            'start'       => 'required|date',
            'end'         => 'required|date|after_or_equal:start',
            'summary'     => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        // Debug: Log token murid sebelum pembuatan event
        \Log::info('Token murid saat membuat event', [
            'student_email' => $student->email,
            'google_token'  => $student->google_token,
        ]);

        // Buat event di kalender murid
        try {
            $studentService   = GoogleCalendarApi::getCalendarService($student);
            $studentEventData = new \Google\Service\Calendar\Event([
                'summary'     => $request->input('summary', 'Event Murid'),
                'description' => $request->input('description', 'Dibuat oleh murid'),
                'start'       => [
                    'dateTime' => Carbon::parse($request->start, 'Asia/Jakarta')->toAtomString(),
                    'timeZone' => 'Asia/Jakarta',
                ],
                'end'         => [
                    'dateTime' => Carbon::parse($request->end, 'Asia/Jakarta')->toAtomString(),
                    'timeZone' => 'Asia/Jakarta',
                ],
            ]);
            $studentEvent = $studentService->events->insert('primary', $studentEventData);
            \Log::info('Event murid berhasil dibuat', ['student_event_id' => $studentEvent->getId()]);
        } catch (\Exception $e) {
            \Log::error('Gagal membuat event di kalender murid', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal membuat event di kalender murid: ' . $e->getMessage());
        }

        // Ambil data guru
        $teacher = User::where('role', 'teacher')->first();
        if (! $teacher || ! $teacher->google_token) {
            return redirect()->back()->with('error', 'Akun guru tidak ditemukan atau tidak terhubung dengan Google.');
        }

        // Debug: Log token guru saat membuat event
        \Log::info('Token guru saat membuat event', [
            'teacher_email' => $teacher->email,
            'google_token'  => $teacher->google_token,
        ]);

        // Buat event di kalender guru
        try {
            $teacherService   = GoogleCalendarApi::getCalendarService($teacher);
            $teacherEventData = new \Google\Service\Calendar\Event([
                'summary'     => $request->input('summary', 'Event Juga di Pengajar'),
                'description' => $request->input('description', 'Dibuat oleh murid'),
                'start'       => [
                    'dateTime' => Carbon::parse($request->start, 'Asia/Jakarta')->toAtomString(),
                    'timeZone' => 'Asia/Jakarta',
                ],
                'end'         => [
                    'dateTime' => Carbon::parse($request->end, 'Asia/Jakarta')->toAtomString(),
                    'timeZone' => 'Asia/Jakarta',
                ],
            ]);
            $teacherEvent = $teacherService->events->insert('primary', $teacherEventData);
            \Log::info('Event guru berhasil dibuat', ['teacher_event_id' => $teacherEvent->getId()]);
        } catch (\Exception $e) {
            \Log::error('Gagal membuat event di kalender guru', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal membuat event di kalender guru: ' . $e->getMessage());
        }

        // Debug: Pastikan kedua event sudah memiliki ID
        if (! $teacherEvent || ! $teacherEvent->getId() || ! $studentEvent || ! $studentEvent->getId()) {
            \Log::error('ID event tidak valid', [
                'teacher_event_id' => $teacherEvent ? $teacherEvent->getId() : null,
                'student_event_id' => $studentEvent ? $studentEvent->getId() : null,
            ]);
            return redirect()->back()->with('error', 'Gagal mendapatkan ID event dari Google Calendar.');
        }

        // Simpan mapping ID event ke database
        try {
            \App\Models\EventMapping::create([
                'teacher_event_id' => $teacherEvent->getId(),
                'student_event_id' => $studentEvent->getId(),
                'teacher_id'       => $teacher->id,
                'student_id'       => $student->id,
            ]);
            \Log::info('Mapping event tersimpan', [
                'teacher_event_id' => $teacherEvent->getId(),
                'student_event_id' => $studentEvent->getId(),
                'teacher_id'       => $teacher->id,
                'student_id'       => $student->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Gagal menyimpan mapping event', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal menyimpan mapping event: ' . $e->getMessage());
        }

        // Redirect (PRG Pattern)
        return redirect()->route('student-teacher.showCreateForm')
            ->with('success', 'Event tersimpan di kalender murid dan guru!');
    }

    /**
     * Menghapus event dari kalender guru dan murid berdasarkan mapping ID event.
     */
    public function deleteEvent($mappingId)
    {
        $mapping = \App\Models\EventMapping::find($mappingId);
        if (! $mapping) {
            return redirect()->back()->with('error', 'Mapping event tidak ditemukan.');
        }

        $student = auth()->user();
        if (! $student || ! $student->google_token) {
            return redirect()->back()->with('error', 'Akun murid tidak ditemukan atau tidak terhubung dengan Google.');
        }

        $teacher = \App\Models\User::find($mapping->teacher_id);
        if (! $teacher || ! $teacher->google_token) {
            return redirect()->back()->with('error', 'Akun guru tidak ditemukan atau tidak terhubung dengan Google.');
        }

        try {
            $teacherService = GoogleCalendarApi::getCalendarService($teacher);
            $teacherService->events->delete('primary', $mapping->teacher_event_id);

            $studentService = GoogleCalendarApi::getCalendarService($student);
            $studentService->events->delete('primary', $mapping->student_event_id);

            $mapping->delete();
            Log::info('Mapping dan event dihapus', [
                'mapping_id'       => $mappingId,
                'teacher_event_id' => $mapping->teacher_event_id,
                'student_event_id' => $mapping->student_event_id,
            ]);

            return redirect()->back()->with('success', 'Event berhasil dihapus dari kalender murid dan guru.');
        } catch (\Google_Service_Exception $e) {
            $errorResponse = json_decode($e->getMessage(), true);
            $errorMessage  = $errorResponse['error']['message'] ?? $e->getMessage();
            Log::error('Gagal menghapus event dari Google Calendar', ['error' => $errorMessage]);
            return redirect()->back()->with('error', 'Gagal menghapus event: ' . $errorMessage);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus event', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal menghapus event: ' . $e->getMessage());
        }
    }
}
