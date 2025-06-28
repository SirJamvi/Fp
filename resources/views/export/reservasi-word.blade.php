<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reservasi Export</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
        .text-muted { color: #777; }
    </style>
</head>
<body>
    <h3 style="text-align: center; margin-bottom: 20px;">Data Reservasi</h3>

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
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $r->kode_reservasi }}</td>
                    <td>{{ $r->nama_pelanggan ?? '-' }}</td>
                    <td>{{ $r->meja_display }}</td>
                    <td class="text-center">{{ $r->jumlah_tamu ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->waktu_kedatangan)->format('d M Y H:i') }}</td>
                    <td class="text-center">{{ ucfirst($r->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>