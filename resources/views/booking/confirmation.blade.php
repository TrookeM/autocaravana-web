@extends('layouts.app')

@section('data-nav-solid', 'true') {{-- <-- ¡¡AÑADIDO PARA LA NAVBAR SÓLIDA!! --}}

{{-- 1. Añadimos el título de la página --}}
@section('title', 'Estado de tu Reserva (#' . $booking->id . ')')

@section('content')
{{-- 
  Usamos el mismo wrapper que contacto.blade.php
--}}
<section class="min-h-screen pt-32 pb-20 bg-gray-50">
  <div class="max-w-4xl mx-auto px-6"> 

    {{-- 
      ==================================================
      ¡¡BLOQUE MODIFICADO!!
      Cambiamos session('success') por request()->query('status')
      ==================================================
    --}}
    @if (request()->query('status') === 'success')
      <div class="mb-8 rounded-xl bg-emerald-100 border border-emerald-200 text-emerald-900 px-5 py-4 shadow-sm flex gap-4">
        <div class="flex-shrink-0">
          {{-- Icono de check --}}
          <svg class="h-6 w-6 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
        </div>
        <div>
          <p class="font-bold text-lg">¡Reserva completada con éxito!</p>
          <p class="mt-1 text-emerald-800">
              Hemos enviado un correo a <strong>{{ $booking->customer_email }}</strong> con el enlace para consultar el estado de tu reserva y la factura adjunta.
          </p>
        </div>
      </div>
    @endif
    {{-- ================================================== --}}
    {{-- FIN DE BLOQUE MODIFICADO --}}
    {{-- ================================================== --}}
  
    {{-- 
      TARJETA DE CONFIRMACIÓN
    --}}
    <div class="bg-white p-8 md:p-12 rounded-2xl shadow-2xl overflow-hidden">
      <div class="space-y-4">
          
          <div class="text-center">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-2">Tu aventura está confirmada</h1>
            <p class="text-lg text-gray-600">¡Gracias por confiar en nosotros, {{ $booking->customer_name }}!</p>
          </div>

          <div class="pt-6 pb-4 border-b border-gray-200">
              <p class="text-sm font-medium text-gray-500">Vehículo</p>
              <p class="text-2xl font-bold text-emerald-700">{{ $booking->campervan->name ?? 'N/D' }}</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                  <p class="text-sm font-medium text-gray-500">ID de reserva</p>
                  <p class="text-lg font-semibold text-gray-800">#{{ $booking->id }}</p>
              </div>
              <div>
                  <p class="text-sm font-medium text-gray-500">Check-in</p>
                  <p class="text-lg font-semibold text-gray-800">{{ \Carbon\Carbon::parse($booking->start_date)->format('d/m/Y') }}</p>
              </div>
              <div>
                  <p class="text-sm font-medium text-gray-500">Check-out</p>
                  <p class="text-lg font-semibold text-gray-800">{{ \Carbon\Carbon::parse($booking->end_date)->format('d/m/Y') }}</p>
              </div>
          </div>
  
        <hr class="my-4">
  
        {{-- Desglose de Precios --}}
        <div class="space-y-2">
          <div class="flex justify-between">
              <p class="text-gray-600">Base (temporada)</p>
              <p class="text-gray-800 font-medium">{{ number_format($base_seasonal_price, 2) }} €</p>
          </div>
          <div class="flex justify-between">
              <p class="text-gray-600">Extras</p>
              <p class="text-gray-800 font-medium">{{ number_format($extras_price, 2) }} €</p>
          </div>
          @if($duration_discount_amount > 0)
          <div class="flex justify-between text-emerald-600">
              <p>Descuento larga estancia</p>
              <p class="font-medium">-{{ number_format($duration_discount_amount, 2) }} €</p>
          </div>
          @endif
          @if($coupon_discount_amount > 0)
           <div class="flex justify-between text-emerald-600">
              <p>Descuento cupón</p>
              <p class="font-medium">-{{ number_format($coupon_discount_amount, 2) }} €</p>
          </div>
          @endif
        </div>
  
        <hr class="my-4">
        
        {{-- Total y Estado de Pago --}}
        <div class="bg-gray-50 p-4 rounded-lg">
          <div class="flex justify-between items-center mb-2">
              <p class="text-lg font-bold text-gray-900">Total Reserva</p>
              <p class="text-2xl font-extrabold text-emerald-700">{{ number_format($booking->total_price, 2) }} €</p>
          </div>

          @if($booking->payment_status === 'full_paid')
          <div class="flex justify-between items-center text-emerald-700">
              <p class="font-semibold">Pagado</p>
              <p class="font-semibold">{{ number_format($booking->amount_paid, 2) }} €</p>
          </div>
          @else
          <div class="flex justify-between items-center text-gray-700">
              <p>Pagado (Señal)</p>
              <p class="font-medium">{{ number_format($booking->amount_paid, 2) }} €</p>
          </div>
          <div class="flex justify-between items-center text-orange-700 mt-1">
              <p class="font-semibold">Pendiente</p>
              <p class="font-semibold">{{ number_format($booking->total_price - $booking->amount_paid, 2) }} €</p>
          </div>
           @if($booking->payment_due_date)
             <p class="text-sm text-gray-600 text-right mt-1">Vence el {{ \Carbon\Carbon::parse($booking->payment_due_date)->format('d/m/Y') }}</p>
           @endif
          @endif
        </div>
  
        <div class="mt-6 pt-6 border-t border-gray-200 flex flex-col sm:flex-row gap-3">
          <a href="{{ route('booking.contract.download', $booking) }}"
             class="w-full sm:w-auto flex-1 text-center px-5 py-3 bg-emerald-600 text-white font-bold rounded-full hover:bg-emerald-700 transition duration-200 shadow-md">
            Descargar contrato (PDF)
          </a>
          <a href="{{ route('home') }}"
             class="w-full sm:w-auto text-center px-5 py-3 bg-gray-100 text-gray-800 font-medium rounded-full hover:bg-gray-200 transition duration-200">
            Volver al inicio
          </a>
        </div>
      </div>
    </div>
  
    {{-- Sección "Nuestra Flota" --}}
    @if(isset($campervans) && $campervans->count())
      <h2 class="text-3xl font-extrabold text-gray-900 mt-20 mb-8 text-center">Otras aventuras que te esperan</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach($campervans as $c)
          <a href="{{ route('campervan.show', $c) }}" 
             class="block bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition transform hover:-translate-y-2 duration-300">
              <img src="{{ $c->main_image_path ? asset('storage/' . $c->main_image_path) : 'https://placehold.co/600x400/E9D5FF/7C3AED?text=Autocaravana' }}"
                   alt="{{ $c->name }}" class="w-full h-48 object-cover">
              <div class="p-5">
                  <h3 class="text-xl font-bold text-gray-800">{{ $c->name }}</h3>
                  <div class="mt-2 flex justify-between items-center">
                      <p class="text-gray-500">Desde:</p>
                      <span class="text-2xl font-extrabold text-emerald-600">
                          {{ number_format($c->price_per_night, 2) }}€
                          <span class="text-sm font-medium text-gray-500">/ noche</span>
                      </span>
                  </div>
              </div>
          </a>
        @endforeach
      </div>
    @endif

  </div>
</section>
@endsection