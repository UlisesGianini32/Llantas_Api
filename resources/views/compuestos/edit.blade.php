<x-layouts.app :title="__('Editar producto compuesto')">

    <div class="mx-auto max-w-xl space-y-6">

        <h1 class="text-2xl font-bold text-white">
            ✏️ Editar producto compuesto – {{ $compuesto->sku }}
        </h1>

        {{-- ✅ ERRORES --}}
        @if ($errors->any())
            <div class="rounded-lg border border-red-800 bg-red-950 p-4 text-red-200">
                <div class="font-semibold mb-2">Hay errores:</div>
                <ul class="list-disc pl-5 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-xl border border-neutral-700 bg-neutral-900 p-6 space-y-4 text-white">

            <form method="POST"
                  action="{{ route('productos.update', $compuesto->id) }}"
                  class="space-y-4">
                @csrf
                @method('PUT')

                {{-- LLANTA BASE --}}
                <div>
                    <p class="text-sm text-gray-400">Llanta base (SKU)</p>
                    <p class="font-mono text-indigo-400">
                        {{ $compuesto->llanta->sku ?? '—' }}
                    </p>
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
                    <label class="block text-sm mb-1 text-gray-400">Título MercadoLibre</label>
                    <input type="text"
                           name="title_familyname"
                           value="{{ old('title_familyname', $compuesto->title_familyname) }}"
                           required
                           class="w-full rounded bg-neutral-800 border border-neutral-600 px-3 py-2 text-white">
                </div>

                {{-- COSTO (MANUAL) --}}
                <div>
                    <label class="block text-sm mb-1 text-gray-400">Costo (manual)</label>
                    <input type="number"
                           step="0.01"
                           name="costo"
                           value="{{ old('costo', $compuesto->costo) }}"
                           class="w-full rounded bg-neutral-800 border border-neutral-600 px-3 py-2 text-white">
                    <p class="mt-1 text-xs text-gray-500">
                        Si lo dejas vacío, se usa el calculado desde la llanta (costo * piezas).
                    </p>
                </div>

                {{-- PRECIO ML (MANUAL) --}}
                <div>
                    <label class="block text-sm mb-1 text-gray-400">Precio MercadoLibre (manual)</label>
                    <input type="number"
                           step="0.01"
                           name="precio_ML"
                           value="{{ old('precio_ML', $compuesto->precio_ML) }}"
                           class="w-full rounded bg-neutral-800 border border-neutral-600 px-3 py-2 text-white">
                    <p class="mt-1 text-xs text-gray-500">
                        Si lo editas, el import ya NO lo pisa.
                    </p>
                </div>

                {{-- MLM --}}
                <div>
                    <label class="block text-sm mb-1 text-gray-400">Código MercadoLibre (MLM)</label>
                    <input type="text"
                           name="MLM"
                           value="{{ old('MLM', $compuesto->MLM) }}"
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
