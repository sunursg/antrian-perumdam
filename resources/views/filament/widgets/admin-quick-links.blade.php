@php
    use App\Filament\Resources\ActivityLogResource;
    use App\Filament\Resources\AnnouncementResource;
    use App\Filament\Resources\LoketResource;
    use App\Filament\Resources\OrganizationResource;
    use App\Filament\Resources\QueueEventResource;
    use App\Filament\Resources\ServiceResource;
    use App\Filament\Resources\UserResource;

    $links = [
        [
            'title' => 'Services',
            'desc' => 'Kelola jenis layanan dan aturan operasional.',
            'url' => ServiceResource::getUrl(),
        ],
        [
            'title' => 'Audit Trail',
            'desc' => 'Riwayat pemanggilan tiket dan status loket.',
            'url' => QueueEventResource::getUrl(),
        ],
        [
            'title' => 'Activity Log',
            'desc' => 'Catatan aktivitas sistem dan user.',
            'url' => ActivityLogResource::getUrl(),
        ],
        [
            'title' => 'Pengumuman',
            'desc' => 'Konten pengumuman dan video display.',
            'url' => AnnouncementResource::getUrl(),
        ],
        [
            'title' => 'Organisasi',
            'desc' => 'Profil instansi, logo, dan jam layanan.',
            'url' => OrganizationResource::getUrl(),
        ],
        [
            'title' => 'Pengguna',
            'desc' => 'Kelola akun operator dan admin.',
            'url' => UserResource::getUrl(),
        ],
        [
            'title' => 'Loket',
            'desc' => 'Data loket dan penugasan.',
            'url' => LoketResource::getUrl(),
        ],
    ];
@endphp

<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    @foreach ($links as $link)
        <a
            href="{{ $link['url'] }}"
            class="rounded-xl border border-gray-200 bg-white px-4 py-4 shadow-sm transition hover:border-primary-300 hover:bg-gray-50"
        >
            <p class="text-sm font-semibold text-gray-900">{{ $link['title'] }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $link['desc'] }}</p>
        </a>
    @endforeach
</div>
