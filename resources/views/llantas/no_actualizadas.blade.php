<x-layouts.app title="Llantas no actualizadas">

<div class="space-y-6">

    <h1 class="text-2xl font-bold text-red-400">
        ⚠️ Llantas NO actualizadas en el último import
    </h1>

    @if(session('success'))
        <div class="p-4 rounded bg-green-900 text-green-200">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('llantas.poner_cero') }}"
          onsubmit="return confirm('¿Seguro que deseas poner en 0 estas llantas?')">
        @csrf

        <button
            type="submit"
            class="mb-4 px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white font-semibold">
            🔥 Mandar todas estas llantas a stock 0
        </button>
    </form>

    <div class="rounded-lg bg-neutral-900 border border-neutral-800 overflow-hidden">
        <table class="w-full text-sm text-gray-300">
            <thead class="bg-neutral-800 text-gray-400">
                <tr>
                    <th class="px-4 py-3 text-left">SKU</th>
                    <th class="px-4 py-3">Marca</th>
                    <th class="px-4 py-3">Medida</th>
                    <th class="px-4 py-3 text-center">Stock</th>
                    <th class="px-4 py-3">Último import</th>
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
                        <td class="px-4 py-2 text-center text-red-400 font-bold">
                            {{ $llanta->stock }}
                        </td>
                        <td class="px-4 py-2 text-xs text-gray-400">
                            {{ $llanta->last_import_at ?? 'Nunca' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-400">
                            Todo está actualizado 🎉
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $llantas->links() }}

</div>

</x-layouts.app>
