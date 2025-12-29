<x-layouts.app :title="__('Editar producto compuesto')">

    <div class="mx-auto max-w-xl space-y-6">

        <h1 class="text-2xl font-bold text-white">
            ✏️ Editar producto compuesto – {{ $compuesto->sku }}
        </h1>

        <div class="rounded-xl border border-neutral-700 bg-neutral-900 p-6 space-y-4 text-white">

            <form method="POST" action="{{ route('productos.update', $compuesto->id) }}" class="space-y-4">
                @csrf

                {{-- LLANTA BASE --}}
                <div>
                    <p class="text-sm text-gray-400">Llanta base</p>
                    <p class="font-mono">{{ $compuesto->llanta->sku ?? '—' }}</p>
                </div>

                {{-- MARCA --}}
                <div>
                    <label class="block text-sm mb-1 text-gray-400">Marca</label>
                    <input type="text"
                           name="marca"
                           value="{{ old('marca', $compuesto->llanta->marca ?? '') }}"
                           required
                           class="w-full rounded bg-neutral-800 border border-neutral-600 px-3 py-2 text-white">
                </div>

                {{-- MEDIDA --}}
                <div>
                    <label class="block text-sm mb-1 text-gray-400">Medida</label>
                    <input type="text"
                           name="medida"
                           value="{{ old('medida', $compuesto->llanta->medida ?? '') }}"
                           required
                           class="w-full rounded bg-neutral-800 border border-neutral-600 px-3 py-2 text-white">
                </div>

                {{-- DESCRIPCIÓN --}}
                <div>
                    <label class="block text-sm mb-1 text-gray-400">Descripción</label>
                    <textarea name="descripcion"
                              rows="3"
                              class="w-full rounded bg-neutral-800 border border-neutral-600 px-3 py-2 text-white">{{ old('descripcion', $compuesto->descripcion) }}</textarea>
                </div>

                {{-- TÍTULO --}}
                <div>
                    <label class="block text-sm mb-1 text-gray-400">Título</label>
                    <input type="text"
                           name="title_familyname"
                           value="{{ old('title_familyname', $compuesto->title_familyname) }}"
                           required
                           class="w-full rounded bg-neutral-800 border border-neutral-600 px-3 py-2 text-white">
                </div>

                {{-- PRECIO ML --}}
                <div>
                    <label class="block text-sm mb-1 text-gray-400">Precio MercadoLibre</label>
                    <input type="number"
                           step="0.01"
                           name="precio_ML"
                           value="{{ old('precio_ML', $compuesto->precio_ML) }}"
                           required
                           class="w-full rounded bg-neutral-800 border border-neutral-600 px-3 py-2 text-white">
                </div>

                {{-- BOTONES --}}
                <div class="flex gap-3 pt-4">
                    <button type="submit"
                            class="rounded bg-indigo-600 px-4 py-2 font-semibold hover:bg-indigo-700">
                        Guardar
                    </button>

                    <a href="{{ route('productos.index') }}"
                       class="rounded bg-neutral-700 px-4 py-2 hover:bg-neutral-600">
                        Cancelar
                    </a>
                </div>

            </form>

        </div>
    </div>

</x-layouts.app>
