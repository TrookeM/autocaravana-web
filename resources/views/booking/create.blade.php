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

        <div class="grid-main">
            {{-- Resumen de la Reserva --}}
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
                    <div class="summary-item">
                        <dt class="summary-label font-bold">Total a pagar</dt>
                        <dd class="total-price">{{ number_format($total_price, 2) }}€</dd>
                    </div>
                </dl>
            </div>

            {{-- Formulario de Datos del Cliente --}}
            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('booking.store') }}" class="form-group">
                    @csrf

                    @if ($errors->any())
                    <div class="warning-section">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <h2 class="text-2xl font-bold text-gray-700 mb-6">Tus Datos</h2>

                    <input type="hidden" name="campervan_id" value="{{ $campervan->id }}">
                    <input type="hidden" name="start_date" value="{{ $start_date }}">
                    <input type="hidden" name="end_date" value="{{ $end_date }}">
                    <input type="hidden" name="total_price" value="{{ $total_price }}">

                    <div class="space-y-6">
                        <div class="relative">
                            <input type="text" name="customer_name" id="customer_name" required
                                value="{{ old('customer_name') }}"
                                class="peer form-input-modern pt-6 pb-2 px-4"
                                placeholder=" ">
                            <label for="customer_name" class="form-label-modern">
                                Nombre Completo <span class="text-red-500">*</span>
                            </label>
                            @error('customer_name')
                            <p class="text-red-500 text-xs mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <div class="relative">
                            <input type="email" name="customer_email" id="customer_email" required
                                value="{{ old('customer_email') }}"
                                class="peer form-input-modern pt-6 pb-2 px-4"
                                placeholder=" ">
                            <label for="customer_email" class="form-label-modern">
                                Email <span class="text-red-500">*</span>
                            </label>
                            @error('customer_email')
                            <p class="text-red-500 text-xs mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <div class="relative">
                            <input type="tel" name="customer_phone" id="customer_phone" required
                                value="{{ old('customer_phone') }}"
                                class="peer form-input-modern pt-6 pb-2 px-4"
                                placeholder=" ">
                            <label for="customer_phone" class="form-label-modern">
                                Teléfono <span class="text-red-500">*</span>
                            </label>
                            @error('customer_phone')
                            <p class="text-red-500 text-xs mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>
                    </div>

                    {{-- Botones --}}
                    <div class="pt-4">
                        <button type="submit" class="btn-primary w-full cursor-pointer">
                            Confirmar Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>