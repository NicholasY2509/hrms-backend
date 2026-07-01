<!DOCTYPE html>
<html>

<head>
    <title>Surat Peringatan</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-justify {
            text-align: justify;
        }

        .header-title {
            text-decoration: underline;
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .document-no {
            margin-top: 0;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .management-header {
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .section-title {
            text-align: center;
            text-decoration: underline;
            font-weight: bold;
            margin: 10px 0 5px 0;
            text-transform: uppercase;
        }

        .legal-list {
            margin-bottom: 10px;
            padding-left: 20px;
        }

        .legal-list li {
            margin-bottom: 4px;
            text-align: justify;
        }

        .details-table {
            width: 100%;
            margin: 10px 0;
            border-collapse: collapse;
        }

        .details-table td {
            padding: 1px 0;
            vertical-align: top;
        }

        .details-table td:first-child {
            width: 130px;
            font-weight: bold;
        }

        .details-table td:nth-child(2) {
            width: 20px;
        }

        .note-content {
            margin: 10px 0;
            min-height: 40px;
            padding: 8px;
            border: 1px dashed #ccc;
        }

        .signature-container {
            width: 100%;
            margin-top: 20px;
        }

        .signature-box {
            width: 45%;
            display: inline-block;
            vertical-align: top;
        }

        .signature-space {
            height: 50px;
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
    @foreach($data as $warningLetter)
        @php
            $template = (new \App\Modules\Disciplinary\Services\WarningLetterTemplateService())->getTemplateData($warningLetter);
        @endphp
        <div class="container">
            <div class="text-center">
                <div class="header-title">SURAT PERINGATAN</div>
                <div class="document-no">No. {{ $warningLetter->document_no }}</div>
            </div>

            <div class="management-header">
                MANAGEMENT PT. XXXX
            </div>

            <div class="section-title">MENGINGAT / MENIMBANG :</div>
            <ol class="legal-list">
                @foreach($template['legal_points'] as $point)
                    <li>{{ $point }}</li>
                @endforeach
            </ol>

            <div class="section-title">MEMUTUSKAN</div>

            <p>Memberikan <b>{{ $template['level'] }}</b> kepada :</p>

            <table class="details-table">
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td><b>{{ $warningLetter->employee?->full_name }}</b></td>
                </tr>
                <tr>
                    <td>NIK</td>
                    <td>:</td>
                    <td>{{ $warningLetter->employee?->employee_id_number }}</td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td>{{ $warningLetter->employee?->position?->name }}</td>
                </tr>
                <tr>
                    <td>Lokasi</td>
                    <td>:</td>
                    <td>{{ $warningLetter->employee?->work_location?->name }}</td>
                </tr>
            </table>

            <p>Ybs telah melakukan kesalahan antara lain :</p>
            <div class="note-content text-justify">
                {!! $warningLetter->note !!}
            </div>

            <p class="text-justify">
                {{ $template['content'] }}
            </p>

            <p class="text-justify">
                Demikian Surat {{ $template['level_headline'] }} ini dibuat agar menjadi perhatian.
            </p>

            <div class="signature-container">
                <div class="signature-box">
                    <p>Dikeluarkan di : Medan</p>
                    <p>Tanggal : {{ \Carbon\Carbon::parse($warningLetter->warning_at)->translatedFormat('d F Y') }}</p>
                    <p><b>PT. XXXX</b></p>
                    <div class="signature-space"></div>
                    <p>__________________________</p>
                    <p>Management</p>
                </div>

                <div class="signature-box" style="float: right; font-size: 9pt;">
                    <p style="margin-bottom: 20px;">Saya telah menerima Surat Peringatan ini dan bersedia menerima segala
                        yang telah diputuskan Perusahaan kepada saya.</p>
                    <div class="signature-space"></div>
                    <p class="text-center"><b><u>{{ $warningLetter->employee?->full_name }}</u></b></p>
                    <p class="text-center">Karyawan</p>
                </div>
            </div>

            <div class="footer-info">
                ID: {{ $warningLetter->id }} | Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}
            </div>
        </div>
        @if(!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>

</html>