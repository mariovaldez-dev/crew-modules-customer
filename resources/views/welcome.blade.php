@extends('layouts.app')

@section('content')
    <div class="flex flex-col items-center justify-center w-full min-h-[calc(100vh-100px)]">
        <div class="w-full max-w-5xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 items-stretch">
                
                {{-- Card: Maniobras --}}
                <a href="#" class="group h-full bg-white rounded-3xl p-8 md:p-12 border border-gray-100 shadow-sm hover:shadow-xl hover:border-green-100 transition-all duration-300 flex flex-col items-center text-center cursor-pointer">
                    <div class="w-20 h-20 bg-green-50 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shrink-0">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight leading-tight">Maniobras</h2>
                    <p class="text-gray-500 mt-4 text-sm md:text-base">Catálogo de tipos de maniobra.</p>
                </a>

                {{-- Card: Cuadrilla --}}
                <a href="#" class="group h-full bg-white rounded-3xl p-8 md:p-12 border border-gray-100 shadow-sm hover:shadow-xl hover:border-blue-100 transition-all duration-300 flex flex-col items-center text-center cursor-pointer">
                    <div class="w-20 h-20 bg-blue-50 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shrink-0">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight leading-tight">Cuadrillas</h2>
                    <p class="text-gray-500 mt-4 text-sm md:text-base">Gestión de cuadrillas y sus tarifas.</p>
                </a>

                {{-- Card: Registro de Maniobras --}}
                <a href="#" class="group h-full bg-white rounded-3xl p-8 md:p-12 border border-gray-100 shadow-sm hover:shadow-xl hover:border-purple-100 transition-all duration-300 flex flex-col items-center text-center cursor-pointer">
                    <div class="w-20 h-20 bg-purple-50 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shrink-0">
                        <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight leading-tight">Registro de Maniobras</h2>
                    <p class="text-gray-500 mt-4 text-sm md:text-base">Consulta y registro manual de maniobras.</p>
                </a>
                
                {{-- Card: Tarifas --}}
                <a href="#" class="group h-full bg-white rounded-3xl p-8 md:p-12 border border-gray-100 shadow-sm hover:shadow-xl hover:border-orange-100 transition-all duration-300 flex flex-col items-center text-center cursor-pointer">
                    <div class="w-20 h-20 bg-orange-50 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shrink-0">
                        <svg class="w-10 h-10 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight leading-tight">Tarifas</h2>
                    <p class="text-gray-500 mt-4 text-sm md:text-base">Consulta de tarifas por cuadrilla y zona.</p>
                </a>

                {{-- Card: Corte de Liquidación --}}
                <a href="#" class="group h-full bg-white rounded-3xl p-8 md:p-12 border border-gray-100 shadow-sm hover:shadow-xl hover:border-teal-100 transition-all duration-300 flex flex-col items-center text-center cursor-pointer">
                    <div class="w-20 h-20 bg-teal-50 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shrink-0">
                        <svg class="w-10 h-10 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight leading-tight">Corte de Liquidación</h2>
                    <p class="text-gray-500 mt-4 text-sm md:text-base">Generación y confirmación de cortes.</p>
                </a>

            </div>
        </div>
    </div>
@endsection
