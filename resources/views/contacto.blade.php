@extends('layouts.contact_form')

@section('content')
<section class="min-h-screen pt-32 pb-20 bg-gray-50">
    <div class="max-w-4xl mx-auto px-6">
        <div class="text-center mb-12">
            <h1 class="text-5xl font-extrabold text-emerald-800 mb-4">
                Ponte en Contacto
            </h1>
            <p class="text-xl text-gray-600">
                ¿Tienes preguntas sobre la flota, disponibilidad o rutas? Envíanos un mensaje.
            </p>
        </div>

        <!-- Bloque de Notificaciones (Éxito y Error) -->
        @if (session('success'))
        <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-8 rounded-lg shadow-md" role="alert">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="font-bold">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded-lg shadow-md" role="alert">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="font-bold">Error al Enviar: {{ session('error') }}</p>
            </div>
        </div>
        @endif
        <!-- Fin Bloque de Notificaciones -->

        <div class="bg-white p-8 md:p-12 rounded-2xl shadow-2xl grid md:grid-cols-2 gap-10">

            {{-- Columna 1: Formulario --}}
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Envía tu Consulta</h2>

                {{-- Formulario básico: Debes configurar la ruta de acción (ej. contact.store) --}}
                <form action="{{ route('contact.store') }}" method="POST" class="space-y-6"> @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo</label>
                        <input type="text" id="name" name="name" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 transition duration-150 shadow-sm"
                            placeholder="Tu nombre">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                        <input type="email" id="email" name="email" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 transition duration-150 shadow-sm"
                            placeholder="tu.correo@ejemplo.com">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Teléfono (Opcional)</label>
                        <input type="tel" id="phone" name="phone"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 transition duration-150 shadow-sm"
                            placeholder="+34 6XX XXX XXX">
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Mensaje</label>
                        <textarea id="message" name="message" rows="4" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 transition duration-150 shadow-sm"
                            placeholder="Escribe tu consulta aquí..."></textarea>
                    </div>

                    <button type="submit"
                        class="w-full py-3 px-4 bg-emerald-600 text-white font-bold rounded-full hover:bg-emerald-700 transition transform hover:scale-[1.01] duration-300 shadow-md">
                        Enviar Consulta
                    </button>
                </form>
            </div>

            {{-- Columna 2: Información de Contacto Directo --}}
            <div class="bg-emerald-700 p-8 rounded-xl text-white">
                <h2 class="text-3xl font-bold mb-6">Datos de Contacto</h2>

                <ul class="space-y-6 text-lg">
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-emerald-300" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.772-1.549a1 1 0 011.06-.54l4.435.74A1 1 0 0118 16.847V17a1 1 0 01-1 1h-1.153a1 1 0 01-.986-.836l-.74-4.435a1 1 0 01.54-1.06l1.548-.773a11.037 11.037 0 00-6.105-6.105l-.772 1.549a1 1 0 01-1.06.54l-4.435-.74A1 1 0 013.153 3H2z" />
                        </svg>
                        <a href="tel:+34600123456" class="hover:text-emerald-200 transition">+34 600 123 456</a>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-emerald-300" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                        </svg>
                        <a href="mailto:info@campers.es" class="hover:text-emerald-200 transition">info@campers.es</a>
                    </li>
                    <li class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-emerald-300 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 1110.63 8.35c-.244.372-.61.737-.997 1.026C13.627 14.62 10 17 10 17s-3.627-2.38-4.686-3.57C4.66 12.137 4.295 11.772 4.05 11.4A7 7 0 015.05 4.05zm5.45 6.45a2 2 0 10-4 0 2 2 0 004 0z" clip-rule="evenodd" />
                        </svg>
                        <span>C. Mayor, 123 - 28001 Madrid, España</span>
                    </li>
                </ul>

                <p class="mt-8 text-sm text-emerald-200 border-t border-emerald-600 pt-6">Horario: Lunes a Viernes 9:00 - 18:00.</p>
            </div>
        </div>
    </div>
</section>
@endsection