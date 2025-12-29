<x-layouts.app :title="__('Dashboard')">

    <div class="min-h-screen bg-black p-6">
        <div class="mx-auto max-w-7xl space-y-6 text-white">

            {{-- ===================== --}}
            {{-- STATS --}}
            {{-- ===================== --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

                <div class="rounded-lg bg-neutral-900 p-4 border border-neutral-800">
                    <p class="text-sm text-gray-400">Llantas individuales</p>
                    <p class="mt-2 text-2xl font-semibold text-blue-400">
                        {{ number_format($totalLlantas) }}
                    </p>
                </div>

                <div class="rounded-lg bg-neutral-900 p-4 border border-neutral-800">
                    <p class="text-sm text-gray-400">Tipos de combos</p>
                    <p class="mt-2 text-2xl font-semibold text-indigo-400">
                        {{ number_format($totalCompuestos) }}
                    </p>
                </div>

                <div class="rounded-lg bg-neutral-900 p-4 border border-neutral-800">
                    <p class="text-sm text-gray-400">Stock llantas</p>
                    <p class="mt-2 text-2xl font-semibold text-green-400">
                        {{ number_format($existenciasLlantas) }}
                    </p>
                </div>

                <div class="rounded-lg bg-neutral-900 p-4 border border-neutral-800">
                    <p class="text-sm text-gray-400">Llantas agotadas</p>
                    <p class="mt-2 text-2xl font-semibold text-red-400">
                        {{ number_format($llantasSinStock) }}
                    </p>
                </div>

            </div>

            {{-- ===================== --}}
            {{-- VALORES --}}
            {{-- ===================== --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <div class="rounded-lg bg-neutral-900 p-4 border border-neutral-800">
                    <p class="text-sm text-gray-400">Valor inventario llantas</p>
                    <p class="mt-1 text-xl font-semibold text-green-400">
                        ${{ number_format($valorInventarioLlantas, 2) }}
                    </p>
                </div>

                <div class="rounded-lg bg-neutral-900 p-4 border border-neutral-800">
                    <p class="text-sm text-gray-400">Valor teórico combos</p>
                    <p class="mt-1 text-xl font-semibold text-indigo-400">
                        ${{ number_format($valorInventarioCompuestos, 2) }}
                    </p>
                </div>

            </div>

            {{-- ===================== --}}
            {{-- BUSCADOR --}}
            {{-- ===================== --}}
            <form method="GET" action="{{ route('dashboard') }}">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Buscar por SKU..."
                    class="w-full rounded-md border border-neutral-700 bg-neutral-900 px-4 py-2 text-white placeholder-gray-500"
                >
            </form>

            {{-- ===================== --}}
            {{-- STOCK CRÍTICO --}}
            {{-- ===================== --}}
            <div class="rounded-lg bg-neutral-900 border border-neutral-800">

                <div class="flex items-center gap-2 border-b border-neutral-800 p-4">
                    <span class="text-red-400">⚠️</span>
                    <h2 class="font-semibold text-red-400">
                        Stock crítico (≤ 4)
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-800 text-gray-300 text-xs">
                            <tr>
                                <th class="px-3 py-2">SKU</th>
                                <th class="px-3 py-2">Marca</th>
                                <th class="px-3 py-2">Medida</th>
                                <th class="px-3 py-2">Descripción</th>
                                <th class="px-3 py-2 text-right">Costo</th>
                                <th class="px-3 py-2 text-right">Precio ML</th>
                                <th class="px-3 py-2">Título</th>
                                <th class="px-3 py-2">MLM</th>
                                <th class="px-3 py-2 text-center">Stock</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($stockBajo as $llanta)
                                <tr class="border-t border-neutral-800 hover:bg-neutral-800">

                                    <td class="px-3 py-2 font-mono text-blue-400">
                                        {{ $llanta->sku }}
                                    </td>

                                    <td class="px-3 py-2">
                                        {{ $llanta->marca ?? 'SIN MARCA' }}
                                    </td>

                                    <td class="px-3 py-2">
                                        {{ $llanta->medida ?? 'N/A' }}
                                    </td>

                                    <td class="px-3 py-2 text-gray-400">
                                        {{ $llanta->descripcion ?? '—' }}
                                    </td>

                                    <td class="px-3 py-2 text-right text-gray-300">
                                        ${{ number_format($llanta->costo, 2) }}
                                    </td>

                                    <td class="px-3 py-2 text-right text-emerald-400 font-semibold">
                                        ${{ number_format($llanta->precio_ML, 2) }}
                                    </td>

                                    <td class="px-3 py-2">
                                        {{ $llanta->title_familyname }}
                                    </td>

                                    <td class="px-3 py-2 text-gray-400">
                                        {{ $llanta->MLM ?? '—' }}
                                    </td>

                                    <td class="px-3 py-2 text-center font-bold text-red-400">
                                        {{ $llanta->stock }}
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-6 text-center text-gray-400">
                                        No se encontraron resultados para ese SKU
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $stockBajo->appends(request()->query())->links() }}
                </div>

            </div>

            {{-- ===================== --}}
            {{-- ACCIONES --}}
            {{-- ===================== --}}
            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">

                <a href="{{ route('llantas.index') }}"
                   class="rounded-md border border-neutral-800 bg-neutral-900 px-4 py-3 hover:bg-neutral-800 transition">
                    <p class="text-xs text-gray-400">Inventario</p>
                    <p class="text-sm font-semibold">Ver llantas</p>
                </a>

                <a href="{{ route('productos.index') }}"
                   class="rounded-md border border-neutral-800 bg-neutral-900 px-4 py-3 hover:bg-neutral-800 transition">
                    <p class="text-xs text-gray-400">Combos</p>
                    <p class="text-sm font-semibold">Productos compuestos</p>
                </a>

                <form action="{{ route('llantas.importar') }}" method="POST" enctype="multipart/form-data"
                      class="rounded-md border border-neutral-800 bg-neutral-900 px-4 py-3 hover:bg-neutral-800 transition">
                    @csrf
                    <label class="cursor-pointer text-sm font-semibold">
                        Importar Excel
                        <input type="file" name="archivo" hidden required>
                    </label>
                </form>
            </div>

        </div>
    </div>

</x-layouts.app>
