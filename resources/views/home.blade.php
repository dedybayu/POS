<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <header class="bg-white shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Home Point Of Salles</h1>
        </div>
    </header>
    <div class="m-10 bg-gray-100 h-screen flex-col items-center justify-center p-6">
        <div class="w-full max-w-6xl grid grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-md text-center">
                <h2 class="text-xl font-semibold">Total Penjualan</h2>
                <p class="text-3xl font-bold mt-2">Rp xx.xxxx.xxx</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-md text-center">
                <h2 class="text-xl font-semibold">Jumlah Transaksi</h2>
                <p class="text-3xl font-bold mt-2">xxx</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-md text-center">
                <h2 class="text-xl font-semibold">Produk Terjual</h2>
                <p class="text-3xl font-bold mt-2">xxx</p>
            </div>
        </div>
    
        <div class="w-full max-w-6xl mt-6 bg-white p-6 rounded-2xl shadow-md">
            <h2 class="text-xl font-semibold mb-4">Riwayat Transaksi</h2>
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-3 text-left">ID</th>
                        <th class="p-3 text-left">Tanggal</th>
                        <th class="p-3 text-left">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t">
                        <td class="p-3">001</td>
                        <td class="p-3">19-02-2025</td>
                        <td class="p-3">Rp 500.000</td>
                    </tr>
                    <tr class="border-t">
                        <td class="p-3">002</td>
                        <td class="p-3">19-02-2025</td>
                        <td class="p-3">Rp 750.000</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
   
</body>
</html>