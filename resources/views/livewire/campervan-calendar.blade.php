<div class="bg-white p-6 rounded-xl shadow-2xl relative"
    x-data="calendar({ 
        unavailableDates: {{ $unavailableDatesJson }}, 
        campervanId: {{ $campervan->id }}, 
        pricePerNight: {{ $campervan->price_per_night }} 
    })">

    {{-- Información de reserva - COMPLETAMENTE AISLADA --}}
    <div class="mb-6 p-4 border rounded-xl bg-pink-50 border-pink-200" wire:ignore> 
        <div class="flex justify-between items-center text-sm font-semibold mb-2">
            <span class="text-gray-600">Noches seleccionadas:</span>
            <span class="text-pink-600" x-text="nightsCount">0</span>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="p-3 border rounded-lg bg-white shadow-sm transition duration-150"
                :class="{'border-pink-500 ring-1 ring-pink-500': dates.checkIn, 'border-gray-300': !dates.checkIn}">
                <label class="block text-xs font-bold text-gray-500 uppercase">Check-in</label>
                <p class="mt-1 text-lg font-bold text-gray-800"
                    x-text="dates.checkIn ? formatDate(dates.checkIn) : 'Elige fecha'"></p>
                <input type="hidden" name="check_in" :value="dates.checkIn">
            </div>
            <div class="p-3 border rounded-lg bg-white shadow-sm transition duration-150"
                :class="{'border-pink-500 ring-1 ring-pink-500': dates.checkOut, 'border-gray-300': !dates.checkOut}">
                <label class="block text-xs font-bold text-gray-500 uppercase">Check-out</label>
                <p class="mt-1 text-lg font-bold text-gray-800"
                    x-text="dates.checkOut ? formatDate(dates.checkOut) : 'Elige fecha'"></p>
                <input type="hidden" name="check_out" :value="dates.checkOut">
            </div>
        </div>

        <p x-cloak x-show="errorMessage" x-text="errorMessage" class="mt-3 text-sm text-red-600 font-medium"></p>
        <p x-cloak x-show="dates.checkIn && !dates.checkOut && !errorMessage" class="mt-3 text-sm text-gray-500 font-medium">
            Selecciona la fecha de Check-out.
        </p>
        <p x-cloak x-show="dates.checkIn && dates.checkOut && !errorMessage" class="mt-3 text-sm text-green-600 font-medium">
            ¡Fechas válidas! Total: <span x-text="totalPrice.toFixed(2)"></span>€
        </p>
    </div>

    {{-- Navegación --}}
    <div class="flex justify-between items-center mb-6">
        <button wire:click="previousMonth"
            @if(!$canGoBack) disabled @endif
            class="p-2 rounded-full text-gray-600 hover:bg-gray-200 transition disabled:opacity-50 disabled:cursor-not-allowed"
            aria-label="Mes anterior">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>

        <div class="text-lg font-bold text-gray-800 text-center">
            {{ $currentMonthName }} {{ $currentYear }} - {{ $nextMonthName }} {{ $nextYear }}
        </div>

        <button wire:click="nextMonth"
            class="p-2 rounded-full text-gray-600 hover:bg-gray-200 transition"
            aria-label="Mes siguiente">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>

    {{-- Calendarios --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Mes Actual --}}
        <div>
            <h3 class="text-lg font-bold text-gray-800 text-center mb-4 capitalize">
                {{ $currentMonthName }} {{ $currentYear }}
            </h3>
            <div class="calendar-grid" wire:key="current-month-{{ $currentYear }}-{{ $currentMonth }}">
                {{-- Código del grid directamente aquí --}}
                @php
                    $dayNames = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
                @endphp
                @foreach ($dayNames as $dayName)
                    <div class="day-header">{{ $dayName }}</div>
                @endforeach

                @foreach ($currentDates as $date)
                    @if ($date['date'] === null)
                        <div></div>
                    @else
                        @php
                            $dateString = $date['date'];
                            $baseClasses = 'day-cell';
                            
                            if ($date['is_disabled']) {
                                $baseClasses .= ' is-disabled';
                            }
                            if ($date['is_unavailable']) {
                                $baseClasses .= ' is-unavailable';
                            }
                            if ($date['is_today'] && !$date['is_disabled']) {
                                $baseClasses .= ' today-indicator';
                            }
                        @endphp
                        
                        <div class="{{ $baseClasses }}"
                            wire:key="day-{{ $dateString }}-current-{{ $currentYear }}-{{ $currentMonth }}"
                            @mouseover="hoverDate = '{{ $dateString }}'"
                            @mouseleave="hoverDate = null"
                            @click="!{{ $date['is_disabled'] ? 'true' : 'false' }} && selectDate('{{ $dateString }}')"
                            :class="{
                                'date-range-start': dates.checkIn === '{{ $dateString }}',
                                'date-range-end': dates.checkOut === '{{ $dateString }}',
                                'date-in-range': isDateInRange('{{ $dateString }}'),
                                'hover-in-range': isDateInRange('{{ $dateString }}') && dates.checkIn && !dates.checkOut && hoverDate
                            }">
                            {{ $date['day_of_month'] }}
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Mes Siguiente --}}
        <div class="hidden md:block">
            <h3 class="text-lg font-bold text-gray-800 text-center mb-4 capitalize">
                {{ $nextMonthName }} {{ $nextYear }}
            </h3>
            <div class="calendar-grid" wire:key="next-month-{{ $nextYear }}-{{ $nextMonth }}">
                {{-- Código del grid directamente aquí --}}
                @php
                    $dayNames = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
                @endphp
                @foreach ($dayNames as $dayName)
                    <div class="day-header">{{ $dayName }}</div>
                @endforeach

                @foreach ($nextDates as $date)
                    @if ($date['date'] === null)
                        <div></div>
                    @else
                        @php
                            $dateString = $date['date'];
                            $baseClasses = 'day-cell';
                            
                            if ($date['is_disabled']) {
                                $baseClasses .= ' is-disabled';
                            }
                            if ($date['is_unavailable']) {
                                $baseClasses .= ' is-unavailable';
                            }
                            if ($date['is_today'] && !$date['is_disabled']) {
                                $baseClasses .= ' today-indicator';
                            }
                        @endphp
                        
                        <div class="{{ $baseClasses }}"
                            wire:key="day-{{ $dateString }}-next-{{ $nextYear }}-{{ $nextMonth }}"
                            @mouseover="hoverDate = '{{ $dateString }}'"
                            @mouseleave="hoverDate = null"
                            @click="!{{ $date['is_disabled'] ? 'true' : 'false' }} && selectDate('{{ $dateString }}')"
                            :class="{
                                'date-range-start': dates.checkIn === '{{ $dateString }}',
                                'date-range-end': dates.checkOut === '{{ $dateString }}',
                                'date-in-range': isDateInRange('{{ $dateString }}'),
                                'hover-in-range': isDateInRange('{{ $dateString }}') && dates.checkIn && !dates.checkOut && hoverDate
                            }">
                            {{ $date['day_of_month'] }}
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Botón de reserva - AISLADO --}}
    <div class="mt-6" wire:ignore>
        <button @click="submitBooking"
            :disabled="!isRangeValid || isSubmitting"
            class="w-full text-white font-bold py-3 rounded-xl transition duration-300 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
            :class="{'bg-pink-600 hover:bg-pink-700': isRangeValid, 'bg-gray-400': !isRangeValid}">
            <span x-show="!isSubmitting">Reservar ahora</span>
            <span x-show="isSubmitting">Redirigiendo...</span>
        </button>
    </div>

</div>