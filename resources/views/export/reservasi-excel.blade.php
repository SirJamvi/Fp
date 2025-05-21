<table>
    <thead>
    <tr>
        <th style="background-color: #4CAF50; color: white;">No</th>
        <th style="background-color: #4CAF50; color: white;">Kode Reservasi</th>
        <th style="background-color: #4CAF50; color: white;">Nama Pelanggan</th>
        <th style="background-color: #4CAF50; color: white;">Catatan</th>
        <th style="background-color: #4CAF50; color: white;">Waktu Kedatangan</th>
        <th style="background-color: #4CAF50; color: white;">Nomor Meja</th>
        <th style="background-color: #4CAF50; color: white;">Status</th>
    </tr>
    </thead>
    <tbody>
    @foreach($reservasis as $index => $r)
        <tr>
            <td>{{ $index + 1 }}</td>
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
