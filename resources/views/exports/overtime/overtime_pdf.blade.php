<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Form Pengajuan Lembur</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 7px;
            margin: 5px;
        }

        .header {
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 8px;
        }

        .info-row {
            margin-bottom: 4px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 10px;
        }

        th {
            background-color: #f0f0f0;
            padding: 2px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
            height: 18px;
        }

        td {
            padding: 2px 4px;
            border: 1px solid #ddd;
            height: 16px;
            vertical-align: middle;
        }

        th:nth-child(8),
        td:nth-child(8) {
            width: 200px;
            max-width: 200px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .signature-section {
            margin-top: 15px;
        }

        .signature-row {}

        .signature-cell {
            text-align: center;
            min-height: 200px;
            vertical-align: top;
            border: none;
        }

        .signature-cell-name {
            padding-top: 60px
        }

        .signature-name {}

        .signature-title {
            font-style: italic;
            font-size: 6px;
            color: #666;
        }
    </style>
</head>

<body>
    @php
        $service = new \App\Modules\Overtime\Services\OvertimeTemplateService();
        $template = $service->getTemplateData($data);
    @endphp

    <div class="header">FORM PENGAJUAN LEMBUR</div>

    <div class="info-row">
        <span class="info-label">NO</span>
        <span>: {{ $report->document_no }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">HAL</span>
        <span>: PENGAJUAN LEMBUR</span>
    </div>
    <div class="info-row">
        <span class="info-label">PERIODE LEMBUR</span>
        <span>:
            {{ \Carbon\Carbon::parse($meta['filters']['start_date'] ?? now())->locale('id')->translatedFormat('d F Y') }}
            -
            {{ \Carbon\Carbon::parse($meta['filters']['end_date'] ?? now())->locale('id')->translatedFormat('d F Y') }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">DEPARTMENT:</span>
        <span>: {{ $template['department_name'] }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Posisi Kerja</th>
                <th>Waktu Mulai</th>
                <th>Waktu Selesai</th>
                <th>Jumlah Jam</th>
                <th>Keterangan</th>
                <th>Nilai Lembur</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->date)->locale('id')->translatedFormat('d F Y') }}</td>
                    <td>{{ strtoupper($item->employee?->alias ?? '') }}</td>
                    <td>{{ $item->employee?->position?->name }}</td>
                    <td>{{ $item->start_time }}</td>
                    <td>{{ $item->finish_time }}</td>
                    <td>{{ $item->total_time }}</td>
                    <td>{{ $item->note }}</td>
                    <td>Rp {{ number_format((float) ($item->real_overtime_price ?? 0), 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" style="text-align: right;"><strong>TOTAL</strong></td>
                <td style="text-align: center;"><strong>{{ number_format($template['totals']['hours'], 1) }}</strong>
                </td>
                <td></td>
                <td colspan="1"><strong>Rp {{ number_format($template['totals']['price'], 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-row">
            <span>Medan, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</span>
        </div>

        <table style="margin-top: 2px; width: 100%; border: none;">
            <tr style="">
                <td class="signature-cell" style="width: 20%;">
                    <div class="signature-name">Dibuat Oleh</div>
                </td>
                <td class="signature-cell" style="width: 20%;">
                    <div class="signature-name">Diajukan Oleh</div>
                </td>
                <td class="signature-cell" colspan="{{ $template['show_som'] ? 3 : 2 }}">
                    <div class="signature-name">Disetujui Oleh</div>
                </td>
            </tr>
            <tr></tr>
            <tr></tr>
            <tr></tr>
            <tr style="">
                <td class="signature-cell-name signature-cell">
                    <div class="signature-name">
                        {{ strtoupper($template['signatures']['dept_head'] ?? $meta['dept_head_name'] ?? '-') }}
                    </div>
                    <div class="signature-title">Dept Head</div>
                </td>
                <td class="signature-cell-name signature-cell">
                    <div class="signature-name">{{ strtoupper($template['signatures']['hrd'] ?? '-') }}</div>
                    <div class="signature-title">HRD</div>
                </td>

                @if($template['show_som'])
                    <td class="signature-cell-name signature-cell">
                        <div class="signature-name">{{ strtoupper($template['signatures']['som'] ?? '-') }}</div>
                        <div class="signature-title">SOM</div>
                    </td>
                @endif

                <td class="signature-cell-name signature-cell">
                    <div class="signature-name">{{ strtoupper($template['signatures']['adh'] ?? '-') }}</div>
                    <div class="signature-title">ADH</div>
                </td>
                <td class="signature-cell-name signature-cell">
                    <div class="signature-name">{{ strtoupper($template['signatures']['branch_manager'] ?? '-') }}</div>
                    <div class="signature-title">Branch Manager</div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>