<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat SKPP</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1cm 1.5cm 1cm 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.3;
            color: #000;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: middle;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
        .mt-4 {
            margin-top: 20px;
        }
        .mb-2 {
            margin-bottom: 10px;
        }
        table.info-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.info-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .indent {
            margin-left: 20px;
        }
        .signature-area {
            width: 300px;
            float: right;
            margin-top: 40px;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .footer-disampaikan {
            margin-top: 60px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="30%" class="text-center">
                LLDIKTI WILAYAH IV<br>
                JL. KHP. H. MUSTAFA<br>
                NO. 38 BANDUNG
            </td>
            <td width="35%" class="text-center fw-bold" style="font-size: 13pt;">
                SURAT KETERANGAN
            </td>
            <td width="35%" style="padding: 0;">
                <table style="width: 100%; border: none; border-collapse: collapse;">
                    <tr>
                        <td style="border: none; padding: 4px 0 4px 8px; width: 35%;">NOMOR</td>
                        <td style="border: none; padding: 4px 0; width: 5%;">:</td>
                        <td style="border: none; padding: 4px 8px 4px 0; width: 60%;">{{ $detail['nomor_skpp'] ?? (date('Y') . '/LL4/PR/2026') }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 0 0 4px 8px;">LAMPIRAN</td>
                        <td style="border: none; padding: 0 0 4px 0;">:</td>
                        <td style="border: none; padding: 0 8px 4px 0;">-</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="mt-4">
        <p style="text-align: justify;">
            Kepala Satker/atas nama Kuasa Pengguna Anggaran Lembaga Layanan Pendidikan Tinggi Wilayah IV Bandung menerangkan bahwa kepada
        </p>

        <table class="info-table" style="margin-left: 10px; margin-bottom: 20px;">
            <tr>
                <td width="25%">Nama</td>
                <td width="3%">:</td>
                <td width="72%">{{ $dosen->Nama ?? '-' }}</td>
            </tr>
            <tr>
                <td>NUPTK/NIDN</td>
                <td>:</td>
                <td>{{ $dosen->NUPTK ?? '-' }} / {{ $dosen->NIDN ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal Lahir</td>
                <td>:</td>
                <td>{{ $dosen->Tanggal_Lahir ? \Carbon\Carbon::parse(str_replace('/', '-', $dosen->Tanggal_Lahir))->isoFormat('D MMMM Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Pangkat Golongan</td>
                <td>:</td>
                <td>{{ $detail['pangkat_golongan'] ?? ($dosen->Jabatan12 ?? ($dosen->Jabatan1 ?? '-')) . ' ' . ($dosen->Gol12 ?? ($dosen->Gol1 ?? '-')) }}</td>
            </tr>
            <tr>
                <td>Jabatan Fungsional</td>
                <td>:</td>
                <td>{{ $dosen->Jabatan12 ?? ($dosen->Jabatan1 ?? '-') }}</td>
            </tr>
            <tr>
                <td>Instansi</td>
                <td>:</td>
                <td>
                    LLDIKTI Wilayah IV Bandung<br>
                    Dosen Tetap Yayasan pada {{ $detail['pts'] ?? ($dosen->PTS ?? '-') }}
                </td>
            </tr>
        </table>

        <p style="text-align: justify;">
            Berdasarkan Surat dari {{ $detail['nama_surat_pts'] ?? ($dosen->PTS ?? '-') }} Nomor : {{ $detail['nomor_surat_pts'] ?? '..........' }} tanggal {{ $detail['tanggal_surat_pts'] ?? '..........' }} tentang Permohonan Surat Keterangan, dan Surat Keterangan Lolos Butuh Nomor : {{ $detail['nomor_surat_lolos_butuh'] ?? '..........' }} tanggal {{ $detail['tanggal_surat_lolos_butuh'] ?? '..........' }} .
        </p>

        <p class="mt-4 fw-bold">
            Terhitung s.d {{ $bulan_terakhir_nama }} {{ $detail['tahun'] ?? date('Y') }} telah dibayarkan :
        </p>

        <table class="info-table" style="margin-bottom: 20px;">
            <tr>
                <td width="35%">Tunjangan Profesi Dosen</td>
                <td width="5%">: Rp.</td>
                <td width="60%">{{ number_format($tpd_kotor, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jumlah Kotor</td>
                <td class="fw-bold">: Rp.</td>
                <td class="fw-bold" style="text-decoration: underline;">{{ number_format($tpd_kotor, 0, ',', '.') }}</td>
            </tr>
        </table>

        <p>Dengan potongan sebagai berikut :</p>
        <table class="info-table" style="margin-bottom: 20px;">
            <tr>
                <td width="35%">Pajak Penghasilan</td>
                <td width="5%">: Rp.</td>
                <td width="60%">{{ number_format($tpd_pajak, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jumlah Potongan</td>
                <td class="fw-bold">: Rp.</td>
                <td class="fw-bold" style="text-decoration: underline;">{{ number_format($tpd_pajak, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jumlah Bersih</td>
                <td class="fw-bold">: Rp.</td>
                <td class="fw-bold">{{ number_format($tpd_bersih, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="clearfix">
            <div class="signature-area">
                Bandung, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}<br>
                Kuasa Pengguna Anggaran,<br>
                <br><br><br><br><br>
                <span class="fw-bold" style="text-decoration: underline;">Dr. Lukman, S.T., M.Hum.</span><br>
                NIP 197805112003121002
            </div>
        </div>

        <div class="footer-disampaikan">
            Disampaikan kepada :<br>
            <div style="padding-left: 30px;">
                1. {{ $dosen->Nama ?? '-' }}<br>
                2. Bendahara Pengeluaran/PPABP LLDIKTI Wilayah {{ $detail['wilayah_lldikti'] ?? 'VI' }} {{ $detail['kota_lldikti'] ?? 'Semarang' }},<br>
                3. Bendahara Pengeluaran/PPABP LLDIKTI Wilayah IV Bandung.
            </div>
        </div>

    </div>

</body>
</html>
