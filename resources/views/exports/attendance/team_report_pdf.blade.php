<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Team Attendance Summary Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1a56db;
            padding-bottom: 15px;
        }

        .header h2 {
            margin: 0;
            text-transform: uppercase;
            color: #1a56db;
        }

        .header p {
            margin: 5px 0;
            color: #666;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            background-color: #f1f5f9;
            padding: 8px;
            margin-bottom: 15px;
            color: #1e293b;
            border-left: 4px solid #1a56db;
        }

        .grid-container {
            width: 100%;
        }

        .summary-card {
            width: 30%;
            display: inline-block;
            vertical-align: top;
            margin-right: 2%;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .card-header {
            background-color: #f8fafc;
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
        }

        .card-body {
            padding: 8px;
        }

        .vertical-table {
            width: 100%;
            border-collapse: collapse;
        }

        .vertical-table td {
            border: none;
            padding: 3px 0;
            text-align: left;
            font-size: 9px;
        }

        .label {
            color: #64748b;
            width: 70%;
        }

        .value {
            font-weight: bold;
            width: 30%;
        }

        .total-row {
            border-top: 1px solid #e2e8f0;
            margin-top: 5px;
            padding-top: 5px;
        }

        .total-row .label {
            color: #1a56db;
            font-weight: bold;
        }

        .total-row .value {
            color: #1a56db;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Ringkasan Kehadiran Per Tim</h2>
        <p>Periode: {{ $filters['start_date'] ?? '-' }} s/d {{ $filters['end_date'] ?? '-' }}</p>
        <p>Diexport pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @php
        $statuses = ['Terlambat', 'Izin', 'Absen', 'Sakit', 'Cuti', 'Training', 'Dinas_Luar_Kota'];
    @endphp

    <div class="section-title">RINGKASAN KEHADIRAN</div>
    <div class="grid-container">
        @foreach($data as $row)
            <div class="summary-card">
                <div class="card-header">
                    <strong>{{ $row->group_name }}</strong> : {{ $row->headcount }} orang
                </div>
                <div class="card-body">
                    <table class="vertical-table">
                        @foreach($statuses as $status)
                            <tr>
                                <td class="label">{{ str_replace('_', ' ', $status) }}</td>
                                <td class="value">: {{ $row->$status }}</td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td class="label">Hadir</td>
                            <td class="value">: {{ $row->Hadir }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</body>

</html>