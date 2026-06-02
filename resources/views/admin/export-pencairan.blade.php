<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekap Pencairan</title>
    <style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-family: Arial, sans-serif;
        font-size: 12px;
    }

    th,
    td {
        border: 1px solid #000;
        padding: 4px;
        text-align: center;
    }

    th {
        background-color: #dbe9f4;
        font-weight: bold;
    }

    .text-left {
        text-align: left;
    }

    .text-center {
        text-align: center;
    }

    .no-border {
        border: none;
    }
    </style>
</head>

<body>

    {{-- HEADER INFORMASI --}}
    <table class="no-border" style="border: none; margin-bottom: 20px;">
        <tr class="no-border">
            <td colspan="22" class="text-left" style="font-family: Arial; font-size: 13px; font-weight: bold;">
                Lampiran Keputusan Koordinator Koordinasi Perguruan Tinggi Swasta Wilayah IV
            </td>
        </tr>
        <tr class="no-border">
            <td colspan="10" class="text-left" style="font-family: Arial; font-size: 13px;">Nomor:</td>
        </tr>
        <tr class="no-border">
            <td colspan="10" style="height: 20px;"></td>
        </tr>
        <tr>
            <td colspan="22" style="text-align:center; font-weight:bold; font-size:14px;">
                DAFTAR PENERIMA TUNJANGAN PROFESI DOSEN
            </td>
        </tr>
        <tr>
            <td colspan="22" style="text-align:center; font-weight:bold; font-size:14px;">
                BAGI DOSEN NON PNS
            </td>
        </tr>
    </table>

    <table class="no-border" style="margin-bottom: 15px;">
        <tr class="no-border">
            <td class="text-left">Satuan Kerja:</td>
            <td class="text-left">Lembaga Layanan Pendidikan Tinggi Wilayah IV</td>
        </tr>
        <tr class="no-border">
            <td class="text-left">Pencairan ke:</td>
            <td class="text-left">{{ $prosesCair->pencairan_ke }}</td>
        </tr>
        <tr class="no-border">
            <td class="text-left">Status Pegawai:</td>
            <td class="text-left">{{ $prosesCair->status_pegawai }}</td>
        </tr>
        <tr class="no-border">
            <td class="text-left">Jenis:</td>
            <td class="text-left">{{ $prosesCair->jenis }}</td>
        </tr>
        <tr class="no-border">
            <td class="text-left">Bank:</td>
            <td class="text-left">{{ $prosesCair->bank }}</td>
        </tr>
        <tr class="no-border">
            <td class="text-left">Eligible Span:</td>
            <td class="text-left">{{ $prosesCair->eligible_span }}</td>
        </tr>
    </table>

    @php
    $pencairanKe = $prosesCair->pencairan_ke === 'Semua' ? 12 : (int) $prosesCair->pencairan_ke;
    $totalKotorSemua = $totalPajakSemua = $totalBersihSemua = 0;
    @endphp

    {{-- TABEL UTAMA --}}
    <table style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;">
        <thead>
            <tr>
                <th style="border: 1px solid black; padding: 4px; text-align: center;">No</th>
                <th style="border: 1px solid black; padding: 4px; text-align: center;">NIDN</th>
                <th style="border: 1px solid black; padding: 4px; text-align: center;">Nama Dosen</th>
                <th style="border: 1px solid black; padding: 4px; text-align: center;">Jabatan</th>
                <th style="border: 1px solid black; padding: 4px; text-align: center;">Golongan</th>
                @php
                $visibleMonths = $showMonths ?? [];
                @endphp
                @foreach ($visibleMonths as $mi)
                <th style="border: 1px solid black; padding: 4px; text-align: center;">{{ $months[$mi] }}</th>
                @endforeach
                    <th style="border: 1px solid black; padding: 4px; text-align: center;">Jumlah Kotor</th>
                    <th style="border: 1px solid black; padding: 4px; text-align: center;">PPH Pasal 21</th>
                    <th style="border: 1px solid black; padding: 4px; text-align: center;">Jumlah Bersih</th>
                    <th style="border: 1px solid black; padding: 4px; text-align: center;">No Rekening</th>
                    <th style="border: 1px solid black; padding: 4px; text-align: center;">Nama Rekening</th>
                    <th style="border: 1px solid black; padding: 4px; text-align: center;">NPWP</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($dataKeuangan as $index => $data)
            @php
            $totalJumlahKotor = $totalPajak = $totalJumlahBersih = 0;
            $jenis = $prosesCair->jenis;
            @endphp

            <tr>
                <td style="border: 1px solid black; padding: 4px; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid black; padding: 4px; text-align: center;">{{ $data->NIDN }}</td>
                <td style="border: 1px solid black; padding: 4px; text-align: left;">{{ $data->Nama }}</td>
                <td style="border: 1px solid black; padding: 4px; text-align: center;">{{ $data->Jabatan12 }}</td>
                <td style="border: 1px solid black; padding: 4px; text-align: center;">{{ $data->Gol1 }}</td>

                @php
                $visibleMonths = $showMonths ?? [];
                @endphp
                @foreach ($visibleMonths as $i)
                @php
                $nilai = 0;
                $bulanField = $bulanPendek[$i - 1];
                if ($data->$bulanField == $pencairanKe || $prosesCair->pencairan_ke === 'Semua') {
                    if ($jenis == 'TPD') {
                        $nilai = $data->{'TPD' . $i} ?? 0;
                        $jumlahKotor = $data->{'TPD' . $i} ?? 0;
                        $pajak = $data->{'nilaiPajakTPD' . $i} ?? 0;
                        $bersih = $data->{'bersihTPD' . $i} ?? 0;
                    } elseif ($jenis == 'TKGB') {
                        $nilai = $data->{'TKGB' . $i} ?? 0;
                        $jumlahKotor = $data->{'TKGB' . $i} ?? 0;
                        $pajak = $data->{'nilaiPajakTKGB' . $i} ?? 0;
                        $bersih = $data->{'bersihTKGB' . $i} ?? 0;
                    } else {
                        $tpd = $data->{'TPD' . $i} ?? 0;
                        $tkgb = $data->{'TKGB' . $i} ?? 0;
                        $nilai = $tpd + $tkgb;
                        $jumlahKotor = $tpd + $tkgb;
                        $pajak = ($data->{'nilaiPajakTPD' . $i} ?? 0) + ($data->{'nilaiPajakTKGB' . $i} ?? 0);
                        $bersih = ($data->{'bersihTPD' . $i} ?? 0) + ($data->{'bersihTKGB' . $i} ?? 0);
                    }
                    $totalJumlahKotor += $jumlahKotor;
                    $totalPajak += $pajak;
                    $totalJumlahBersih += $bersih;
                }
                @endphp
                <td style="border: 1px solid black; padding: 4px; text-align: right;">{{ $nilai ? number_format($nilai, 0, ',', '.') : '0' }}</td>
                @endforeach

                    <td style="border: 1px solid black; padding: 4px; text-align: right;">
                        {{ number_format($totalJumlahKotor, 0, ',', '.') }}
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: right;">
                        {{ number_format($totalPajak, 0, ',', '.') }}
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: right;">
                        {{ number_format($totalJumlahBersih, 0, ',', '.') }}
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center;">
                        {{ $data->No_Rekening ?? '-' }}
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center;">
                        {{ $data->Nama_Rekening ?? '-' }}
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center;">{{ $data->NPWP ?? '-' }}</td>
            </tr>

            @php
            $totalKotorSemua += $totalJumlahKotor;
            $totalPajakSemua += $totalPajak;
            $totalBersihSemua += $totalJumlahBersih;
            @endphp
            @endforeach

            {{-- BARIS TOTAL --}}
            <tr style="font-weight: bold; background-color: #bbd3e7;">
                <td colspan="{{ 5 + (count($showMonths ?? []) ) }}" style="border: 1px solid black; padding: 4px; text-align: center;">Jumlah
                </td>
                <td style="border: 1px solid black; padding: 4px; text-align: right;">
                    {{ number_format($totalKotorSemua, 0, ',', '.') }}
                </td>
                <td style="border: 1px solid black; padding: 4px; text-align: right;">
                    {{ number_format($totalPajakSemua, 0, ',', '.') }}
                </td>
                <td style="border: 1px solid black; padding: 4px; text-align: right;">
                    {{ number_format($totalBersihSemua, 0, ',', '.') }}
                </td>
                <td colspan="3" style="border: 1px solid black; padding: 4px;"></td>
            </tr>
        </tbody>
    </table>


    {{-- BAGIAN TANDA TANGAN --}}
    <br><br><br>
    <table class="no-border" style="border: none; width: 100%; margin-top: 20px;">
        <tr class="no-border">
            <td colspan="7" class="no-border text-left" style="width: 33%; text-align: center; height: 150px;">
                Mengetahui<br>
                Kuasa Pengguna Anggaran,<br><br><br><br>
                <u>{{ $pejabat->pejabat1 ?? '' }}</u><br>
                NIP. {{ $pejabat->nip_pejabat1 ?? '' }}
            </td>

            <td class="no-border text-center" colspan="8" style="width: 33%; text-align: center;">
                Bandung, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}<br>
                Bendahara Pengeluaran,<br><br><br><br>
                <u>{{ $pejabat->pejabat2 ?? '' }}</u><br>
                NIP. {{ $pejabat->nip_pejabat2 ?? '' }}
            </td>

            <td class="no-border text-right" colspan="7" style="width: 33%; text-align: center;">
                Pejabat Pembuat Komitmen,<br><br><br><br><br>
                <u>{{ $pejabat->pejabat3 ?? '' }}</u><br>
                NIP. {{ $pejabat->nip_pejabat3 ?? '' }}
            </td>
        </tr>
    </table>

</body>

</html>
