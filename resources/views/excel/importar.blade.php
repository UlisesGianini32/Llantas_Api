<x-layouts.app title="Importar Excel">

<div class="max-w-xl mx-auto space-y-6">

    <h1 class="text-2xl font-bold text-white">
        📥 Importar inventario desde Excel
    </h1>

    {{-- MENSAJES --}}
    @if(session('success'))
        <div class="rounded-lg bg-green-900/30 border border-green-700 text-green-300 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg bg-red-900/30 border border-red-700 text-red-300 px-4 py-3">
            {{ session('error') }}
        </div>
    @endif

    {{-- FORM --}}
    <form
        method="POST"
        action="{{ route('excel.importar') }}"
        enctype="multipart/form-data"
        class="space-y-4 bg-neutral-900 border border-neutral-800 rounded-xl p-6"
    >
        @csrf

        <div>
            <label class="block text-sm text-gray-400 mb-1">
                Archivo Excel (.xlsx)
            </label>

            <input
                type="file"
                name="archivo"
                required
                class="block w-full text-sm text-gray-300
                       file:bg-neutral-800 file:border file:border-neutral-700
                       file:rounded-md file:px-4 file:py-2
                       file:text-white file:cursor-pointer
                       hover:file:bg-neutral-700"
            >
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('dashboard') }}"
               class="px-4 py-2 rounded-md bg-neutral-800 text-gray-300 hover:bg-neutral-700">
                Cancelar
            </a>

            <button
                type="submit"
                class="px-5 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-500 font-semibold"
            >
                ⬆ Importar
            </button>
        </div>
    </form>

    {{-- INFO --}}
    <div class="text-sm text-gray-500">
        <p>• El archivo debe contener las columnas esperadas.</p>
        <p>• Las llantas nuevas se crearán automáticamente.</p>
        <p>• Los productos compuestos se generan solos.</p>
    </div>

</div>

</x-layouts.app>
