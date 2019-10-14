<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
<div><center>Laporan Perso {{ ambil_tanggal($awal) }} - {{ ambil_tanggal($akhir) }}</center></div>        
    <br>
    <br>
    <br>
    @foreach ($data as $cari)    
    <?php
    $no = 1;
    ?>
    Tanggal : {{ $cari['tanggal'] }}
    <table width="100%" style="border: solid 1px black" style="width:100%" border="1">        
        <thead>            
            <tr>
                <td>No</td>
                <td>Proses</td>
                <td>kode pol</td>
                <td>Nama Pol</td>
                <td>durasi</td>
                <td>total good</td>
                <td>total Reject</td>        
            </tr>
        </thead>
        <tbody>
            @foreach ($cari['data'] as $item)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $item->proses }}</td>
                <td>{{ $item->kode_pol }}</td>
                <td>{{ $item->nama_pol }}</td>
                <td>07:00 - 23:00</td>
                <td>{{ $item->total }}</td>
                <td>{{ $item->dead }}</td>
            </tr>    
            @endforeach            
        </tbody>        
    </table>
    <br>
    <br>
    @endforeach
</body>
</html>