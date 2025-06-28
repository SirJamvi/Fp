<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
</head>
<body>
    <table>
        <tr>
            <td colspan="5" style="text-align: center; font-weight: bold; font-size: 16pt; color: #1F497D;">LAPORAN PELANGGAN</td>
        </tr>
        <tr>
            <td colspan="5" style="text-align: center; font-style: italic; font-size: 9pt;">Dicetak pada: {{ $printed_at }}</td>
        </tr>
        <tr></tr>
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