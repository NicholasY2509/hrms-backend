<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Attendance Report</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; text-transform: uppercase; color: #1a56db; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px; text-align: left; }
        th { background-color: #f8fafc; font-weight: bold; color: #475569; }
        tr:nth-child(even) { background-color: #f1f5f9; }
        .summary-section { margin-top: 30px; page-break-inside: avoid; }
        .summary-title { font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px; }
        .summary-table { width: 50%; }
        .text-center { text-align: center; }
        .badge { padding: 2px 5px; border-radius: 4px; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Kehadiran Harian</h2>
        <p>Periode: {{ $filters['start_date'] ?? '-' }} s/d {{ $filters['end_date'] ?? '-' }}</p>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>NIK</th>
                <th>Nama Karyawan</th>
                <th>Jabatan</th>
                <th>Departemen</th>
                <th>Tanggal</th>
                <th>Clock In</th>
                <th>Clock Out</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php $summary = []; @endphp
            @foreach($data as $row)
                @php
                    $employee = $row->attendance_working_hour->employee ?? null;
                    $deptName = $employee->department->name ?? 'Tanpa Departemen';
                    $statusName = $row->attendance_status ? $row->attendance_status->name : 'Tanpa Status';
                    if (!isset($summary[$statusName])) $summary[$statusName] = 0;
                    $summary[$statusName]++;
                @endphp
                <tr>
                    <td>{{ $employee ? $employee->nik : '-' }}</td>
                    <td>{{ $employee ? $employee->full_name : '-' }}</td>
                    <td>{{ $employee && $employee->position ? $employee->position->name : '-' }}</td>
                    <td>{{ $deptName }}</td>
                    <td>{{ $row->attendance_working_hour ? $row->attendance_working_hour->attendance_at : '-' }}</td>
                    <td>{{ $row->incoming_scan ?: '-' }}</td>
                    <td>{{ $row->outgoing_scan ?: '-' }}</td>
                    <td>{{ $row->attendance_status ? $row->attendance_status->name : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-section">
        <div class="summary-title">RINGKASAN STATUS KEHADIRAN</div>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th class="text-center">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary as $status => $count)
                <tr>
                    <td>{{ $status }}</td>
                    <td class="text-center">{{ $count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
