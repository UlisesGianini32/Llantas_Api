<x-layouts.app title="Productos Compuestos">

<div class="space-y-6">

    <h1 class="text-2xl font-bold text-white">📦 Productos compuestos</h1>

    {{-- 🔍 BUSCADOR POR SKU --}}
    <form method="GET" action="{{ route('productos.index') }}">
        <div class="flex gap-2 max-w-md">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Buscar por SKU..."
                class="w-full rounded-md bg-neutral-800 border border-neutral-700 px-4 py-2 text-sm text-white placeholder-gray-400 focus:outline-none focus:ring focus:ring-indigo-500"
            >

            <button
                type="submit"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                🔍 Buscar
            </button>
        </div>
    </form>

    <div class="rounded-lg bg-neutral-900 border border-neutral-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-300">
                <thead class="bg-neutral-800 text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left">SKU</th>
                        <th class="px-4 py-3">Marca</th>
                        <th class="px-4 py-3">Medida</th>
                        <th class="px-4 py-3">Descripción</th>
                        <th class="px-4 py-3 text-right">Costo</th>
                        <th class="px-4 py-3 text-right">Precio ML</th>
                        <th class="px-4 py-3">Título</th>
                        <th class="px-4 py-3">MLM</th>
                        <th class="px-4 py-3 text-center">Stock disp.</th>
                        <th class="px-4 py-3 text-center">Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($compuestos as $compuesto)
                        <tr class="border-t border-neutral-800 hover:bg-neutral-800">
                            <td class="px-4 py-2 font-mono text-blue-400">
                                {{ $compuesto->sku }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $compuesto->llanta->marca ?? 'SIN MARCA' }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $compuesto->llanta->medida ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-2 text-gray-400">
                                {{ $compuesto->descripcion }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                ${{ number_format($compuesto->costo, 2) }}
                            </td>
                            <td class="px-4 py-2 text-right text-green-400 font-semibold">
                                ${{ number_format($compuesto->precio_ML, 2) }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $compuesto->title_familyname }}
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-400">
                                {{ $compuesto->MLM ?? '—' }}
                            </td>
                            <td class="px-4 py-2 text-center font-semibold">
                                {{ $compuesto->stock_disponible }}
                            </td>
                            <td class="px-4 py-2 text-center">
                                <a href="{{ route('productos.edit', $compuesto->id) }}"
                                   class="text-indigo-400 hover:text-indigo-300">
                                    ✏️ Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-6 text-center text-gray-400">
                                No se encontraron productos
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINACIÓN --}}
    {{ $compuestos->links() }}

</div>

</x-layouts.app>
