    <h1>Report Ratings</h1>

    @if($ratings->isEmpty())
    <p>Tidak ada data rating.</p>
    @else
    <table border="1" cellpadding="5" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>Kategori</th>
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
            <td>Makanan</td>
            <td>{{ $rating->food_rating }}</td>
            <td>{{ $rating->service_rating }}</td>
            <td>{{ $rating->app_rating }}</td>
            <td>{{ $rating->comment }}</td>
            <td>{{ $rating->pengguna->nama ?? 'Anonim' }}</td>
            <td>{{ $rating->created_at->format('d M Y') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif
