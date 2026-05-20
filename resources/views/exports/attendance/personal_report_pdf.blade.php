<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Personal Attendance Report</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        .header { margin-bottom: 30px; border-bottom: 2px solid #1a56db; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #1a56db; }
        .info-grid { width: 100%; margin-top: 15px; }
        .info-grid td { border: none; padding: 2px 0; }
        .label { font-weight: bold; width: 100px; color: #64748b; }
        
        .summary-boxes { width: 100%; margin-top: 20px; }
        .summary-box { border: 1px solid #e2e8f0; padding: 10px; text-align: center; width: 23%; display: inline-block; margin-right: 1%; }
        .summary-count { font-size: 18px; font-weight: bold; display: block; color: #1e293b; }
        .summary-label { font-size: 8px; color: #64748b; text-transform: uppercase; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px; text-align: left; }
        th { background-color: #f8fafc; font-weight: bold; color: #475569; }
    </style>
</head>
<body>
    @php
        $first = $data->first();
        $employee = $first->attendance_working_hour->employee ?? null;
        
        $statusCounts = [];
        foreach($data as $row) {
            $statusName = $row->attendance_status->name ?? 'Unknown';
            if (!isset($statusCounts[$statusName])) $statusCounts[$statusName] = 0;
            $statusCounts[$statusName]++;
        }
    @endphp

    <div class="header">
        <h2>Laporan Kehadiran Karyawan</h2>
        <p>Periode: {{ $filters['start_date'] ?? '-' }} s/d {{ $filters['end_date'] ?? '-' }}</p>
    </div>

    @if($employee)
    <table class="info-grid">
        <tr>
            <td class="label">Nama</td>
            <td>: {{ $employee->full_name }}</td>
            <td class="label">Departemen</td>
            <td>: {{ $employee->department->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">NIK</td>
            <td>: {{ $employee->nik }}</td>
            <td class="label">Jabatan</td>
            <td>: {{ $employee->position->name ?? '-' }}</td>
        </tr>
    </table>
    @endif

    <div style="margin-top: 30px; margin-bottom: 15px; font-weight: bold; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">
        RINGKASAN KEHADIRAN
    </div>
    
    <div style="width: 100%; margin-bottom: 20px;">
        @foreach($statusCounts as $status => $count)
            <div style="border: 1px solid #e2e8f0; padding: 10px; display: inline-block; width: 100px; margin-right: 10px; margin-bottom: 10px; text-align: center;">
                <span style="font-size: 16px; font-weight: bold; display: block;">{{ $count }}</span>
                <span style="font-size: 8px; color: #64748b; text-transform: uppercase;">{{ $status }}</span>
            </div>
        @endforeach
        <div style="clear: both;"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jam Kerja</th>
                <th>Clock In</th>
                <th>Clock Out</th>
                <th>Terlambat</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                @php $workingHour = $row->attendance_working_hour->working_hour ?? null; @endphp
                <tr>
                    <td>{{ $row->attendance_working_hour ? $row->attendance_working_hour->attendance_at : '-' }}</td>
                    <td>{{ $workingHour ? $workingHour->name : '-' }}</td>
                    <td>{{ $row->incoming_scan ?: '-' }}</td>
                    <td>{{ $row->outgoing_scan ?: '-' }}</td>
                    <td>{{ $row->late_time ? $row->late_time . 'm' : '-' }}</td>
                    <td>{{ $row->attendance_status ? $row->attendance_status->name : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
