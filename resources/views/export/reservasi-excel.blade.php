<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
</head>
<body>
    <table>
        <tr>
            <td colspan="7" style="text-align: center; font-weight: bold; font-size: 16pt; color: #1F497D;">LAPORAN RESERVASI</td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center; font-style: italic; font-size: 9pt;">Dicetak pada: {{ $printed_at }}</td>
        </tr>
        <tr></tr>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Reservasi</th>
                <th>Nama Pelanggan</th>
                <th>Catatan</th>
                <th>Waktu Kedatangan</th>
                <th>Nomor Meja</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservasis as $index => $r)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $r->kode_reservasi }}</td>
                    <td>{{ $r->nama_pelanggan ?? ($r->pengguna->nama ?? '-') }}</td>
                    <td>{{ $r->catatan ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->waktu_kedatangan)->format('d M Y H:i') }}</td>
                    <td>{{ $r->meja_display }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $r->status)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>