<?php 
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GoogleCalendarApi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            $teacherService      = GoogleCalendarApi::getCalendarService($teacher);
            $teacherEventsResult = $teacherService->events->listEvents('primary');
            $events              = $teacherEventsResult->getItems();
        }

        // Return view dengan variabel $events (berisi daftar event guru)
        return view('calendar.create-event', compact('events'));
    }

    /**
     * Membuat event di Google Calendar murid dan juga di kalender guru.
     * Setelah event dibuat, mengambil daftar event terbaru dari kalender guru.
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

        // Buat event di kalender murid
        $studentService   = GoogleCalendarApi::getCalendarService($student);
        $studentEventData = new \Google\Service\Calendar\Event([
            'summary'     => $request->input('summary', 'Event Murid'),
            'description' => $request->input('description', 'Dibuat oleh murid'),
            'start'       => [
                'dateTime' => Carbon::parse($request->start)->toAtomString(),
                'timeZone' => 'Asia/Jakarta',
            ],
            'end'         => [
                'dateTime' => Carbon::parse($request->end)->toAtomString(),
                'timeZone' => 'Asia/Jakarta',
            ],
        ]);
        $studentService->events->insert('primary', $studentEventData);

                                                            // Buat event di kalender guru (pengajar)
        $teacher = User::where('role', 'teacher')->first(); // ambil guru pertama
        if ($teacher && $teacher->google_token) {
            $teacherService   = GoogleCalendarApi::getCalendarService($teacher);
            $teacherEventData = new \Google\Service\Calendar\Event([
                'summary'     => $request->input('summary', 'Event Juga di Pengajar'),
                'description' => 'Event ini juga masuk ke kalender pengajar',
                'start'       => [
                    'dateTime' => Carbon::parse($request->start)->toAtomString(),
                    'timeZone' => 'Asia/Jakarta',
                ],
                'end'         => [
                    'dateTime' => Carbon::parse($request->end)->toAtomString(),
                    'timeZone' => 'Asia/Jakarta',
                ],
            ]);
            $teacherService->events->insert('primary', $teacherEventData);
        }

        // Ambil daftar event terbaru dari kalender guru untuk ditampilkan ke semua murid
        $events = collect();
        if ($teacher && $teacher->google_token) {
            $teacherService      = GoogleCalendarApi::getCalendarService($teacher);
            $teacherEventsResult = $teacherService->events->listEvents('primary');
            $events              = $teacherEventsResult->getItems();
        }

        return view('calendar.create-event', compact('events'))
            ->with('success', 'Event tersimpan di kalender murid dan guru!');
    }
}
?>



