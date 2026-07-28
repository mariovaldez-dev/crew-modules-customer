@props(['message' => 'No hay información disponible'])

<div class="p-10 flex flex-col items-center justify-center text-center">
    <div class="w-16 h-16 bg-gray-50 dark:bg-white/5 rounded-full flex items-center justify-center mb-4">
        <i class="fa-solid fa-inbox text-2xl text-gray-400 dark:text-gray-500"></i>
    </div>
    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Sin Resultados</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">{{ $message }}</p>
</div>
