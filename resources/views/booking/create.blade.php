<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Confirma tu Reserva - {{ $campervan->name }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 font-sans">
    <div class="container-main">

        <h1 class="text-4xl font-extrabold text-gray-800 mb-8 border-b pb-4">Confirma tu Reserva</h1>

        {{-- INICIO DEL FORMULARIO ÚNICO --}}
        <form method="POST"
            action="{{ route('booking.store') }}"
            x-data="couponLogic()"
            data-base-price="{{ $base_price ?? 0 }}"
            data-nights="{{ $nights }}"
            
            data-extras='{!! $extras->mapWithKeys(fn($e) => [$e->id => ['precio'=> $e->precio, 'es_por_dia' => $e->es_por_dia]])->toJson() !!}'
            
            data-old-extras='{!! json_encode(old('extras', [])) !!}'
            data-deposit-percentage="{{ App\Models\Booking::DEPOSIT_PERCENTAGE }}"
            data-due-date="{{ $due_date }}"
            data-route-apply="{{ route('coupon.apply') }}"
            data-route-remove="{{ route('coupon.remove') }}"
            data-session-discount="{{ session('coupon_discount_amount', 0) }}"
            data-session-code="{{ session('coupon_code') ?? '' }}"
            data-session-success="{{ session('coupon_success') ?? '' }}"
            data-session-error="{{ session('coupon_error') ?? '' }}"
            x-init="initData()"
            >
            @csrf

            <div class="grid-main">
                {{-- Resumen de la Reserva (Columna 1) --}}
                <div class="booking-summary">
                    <h2 class="text-xl font-bold text-gray-700 mb-4">Resumen</h2>
                    <dl class="divide-y divide-gray-100">
                        <div class="summary-item">
                            <dt class="summary-label">Autocaravana</dt>
                            <dd class="summary-value">{{ $campervan->name }}</dd>
                        </div>
                        <div class="summary-item">
                            <dt class="summary-label">Check-in</dt>
                            <dd class="summary-value">{{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}</dd>
                        </div>
                        <div class="summary-item">
                            <dt class="summary-label">Check-out</dt>
                            <dd class="summary-value">{{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}</dd>
                        </div>
                        <div class="summary-item">
                            <dt class="summary-label">Noches</dt>
                            <dd class="summary-value">{{ $nights }}</dd>
                        </div>
                        <div class="summary-item pt-4">
                            <dt class="summary-label">Precio Base</dt>
                            <dd class="summary-value font-medium">{{ number_format($base_price, 2) }}€</dd>
                        </div>
                        <div class="summary-item">
                            <dt class="summary-label">Coste Extras</dt>
                            <dd class="summary-value font-medium">+ <span x-text="calculateExtrasCost().toFixed(2)"></span>€</dd>
                        </div>
                        <template x-if="couponDiscount > 0">
                            <div class="summary-item !py-1 bg-red-50 rounded-md">
                                <dt class="text-sm font-bold text-red-600">Descuento Cupón (<span x-text="couponCodeApplied"></span>)</dt>
                                <dd class="text-red-600 font-semibold">-<span x-text="couponDiscount.toFixed(2)"></span>€</dd>
                            </div>
                        </template>
                        <div class="summary-item bg-gray-100 rounded-lg p-2 mt-2">
                            <dt class="font-bold text-lg">Precio Total Final</dt>
                            <dd class="text-emerald-700 font-extrabold text-lg"><span x-text="finalPriceWithCoupon.toFixed(2)"></span>€</dd>
                        </div>
                    </dl>
                    
                    {{-- CUPÓN - DISEÑO SIMPLIFICADO --}}
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <h3 class="text-lg font-bold text-gray-700 mb-3">¿Tienes un cupón?</h3>
                        
                        {{-- Estado cuando NO hay cupón aplicado --}}
                        <template x-if="!couponCodeApplied">
                            <div class="space-y-3">
                                <div class="flex space-x-2">
                                    <input type="hidden" name="price_for_coupon" :value="totalConExtras">
                                    <input type="text" name="code" x-model="couponCodeInput" placeholder="Introduce el código"
                                        class="flex-grow border-gray-300 rounded-lg shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2 px-3">
                                    <button type="button" @click.prevent="applyCoupon"
                                        class="bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-md transition duration-300 whitespace-nowrap">
                                        Aplicar
                                    </button>
                                </div>
                                
                                {{-- Botón para limpiar campo --}}
                                <div class="flex justify-end" x-show="couponCodeInput">
                                    <button type="button" @click="clearField()"
                                        class="text-gray-500 hover:text-gray-700 text-sm flex items-center space-x-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        <span>Limpiar campo</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                        
                        {{-- Estado cuando SÍ hay cupón aplicado --}}
                        <template x-if="couponCodeApplied">
                            <div class="space-y-3">
                                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 text-center">
                                    <div class="mb-2">
                                        <span class="font-semibold text-emerald-800 text-lg" x-text="couponCodeApplied"></span>
                                        <p class="text-sm text-emerald-600 mt-1" x-text="'Ahorras: ' + couponDiscount.toFixed(2) + '€'"></p>
                                    </div>
                                    <button type="button" @click="removeCoupon()"
                                        class="text-blue-500 hover:text-blue-700 text-sm font-medium underline">
                                        Usar otro cupón
                                    </button>
                                </div>
                            </div>
                        </template>
                        
                        {{-- Mensajes de estado --}}
                        <div class="mt-2">
                            <template x-if="couponError">
                                <p class="text-sm text-red-600" x-text="couponError"></p>
                            </template>
                            <template x-if="couponSuccess">
                                <p class="text-sm text-emerald-600 font-medium" x-text="couponSuccess"></p>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Formulario de Datos del Cliente y Pagos (Columna 2) --}}
                <div class="lg:col-span-2">
                    <div class="form-group p-6 bg-white shadow-xl rounded-2xl">
                        @if ($errors->any())
                        <div class="bg-red-100 border border-red-200 text-red-700 p-3 rounded-md mb-4">
                            <ul class="list-disc ml-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                        @endif
                        <h2 class="text-2xl font-bold text-gray-700 mb-6">Tus Datos</h2>
                        <input type="hidden" name="campervan_id" value="{{ $campervan->id }}">
                        <input type="hidden" name="start_date" value="{{ $start_date }}">
                        <input type="hidden" name="end_date" value="{{ $end_date }}">
                        <input type="hidden" name="total_price" :value="totalConExtras.toFixed(2)">
                        <input type="hidden" name="deposit_amount" :value="finalDepositAmount.toFixed(2)">
                        <input type="hidden" name="remaining_amount" :value="finalRemainingAmount.toFixed(2)">
                        <input type="hidden" name="coupon_code" x-bind:value="couponCodeApplied">
                        <input type="hidden" name="final_price_after_coupon" x-bind:value="finalPriceWithCoupon.toFixed(2)">
                        <div class="space-y-6">
                            <div class="relative">
                                <input type="text" name="customer_name" id="customer_name" required value="{{ old('customer_name') }}" class="peer form-input-modern" placeholder=" ">
                                <label for="customer_name" class="form-label-modern">Nombre Completo <span class="text-red-500">*</span></label>
                                @error('customer_name') <p class="text-red-500 text-xs mt-2 flex items-center">{{ $message }}</p> @enderror
                            </div>
                            <div class="relative">
                                <input type="email" name="customer_email" id="customer_email" required value="{{ old('customer_email') }}" class="peer form-input-modern" placeholder=" ">
                                <label for="customer_email" class="form-label-modern">Email <span class="text-red-500">*</span></label>
                                @error('customer_email') <p class="text-red-500 text-xs mt-2 flex items-center">{{ $message }}</p> @enderror
                            </div>
                            <div class="relative">
                                <input type="tel" name="customer_phone" id="customer_phone" required value="{{ old('customer_phone') }}" class="peer form-input-modern" placeholder=" ">
                                <label for="customer_phone" class="form-label-modern">Teléfono <span class="text-red-500">*</span></label>
                                @error('customer_phone') <p class="text-red-500 text-xs mt-2 flex items-center">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h3 class="text-2xl font-bold text-gray-700 mb-6">Añade Extras Opcionales</h3>
                            <div class="space-y-3">
                                @forelse ($extras as $extra)
                                <label class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 transition cursor-pointer">
                                    <div>
                                        <span class="font-semibold text-gray-800">{{ $extra->nombre }}</span>
                                        <p class="text-sm text-gray-600">{{ number_format($extra->precio, 2) }}€ <span class="font-normal">{{ $extra->es_por_dia ? 'por día' : 'por alquiler' }}</span></p>
                                    </div>
                                    <input type="checkbox"
                                        name="extras[]"
                                        value="{{ $extra->id }}"
                                        x-model="selectedExtras"
                                        @change="handleExtraChange()"
                                        class="form-checkbox h-5 w-5 text-emerald-600 rounded focus:ring-emerald-500 border-gray-300">
                                </label>
                                @empty
                                <p class="text-sm text-gray-500">No hay extras disponibles para esta reserva.</p>
                                @endforelse
                            </div>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-700 mb-6 pt-6 border-t">Opciones de Pago</h2>
                        <div class="space-y-6">
                            @if ($isDepositAllowed)
                            <label class="payment-option-card {{ old('payment_option', $defaultOption) === 'deposit' ? 'active' : '' }}" for="option_deposit">
                                <input type="radio" name="payment_option" id="option_deposit" value="deposit" class="form-radio" {{ old('payment_option', $defaultOption) === 'deposit' ? 'checked' : '' }}>
                                <div class="ml-4 flex-grow">
                                    <span class="font-bold text-lg text-gray-800 block">Pagar Señal: <span x-text="finalDepositAmount.toFixed(2)"></span>€</span>
                                    <p class="text-sm text-gray-500">Paga el 30% ahora. El resto (<span x-text="finalRemainingAmount.toFixed(2)"></span>€) vence el {{ $due_date }}.</p>
                                </div>
                            </label>
                            @endif
                            <label class="payment-option-card {{ old('payment_option', $defaultOption) === 'full' ? 'active' : '' }}" for="option_full">
                                <input type="radio" name="payment_option" id="option_full" value="full" class="form-radio" {{ old('payment_option', $defaultOption) === 'full' ? 'checked' : '' }}>
                                <div class="ml-4 flex-grow">
                                    <span class="font-bold text-lg text-gray-800 block">Pagar Total: <span x-text="finalPriceWithCoupon.toFixed(2)"></span>€</span>
                                    <p class="text-sm text-gray-500">Paga el 100% ahora y evita gestiones posteriores.</p>
                                </div>
                            </label>
                        </div>
                        <div class="pt-8">
                            <button type="submit" class="btn-primary w-full">Proceder al Pago y Confirmar Reserva</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</body>

<script>
    document.querySelectorAll('.payment-option-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.payment-option-card').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });
</script>

</html>