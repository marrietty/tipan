<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-medium tracking-wide text-gray-500">Your care</p>
        <h1 class="mt-1 text-3xl font-semibold text-gray-900">Medical records</h1>
        <p class="mt-2 text-gray-600 max-w-xl">
            A history of your visits &mdash; diagnoses, notes, and the medications
            you were prescribed. Most recent first.
        </p>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @forelse ($records as $record)
            <article class="rounded-2xl bg-white border border-gray-200/80 overflow-hidden mb-5">
                <div class="px-7 py-5 border-b border-gray-100">
                    <div class="flex items-baseline justify-between gap-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ $record->diagnosis ?: 'Visit summary' }}
                        </h2>
                        <span class="shrink-0 text-sm text-gray-500">
                            {{ $record->recorded_at->format('M j, Y') }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        Dr. {{ $record->doctor->first_name }} {{ $record->doctor->last_name }}
                        &middot; {{ $record->doctor->specialization->display_name }}
                    </p>
                </div>

                <div class="px-7 py-5 space-y-5">
                    @if ($record->notes)
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Notes</div>
                            <p class="mt-1 text-gray-800 leading-relaxed whitespace-pre-line">{{ $record->notes }}</p>
                        </div>
                    @endif

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Prescriptions</div>
                        @if ($record->prescriptions->isEmpty())
                            <p class="mt-1 text-sm text-gray-500">None.</p>
                        @else
                            <ul class="mt-2 space-y-2">
                                @foreach ($record->prescriptions as $rx)
                                    <li class="rounded-xl bg-gray-50 px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ $rx->medication_name }}</div>
                                        <div class="mt-0.5 text-sm text-gray-600">
                                            {{ $rx->dosage }} &middot; {{ $rx->frequency }}@if ($rx->duration_days) &middot; {{ $rx->duration_days }} {{ Str::plural('day', $rx->duration_days) }}@endif
                                        </div>
                                        @if ($rx->instructions)
                                            <div class="mt-1 text-sm text-gray-500">{{ $rx->instructions }}</div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white/60 px-8 py-16 text-center">
                <h2 class="text-lg font-semibold text-gray-900">No medical records yet</h2>
                <p class="mt-2 text-gray-600">
                    After a visit, your doctor&rsquo;s notes and any prescriptions will appear here.
                </p>
            </div>
        @endforelse
    </div>
</x-app-layout>
