<!DOCTYPE html>
<html>

<head>
    <title>Surat Keterangan Kerja</title>
    <style>
        @page {
            size: A4;
            margin: 2cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
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
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .document-no {
            margin-top: 0;
            margin-bottom: 30px;
        }

        .content-section {
            margin-bottom: 20px;
        }

        .details-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        .details-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .details-table td:first-child {
            width: 180px;
        }

        .details-table td:nth-child(2) {
            width: 20px;
        }

        .signature-section {
            margin-top: 50px;
            float: right;
            width: 250px;
            text-align: center;
        }

        .signature-space {
            height: 80px;
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
    @foreach($data as $coe)
        <div class="container">

            <div class="text-center">
                <div class="header-title">SURAT KETERANGAN</div>
                <div class="document-no">Nomor : {{ $coe->document_no }}</div>
            </div>

            <div class="content-section">
                <p>Yang bertanda tangan dibawah ini, dengan ini menerangkan bahwa:</p>
            </div>

            <table class="details-table">
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td>{{ strtoupper($coe->employee?->full_name) }}</td>
                </tr>
                <tr>
                    <td>NIK</td>
                    <td>:</td>
                    <td>{{ $coe->employee?->employee_id_number }}</td>
                </tr>
                <tr>
                    <td>Tempat/Tgl Lahir</td>
                    <td>:</td>
                    <td>{{ strtoupper($coe->employee?->place_birth) }} / {{ \Carbon\Carbon::parse($coe->employee?->date_birth)->translatedFormat('d F Y') }}</td>
                </tr>
            </table>

            <div class="content-section text-justify">
                <p>
                    Benar pernah bekerja di <b>PT. Deltamas Surya Indah Mulia</b> sejak {{ \Carbon\Carbon::parse($coe->employee?->join_date)->translatedFormat('d F Y') }} dengan
                    jabatan terakhir sebagai <b>{{ strtoupper($coe->employee?->position?->name) }}</b>.
                </p>
                <p>
                    Sejak tanggal <b>{{ \Carbon\Carbon::parse($coe->employee?->latestResignation?->effective_date)->translatedFormat('d F Y') }}</b>, telah mengundurkan diri dari perusahaan atas kemauannya
                    sendiri. Selama bekerja ia telah memperlihatkan dedikasi kerja dan kerajinan yang baik
                    dalam menjalankan tugasnya.
                </p>
                <p>
                    Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan seperlunya.
                </p>
            </div>

            <div class="signature-section">
                <p>Medan, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p><b>PT. Deltamas Surya Indah Mulia</b></p>
                <div class="signature-space"></div>
                <p><b><u>Nicholas Boediman</u></b></p>
                <p>Kepala Cabang</p>
            </div>

            <div class="footer-info">
                <table style="border: none; padding: 0; margin: 0; border-spacing: 0;">
                    <tr>
                        <td style="padding: 0; padding-right: 10px; vertical-align: bottom;">
                            @php
                                $qrUrl = config('app.url') . '/api/v1/certificateofemployment/public/verify/certificate-of-employments/' . $coe->id;
                            @endphp
                            <img src="data:image/svg+xml;base64,{!! base64_encode(QrCode::format('svg')->size(60)->margin(0)->generate($qrUrl)) !!}"
                                alt="QR Code" style="width: 50px; height: 50px;">
                        </td>
                        <td style="padding: 0; vertical-align: bottom; padding-bottom: 2px;">
                            ID Sertifikat: {{ $coe->id }}<br>
                            Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        @if(!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>

</html>