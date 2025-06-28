<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Reservasi</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 20px; }
        h3 { text-align: center; margin-bottom: 20px; color: #34495e; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #444; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #34495e; color: white; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
        .text-muted { color: #777; }
    </style>
</head>
<body>
    <h3>Data Reservasi</h3>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Reservasi</th>
                <th>Nama Pelanggan</th>
                <th>Meja</th>
                <th>Jumlah Tamu</th>
                <th>Waktu Kedatangan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservasis as $i => $r)
                @php
                    $mejaList = collect();
                    if ($r->combined_tables) {
                        $decoded = json_decode($r->combined_tables, true) ?: [];
                        if (is_string($decoded)) {
                            $decoded = json_decode($decoded, true);
                        }
                        if (is_array($decoded)) {
                            $mejaList = \App\Models\Meja::whereIn('id', $decoded)->get();
                        }
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $r->kode_reservasi }}</td>
                    <td>{{ $r->nama_pelanggan ?? '-' }}</td>
                    <td>
                         @php
                                        $mejaList = $r->meja ?? collect();

                                        if ($mejaList instanceof \Illuminate\Database\Eloquent\Collection && $mejaList->isEmpty() && $r->combined_tables) {
                                            $decoded = json_decode($r->combined_tables, true) ?: [];
                                            $mejaList = \App\Models\Meja::whereIn('id', $decoded)->get();
                                        }
                                    @endphp

                                    @if($mejaList->isNotEmpty())
                                        @foreach($mejaList as $mejaObj)
                                            <span class="inline-block bg-teal-100 text-teal-800 text-xs font-medium px-2.5 py-0.5 rounded mr-1 mb-1">
                                                {{ $mejaObj->nomor_meja }} ({{ $mejaObj->area }})
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                    </td>
                    <td class="text-center">{{ $r->jumlah_tamu ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->waktu_kedatangan)->format('d M Y H:i') }}</td>
                    <td class="text-center">{{ ucfirst($r->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
