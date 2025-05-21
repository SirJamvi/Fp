<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reservasi Export</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
<h3>Data Reservasi</h3>
<table>
    <thead>
    <tr>
        <th>Kode Reservasi</th>
        <th>Nama</th>
        <th>Catatan</th>
        <th>Waktu Kedatangan</th>
        <th>Nomor Meja</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
    @foreach($reservasis as $r)
        <tr>
            <td>{{ $r->kode_reservasi }}</td>
            <td>{{ $r->nama_pelanggan }}</td>
            <td>{{ $r->catatan }}</td>
            <td>{{ \Carbon\Carbon::parse($r->waktu_kedatangan)->format('d M Y H:i') }}</td>
            <td>{{ optional($r->meja)->nomor_meja }}</td>
            <td>{{ ucfirst($r->status) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
