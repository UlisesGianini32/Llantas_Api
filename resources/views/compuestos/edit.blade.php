<x-layouts.app :title="__('Editar producto compuesto')">

    <div class="mx-auto max-w-xl space-y-6">

        <h1 class="text-2xl font-bold text-white">
            ✏️ Editar producto compuesto – {{ $compuesto->sku }}
        </h1>

        <div class="rounded-xl border border-neutral-700 bg-neutral-900 p-6 space-y-4 text-white">

            <form method="POST"
                  action="{{ route('productos.update', $compuesto->id) }}">
                @csrf
                @method('PUT')

                {{-- LLANTA BASE --}}
                <div>
                    <p class="text-sm text-gray-400">Llanta base</p>
                    <p class="font-mono text-indigo-400">
                        {{ $compuesto->llanta->sku ?? '—' }}
                    </p>
                </div>

                {{-- MARCA --}}
                <div>
                    <label class="text-sm text-gray-400">Marca</label>
                    <input type="text"
                           name="marca"
                           value="{{ old('marca', $compuesto->llanta->marca ?? '') }}"
                           required
                           class="w-full rounded bg-neutral-800 p-2 text-white">
                </div>

                {{-- MEDIDA --}}
                <div>
                    <label class="text-sm text-gray-400">Medida</label>
                    <input type="text"
                           name="medida"
                           value="{{ old('medida', $compuesto->llanta->medida ?? '') }}"
                           required
                           class="w-full rounded bg-neutral-800 p-2 text-white">
                </div>

                {{-- DESCRIPCIÓN --}}
                <div>
                    <label class="text-sm text-gray-400">Descripción</label>
                    <textarea name="descripcion"
                              class="w-full rounded bg-neutral-800 p-2 text-white">{{ old('descripcion', $compuesto->descripcion) }}</textarea>
                </div>

                {{-- TÍTULO ML --}}
                <div>
                    <label class="text-sm text-gray-400">Título MercadoLibre</label>
                    <input type="text"
                           name="title_familyname"
                           value="{{ old('title_familyname', $compuesto->title_familyname) }}"
                           required
                           class="w-full rounded bg-neutral-800 p-2 text-white">
                </div>

                {{-- PRECIO ML MANUAL --}}
                <div>
                    <label class="text-sm text-gray-400">Precio MercadoLibre</label>
                    <input type="number"
                           step="0.01"
                           name="precio_ML"
                           value="{{ old('precio_ML', $compuesto->precio_ML) }}"
                           class="w-full rounded bg-neutral-800 p-2 text-white">
                </div>

                {{-- MLM --}}
                <div>
                    <label class="text-sm text-gray-400">Código MercadoLibre (MLM)</label>
                    <input type="text"
                           name="MLM"
                           value="{{ old('MLM', $compuesto->MLM) }}"
                           class="w-full rounded bg-neutral-800 p-2 text-white">
                </div>

                {{-- BOTONES --}}
                <div class="flex gap-3 pt-4">
                    <button class="rounded bg-indigo-600 px-4 py-2 text-white">
                        Guardar
                    </button>

                    <a href="{{ route('productos.index') }}"
                       class="rounded bg-neutral-700 px-4 py-2 text-white">
                        Cancelar
                    </a>
                </div>

            </form>

        </div>
    </div>

</x-layouts.app>
