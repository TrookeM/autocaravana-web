@extends('layouts.app')

{{-- 1. Título de la página --}}
@section('title', 'Estado de tu Reserva (#' . $booking->id . ')')

{{-- 2. "Empujamos" los estilos CSS específicos de esta página al <head> del layout --}}
@push('styles')
<style>
    /* Como usamos Tailwind, solo necesitamos un par de estilos 
      para la tabla que no son estándar
    */
    .details-table th { 
        text-align: left; 
        width: 40%; /* Damos un ancho fijo a las etiquetas */
    }
    .details-table th,
    .details-table td {
        padding-top: 0.75rem; /* 12px */
        padding-bottom: 0.75rem; /* 12px */
    }
</style>
@endpush


@section('content')
{{-- 
  3. USAMOS EL MISMO WRAPPER QUE contacto.blade.php
  Esto añade el padding-top (pt-32) para que el contenido no quede
  oculto detrás de la barra de navegación fija.
--}}
<section class="min-h-screen pt-32 pb-20 bg-gray-50">
    <div class="max-w-4xl mx-auto px-6">
        
        {{-- Título --}}
        <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold text-emerald-800">Estado de tu Reserva</h1>
            <p class="text-lg text-gray-700 mt-2">Hola <strong>{{ $booking->customer_name }}</strong>, aquí tienes todos los detalles.</p>
        </div>

        {{-- 
          4. TARJETA PRINCIPAL DE DETALLES
        --}}
        <div class="bg-white p-8 md:p-12 rounded-2xl shadow-2xl overflow-hidden">
            
            <table class="w-full text-sm details-table">
                <tbody>
                    <tr class="border-b border-gray-100">
                        <th class="py-3 px-2 font-medium text-gray-500">Número de Reserva</th>
                        <td class="py-3 px-2 font-bold text-gray-900">#{{ $booking->id }}</td>
                    </tr>
                     <tr class="border-b border-gray-100">
                        <th class="py-3 px-2 font-medium text-gray-500">Estado de la Reserva</th>
                        <td class="py-3 px-2 font-bold text-emerald-600">{{ ucfirst($booking->status) }}</td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <th class="py-3 px-2 font-medium text-gray-500">Autocaravana</th>
                        <td class="py-3 px-2 font-bold text-gray-900">{{ $booking->campervan->name }}</td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <th class="py-3 px-2 font-medium text-gray-500">Check-in</th>
                        <td class="py-3 px-2 font-bold text-gray-900">
                            {{ $booking->start_date->format('d/m/Y') }}
                            @if($booking->campervan->check_in_time)
                                <span class="text-gray-500 font-medium text-xs block">
                                    (a las {{ \Carbon\Carbon::parse($booking->campervan->check_in_time)->format('H:i') }})
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <th class="py-3 px-2 font-medium text-gray-500">Check-out</th>
                        <td class="py-3 px-2 font-bold text-gray-900">
                            {{ $booking->end_date->format('d/m/Y') }}
                            @if($booking->campervan->check_out_time)
                                <span class="text-gray-500 font-medium text-xs block">
                                    (a las {{ \Carbon\Carbon::parse($booking->campervan->check_out_time)->format('H:i') }})
                                </span>
                            @endif
                        </td>
                    </tr>

                    @if ($booking->inventoryItems->isNotEmpty())
                        <tr class="border-b border-gray-100">
                            <th class="py-3 px-2 font-medium text-gray-500 align-top">Extras Contratados</th>
                            <td class="py-3 px-2 font-bold text-gray-900">
                                <ul class="list-none m-0 p-0 space-y-1">
                                    @foreach ($booking->inventoryItems as $item)
                                        <li>
                                            {{ $item->name }}
                                            <span class="text-gray-500 font-medium">({{ number_format($item->pivot->precio_cobrado, 2) }}€)</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endif
                    
                    {{-- =================================== --}}
                    {{-- DESGLOSE DE PRECIO IDÉNTICO A CONFIRMATION --}}
                    {{-- =================================== --}}
                    <tr class="border-b border-gray-100">
                        <th class="py-3 px-2 font-medium text-gray-500">Base (temporada)</th>
                        <td class="py-3 px-2 font-medium text-gray-800">{{ number_format($base_seasonal_price, 2) }} €</td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <th class="py-3 px-2 font-medium text-gray-500">Extras</th>
                        <td class="py-3 px-2 font-medium text-gray-800">{{ number_format($extras_price, 2) }} €</td>
                    </tr>
                    @if($duration_discount_amount > 0)
                    <tr class="border-b border-gray-100">
                        <th class="py-3 px-2 font-medium text-emerald-600">Descuento larga estancia</th>
                        <td class="py-3 px-2 font-medium text-emerald-600">-{{ number_format($duration_discount_amount, 2) }} €</td>
                    </tr>
                    @endif
                    @if($coupon_discount_amount > 0)
                     <tr class="border-b border-gray-100">
                        <th class="py-3 px-2 font-medium text-emerald-600">Descuento cupón</th>
                        <td class="py-3 px-2 font-medium text-emerald-600">-{{ number_format($coupon_discount_amount, 2) }} €</td>
                    </tr>
                    @endif
                    {{-- =================================== --}}
                    {{-- FIN DEL DESGLOSE --}}
                    {{-- =================================== --}}

                    <tr class="border-b border-gray-100 text-base">
                        <th class="py-4 px-2 font-bold text-gray-900">PRECIO TOTAL</th>
                        <td class="py-4 px-2 text-xl font-extrabold text-emerald-700">{{ number_format($booking->total_price, 2) }}€</td>
                    </tr>

                    @if ($booking->payment_status === 'deposit_paid')
                        <tr class="border-b border-gray-100">
                            <th class="py-3 px-2 font-medium text-gray-500">Pagado (Señal)</th>
                            <td class="py-3 px-2 font-bold text-emerald-600">{{ number_format($booking->amount_paid, 2) }}€</td>
                        </tr>
                        <tr class="bg-yellow-50">
                            <th class="py-3 px-2 font-bold text-yellow-800">Pendiente de Pago</th>
                            <td class="py-3 px-2 font-bold text-yellow-800">{{ number_format($booking->amount_due, 2) }}€</td>
                        </tr>
                    @else
                        <tr class="bg-emerald-50">
                            <th class="py-3 px-2 font-bold text-emerald-800">Total Pagado (100%)</th>
                            <td class="py-3 px-2 font-bold text-emerald-800">{{ number_format($booking->total_price, 2) }}€</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            @if ($booking->payment_status === 'deposit_paid')
                <div class="mt-6 p-4 rounded-lg bg-yellow-100 border border-yellow-200 text-yellow-800">
                    <p class="font-bold">¡Importante!</p>
                    <p class="text-sm">
                        El pago restante de <strong>{{ number_format($booking->amount_due, 2) }}€</strong> vence el <strong>{{ $booking->payment_due_date ? $booking->payment_due_date->format('d/m/Y') : 'N/A' }}</strong>.
                    </p>
                </div>
            @endif
        </div>
        
        {{-- 
          5. NUEVA SECCIÓN DE DOCUMENTOS
        --}}
        <div class="mt-12">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-6 text-center">Tus Documentos</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- Botón de Contrato (Estilo Tailwind) --}}
                <a href="{{ route('booking.contract.download', $booking) }}"
                   class="flex flex-col items-center justify-center p-6 bg-white rounded-2xl shadow-xl hover:shadow-2xl transition transform hover:-translate-y-1 duration-300 text-center">
                    <svg class="h-12 w-12 text-emerald-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <span class="text-xl font-bold text-gray-800">Descargar Contrato</span>
                    <span class="text-sm text-gray-500 mt-1">Tu acuerdo de alquiler (PDF)</span>
                </a>
                
                {{-- Botón de Factura (Estilo Tailwind) --}}
                <a href="{{ route('booking.invoice.download', $booking) }}"
                   class="flex flex-col items-center justify-center p-6 bg-white rounded-2xl shadow-xl hover:shadow-2xl transition transform hover:-translate-y-1 duration-300 text-center">
                    <svg class="h-12 w-12 text-emerald-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3m0 0l3-3m-3 3v-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span class="text-xl font-bold text-gray-800">Descargar Factura</span>
                    <span class="text-sm text-gray-500 mt-1">Tu justificante de pago (PDF)</span>
                </a>
            </div>
        </div>

        {{-- 
          6. NUEVA SECCIÓN DE GUÍAS
        --}}
        @if ($booking->campervan->guides->isNotEmpty())
            <div class="mt-16">
                <h2 class="text-3xl font-extrabold text-gray-900 mb-8 text-center">Guías y Manuales</h2>
                
                <div class="space-y-6">
                    @foreach ($booking->campervan->guides as $guide)
                        <div class="bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row">
                            {{-- Icono --}}
                            <div class="flex-shrink-0 p-6 bg-emerald-50 flex items-center justify-center md:w-32">
                                <svg class="h-10 w-10 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18c-2.305 0-4.408.867-6 2.292m0-14.25v14.25" />
                                </svg>
                            </div>
                            
                            {{-- Contenido --}}
                            <div class="p-6 flex-grow">
                                <h3 class="text-xl font-bold text-emerald-800">{{ $guide->title }}</h3>

                                @if ($guide->content)
                                    {{-- Aplicamos 'prose' para formatear el HTML del editor --}}
                                    <div class="prose prose-sm prose-emerald mt-2 text-gray-600 max-w-none">
                                        {!! $guide->content !!}
                                    </div>
                                @endif
                            </div>

                            {{-- Botón de descarga (si existe) --}}
                            @if ($guide->pdf_path)
                                <div class="flex-shrink-0 p-6 bg-gray-50/50 flex items-center justify-center">
                                    <a href="{{ asset('storage/'. $guide->pdf_path) }}" 
                                       class="inline-block px-5 py-2 bg-emerald-600 text-white font-semibold rounded-full hover:bg-emerald-700 transition duration-200 text-sm" 
                                       target="_blank" 
                                       download>
                                        Descargar PDF
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</section>
@endsection