<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Nomor HP</th>
            <th>Tanggal Daftar</th>
        </tr>
    </thead>
    <tbody>
        @foreach($customers as $idx => $cust)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $cust->nama }}</td>
                <td>{{ $cust->email }}</td>
                <td>{{ $cust->nomor_hp }}</td>
                <td>{{ $cust->created_at->format('d M Y H:i') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
