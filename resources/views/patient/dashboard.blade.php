<x-app-layout>
    <x-slot name="header">
        <h1 class="text-3xl font-semibold text-heading">
            {{ $greeting }}, {{ Auth::user()->displayName() }}.
        </h1>
        <p class="mt-2 text-body max-w-xl">
            Find a doctor and book a time that works for you. Your appointments and
            records live here too.
        </p>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left Column: Primary Content -->
            <div class="lg:col-span-8 flex flex-col gap-6">
                
                <!-- Next Appointment Card -->
                @if($nextAppointment)
                    <div class="rounded-2xl bg-white border border-line p-6 sm:p-8 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="text-sm font-semibold text-primary uppercase tracking-wider mb-1">Your next appointment</div>
                            <h2 class="text-xl font-semibold text-heading">Dr. {{ $nextAppointment->doctor->user->displayName() }}</h2>
                            <p class="text-body">{{ $nextAppointment->doctor->specialization->display_name ?? '' }}</p>
                            <div class="mt-3 flex items-center gap-2 text-sm text-heading bg-canvas inline-flex px-3 py-1.5 rounded-lg border border-line/50">
                                <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>{{ \Carbon\Carbon::parse($nextAppointment->appointment_dt)->format('D M j, g:i A') }}</span>
                            </div>
                        </div>
                        <div class="mt-6">
                            <a href="{{ route('patient.appointments.index') }}" class="inline-flex items-center justify-center rounded-xl bg-white border border-line px-6 py-3 text-base font-medium text-heading shadow-sm transition-all hover:bg-canvas active:scale-95">
                                View details
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Book an Appointment Card (Primary CTA) -->
                <div class="rounded-2xl bg-white border border-line p-8 shadow-sm relative overflow-hidden flex-1 flex flex-col justify-center">
                    <div class="relative z-10 max-w-lg">
                        <h2 class="text-xl font-semibold text-heading">Book a new appointment</h2>
                        <p class="mt-2 text-body">
                            Choose a doctor, see their open times, and confirm in a few taps.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('patient.booking.doctors') }}"
                               class="inline-flex items-center justify-center rounded-xl bg-primary px-6 py-3 text-base font-medium text-white shadow-sm transition-all duration-200 hover:bg-primary-dark active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                                Find a doctor
                            </a>
                        </div>
                    </div>
                    <!-- Decorative background element for the primary card -->
                    <div class="absolute right-0 bottom-0 opacity-5 pointer-events-none transform translate-x-1/4 translate-y-1/4">
                        <svg class="w-64 h-64 text-primary" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm4.59-12.42L10 14.17l-2.59-2.58L6 13l4 4 8-8z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sidebar & Illustration -->
            <div class="lg:col-span-4 flex flex-col justify-end">
                <!-- Illustration -->
                <div class="hidden lg:flex justify-center items-end -mb-1 relative z-0">
                    <img src="{{ asset('images/doctor.png') }}" alt="Medical Illustration" class="w-full max-w-[260px] h-auto drop-shadow-md">
                </div>

                <!-- Secondary Actions -->
                <div class="flex flex-col gap-6 relative z-10">
                    <a href="{{ route('patient.appointments.index') }}" class="group flex items-center justify-between rounded-xl px-4 py-3 text-body bg-white border border-line shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow hover:border-primary/30">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 text-primary">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <div class="font-medium text-sm text-heading">My appointments</div>
                                <div class="mt-0.5 text-xs text-body">
                                    @if($appointmentsCount > 0)
                                        {{ $appointmentsCount }} total {{ Str::plural('appointment', $appointmentsCount) }}
                                    @else
                                        No upcoming visits yet
                                    @endif
                                </div>
                            </div>
                        </div>
                        <svg class="h-4 w-4 text-gray-300 transition-colors group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    
                    <a href="{{ route('patient.records.index') }}" class="group flex items-center justify-between rounded-xl px-4 py-3 text-body bg-white border border-line shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow hover:border-primary/30">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 text-primary">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <div class="font-medium text-sm text-heading">My records</div>
                                <div class="mt-0.5 text-xs text-body">
                                    @if($recordsCount > 0)
                                        {{ $recordsCount }} medical {{ Str::plural('record', $recordsCount) }}
                                    @else
                                        No records yet
                                    @endif
                                </div>
                            </div>
                        </div>
                        <svg class="h-4 w-4 text-gray-300 transition-colors group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
