<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kategori Barang</title>
</head>

<body>
    <h1>Data Kategori Barang</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Kode Kategori</th>
            <th>Nama Kategori</th>
        </tr>
        @foreach ($data as $d)
            <tr>
                <th>{{$d->kategori_id}}</th>
                <th>{{$d->kategori_kode}}</th>
                <th>{{$d->kategori_nama}}</th>
            </tr>
        @endforeach
    </table>
</body>

</html>