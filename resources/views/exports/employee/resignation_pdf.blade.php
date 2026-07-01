<!DOCTYPE html>
<html>

<head>
    <title>Surat Pengunduran Diri</title>
    <style>
        @page {
            size: A4;
            margin: 2.5cm;
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

        .date-section {
            margin-bottom: 30px;
        }

        .address-section {
            margin-bottom: 30px;
        }

        .subject-table {
            margin-bottom: 30px;
        }

        .subject-table td {
            vertical-align: top;
            padding: 2px 0;
        }

        .subject-table td:first-child {
            width: 80px;
        }

        .subject-table td:nth-child(2) {
            width: 20px;
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

        .content-section {
            margin-bottom: 20px;
        }

        .signature-container {
            margin-top: 50px;
            width: 100%;
            display: table;
        }

        .signature-row {
            display: table-row;
        }

        .signature-box {
            display: table-cell;
            text-align: center;
            width: 33.33%;
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
    @foreach($data as $resignation)
        @php
            $template = (new \App\Modules\Employee\Services\ResignationTemplateService())->getTemplateData($resignation);
        @endphp
        <div class="container">
            <div class="text-right date-section">
                Medan,
                {{ \Carbon\Carbon::parse($resignation->confirmed_at ?? $resignation->created_at)->translatedFormat('d F Y') }}
            </div>

            <div class="address-section">
                Kepada Yth,<br>
                <b>Bapak/Ibu Kepala Department dan HRD</b><br>
                PT. XXX<br>
                Ditempat
            </div>

            <table class="subject-table">
                <tr>
                    <td>Perihal</td>
                    <td>:</td>
                    <td><b>{{ $template['subject'] }}</b></td>
                </tr>
            </table>

            <p>{{ $template['salutation'] }}</p>
            <p>{{ $template['opening'] }}</p>

            <table class="details-table">
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td><b>{{ $resignation->employee?->full_name }}</b></td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td>{{ $resignation->employee?->position?->name }}</td>
                </tr>
                <tr>
                    <td>NIK</td>
                    <td>:</td>
                    <td>{{ $resignation->employee?->employee_id_number }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{ $resignation->employee?->current_address ?? '-' }}</td>
                </tr>
            </table>

            <div class="content-section text-justify">
                <p>{{ $template['main_content'] }}</p>
                <p>{{ $template['closing'] }}</p>
                <p>{{ $template['footer'] }}</p>
            </div>

            <div class="signature-container">
                <div class="signature-row">
                    <div class="signature-box">
                        <p>Hormat Saya,</p>
                        <div class="signature-space"></div>
                        <p><b><u>{{ $template['signatures']['sender'] }}</u></b></p>
                        <p>Karyawan</p>
                    </div>
                    <div class="signature-box">
                        <p>Diketahui Oleh,</p>
                        <div class="signature-space"></div>
                        <p>___________________</p>
                        <p>{{ $template['signatures']['supervisor'] }}</p>
                    </div>
                    <div class="signature-box">
                        <p>Diterima HRD,</p>
                        <div class="signature-space"></div>
                        <p>___________________</p>
                        <p>{{ $template['signatures']['hrd'] }}</p>
                    </div>
                </div>
            </div>

            <div class="footer-info">
                ID: {{ $resignation->id }} | Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}
            </div>
        </div>
        @if(!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>

</html>