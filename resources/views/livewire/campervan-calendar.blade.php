<div class="calendar-container w-full"
     x-data="calendar({ 
         unavailableDates: {{ $unavailableDatesJson }}, 
         campervanId: {{ $campervan->id }}, 
         pricePerNight: {{ $campervan->price_per_night }} 
     })">

    {{-- Información de reserva (Sin cambios significativos) --}}
    <div class="mb-6 p-4 border rounded-xl bg-emerald-50 border-emerald-200" wire:ignore>
        <div class="flex justify-between items-center text-sm font-semibold mb-2">
            <span class="text-gray-600">Noches seleccionadas:</span>
            <span class="text-emerald-600" x-text="nightsCount">0</span>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="p-3 border rounded-lg bg-white shadow-sm transition duration-150"
                :class="{'border-emerald-500 ring-1 ring-emerald-500': dates.checkIn, 'border-gray-300': !dates.checkIn}">
                <label class="block text-xs font-bold text-gray-500 uppercase">Check-in</label>
                <p class="mt-1 text-lg font-bold text-gray-800"
                    x-text="dates.checkIn ? formatDate(dates.checkIn) : 'Elige fecha'"></p>
                <input type="hidden" name="check_in" :value="dates.checkIn">
            </div>
            <div class="p-3 border rounded-lg bg-white shadow-sm transition duration-150"
                :class="{'border-emerald-500 ring-1 ring-emerald-500': dates.checkOut, 'border-gray-300': !dates.checkOut}">
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
        <p x-cloak x-show="dates.checkIn && dates.checkOut && !errorMessage" class="mt-3 text-sm text-emerald-600 font-medium">
            ¡Fechas válidas! Total: <span x-text="totalPrice.toFixed(2)"></span>€
        </p>
    </div>

    {{-- Navegación (Título dinámico) --}}
    <div class="calendar-navigation flex justify-between items-center mb-4">
        <button wire:click="previousMonth"
            @if(!$canGoBack) disabled @endif
            class="p-2 rounded-full text-gray-600 hover:bg-gray-200 transition disabled:opacity-50 disabled:cursor-not-allowed"
            aria-label="Mes anterior">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>

        <div class="text-lg font-bold text-gray-800 text-center">
            {{-- Muestra rango de 2 meses SÓLO en MD (mitad de pantalla) --}}
            <span class="hidden md:inline lg:hidden">
                {{ $currentMonthName }} {{ $currentYear }} - {{ $nextMonthName }} {{ $nextYear }}
            </span>
            {{-- Muestra solo el mes actual en dispositivos pequeños y en pantalla completa (LG+) --}}
            <span class="md:hidden lg:inline">
                {{ $currentMonthName }} {{ $currentYear }}
            </span>
        </div>

        <button wire:click="nextMonth"
            class="p-2 rounded-full text-gray-600 hover:bg-gray-200 transition"
            aria-label="Mes siguiente">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>

    {{-- Calendarios (MODIFICADO: AÑADIDO lg:grid-cols-1 para anular el grid de dos columnas en pantalla completa) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-6 md:gap-8 lg:gap-0">
        
        {{-- Mes Actual --}}
        <div>
            <h3 class="text-base font-bold text-gray-800 text-center mb-3 capitalize">
                {{ $currentMonthName }} {{ $currentYear }}
            </h3>
            
            <div class="calendar-grid grid grid-cols-7 gap-1" wire:key="current-month-{{ $currentYear }}-{{ $currentMonth }}-{{ $timestamp }}">
                @foreach ($dayNames as $dayName)
                <div class="day-header text-xs font-semibold text-gray-500 text-center">{{ $dayName }}</div>
                @endforeach

                @foreach ($currentDates as $date)
                @if ($date['date'] === null)
                <div class="day-cell"></div>
                @else
                @php
                $dateString = $date['date'];
                $baseClasses = 'day-cell w-full h-14 rounded-md transition duration-150 border border-transparent flex flex-col items-center justify-center p-1';

                if ($date['is_disabled']) {
                    $baseClasses .= ' text-gray-300 bg-gray-100 cursor-not-allowed is-disabled';
                } else {
                    $baseClasses .= ' hover:bg-emerald-100 cursor-pointer';
                }
                if ($date['is_unavailable']) {
                    $baseClasses .= ' is-unavailable bg-red-100 text-red-600 hover:bg-red-200/50 cursor-not-allowed';
                }
                if ($date['is_today'] && !$date['is_disabled']) {
                    $baseClasses .= ' today-indicator border-emerald-500';
                }
                @endphp

                <div class="{{ $baseClasses }}"
                    wire:key="day-{{ $dateString }}-current-{{ $timestamp }}"
                    @click="!{{ $date['is_disabled'] ? 'true' : 'false' }} && selectDate('{{ $dateString }}')"
                    :class="{
                        'date-range-start bg-emerald-500 text-white font-bold hover:bg-emerald-600': dates.checkIn === '{{ $dateString }}',
                        'date-range-end bg-emerald-500 text-white font-bold hover:bg-emerald-600': dates.checkOut === '{{ $dateString }}',
                        'date-in-range bg-emerald-200/50 text-gray-800 rounded-none': isDateInRange('{{ $dateString }}'),
                        '!bg-red-500 !text-white !font-bold !cursor-not-allowed': dates.checkOut === '{{ $dateString }}' && errorMessage.length > 0
                    }">
                    
                    <span class="font-semibold text-base">{{ $date['day_of_month'] }}</span>
                    
                    @if (!$date['is_disabled'] && !$date['is_unavailable'])
                        <span class="text-xs font-bold text-emerald-600 leading-none"
                            :class="{ 
                                'text-white': dates.checkIn === '{{ $dateString }}' || dates.checkOut === '{{ $dateString }}' || isDateInRange('{{ $dateString }}') 
                            }"
                            x-show="dates.checkIn !== '{{ $dateString }}' || dates.checkOut === '{{ $dateString }}' || isDateInRange('{{ $dateString }}')">
                            {{ round($date['price']) }}€
                        </span>
                    @endif
                </div>
                @endif
                @endforeach
            </div>
        </div>

        {{-- Mes Siguiente (CLAVE: Visible SÓLO en MD, oculto en LG+) --}}
        <div class="hidden md:block lg:hidden">
            <h3 class="text-base font-bold text-gray-800 text-center mb-3 capitalize">
                {{ $nextMonthName }} {{ $nextYear }}
            </h3>
            
            <div class="calendar-grid grid grid-cols-7 gap-1" wire:key="next-month-{{ $nextYear }}-{{ $nextMonth }}-{{ $timestamp }}">
                @foreach ($dayNames as $dayName)
                <div class="day-header text-xs font-semibold text-gray-500 text-center">{{ $dayName }}</div>
                @endforeach

                @foreach ($nextDates as $date)
                @if ($date['date'] === null)
                <div class="day-cell"></div>
                @else
                @php
                $dateString = $date['date'];
                $baseClasses = 'day-cell w-full h-14 rounded-md transition duration-150 border border-transparent flex flex-col items-center justify-center p-1';

                if ($date['is_disabled']) {
                    $baseClasses .= ' text-gray-300 bg-gray-100 cursor-not-allowed is-disabled';
                } else {
                    $baseClasses .= ' hover:bg-emerald-100 cursor-pointer';
                }
                if ($date['is_unavailable']) {
                    $baseClasses .= ' is-unavailable bg-red-100 text-red-600 hover:bg-red-200/50 cursor-not-allowed';
                }
                if ($date['is_today'] && !$date['is_disabled']) {
                    $baseClasses .= ' today-indicator border-emerald-500';
                }
                @endphp

                <div class="{{ $baseClasses }}"
                    wire:key="day-{{ $dateString }}-next-{{ $timestamp }}"
                    @click="!{{ $date['is_disabled'] ? 'true' : 'false' }} && selectDate('{{ $dateString }}')"
                    :class="{
                        'date-range-start bg-emerald-500 text-white font-bold hover:bg-emerald-600': dates.checkIn === '{{ $dateString }}',
                        'date-range-end bg-emerald-500 text-white font-bold hover:bg-emerald-600': dates.checkOut === '{{ $dateString }}',
                        'date-in-range bg-emerald-200/50 text-gray-800 rounded-none': isDateInRange('{{ $dateString }}'),
                        '!bg-red-500 !text-white !font-bold !cursor-not-allowed': dates.checkOut === '{{ $dateString }}' && errorMessage.length > 0
                    }">
                    <span class="font-semibold text-base">{{ $date['day_of_month'] }}</span>
                    
                    @if (!$date['is_disabled'] && !$date['is_unavailable'])
                        <span class="text-xs font-bold text-emerald-600 leading-none"
                            :class="{ 
                                'text-white': dates.checkIn === '{{ $dateString }}' || dates.checkOut === '{{ $dateString }}' || isDateInRange('{{ $dateString }}') 
                            }"
                            x-show="dates.checkIn !== '{{ $dateString }}' || dates.checkOut === '{{ $dateString }}' || isDateInRange('{{ $dateString }}')">
                            {{ round($date['price']) }}€
                        </span>
                    @endif
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Botón de reserva --}}
    <div class="mt-6" wire:ignore>
        <button @click="submitBooking"
            :disabled="!isRangeValid || isSubmitting"
            class="btn-full w-full py-3 px-6 rounded-lg text-white font-bold shadow-lg transition duration-200 focus:outline-none focus:ring-4 focus:ring-offset-2 focus:ring-emerald-500"
            :class="{
                'bg-emerald-500 hover:bg-emerald-600 cursor-pointer': isRangeValid && !isSubmitting,
                'bg-gray-400 cursor-not-allowed': !isRangeValid || isSubmitting
            }">
            <span x-show="!isSubmitting">Reservar ahora</span>
            <span x-show="isSubmitting">Redirigiendo...</span>
        </button>
    </div>
</div>