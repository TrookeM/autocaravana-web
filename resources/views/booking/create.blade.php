<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmación de Reserva - {{ $campervan->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 font-sans">
    <div class="container-main">

        {{-- Botón de Volver --}}
        <div class="mb-6">
            <a href="{{ route('campervan.show', $campervan) }}" class="link-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Volver al detalle
            </a>
        </div>

        <h1 class="text-4xl font-extrabold text-gray-800 mb-8 border-b pb-4">Confirma tu Reserva</h1>

        {{-- =============================================== --}}
        {{-- INICIO DEL FORMULARIO ÚNICO --}}
        {{-- Apunta a la ruta de guardado por defecto --}}
        <form method="POST" action="{{ route('booking.store') }}">
            @csrf {{-- Token CSRF único para todo el formulario --}}

            <div class="grid-main">
                {{-- Resumen de la Reserva (Columna 1) --}}
                <div class="booking-summary">
                    <h2 class="text-xl font-bold text-gray-700 mb-4">Resumen</h2>
                    <dl class="divide-y divide-gray-100">
                        {{-- ... (items de resumen: campervan, check-in, check-out, noches) ... --}}
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
                        <div class="summary-item bg-gray-100 rounded-lg p-2 mt-2">
                            <dt class="summary-label font-bold text-lg">Precio Total</dt>
                            <dd class="total-price font-extrabold text-lg text-emerald-700">
                                {{ number_format($total_price, 2) }}€
                            </dd>
                        </div>
                    </dl>

                    {{-- SECCIÓN CUPÓN (AHORA SIN <form>) --}}
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <h3 class="text-lg font-bold text-gray-700 mb-3">¿Tienes un cupón?</h3>
                        
                        <div x-data="{ couponCode: '{{ session('coupon_code') ?? old('code') }}', couponError: '{{ session('coupon_error') }}', couponSuccess: '{{ session('coupon_success') }}' }" class="flex space-x-2">
                            {{-- NO HAY <form> AQUÍ --}}
                            {{-- NO HAY @csrf AQUÍ --}}

                            {{-- Inputs para el cupón (ocultos) --}}
                            <input type="hidden" name="total_price" value="{{ $total_price }}">
                            
                            <input type="text" name="code" x-model="couponCode" placeholder=" Introduce el código"
                                class="flex-grow border-gray-300 rounded-lg shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2 px-3">
                            
                            {{-- CAMBIO: Botón con 'formaction' --}}
                            <button type="submit" 
                                    formaction="{{ route('coupon.apply') }}" 
                                    class="bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-md transition duration-300">
                                Aplicar
                            </button>
                        </div>

                        {{-- Muevo los mensajes fuera del div 'flex' --}}
                        <div class="mt-2">
                            @if (session('coupon_error'))
                                <p class="text-sm text-red-600" x-text="couponError">{{ session('coupon_error') }}</p>
                            @elseif (session('coupon_success'))
                                <p class="text-sm text-emerald-600 font-medium" x-text="couponSuccess">{{ session('coupon_success') }}</p>
                            @endif
                        </div>
                    </div>

                    @if (session('coupon_discount_amount'))
                    <dl class="divide-y divide-gray-100 mt-3 border-t">
                        <div class="summary-item !py-1">
                            <dt class="summary-label text-sm text-gray-600">Descuento aplicado</dt>
                            <dd class="summary-value text-red-500 font-semibold">- {{ number_format(session('coupon_discount_amount'), 2) }}€</dd>
                        </div>
                        <div class="summary-item bg-emerald-100 rounded-lg p-2">
                            <dt class="summary-label font-bold text-lg text-emerald-800">TOTAL CON CUPÓN</dt>
                            <dd class="total-price font-extrabold text-lg text-emerald-800">
                                {{ number_format(session('final_price'), 2) }}€
                            </dd>
                        </div>
                    </dl>
                    @endif
                </div>

                {{-- Formulario de Datos del Cliente y Pagos (Columna 2) --}}
                <div class="lg:col-span-2">
                    {{-- CAMBIO: Esto ahora es un DIV, no un FORM --}}
                    <div class="form-group p-6 bg-white shadow-xl rounded-2xl">
                        {{-- EL @csrf se movió al formulario principal --}}

                        @if ($errors->any())
                        <div class="warning-section bg-red-100 border border-red-200 text-red-700">
                            <ul class="list-disc ml-5">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <h2 class="text-2xl font-bold text-gray-700 mb-6">Tus Datos</h2>

                        {{-- CAMPOS OCULTOS REQUERIDOS --}}
                        <input type="hidden" name="campervan_id" value="{{ $campervan->id }}">
                        <input type="hidden" name="start_date" value="{{ $start_date }}">
                        <input type="hidden" name="end_date" value="{{ $end_date }}">
                        <input type="hidden" name="total_price" value="{{ $total_price }}">
                        <input type="hidden" name="deposit_amount" value="{{ $deposit_amount }}">
                        <input type="hidden" name="remaining_amount" value="{{ $remaining_amount }}">

                        {{-- CAMPOS OCULTOS DEL CUPÓN (Solo se incluyen si están en la sesión) --}}
                        @if (session('coupon_code'))
                            <input type="hidden" name="coupon_code" value="{{ session('coupon_code') }}">
                            <input type="hidden" name="final_price_after_coupon" value="{{ session('final_price') }}">
                        @endif

                        <div class="space-y-6">
                            {{-- CAMPOS DE DATOS DEL CLIENTE --}}
                            <div class="relative">
                                <input type="text" name="customer_name" id="customer_name" required
                                    value="{{ old('customer_name') }}"
                                    class="peer form-input-modern"
                                    placeholder=" ">
                                <label for="customer_name" class="form-label-modern">Nombre Completo <span class="text-red-500">*</span></label>
                                @error('customer_name') <p class="text-red-500 text-xs mt-2 flex items-center">{{ $message }}</p> @enderror
                            </div>

                            <div class="relative">
                                <input type="email" name="customer_email" id="customer_email" required
                                    value="{{ old('customer_email') }}"
                                    class="peer form-input-modern"
                                    placeholder=" ">
                                <label for="customer_email" class="form-label-modern">Email <span class="text-red-500">*</span></label>
                                @error('customer_email') <p class="text-red-500 text-xs mt-2 flex items-center">{{ $message }}</p> @enderror
                            </div>

                            <div class="relative">
                                <input type="tel" name="customer_phone" id="customer_phone" required
                                    value="{{ old('customer_phone') }}"
                                    class="peer form-input-modern"
                                    placeholder=" ">
                                <label for="customer_phone" class="form-label-modern">Teléfono <span class="text-red-500">*</span></label>
                                @error('customer_phone') <p class="text-red-500 text-xs mt-2 flex items-center">{{ $message }}</p> @enderror
                            </div>

                            {{-- ... (Opciones de Pago) ... --}}
                            <h2 class="text-2xl font-bold text-gray-700 mb-6 pt-4 border-t">Opciones de Pago</h2>

                            {{-- OPCIÓN 1: PAGO DE SEÑAL --}}
                            @if ($isDepositAllowed)
                            <label class="payment-option-card {{ old('payment_option', $defaultOption) === 'deposit' ? 'active' : '' }}" for="option_deposit">
                                <input type="radio" name="payment_option" id="option_deposit" value="deposit" class="form-radio" {{ old('payment_option', $defaultOption) === 'deposit' ? 'checked' : '' }}>
                                <div class="ml-4 flex-grow">
                                    <span class="font-bold text-lg text-gray-800 block">Pagar Señal: {{ number_format($deposit_amount, 2) }}€</span>
                                    <p class="text-sm text-gray-500">Paga el **30%** ahora. El {{ number_format($remaining_amount, 2) }}€ restante vence el **{{ $due_date }}**.</p>
                                </div>
                            </label>
                            @endif

                            {{-- OPCIÓN 2: PAGO TOTAL --}}
                            <label class="payment-option-card {{ old('payment_option', $defaultOption) === 'full' ? 'active' : '' }}" for="option_full">
                                <input type="radio" name="payment_option" id="option_full" value="full" class="form-radio" {{ old('payment_option', $defaultOption) === 'full' ? 'checked' : '' }}>
                                <div class="ml-4 flex-grow">
                                    @php $displayPrice = session('final_price') ?? $total_price; @endphp
                                    <span class="font-bold text-lg text-gray-800 block">Pagar Total: {{ number_format($displayPrice, 2) }}€</span>
                                    <p class="text-sm text-gray-500">Paga el 100% ahora y olvídate de gestiones posteriores.</p>
                                </div>
                            </label>
                        </div>

                        {{-- Botones --}}
                        <div class="pt-8">
                            <button type="submit" class="btn-primary w-full cursor-pointer">
                                Proceder al Pago y Confirmar Reserva
                            </button>
                        </div>
                    </div> {{-- FIN del DIV que era un FORM --}}
                </div>
            </div> {{-- FIN de .grid-main --}}
        </form> {{-- FIN DEL FORMULARIO ÚNICO --}}
        {{-- =============================================== --}}
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
