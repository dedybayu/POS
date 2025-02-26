<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User</title>
</head>

<body>
    <h1>Data User</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Nama</th>
            <th>ID Level Pengguna</th>
        </tr>
        @foreach ($data as $d)
            <tr>
                <th>{{$d->user_id}}</th>
                <th>{{$d->username}}</th>
                <th>{{$d->nama}}</th>
                <th>{{$d->level_id}}</th>
            </tr>
        @endforeach
    </table>
</body>

</html>