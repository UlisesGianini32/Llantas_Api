<x-layouts.app title="Llantas agotadas">

<div class="space-y-6">

    <h1 class="text-2xl font-bold text-red-400">
        🔴 Llantas agotadas (Stock = 0)
    </h1>

    {{-- ===================== --}}
    {{-- BUSCADOR POR SKU --}}
    {{-- ===================== --}}
    <form method="GET" action="{{ route('llantas.agotadas') }}" class="mb-4">
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Buscar por SKU..."
            class="w-full rounded-md border border-neutral-700 bg-neutral-900 px-4 py-2 text-white placeholder-gray-500"
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
                        <th class="px-4 py-3 text-center">Stock</th>
                        <th class="px-4 py-3">Descripción</th>
                        <th class="px-4 py-3 text-right">Costo</th>
                        <th class="px-4 py-3 text-right">Precio ML</th>
                        <th class="px-4 py-3">Título</th>
                        <th class="px-4 py-3">MLM</th>
                        <th class="px-4 py-3 text-center">Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($llantas as $llanta)
                        <tr class="border-t border-neutral-800 hover:bg-neutral-800">

                            <td class="px-4 py-2 font-mono text-blue-400">
                                {{ $llanta->sku }}
                            </td>

                            <td class="px-4 py-2">
                                {{ $llanta->marca ?? 'SIN MARCA' }}
                            </td>

                            <td class="px-4 py-2">
                                {{ $llanta->medida ?? 'N/A' }}
                            </td>

                            {{-- STOCK SIEMPRE ROJO --}}
                            <td class="px-4 py-2 text-center font-bold text-red-400">
                                {{ $llanta->stock }}
                            </td>

                            <td class="px-4 py-2 text-gray-400">
                                {{ $llanta->descripcion }}
                            </td>

                            <td class="px-4 py-2 text-right">
                                ${{ number_format($llanta->costo, 2) }}
                            </td>

                            <td class="px-4 py-2 text-right text-green-400 font-semibold">
                                ${{ number_format($llanta->precio_ML, 2) }}
                            </td>

                            <td class="px-4 py-2">
                                {{ $llanta->title_familyname }}
                            </td>

                            <td class="px-4 py-2 text-xs text-gray-400">
                                {{ $llanta->MLM ?? '—' }}
                            </td>

                            <td class="px-4 py-2 text-center">
                                <a href="{{ route('llantas.edit', $llanta->id) }}"
                                   class="text-indigo-400 hover:text-indigo-300">
                                    ✏️ Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-6 text-center text-gray-400">
                                No hay llantas agotadas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== --}}
    {{-- PAGINACIÓN --}}
    {{-- ===================== --}}
    {{ $llantas->appends(request()->query())->links() }}

</div>

</x-layouts.app>
