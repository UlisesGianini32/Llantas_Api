<x-layouts.app title="Productos compuestos">

<div class="space-y-6">

    <h1 class="text-2xl font-bold text-white">📦 Productos compuestos</h1>

    {{-- BUSCADOR --}}
    <form method="GET" action="{{ route('productos.index') }}">
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Buscar por SKU..."
            class="w-full rounded-lg bg-neutral-900 border border-neutral-800 px-4 py-2 text-sm text-gray-300"
        >
    </form>

    <div class="rounded-lg bg-neutral-900 border border-neutral-800 overflow-hidden">
        <table class="w-full text-sm text-gray-300">
            <thead class="bg-neutral-800 text-gray-400">
                <tr>
                    <th class="px-4 py-2">SKU</th>
                    <th>Marca</th>
                    <th>Medida</th>
                    <th class="text-right">Costo</th>
                    <th class="text-right">Precio ML</th>
                    <th>Título</th>
                    <th class="text-center">Stock disp.</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @foreach ($compuestos as $c)
                <tr class="border-t border-neutral-800">
                    <td class="px-4 py-2 text-blue-400 font-mono">{{ $c->sku }}</td>
                    <td>{{ $c->llanta->marca }}</td>
                    <td>{{ $c->llanta->medida ?? 'N/A' }}</td>

                    <td class="text-right">
                        ${{ number_format($c->costo_calculado, 2) }}
                    </td>

                    <td class="text-right text-green-400 font-semibold">
                        ${{ number_format($c->precio_ml_calculado, 2) }}
                    </td>

                    <td>{{ $c->titulo_real }}</td>

                    <td class="text-center font-bold">
                        {{ $c->stock_disponible }}
                    </td>

                    <td class="text-center">
                        <a href="{{ route('productos.edit', $c->id) }}" class="text-indigo-400">
                            ✏️ Editar
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $compuestos->links() }}

</div>
</x-layouts.app>
