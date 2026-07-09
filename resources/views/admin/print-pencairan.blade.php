<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width initial-scale=1" />
  <title>Print SPTJM Online</title>
  <link rel="icon" type="image/x-icon"
    href="https://www.freepnglogos.com/uploads/tut-wuri-handayani-png-logo/vector-wuri-handayani-warna-0.png" />
  <style>
    th {
      background-color: #aac5dd;
      border: 1px solid black;
      font-size: 10px;
    }

    td {
      border: 1px solid black;
      font-size: 10px;
      height: 20px;
    }

    table {
      border-collapse: collapse;
    }

    @media print {

      td,
      th {
        border: 1px solid black;
        font-size: 7px;
      }
    }
  </style>
</head>

<body style="padding:10px">
  <div style="width: 100%;">
    <div style="text-align: left; font-family: Arial; font-size: 13px; font-weight: bold;">
      Lampiran Keputusan Koordinator Koordinasi Perguruan Tinggi Swasta Wilayah IV
    </div>
    <div style="text-align: left; font-family: Arial; font-size: 13px;">Nomor:</div>
    <div style="margin: 50px 0 50px 0">
      <div style="text-align: center; font-family: Arial; font-size: 15px; font-weight: bold;">
        DAFTAR PENERIMA TUNJANGAN PROFESI DOSEN
      </div>
      <div style="text-align: center; font-family: Arial; font-size: 15px; font-weight: bold;">
        BAGI DOSEN {{ $prosesCair->status_pegawai }}
      </div>
    </div>
    <br>
    <div style="text-align: left; font-family: Arial; font-size: 13px;">
      Satuan Kerja: Lembaga Layanan Pendidikan Tinggi Wilayah IV
    </div>
    <div style="text-align: left; font-family: Arial; font-size: 13px;">Pencairan ke:
      {{ $prosesCair->pencairan_ke }}
    </div>
    <div style="text-align: left; font-family: Arial; font-size: 13px;">Status Pegawai:
      {{ $prosesCair->status_pegawai }}
    </div>
    <div style="text-align: left; font-family: Arial; font-size: 13px;">Jenis: {{ $prosesCair->jenis }}</div>
    <div style="text-align: left; font-family: Arial; font-size: 13px;">Bank: {{ $prosesCair->bank }}</div>
    <div style="text-align: left; font-family: Arial; font-size: 13px;">Eligible Span:
      {{ $prosesCair->eligible_span }}
    </div>
  </div>
  <br>

  @php
  $pencairanKe = $prosesCair->pencairan_ke === 'Semua' ? 12 : (int) $prosesCair->pencairan_ke;
  @endphp

  @php
  // Determine which month columns actually have non-zero data (across all rows)
  $showMonths = [];
  for ($m = 1; $m <= 12; $m++) {
    $has = false;
    foreach ($dataKeuangan as $d) {
      if ($prosesCair->jenis == 'TPD') {
        $v = (float) ($d->{'TPD' . $m} ?? 0);
      } elseif ($prosesCair->jenis == 'TKGB') {
        $v = (float) ($d->{'TKGB' . $m} ?? 0);
      } else {
        $v = (float) ($d->{'TPD' . $m} ?? 0) + (float) ($d->{'TKGB' . $m} ?? 0);
      }
      if ($v != 0) {
        $has = true;
        break;
      }
    }
    if ($has) $showMonths[] = $m;
  }
  $visibleMonthCount = count($showMonths);
  @endphp

  <table width="100%">
    <thead>
      <tr>
        <th>No</th>
        <th>NIDN</th>
        <th>Nama Dosen</th>
        <th>Jabatan</th>
        <th>Golongan</th>
        @foreach ($showMonths as $mi)
        <th>{{ $months[$mi] }}</th>
        @endforeach
          <th>Jumlah Kotor</th>
          <th>PPH Pasal 21</th>
          <th>Jumlah Bersih</th>
          <th>No Rekening</th>
          <th>NPWP</th>
      </tr>
    </thead>
    @php
    $totalKotorSemua = $totalPajakSemua= $totalBersihSemua = 0;
    @endphp
    <tbody style="text-align: center;"">
      @foreach ($dataKeuangan as $index => $data)
      @php
      $totalJumlahKotor = $totalPajak = $totalJumlahBersih = 0;

      $jenis = $prosesCair->jenis;



      @endphp

      <tr>
        <td>{{ $index + 1 }}</td>
        <td>{{ $data->NIDN }}</td>
        <td>{{ $data->Nama }}</td>
        @if ($pencairanKe > 12)
        <td>{{ $data->Jabatan12 }}</td>
        <td>{{ $data->Gol12 }}</td>
        @else
        <td>{{ $data->{ "Jabatan".$pencairanKe } }}</td>
        <td>{{ $data->{ "Gol".$pencairanKe } }}</td>

        @endif
        @foreach ($showMonths as $i)
        <td>
          @php
          $nilai = 0;
          $bulanField = $bulanPendek[$i-1];
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
          {{ number_format($nilai, 0, ',', '.') }}
        </td>
        @endforeach


      <td>{{ number_format($totalJumlahKotor, 0, ',', '.') }}</td>
      <td>{{ number_format($totalPajak, 0, ',', '.') }}</td>
      <td>{{ number_format($totalJumlahBersih, 0, ',', '.') }}</td>
      <td>{{ $data->No_Rekening ?? '-' }}</td>
      <td>{{ $data->NPWP ?? '-' }}</td>
      </tr>
      @php
        $totalKotorSemua += $totalJumlahKotor;
        $totalPajakSemua += $totalPajak;
        $totalBersihSemua += $totalJumlahBersih
      @endphp
      @endforeach

      {{-- Baris Jumlah Total --}}
      <tr style=" font-weight: bold; background-color: #bbd3e7;">
      <td colspan="{{ 5 + $visibleMonthCount }}" style="text-align: center;">Jumlah</td>
      <td>{{ number_format($totalKotorSemua, 0, ',', '.') }}</td>
      <td>{{ number_format($totalPajakSemua, 0, ',', '.') }}</td>
      <td>{{ number_format($totalBersihSemua, 0, ',', '.') }}</td>
      <td colspan="2"></td>
      </tr>
    </tbody>
  </table>

  @php
  $m_pejabat = DB::table('m_pejabat')->get();
  $pejabat = new \stdClass();
  foreach ($m_pejabat as $p) {
      $pejabat->{"pejabat{$p->urutan}"} = $p->nama;
      $pejabat->{"nip_pejabat{$p->urutan}"} = $p->nip;
      $pejabat->{"jabatan{$p->urutan}"} = $p->jabatan;
  }
  @endphp
  <br>
  <br>
  <div style="
    width: 100%;
    font-family: Arial;
    font-size: 12px;
    margin-top: 10px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end; /* 🔹 agar sejajar rata bawah */
    text-align: center;
  ">
    <div style="flex: 1; text-align: left;">
      Mengetahui<br />
      Kuasa Pengguna Anggaran,<br /><br /><br /><br />
      <u>{{ $pejabat->pejabat1 ?? '' }}</u><br />
      NIP. {{ $pejabat->nip_pejabat1 ?? '' }}
    </div>

    <div style="flex: 1; text-align: center;">
      <span style="margin-left: 10px;">Bandung,
        {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</span><br />
      Bendahara Pengeluaran,<br /><br /><br /><br />
      <u>{{ $pejabat->pejabat2 ?? '' }}</u><br />
      NIP. {{ $pejabat->nip_pejabat2 ?? '' }}
    </div>

    <div style="flex: 1; text-align: right;">
      Pejabat Pembuat Komitmen,<br /><br /><br /><br /><br>
      <u>{{ $pejabat->pejabat3 ?? '' }}</u><br />
      NIP. {{ $pejabat->nip_pejabat3 ?? '' }}
    </div>
  </div>

</body>

</html>