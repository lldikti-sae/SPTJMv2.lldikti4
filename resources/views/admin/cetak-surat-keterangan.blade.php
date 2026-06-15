<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 4.5cm 2.5cm 2cm 2.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
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
            margin-bottom: 30px;
        }
        .data-box {
            padding: 10px 15px;
            margin: 15px 40px 15px 40px;
        }
        .data-box table {
            border-collapse: collapse;
            width: 100%;
        }
        .data-box table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .signature-area {
            width: 300px;
            float: right;
            margin-top: 30px;
            line-height: 1.4;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        p {
            text-align: justify;
            margin: 8px 0;
        }
    </style>
</head>
<body>

    {{-- Judul Surat --}}
    <div class="text-center" style="margin-top: 20px;">
        <span class="title-underline">SURAT KETERANGAN</span>
    </div>
    <div class="nomor">
        Nomor : {{ $detail['nomor_skpp'] ?? '......./LL4/PR/' . date('Y') }}
    </div>

    {{-- Paragraf Pembuka --}}
    <p>
        Berdasarkan Surat dari {{ $detail['nama_surat_pts'] ?? ($dosen->PTS ?? '-') }} Nomor: {{ $detail['nomor_surat_pts'] ?? '..........' }}
        tanggal {{ $detail['tanggal_surat_pts'] ?? '..........' }} perihal Permohonan Surat Keterangan Pemberhentian Pembayaran (SKPP)
        Dosen a.n. {{ $dosen->Nama ?? '-' }}, {{ rtrim($detail['ttd_jabatan'] ?? 'Kuasa Pengguna Anggaran', ', ') }} Lembaga Layanan Pendidikan Tinggi Wilayah IV dengan ini menerangkan bahwa nama tersebut di bawah ini:
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
    <p style="margin-top: 15px;">
        Demikian surat keterangan ini kami buat untuk dapat dipergunakan sebagaimana mestinya.
    </p>

    {{-- Tanda Tangan --}}
    <div class="clearfix">
        <div class="signature-area">
            Bandung, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}<br>
            {{ $detail['ttd_jabatan'] ?? 'Kuasa Pengguna Anggaran,' }}<br>
            <br><br><br><br><br>
            <span class="fw-bold" style="text-decoration: underline;">{{ $detail['ttd_nama'] ?? 'Dr. Lukman, S.T., M.Hum.' }}</span><br>
            NIP. {{ $detail['ttd_nip'] ?? '197805112003121002' }}
        </div>
    </div>

</body>
</html>
