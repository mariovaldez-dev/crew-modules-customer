@extends('layouts.app')

@push('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: radial-gradient(ellipse at 65% 35%, #f9d4f0 0%, #ede8fb 45%, #f5f0ff 100%);
            margin: 0;
        }

        .error-fade {
            animation: errorFadeIn 0.25s ease forwards;
        }

        @keyframes errorFadeIn {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endpush

@section('content')
    blade<div class="min-h-screen flex">

        {{-- Lado izquierdo: imagen — solo desktop --}}
        <div class="hidden lg:block lg:w-1/2 relative">
            <img src="{{ asset('imp-atardecer.webp') }}" alt="Login background"
                class="absolute inset-0 w-full h-full object-cover" />
            {{-- Overlay oscuro opcional --}}
            <div class="absolute inset-0 bg-black/30"></div>
            {{-- Texto sobre la imagen opcional --}}
            <div class="absolute inset-0 flex flex-col justify-end p-10">
                <h2 class="text-white text-3xl font-bold">Administrador</h2>
                <p class="text-white/70 text-sm mt-2">Biblioteca de Cultivos</p>
            </div>
        </div>

        {{-- Lado derecho: formulario --}}
        <div class="flex-1 flex items-center justify-center px-6 py-12 bg-white">
            <div class="w-full max-w-sm">

                {{-- Logo --}}
                <div class="flex justify-center mb-8">
                    <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="h-10" />
                </div>

                {{-- Título --}}
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Bienvenido</h1>
                <p class="text-sm text-gray-500 mb-8">Ingresa tus credenciales para continuar.</p>

                {{-- Form --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">
                            Correo electrónico
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="correo@ejemplo.com" class="w-full h-11 px-4 border border-gray-200 rounded-xl text-sm
                                                           focus:border-green-500 focus:ring-1 focus:ring-green-500
                                                           outline-none transition-colors" required autofocus />
                        @error('email')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">
                                Contraseña
                            </label>
                            @if(Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                    class="text-xs text-green-600 hover:text-green-700 transition-colors">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            @endif
                        </div>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" name="password" placeholder="••••••••" class="w-full h-11 px-4 pr-11 border border-gray-200 rounded-xl text-sm
                                                               focus:border-green-500 focus:ring-1 focus:ring-green-500
                                                               outline-none transition-colors" required />
                            <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600">
                                <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    x-cloak>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Recuérdame --}}
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="remember" id="remember"
                            class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500" />
                        <label for="remember" class="text-sm text-gray-500 select-none">
                            Recordar sesión
                        </label>
                    </div>

                    <button type="submit" class="w-full h-11 bg-green-600 hover:bg-green-700 text-white text-sm font-medium
                                                       rounded-xl transition-colors mt-2">
                        Iniciar sesión
                    </button>

                </form>

            </div>
        </div>

    </div>
@endsection