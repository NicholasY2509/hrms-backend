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
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 20px;
            text-decoration: underline;
        }

        .info-section {
            margin-bottom: 15px;
        }

        .info-table {
            border: none;
            width: auto;
        }

        .info-table td {
            padding: 2px 0;
            border: none;
        }

        .info-table td:first-child {
            width: 150px;
            font-weight: bold;
        }

        .info-table td:nth-child(2) {
            width: 20px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: left;
            font-size: 7.5pt;
        }

        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .signature-section {
            margin-top: 30px;
            width: 100%;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            text-align: center;
            vertical-align: top;
            padding-top: 10px;
            border: none;
            width: 20%;
        }

        .signature-space {
            height: 60px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .footer-info {
            position: fixed;
            bottom: 0;
            left: 0;
            font-size: 8pt;
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

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td>NOMOR DOKUMEN</td>
                <td>:</td>
                <td>{{ $report->document_no }}</td>
            </tr>
            <tr>
                <td>PERIODE</td>
                <td>:</td>
                <td>{{ \Carbon\Carbon::parse($meta['filters']['start_date'] ?? now())->translatedFormat('d F Y') }} -
                    {{ \Carbon\Carbon::parse($meta['filters']['end_date'] ?? now())->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                <td>DEPARTMENT</td>
                <td>:</td>
                <td>{{ $template['department_name'] }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="10%">Tanggal</th>
                <th width="15%">Nama Karyawan</th>
                <th width="12%">Posisi</th>
                <th width="8%">Mulai</th>
                <th width="8%">Selesai</th>
                <th width="8%">Jam</th>
                <th>Keterangan</th>
                <th width="12%">Nilai Lembur</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->date)->translatedFormat('d M Y') }}</td>
                    <td>{{ $item->employee?->full_name }}</td>
                    <td>{{ $item->employee?->position?->name }}</td>
                    <td class="text-center">{{ $item->start_time }}</td>
                    <td class="text-center">{{ $item->finish_time }}</td>
                    <td class="text-center">{{ $item->total_time }}</td>
                    <td>{{ $item->note }}</td>
                    <td class="text-right">Rp {{ number_format((float) ($item->real_overtime_price ?? 0), 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTAL</td>
                <td class="text-center">{{ number_format($template['totals']['hours'], 1) }}</td>
                <td></td>
                <td class="text-right">Rp {{ number_format($template['totals']['price'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="signature-section">
        <p>Medan, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>

        <table class="signature-table">
            <tr>
                <td>Dibuat Oleh,</td>
                <td>Diajukan Oleh,</td>
                <td colspan="{{ $template['show_som'] ? 3 : 2 }}">Disetujui Oleh,</td>
            </tr>
            <tr>
                <td>
                    <div class="signature-space"></div>
                    <div class="signature-name">
                        {{ $template['signatures']['dept_head'] ?? $meta['dept_head_name'] ?? '..........................' }}
                    </div>
                    <div>Dept Head</div>
                </td>
                <td>
                    <div class="signature-space"></div>
                    <div class="signature-name">{{ $template['signatures']['hrd'] }}</div>
                    <div>HRD</div>
                </td>

                @if($template['show_som'])
                    <td>
                        <div class="signature-space"></div>
                        <div class="signature-name">{{ $template['signatures']['som'] }}</div>
                        <div>SOM</div>
                    </td>
                @endif

                <td>
                    <div class="signature-space"></div>
                    <div class="signature-name">{{ $template['signatures']['adh'] }}</div>
                    <div>ADH</div>
                </td>
                <td>
                    <div class="signature-space"></div>
                    <div class="signature-name">{{ $template['signatures']['branch_manager'] }}</div>
                    <div>Branch Manager</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer-info">
        Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} | Sistem HRMS Deltamas
    </div>
</body>

</html>