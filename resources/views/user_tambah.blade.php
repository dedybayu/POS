<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Tambah User</title>
</head>
<body>
    <h3>Form Tambah User</h3>
    <form method="post" action="/user/tambah_simpan">
        @csrf
        <label for="username">Username</label>
        <input type="text" name="username" id="username" placeholder="Masukan Username">
        <br>
        <label for="nama">Nama</label>
        <input type="text" name="nama" id="nama" placeholder="Masukan Nama">
        <br>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Masukan password">
        <br>
        <label for="level_id">Level ID</label>
        <input type="number" name="level_id" id="level_id" placeholder="Masukan Level ID">
        <br><br>
        <input type="submit" class="btn btn-success" value="Simpan"></input>

    </form>
</body>
</html>