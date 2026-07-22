<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat SKPP</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm 1.5cm 1cm 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .header-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
        table.info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        table.info-table td {
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
        .footer-disampaikan {
            margin-top: 20px;
            line-height: 1.4;
        }
        table.payment-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }
        table.payment-table th,
        table.payment-table td {
            border: 1px solid #000;
            padding: 4px 8px;
            vertical-align: middle;
            font-size: 11pt;
        }
        table.payment-table th {
            text-align: center;
            font-weight: bold;
        }
        p {
            margin: 4px 0;
            text-align: justify;
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



    {{-- Tabel Header SKPP --}}
    <table class="header-table">
        <tr>
            <td width="30%" class="text-center" style="font-size: 10pt; line-height: 1.3;">
                LLDIKTI WILAYAH IV<br>
                JL. KHP. H. MUSTAFA<br>
                NO. 38 BANDUNG
            </td>
            <td width="35%" class="text-center fw-bold" style="font-size: 12pt; line-height: 1.3;">
                SURAT KETERANGAN<br>
                PENGHENTIAN<br>
                PEMBAYARAN
            </td>
            <td width="35%" style="padding: 0;">
                <table style="width: 100%; border: none; border-collapse: collapse;">
                    <tr>
                        <td style="border: none; padding: 3px 0 3px 6px; width: 35%;">NOMOR</td>
                        <td style="border: none; padding: 3px 0; width: 5%;">:</td>
                        <td style="border: none; padding: 3px 6px 3px 0; width: 60%;">{{ $detail['nomor_skpp'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 0 0 3px 6px;">LAMPIRAN</td>
                        <td style="border: none; padding: 0 0 3px 0;">:</td>
                        <td style="border: none; padding: 0 6px 3px 0;">-</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Paragraf Pembuka --}}
    <p style="margin-bottom: 8px;">
        Kepala Satker/atas nama {{ rtrim($detail['ttd_jabatan'] ?? 'Kuasa Pengguna Anggaran', ', ') }} Lembaga Layanan Pendidikan Tinggi Wilayah IV Bandung menerangkan bahwa kepada
    </p>

    {{-- Data Dosen --}}
    <table class="info-table" style="width: 100%;">
        <tr>
            <td width="25%">Nama</td>
            <td width="3%" class="text-center">:</td>
            <td width="72%">{{ $dosen->Nama ?? '-' }}</td>
        </tr>
        <tr>
            <td>NIDN/NUPTK</td>
            <td class="text-center">:</td>
            <td>
                @if(($dosen->NIDN ?? '-') === ($dosen->NUPTK ?? '-'))
                    {{ $dosen->NIDN ?? '-' }}
                @else
                    {{ $dosen->NIDN ?? '-' }} / {{ $dosen->NUPTK ?? '-' }}
                @endif
            </td>
        </tr>
        <tr>
            <td>Tanggal Lahir</td>
            <td class="text-center">:</td>
            <td>
                @php
                    $tglLahirFormatted = '-';
                    if (!empty($dosen->Tanggal_Lahir)) {
                        try {
                            $tglLahirFormatted = \Carbon\Carbon::parse(str_replace('/', '-', $dosen->Tanggal_Lahir))->locale('id')->translatedFormat('d F Y');
                        } catch (\Exception $e) {
                            $tglLahirFormatted = $dosen->Tanggal_Lahir;
                        }
                    }
                @endphp
                {{ $tglLahirFormatted }}
            </td>
        </tr>
        <tr>
            <td>Pangkat Golongan</td>
            <td class="text-center">:</td>
            <td>{{ $detail['pangkat_golongan'] ?? ($dosen->Jabatan12 ?? ($dosen->Jabatan1 ?? '-')) . ' ' . ($dosen->Gol12 ?? ($dosen->Gol1 ?? '-')) }}</td>
        </tr>
        <tr>
            <td>Jabatan Fungsional</td>
            <td class="text-center">:</td>
            <td>{{ $dosen->Jabatan12 ?? ($dosen->Jabatan1 ?? '-') }}</td>
        </tr>
        <tr>
            <td>Instansi</td>
            <td class="text-center">:</td>
            <td>
                LLDIKTI Wilayah IV Bandung<br>
                Dosen Tetap {{ str_contains(strtolower($detail['pts'] ?? ''), 'universitas') || str_contains(strtolower($detail['pts'] ?? ''), 'institut') || str_contains(strtolower($detail['pts'] ?? ''), 'sekolah tinggi') || str_contains(strtolower($detail['pts'] ?? ''), 'akademi') || str_contains(strtolower($detail['pts'] ?? ''), 'politeknik') ? 'Yayasan pada ' : 'pada ' }}{{ $detail['pts'] ?? ($dosen->PTS ?? '-') }}
            </td>
        </tr>
    </table>

    {{-- Paragraf Dasar Surat --}}
    <p>
        Berdasarkan Surat dari {{ $detail['nama_surat_pts'] ?? ($dosen->PTS ?? '-') }} Nomor : {{ $detail['nomor_surat_pts'] ?? '..........' }} tanggal {{ $detail['tanggal_surat_pts'] ?? '..........' }} tentang Permohonan SKPP, dan Surat Keterangan Lolos Butuh Nomor : {{ $detail['nomor_surat_lolos_butuh'] ?? '..........' }} tanggal {{ $detail['tanggal_surat_lolos_butuh'] ?? '..........' }}.
    </p>

    {{-- Judul Tabel Pembayaran --}}
    <p class="fw-bold" style="margin-top: 10px; margin-bottom: 6px; text-decoration: underline;">
        @php
            $bulanTerhitung = $bulan_terakhir_nama ?? '-';
            $tahunTerhitung = $detail['tahun'] ?? date('Y');
            $bulanFormatted = $bulanTerhitung . ' ' . $tahunTerhitung;
        @endphp
        Terhitung s.d {{ $bulanFormatted }} telah dibayarkan :
    </p>

    {{-- Tabel Keterangan Pembayaran --}}
    <table class="payment-table" style="width: 100%;">
        <thead>
            <tr>
                <th style="border: 1px solid #000;">Uraian</th>
                <th style="border: 1px solid #000;">Tunjangan Profesi</th>
                <th style="border: 1px solid #000;">Tunjangan Kehormatan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="width: 35%; border: 1px solid #000;">Tunjangan Perbulan</td>
                <td style="width: 30%; padding-left: 15px; border: 1px solid #000;">Rp. {{ number_format($tpd_kotor, 0, ',', '.') }}</td>
                <td style="width: 30%; padding-left: 15px; border: 1px solid #000;">
                    @if($is_guru_besar)
                        Rp. {{ number_format($tkgb_kotor, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000;">PPh. 21</td>
                <td style="padding-left: 15px; border: 1px solid #000;">Rp. {{ number_format($tpd_pajak, 0, ',', '.') }}</td>
                <td style="padding-left: 15px; border: 1px solid #000;">
                    @if($is_guru_besar)
                        Rp. {{ number_format($tkgb_pajak, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000;">Tunjangan Bersih Perbulan</td>
                <td style="padding-left: 15px; border: 1px solid #000;">Rp. {{ number_format($tpd_bersih, 0, ',', '.') }}</td>
                <td style="padding-left: 15px; border: 1px solid #000;">
                    @if($is_guru_besar)
                        Rp. {{ number_format($tkgb_bersih, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Paragraf Penutup --}}
    <p style="margin-top: 10px;">
        Demikian surat ini diterbitkan, untuk dapat dipergunakan sebagaimana mestinya.
    </p>
    <p>
        Atas perhatian Saudara kami ucapkan terima kasih.
    </p>

        <div class="clearfix">
            <div class="signature-area">
                @php
                    $tanggalSurat = !empty($detail['tanggal_cetak']) ? $detail['tanggal_cetak'] : \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');
                @endphp
                Bandung, {{ $tanggalSurat }}<br>
                An. {{ $detail['ttd_jabatan'] ?? 'Kuasa Pengguna Anggaran,' }}<br>
                Pejabat Pembuat Komitmen,<br>
                <br><br><br><br>
                <span class="fw-bold" style="text-decoration: underline;">{{ $detail['ttd_nama'] ?? 'Dr. Lukman, S.T., M.Hum.' }}</span><br>
                NIP. {{ $detail['ttd_nip'] ?? '197805112003121002' }}
            </div>
        </div>

    {{-- Disampaikan Kepada --}}
    <div class="footer-disampaikan">
        Disampaikan kepada :<br>
        <div style="padding-left: 30px;">
            1. {{ $dosen->Nama ?? '-' }}<br>
            @if(!empty($detail['wilayah_lldikti']) && $detail['wilayah_lldikti'] !== 'Lainnya')
            2. Bendahara Pengeluaran/PPABP LLDIKTI Wilayah {{ $detail['wilayah_lldikti'] }} {{ $detail['kota_lldikti'] ?? '' }}<br>
            @elseif(!empty($detail['wilayah_lldikti']) && $detail['wilayah_lldikti'] === 'Lainnya')
            2. Bendahara Pengeluaran/PPABP {{ $detail['wilayah_lldikti_custom'] ?? '-' }}<br>
            @else
            2. {{ $detail['nama_surat_pts'] ?? ($dosen->PTS ?? '-') }}<br>
            @endif
            3. Bendahara Pengeluaran/PPABP LLDIKTI Wilayah IV Bandung
        </div>
    </div>

</body>
</html>
