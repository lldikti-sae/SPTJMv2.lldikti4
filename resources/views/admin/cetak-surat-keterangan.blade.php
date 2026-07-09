<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm 2.5cm 1.5cm 2.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
        .title-underline {
            text-decoration: underline;
            font-weight: bold;
            font-size: 14pt;
        }
        .nomor {
            text-align: center;
            margin-bottom: 25px;
        }
        .data-box {
            padding: 4px 0;
            margin: 8px 0;
        }
        .data-box table {
            border-collapse: collapse;
            width: 100%;
        }
        .data-box table td {
            padding: 1px 0;
            vertical-align: top;
        }
        .signature-area {
            width: 300px;
            float: right;
            margin-top: 15px;
            line-height: 1.3;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        p {
            text-align: justify;
            margin: 4px 0;
        }
        .kop-img {
            width: 100%;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    {{-- Beri margin buatan karena menggunakan background PDF --}}
    <div style="height: 145px;"></div>

    <!-- KOP SURAT LLDIKTI -->
    <img src="{{ public_path('assets/img/KopSurat_LLDIKTI4.png') }}" alt="Kop Surat LLDIKTI Wilayah IV" class="kop-img">

    {{-- Header Surat (Nomor, Lampiran, Perihal) --}}
    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 15px;">
        <tr>
            <td style="width: 10%; padding: 1px 0;">Nomor</td>
            <td style="width: 2%; padding: 1px 0;">:</td>
            <td style="width: 88%; padding: 1px 0;">{{ $detail['nomor_skpp'] ?? '......./LL4/PR/' . date('Y') }}</td>
        </tr>
        <tr>
            <td style="padding: 1px 0;">Lampiran</td>
            <td style="padding: 1px 0;">:</td>
            <td style="padding: 1px 0;">-</td>
        </tr>
        <tr>
            <td style="padding: 1px 0; vertical-align: top;">Perihal</td>
            <td style="padding: 1px 0; vertical-align: top;">:</td>
            <td style="padding: 1px 0; vertical-align: top;">Surat Keterangan Penghentian Pembayaran (SKPP)</td>
        </tr>
    </table>

    {{-- Paragraf Pembuka --}}
    <p>
        @php
            $tanggalSuratPts = $detail['tanggal_surat_pts'] ?? '..........';
        @endphp
        Berdasarkan Surat dari {{ $detail['nama_surat_pts'] ?? ($dosen->PTS ?? '-') }} Nomor: {{ $detail['nomor_surat_pts'] ?? '..........' }}
        tanggal {{ $tanggalSuratPts }} perihal Permohonan Surat Keterangan Pemberhentian Pembayaran (SKPP)
        Dosen {{ $dosen->Nama ?? '-' }}, {{ rtrim($detail['ttd_jabatan'] ?? 'Kuasa Pengguna Anggaran', ', ') }} Lembaga Layanan Pendidikan Tinggi Wilayah IV dengan ini menerangkan bahwa nama tersebut di bawah ini:
    </p>

    {{-- Data Dosen (dalam kotak) --}}
    <div class="data-box">
        <table>
            <tr>
                <td width="20%" class="fw-bold">Nama</td>
                <td width="3%">:</td>
                <td>{{ $dosen->Nama ?? '-' }}</td>
            </tr>
            <tr>
                <td class="fw-bold">NUPTK</td>
                <td>:</td>
                <td>{{ $dosen->NUPTK ?? '-' }}</td>
            </tr>
            <tr>
                <td class="fw-bold">Instansi</td>
                <td>:</td>
                <td>Dosen Tetap pada {{ $detail['pts'] ?? ($dosen->PTS ?? '-') }}</td>
            </tr>
        </table>
    </div>

    {{-- Paragraf Isi --}}
    <p>
        yang bersangkutan merupakan dosen yang telah lulus sertifikasi dosen, namun sampai dengan surat ini dibuat dan setelah dilakukan pengecekan terhadap data keuangan sejak tahun {{ $detail['tahun'] ?? date('Y') }}, yang bersangkutan tidak ada pembayaran tunjangan profesi dosen dari LLDIKTI Wilayah IV.
    </p>

    {{-- Paragraf Penutup --}}
    <p style="margin-top: 8px;">
        Demikian surat keterangan ini kami buat untuk dapat dipergunakan sebagaimana mestinya.
    </p>

    {{-- Tanda Tangan --}}
    <div class="clearfix">
        <div class="signature-area">
            @php
                $tanggalSurat = !empty($detail['tanggal_cetak']) ? $detail['tanggal_cetak'] : \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');
            @endphp
            Bandung, {{ $tanggalSurat }}<br>
            {{ $detail['ttd_jabatan'] ?? 'Kuasa Pengguna Anggaran,' }}<br>
            <br><br><br><br><br>
            <span class="fw-bold" style="text-decoration: underline;">{{ $detail['ttd_nama'] ?? 'Dr. Lukman, S.T., M.Hum.' }}</span><br>
            NIP. {{ $detail['ttd_nip'] ?? '197805112003121002' }}
        </div>
    </div>

</body>
</html>
