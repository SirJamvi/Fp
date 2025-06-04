<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pelanggan</title>
    <style>
        /* Tambahkan style CSS PDF sederhana */
        body { font-family: DejaVu Sans, sans-serif; }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 1rem;
        }
        th, td {
            border: 1px solid #888;
            padding: 6px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h3 style="text-align: center;">Laporan Pelanggan</h3>
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
</body>
</html>
