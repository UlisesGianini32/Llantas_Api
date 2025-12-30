<x-layouts.app title="Editar llanta">

<div class="mx-auto max-w-lg rounded-lg border border-neutral-800 bg-neutral-900 p-6">

    <h1 class="mb-6 text-xl font-bold text-white">✏️ Editar llanta</h1>

    <form method="POST" action="{{ route('llantas.update', $llanta->id) }}">
        @csrf
        @method('PUT')

        {{-- MARCA --}}
        <div class="mb-4">
            <label class="text-sm text-gray-400">Marca</label>
            <input
                type="text"
                name="marca"
                value="{{ old('marca', $llanta->marca) }}"
                required
                class="w-full rounded bg-neutral-800 p-2 text-white"
            >
        </div>

        {{-- MEDIDA --}}
        <div class="mb-4">
            <label class="text-sm text-gray-400">Medida</label>
            <input
                type="text"
                name="medida"
                value="{{ old('medida', $llanta->medida) }}"
                required
                class="w-full rounded bg-neutral-800 p-2 text-white"
            >
        </div>

        {{-- DESCRIPCIÓN --}}
        <div class="mb-4">
            <label class="text-sm text-gray-400">Descripción</label>
            <textarea
                name="descripcion"
                class="w-full rounded bg-neutral-800 p-2 text-white"
            >{{ old('descripcion', $llanta->descripcion) }}</textarea>
        </div>

        {{-- TÍTULO --}}
        <div class="mb-4">
            <label class="text-sm text-gray-400">Título</label>
            <input
                type="text"
                name="title_familyname"
                value="{{ old('title_familyname', $llanta->title_familyname) }}"
                required
                class="w-full rounded bg-neutral-800 p-2 text-white"
            >
        </div>

        {{-- PRECIO ML --}}
        <div class="mb-4">
            <label class="text-sm text-gray-400">Precio MercadoLibre</label>
            <input
                type="number"
                step="0.01"
                name="precio_ML"
                value="{{ old('precio_ML', $llanta->precio_ML) }}"
                required
                class="w-full rounded bg-neutral-800 p-2 text-white"
            >
        </div>

        {{-- ✅ MLM --}}
        <div class="mb-4">
            <label class="text-sm text-gray-400">
                Código MercadoLibre (MLM)
            </label>
            <input
                type="text"
                name="MLM"
                value="{{ old('MLM', $llanta->MLM) }}"
                placeholder="MLM123456789"
                class="w-full rounded bg-neutral-800 p-2 text-white"
            >
        </div>

        {{-- STOCK --}}
        <div class="mb-6">
            <label class="text-sm text-gray-400">Existencias</label>
            <input
                type="number"
                name="stock"
                value="{{ old('stock', $llanta->stock) }}"
                required
                class="w-full rounded bg-neutral-800 p-2 text-white"
            >
        </div>

        <div class="flex justify-between">
            <a href="{{ route('llantas.index') }}" class="text-gray-400">
                Cancelar
            </a>
            <button class="rounded bg-indigo-600 px-4 py-2 text-white">
                Guardar
            </button>
        </div>
    </form>

</div>

</x-layouts.app>
