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

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60pt;
            color: rgba(0, 0, 0, 0.05);
            z-index: -1;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    @foreach($data as $coe)
        <div class="container">
            <div class="watermark">ASLI / ORIGINAL</div>

            <div class="text-center">
                <div class="header-title">SURAT KETERANGAN KERJA</div>
                <div class="document-no">Nomor: {{ $coe->document_no }}</div>
            </div>

            <div class="content-section">
                <p>Yang bertanda tangan di bawah ini, Manajemen <b>PT. Deltamas Surya Indah Mulia</b>, dengan ini
                    menerangkan bahwa:</p>
            </div>

            <table class="details-table">
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td><b>{{ $coe->employee?->full_name }}</b></td>
                </tr>
                <tr>
                    <td>Nomor Induk Karyawan</td>
                    <td>:</td>
                    <td>{{ $coe->employee?->employee_id_number }}</td>
                </tr>
                <tr>
                    <td>Jabatan Terakhir</td>
                    <td>:</td>
                    <td>{{ $coe->employee?->position?->name }}</td>
                </tr>
                <tr>
                    <td>Masa Kerja</td>
                    <td>:</td>
                    <td>
                        {{ \Carbon\Carbon::parse($coe->employee?->join_date)->translatedFormat('d F Y') }}
                        s/d
                        {{ \Carbon\Carbon::parse($coe->employee?->latestResignation?->effective_date)->translatedFormat('d F Y') }}
                    </td>
                </tr>
            </table>

            <div class="content-section text-justify">
                <p>
                    Adalah benar yang bersangkutan pernah bekerja pada <b>PT. Deltamas Surya Indah Mulia</b> dalam kurun
                    waktu tersebut di atas. Selama bekerja, yang bersangkutan telah menunjukkan dedikasi dan loyalitas yang
                    baik terhadap perusahaan.
                </p>
                <p>
                    Kami mengucapkan terima kasih atas segala bantuan dan partisipasi yang telah diberikan kepada perusahaan
                    selama ini. Semoga prestasi dan pengalaman yang didapat selama bekerja di perusahaan kami dapat
                    bermanfaat untuk kesuksesan di masa yang akan datang.
                </p>
                <p>
                    Demikian Surat Keterangan Kerja ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
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
                            @if($coe->attachment)
                                <img src="data:image/png;base64,{!! base64_encode(QrCode::format('png')->size(60)->margin(0)->generate(\Illuminate\Support\Facades\Storage::disk('gcs')->url($coe->attachment))) !!}"
                                    alt="QR Code" style="width: 50px; height: 50px;">
                            @endif
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