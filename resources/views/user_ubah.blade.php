<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Ubah User</title>
</head>
<body>
    <h3>Form Ubah User</h3>
    <a href="/user">Kembali</a>
    <form method='post' action="/user/ubah_simpan/{{$data->user_id}}">
        @csrf
        @method('PUT')

        <label for="username">Username</label>
        <input type="text" name="username" id="username" placeholder="Masukan Username" value="{{$data->username}}">
        <br>
        <label for="nama">Nama</label>
        <input type="text" name="nama" id="nama" placeholder="Masukan Nama" value="{{$data->nama}}">
        <br>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Masukan password" value="{{$data->password}}">
        <br>
        <label for="level_id">Level ID</label>
        <input type="number" name="level_id" id="level_id" placeholder="Masukan Level ID" value="{{$data->level_id}}">
        <br><br>
        <input type="submit" class="btn btn-success" value="Simpan"></input>
    </form>
</body>
</html>