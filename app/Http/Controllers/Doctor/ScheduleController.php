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
     * Create one slot for the authenticated doctor. The Form Request mirrors
     * the slot_order and unique_doctor_slot constraints, so by here the input
     * is sound; is_booked starts false.
     */
    public function store(StoreScheduleRequest $request): RedirectResponse
    {
        Schedule::create([
            'id' => (string) Str::uuid(),
            ...$request->slotAttributes(),
        ]);

        return redirect()
            ->route('doctor.schedule.index')
            ->with('status', 'Slot added.');
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
