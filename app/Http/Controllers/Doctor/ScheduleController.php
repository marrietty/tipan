<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Models\Schedule;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * The doctor's upcoming slots, grouped by date for a scannable list.
     */
    public function index(Request $request): View
    {
        $doctor = $request->user()->doctor;

        $slots = $doctor->schedules()
            ->where('available_date', '>=', Carbon::today())
            ->orderBy('available_date')
            ->orderBy('slot_start')
            ->get()
            ->groupBy(fn (Schedule $slot) => $slot->available_date->toDateString());

        return view('doctor.schedule.index', [
            'slotsByDate' => $slots,
        ]);
    }

    /**
     * The create-slot form.
     */
    public function create(): View
    {
        return view('doctor.schedule.create');
    }

    /**
     * Generate every slot in the chosen window for the authenticated doctor.
     * The window is expanded into back-to-back slots of the chosen duration;
     * any that collide with the doctor's existing slots (same date and start)
     * are skipped rather than failing the batch, so re-submitting an overlapping
     * window safely tops up the gaps. The summary reports added and skipped.
     */
    public function store(StoreScheduleRequest $request): RedirectResponse
    {
        $doctor = $request->user()->doctor;
        $date = $request->input('available_date');

        $candidates = $request->generatedSlots();

        // Existing start times for this doctor on this date, used to skip clashes.
        $taken = $doctor->schedules()
            ->where('available_date', $date)
            ->pluck('slot_start')
            ->map(fn ($t) => Carbon::parse($t)->format('H:i'))
            ->all();

        $rows = [];
        $skipped = 0;
        foreach ($candidates as $slot) {
            if (in_array($slot['slot_start'], $taken, true)) {
                $skipped++;
                continue;
            }

            // Guard against duplicate starts within this same batch too.
            $taken[] = $slot['slot_start'];
            $rows[] = [
                'id' => (string) Str::uuid(),
                'doctor_id' => $doctor->id,
                'available_date' => $slot['available_date'],
                'slot_start' => $slot['slot_start'],
                'slot_end' => $slot['slot_end'],
                'is_booked' => false,
            ];
        }

        if ($rows !== []) {
            Schedule::insert($rows);
        }

        $added = count($rows);

        return redirect()
            ->route('doctor.schedule.index')
            ->with('status', $this->summaryMessage($added, $skipped));
    }

    /**
     * A plain-language summary of a bulk slot creation.
     */
    private function summaryMessage(int $added, int $skipped): string
    {
        if ($added === 0 && $skipped === 0) {
            return 'That window is shorter than one slot, so nothing was added.';
        }

        if ($added === 0 && $skipped > 0) {
            return 'No new slots added; all of those times already existed.';
        }

        $message = $added === 1 ? '1 slot added.' : "{$added} slots added.";

        if ($skipped > 0) {
            $message .= " {$skipped} skipped (already existed).";
        }

        return $message;
    }

    /**
     * Delete an unbooked slot the doctor owns. The policy enforces both
     * ownership and the unbooked condition; the RESTRICT on a booked slot is
     * handled as a defensive backstop with a clear message, never a raw error.
     */
    public function destroy(Request $request, Schedule $schedule): RedirectResponse
    {
        $this->authorize('delete', $schedule);

        try {
            $schedule->delete();
        } catch (QueryException) {
            return redirect()
                ->route('doctor.schedule.index')
                ->with('error', 'That slot is booked and cannot be removed.');
        }

        return redirect()
            ->route('doctor.schedule.index')
            ->with('status', 'Slot removed.');
    }
}
