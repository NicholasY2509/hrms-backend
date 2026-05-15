<!DOCTYPE html>
<html>

<head>
    <title>Transisi Karir</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
        }

        .header-section {
            width: 100%;
            margin-bottom: 20px;
        }

        .logo-box {
            text-align: right;
            margin-bottom: 15px;
        }

        .title-box {
            text-align: center;
            border: 1px solid #000;
            padding: 5px;
            margin-bottom: 20px;
        }

        .title-box h1 {
            font-size: 14pt;
            margin: 0;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .info-table td:first-child {
            width: 180px;
            font-weight: bold;
        }

        .info-table td:nth-child(2) {
            width: 20px;
        }

        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .comparison-table th,
        .comparison-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .comparison-table th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .section-header {
            font-weight: bold;
            margin: 15px 0 5px 0;
            text-decoration: underline;
        }

        .note-box {
            border: 1px solid #000;
            padding: 10px;
            min-height: 80px;
            margin-bottom: 30px;
        }

        .approval-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }

        .approval-table th,
        .approval-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
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
    @foreach($data as $career)
        @php
            $template = (new \App\Modules\Career\Services\CareerTemplateService())->getTemplateData($career);
        @endphp
        <div class="container">
            <div class="logo-box">
                {{-- Logo placeholder --}}
                <b style="font-size: 14pt;">PT. DELTAMAS SURYA INDAH MULIA</b>
            </div>

            <div class="title-box">
                <h1>{{ $template['title'] }}</h1>
                <div>{{ $template['type'] }}</div>
            </div>

            <table class="info-table">
                <tr>
                    <td>Nama Lengkap</td>
                    <td>:</td>
                    <td><b>{{ $template['employee_name'] }}</b></td>
                </tr>
                <tr>
                    <td>NIK</td>
                    <td>:</td>
                    <td>{{ $template['nik'] }}</td>
                </tr>
                <tr>
                    <td>Tanggal Bergabung</td>
                    <td>:</td>
                    <td>{{ \Carbon\Carbon::parse($template['join_date'])->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td>Tanggal Efektif {{ $template['type'] }}</td>
                    <td>:</td>
                    <td><b>{{ \Carbon\Carbon::parse($template['effective_date'])->translatedFormat('d F Y') }}</b></td>
                </tr>
            </table>

            <table class="comparison-table">
                <thead>
                    <tr>
                        <th width="30%">Kategori</th>
                        <th width="35%">Sebelum Perubahan</th>
                        <th width="35%">Usulan Perubahan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($template['comparisons'] as $label => $values)
                        <tr>
                            <td><b>{{ $label }}</b></td>
                            <td>{{ $values['before'] }}</td>
                            <td>{{ $values['after'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="section-header">ALASAN / CATATAN :</div>
            <div class="note-box">
                {{ $template['note'] ?? '-' }}
            </div>

            <div class="section-header">PERSETUJUAN :</div>
            <table class="approval-table">
                <thead>
                    <tr>
                        <th width="10%">#</th>
                        <th width="65%">Nama</th>
                        <th width="25%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @if($career->approvalRequest && $career->approvalRequest->steps)
                        @foreach($career->approvalRequest->steps as $step)
                            <tr>
                                <td align="center">{{ $loop->iteration }}</td>
                                <td>{{ $step->getResolvedApproverNames() ?? '-' }}</td>
                                <td align="center">{{ ucfirst($step->status) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3" align="center">Menunggu persetujuan</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div class="footer-info">
                ID: {{ $career->id }} | Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}
            </div>
        </div>
        @if(!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>

</html>