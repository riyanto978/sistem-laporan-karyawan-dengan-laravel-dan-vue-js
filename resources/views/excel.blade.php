<table >
    <thead>
        <tr>
            <th>id</th>
            <th>nama</th>
            <th>username</th>
            <th>level</th>
        </tr>
        
    </thead>
    <tbody>
        @foreach ($user as $item)
        <tr>
            <td>{{ $item->id_pol }}</td>
            <td>{{ $item->kode_pol }}</td>
            <td>{{ $item->tahun }}</td>
            <td>{{ $item->nama_pol }}</td>
        </tr>    
        @endforeach
        
    </tbody>
</table>