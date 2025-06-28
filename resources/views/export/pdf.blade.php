<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Ratings</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2c3e50;
            margin: 0;
            padding: 20px;
        }
        h1 {
            font-size: 22px;
            color: #34495e;
            border-bottom: 2px solid #ddd;
            padding-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        thead {
            background-color: #34495e;
        }
        thead th {
            color: #ecf0f1;
            font-weight: 600;
            font-size: 14px;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tbody td {
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        .comment {
            font-style: italic;
            color: #7f8c8d;
        }
        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #95a5a6;
            text-align: right;
        }
    </style>
</head>
<body>
    <h1>Report Ratings</h1>
    @if($ratings->isEmpty())
        <p>Tidak ada data rating.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Food Rating</th>
                    <th>Service Rating</th>
                    <th>App Rating</th>
                    <th>Komentar</th>
                    <th>Pengguna</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ratings as $rating)
                    <tr>
                        <td>{{ $rating->rating_makanan }}</td>
                        <td>{{ $rating->rating_pelayanan }}</td>
                        <td>{{ $rating->rating_aplikasi }}</td>
                        <td class="comment">{{ $rating->komentar ?? '-' }}</td>
                        <td>{{ optional($rating->pengguna)->nama ?? 'Anonim' }}</td>
                        <td>{{ $rating->created_at->format('d M Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    <div class="footer">
        Dicetak pada {{ now()->format('d M Y H:i') }}
    </div>
</body>
</html>
