<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kehadiran</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f4f4f4; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h2 class="text-center">Laporan Kehadiran Karyawan</h2>
    <p class="text-center">Diexport pada: {{ now()->format('d M Y H:i') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>NIK</th>
                <th>Nama</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                @php
                    $employee = $row->attendance_working_hour->employee ?? null;
                @endphp
                <tr>
                    <td>{{ $employee ? $employee->nik : '-' }}</td>
                    <td>{{ $employee ? $employee->full_name : '-' }}</td>
                    <td>{{ $row->attendance_working_hour ? $row->attendance_working_hour->attendance_at : '-' }}</td>
                    <td>{{ $row->incoming_scan ?: '-' }}</td>
                    <td>{{ $row->outgoing_scan ?: '-' }}</td>
                    <td>{{ $row->attendance_status ? $row->attendance_status->name : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
