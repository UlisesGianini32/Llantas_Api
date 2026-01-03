<x-layouts.app title="Llantas no actualizadas">

<div class="space-y-6">

    {{-- TÍTULO --}}
    <h1 class="text-2xl font-bold text-white">
        ⚠️ Llantas no actualizadas en el último import
    </h1>

    {{-- MENSAJE DE ÉXITO --}}
    @if(session('success'))
        <div class="bg-green-900 border border-green-700 text-green-200 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- BOTÓN PONER STOCK EN 0 --}}
    @if($llantas->count())
        <form
            method="POST"
            action="{{ route('llantas.poner_cero') }}"
            onsubmit="return confirm('¿Seguro que deseas poner en 0 el stock de TODAS estas llantas?');"
        >
            @csrf

            <button
                type="submit"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold"
            >
                🔥 Poner stock en 0 a estas llantas
            </button>
        </form>
    @endif

    {{-- TABLA --}}
    <div class="rounded-lg bg-neutral-900 border border-neutral-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-300">
                <thead class="bg-neutral-800 text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left">SKU</th>
                        <th class="px-4 py-3">Marca</th>
                        <th class="px-4 py-3">Medida</th>
                        <th class="px-4 py-3">Descripción</th>
                        <th class="px-4 py-3 text-center">Stock</th>
                        <th class="px-4 py-3 text-center">Última importación</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($llantas as $llanta)
                        <tr class="border-t border-neutral-800 hover:bg-neutral-800">

                            <td class="px-4 py-2 font-mono text-blue-400">
                                {{ $llanta->sku }}
                            </td>

                            <td class="px-4 py-2">
                                {{ $llanta->marca }}
                            </td>

                            <td class="px-4 py-2">
                                {{ $llanta->medida }}
                            </td>

                            <td class="px-4 py-2 text-gray-400">
                                {{ $llanta->descripcion }}
                            </td>

                            <td class="px-4 py-2 text-center font-bold text-red-400">
                                {{ $llanta->stock }}
                            </td>

                            <td class="px-4 py-2 text-center text-gray-400 text-xs">
                                {{ $llanta->last_import_at
                                    ? $llanta->last_import_at->format('Y-m-d H:i')
                                    : 'Nunca'
                                }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-400">
                                No hay llantas pendientes
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINACIÓN --}}
    {{ $llantas->links() }}

</div>

</x-layouts.app>
