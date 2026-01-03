<x-layouts.app title="Llantas no actualizadas">

<div class="space-y-6">

    <h1 class="text-2xl font-bold text-white">
        ⚠️ Llantas no actualizadas en el último import
    </h1>

    <div class="rounded-lg bg-neutral-900 border border-neutral-800 overflow-hidden">
        <table class="w-full text-sm text-gray-300">
            <thead class="bg-neutral-800 text-gray-400">
                <tr>
                    <th class="px-4 py-3 text-left">SKU</th>
                    <th class="px-4 py-3 text-center">Stock</th>
                    <th class="px-4 py-3 text-center">Última importación</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($llantas as $llanta)
                    <tr class="border-t border-neutral-800">
                        <td class="px-4 py-2 font-mono text-blue-400">
                            {{ $llanta->sku }}
                        </td>
                        <td class="px-4 py-2 text-center text-red-400 font-bold">
                            {{ $llanta->stock }}
                        </td>
                        <td class="px-4 py-2 text-center text-gray-400">
                            {{ $llanta->last_import_at ?? 'Nunca' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-gray-400">
                            No hay llantas pendientes
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $llantas->links() }}

</div>

</x-layouts.app>
