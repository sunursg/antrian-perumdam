<x-filament::page>
    <div class="space-y-3">
        <p class="text-sm text-slate-600">Menampilkan 200 baris terakhir dari <code>storage/logs/laravel.log</code>.</p>
        <div class="rounded-lg border border-slate-200 bg-slate-50 max-h-[600px] overflow-auto text-xs font-mono p-4 space-y-1">
            @forelse($this->lines as $line)
                <div>{{ $line }}</div>
            @empty
                <p class="text-slate-500">Log kosong.</p>
            @endforelse
        </div>
    </div>
</x-filament::page>
