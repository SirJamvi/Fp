<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Reservasi</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        h3 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #444; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
    </style>
</head>
<body>
    <h3>Data Reservasi</h3>
    @include('export.reservasi-excel', ['reservasis' => $reservasis])
</body>
</html>
