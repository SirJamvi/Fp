<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data Costumer</title>
    <style>
        /* Base font for PDF */
        @page { margin: 20px; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #2c3e50; margin: 0; padding: 0; }
        h3 {
            text-align: center;
            font-size: 20px;
            margin: 20px 0;
            color: #34495e;
        }
        /* Table styling */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead { background-color: #34495e; }
        thead th {
            color: #ecf0f1;
            font-weight: 600;
            font-size: 12px;
            padding: 8px;
            text-align: left;
        }
        tbody tr:nth-child(even) { background-color: #f9f9f9; }
        tbody td {
            padding: 8px;
            font-size: 12px;
            border-bottom: 1px solid #ddd;
        }
        /* Footer if needed */
        .footer {
            position: fixed;
            bottom: 20px;
            width: 100%;
            text-align: right;
            font-size: 10px;
            color: #95a5a6;
        }
    </style>
</head>
<body>
    <h3>Laporan Pelanggan</h3>
       <div class="footer">
        Dicetak pada {{ now()->format('d M Y H:i') }}
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
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
