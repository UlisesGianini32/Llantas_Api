<x-layouts.app title="Productos Compuestos">

<div class="space-y-6">

    <h1 class="text-2xl font-bold text-white">📦 Productos compuestos</h1>

    {{-- 🔍 BUSCADOR --}}
    <form method="GET" action="{{ route('productos.index') }}" class="mb-4">
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Buscar por SKU..."
            class="w-full rounded-lg bg-neutral-900 border border-neutral-800 px-4 py-2 text-sm text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
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
                                {{ $compuesto->llanta->marca ?? '—' }}
                            </td>

                            <td class="px-4 py-2">
                                {{ $compuesto->llanta->medida ?? '—' }}
                            </td>

                            <td class="px-4 py-2 text-gray-400">
                                {{ $compuesto->descripcion ?? '—' }}
                            </td>

                            {{-- ✅ COSTO CALCULADO --}}
                            <td class="px-4 py-2 text-right">
                                ${{ number_format($compuesto->costo_calculado, 2) }}
                            </td>

                            {{-- ✅ PRECIO ML CALCULADO --}}
                            <td class="px-4 py-2 text-right text-green-400 font-semibold">
                                ${{ number_format($compuesto->precio_ml_calculado, 2) }}
                            </td>

                            {{-- ✅ TÍTULO REAL --}}
                            <td class="px-4 py-2">
                                {{ $compuesto->titulo_real }}
                            </td>

                            <td class="px-4 py-2 text-xs text-gray-400">
                                {{ $compuesto->MLM ?? '—' }}
                            </td>

                            {{-- ✅ STOCK DISPONIBLE --}}
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

    {{ $compuestos->links() }}

</div>

</x-layouts.app>
