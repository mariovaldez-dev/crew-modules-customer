<x-modal name="resumen-maniobras-modal" title="Resumen de Cuadrilla" maxWidth="2xl">
    
    <div class="mb-4 text-sm text-gray-500 dark:text-gray-400">
        <p>A continuación se detalla el total de las maniobras realizadas por esta cuadrilla en el periodo de corte.</p>
    </div>

    <div class="overflow-hidden border border-gray-100 dark:border-white/5 rounded-2xl mb-6">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-gray-400 dark:text-gray-500 text-[10px] font-black uppercase tracking-widest">
                    <th class="px-4 py-3">Folio</th>
                    <th class="px-4 py-3">Concepto</th>
                    <th class="px-4 py-3 text-right">Ton.</th>
                    <th class="px-4 py-3 text-right">Precio</th>
                    <th class="px-4 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-sm">
                @forelse($maniobras as $maniobra)
                    <tr class="hover:bg-gray-50/40 dark:hover:bg-white/5 transition-colors duration-150">
                        <td class="px-4 py-3.5 font-bold text-gray-900 dark:text-white">{{ $maniobra['folio'] }}</td>
                        <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400 font-medium">{{ $maniobra['concepto'] }}</td>
                        <td class="px-4 py-3.5 font-mono text-gray-600 dark:text-gray-400 text-right">{{ number_format($maniobra['toneladas'], 3) }}</td>
                        <td class="px-4 py-3.5 font-mono text-gray-600 dark:text-gray-400 text-right">${{ number_format($maniobra['precio'], 2) }}</td>
                        <td class="px-4 py-3.5 font-mono font-bold text-gray-900 dark:text-white text-right">${{ number_format($maniobra['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-400 dark:text-gray-500">No hay detalles disponibles</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-50/50 dark:bg-white/5 font-bold border-t border-gray-100 dark:border-white/5">
                <tr>
                    <td colspan="4" class="px-4 py-3.5 text-right text-gray-900 dark:text-white uppercase text-[10px] tracking-widest">Gran Total a Pagar</td>
                    <td class="px-4 py-3.5 text-right text-green-600 dark:text-green-400 text-base font-mono font-black">${{ number_format($granTotal, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <x-slot name="footer">
        <x-button variant="secondary" x-on:click="show = false">
            Cerrar
        </x-button>
        <x-button wire:click="confirmarCuadrilla" variant="primary">
            Confirmar Cuadrilla
        </x-button>
    </x-slot>
</x-modal>
