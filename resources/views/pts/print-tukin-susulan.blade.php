<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Print Tukin Susulan</title>
  <style>
    /* match SPTJM print widths and typographic scale */
    body { font-family: "Times New Roman", Times, serif; margin: 40px; font-size: 12pt; }
    @media print { thead { display: table-header-group; } }
    #laporan td, #laporan th { border: 1px solid #ddd; padding: 8px; }
    #laporan { border-collapse: collapse; width: 100%; }
    #laporan th { background-color: #D3DCE3; font-size: 13px; }
    table tr td { font-size: 12pt; }
    .judul { text-align: center; font-size: 15px; font-weight: bold; }
    .nomor { text-align: center; font-size: 12px; font-weight: bold; }
    .template { text-align: justify; font-size: 12px; }
    hr { border: 0; height: 2px; background: #333; }
    /* page break utility for print */
    .page-break { page-break-before: always; break-before: page; }
  </style>
  </head>

  <body>
  @php
    $namaBulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    $bulanTeks = $namaBulan[$bulan ?? now()->month] ?? '';
  @endphp

  <div style="height: 140px"></div>
  <div class="judul"><u>SURAT PERNYATAAN TANGGUNG JAWAB MUTLAK</u></div>
  <div class="nomor">Nomor: <span id="Nomor_Surat">-</span></div>
  <br><br>
  <div>Yang bertanda tangan di bawah ini:</div>
  <br>
  <div>Nama: <span id="Nama">-</span></div>
  <div>Jabatan: <span id="Jabatan">-</span></div>
  <div>PTS: {{ $pts ?? '-' }}</div>
  <br>
  <div>Menyatakan dengan sesungguhnya bahwa:</div>
  <table border="0" width="100%">
    <tr>
      <td valign="top" width="2%">1.</td>
      <td class="template">Nama-nama dosen sebagaimana daftar terlampir telah memenuhi syarat kinerja dasar dan kinerja prestasi sesuai dengan ketentuan peraturan perundang-undangan yang berlaku.</td>
    </tr>
    <tr>
      <td valign="top">2.</td>
      <td class="template">Proses pelaksanaan verifikasi kinerja dasar dan kinerja prestasi telah dilaksanakan secara tertib dan dapat dipertanggungjawabkan.</td>
    </tr>
  </table>
  <br>
  <div class="template">Demikian surat pernyataan ini dibuat dengan sebenarnya, apabila di kemudian hari terdapat kekeliruan dan ketidaksesuaian, saya bertanggungjawaban sepenuhnya sesuai dengan ketentuan yang berlaku.</div>
  <br><br><br><br>
  <div style="width:100%; display:flex; justify-content:flex-end;">
    <div style="text-align:right; width:40%;">
      <span id="Kota">-</span>, {{ $tanggal ?? now()->translatedFormat('d F Y') }}<br>
      Pembuat Pernyataan:<br><br><br><br><br><br><br><br><br>
      <b><u><span id="NamaTTD">-</span></u></b><br>
      NIDN. <span id="NIDN">-</span>
    </div>
  </div>

  <div style="height: 100px"></div>

  <div class="page-break">
    Lampiran SPTJM Tunjangan Kinerja (Susulan) Nomor: <span id="Nomor_Surat_2">-</span><br>
    Kode PTS: {{ $kode_pts ?? '-' }}<br>
    Nama PTS: {{ $pts ?? '-' }}<br>
    Alamat PTS: <span id="Alamat_PTS">-</span><br>
    Laporan Bulan: <span id="PeriodeBulan">{{ $bulanTeks }}</span> {{ now()->year }}
  </div>
  <br>

  <table id="laporan">
    <colgroup>
      <col style="width:4%">
      <col style="width:9%">
      <col style="width:9%">
      <col style="width:22%">
      <col style="width:10%">
      <col style="width:6%">
      <col style="width:9%">
      <col style="width:7%">
      <col style="width:4%">
      <col style="width:4%">
      <col style="width:4%">
      <col style="width:6%">
      <col style="width:6%">
    </colgroup>
    <thead>
      <tr>
        <th>No</th>
        <th>NIDN</th>
        <th>NUPTK</th>
        <th>Nama Dosen</th>
        <th>Jabatan</th>
        <th>Kelas Jabatan</th>
        <th>Nilai Tukin Kelas Jabatan</th>
        <th>Sertifikat</th>
        <th>KD</th>
        <th>KP</th>
        <th>PP</th>
        <th>Status</th>
        <th>Ket. Status</th>
      </tr>
    </thead>
    <tbody>
      @php $no=1; @endphp
      @forelse($dosenList as $row)
        @php
          $kelas='-'; $nilai='-';
          $jab = $row->jabatan;
          if (in_array($jab, ['Guru Besar', 'Guru Besar 1050'])) { $kelas='15'; $nilai='Rp. 19.280.000'; }
          elseif ($jab === 'Lektor Kepala') { $kelas='13'; $nilai='Rp. 10.936.000'; }
          elseif ($jab === 'Lektor') { $kelas='11'; $nilai='Rp. 8.757.600'; }
          elseif ($jab === 'Asisten Ahli') { $kelas='9'; $nilai='Rp. 5.079.200'; }
          elseif ($jab === 'Tanpa Jabatan') { $kelas='8'; $nilai='Rp. 4.595.150'; }
          elseif ($jab === 'CPNS') { $kelas='7'; $nilai='Rp. 3.915.950'; }
        @endphp
        <tr>
          <td style="text-align:center">{{ $no++ }}</td>
          <td style="text-align:center"><strong>{{ $row->nidn }}</strong></td>
          <td style="text-align:center">{{ $row->nuptk ?? '-' }}</td>
          <td>{{ $row->nama }}</td>
          <td style="text-align:center">{{ $row->jabatan ?? '-' }}</td>
          <td style="text-align:center">{{ $kelas }}</td>
          <td style="text-align:center">{{ $nilai }}</td>
          <td>{{ $row->sertifikat_dosen ?? '-' }}</td>
          <td style="text-align:center">{{ $row->kd ?? '-' }}</td>
          <td style="text-align:center">{{ $row->kp ?? '-' }}</td>
          <td style="text-align:center">{{ $row->pp ?? '-' }}</td>
          <td style="text-align:center">{!! ($row->aktif ?? 0) == 1 ? '<span class="badge bg-label-success border border-success">Aktif</span>' : '<span class="badge bg-label-danger">Tidak Aktif</span>' !!}</td>
          <td style="text-align:center"><strong>{{ $row->keterangan ?? '-' }}</strong></td>
        </tr>
      @empty
        <tr><td colspan="13">Data tidak ditemukan.</td></tr>
      @endforelse
    </tbody>
  </table>

  <br>
  <div>
    Keterangan:<br>
    <span style="display:inline-block;width:30px;">KD</span>: Kinerja Dasar<br>
    <span style="display:inline-block;width:30px;">KP</span>: Kinerja Prestasi<br>
    <span style="display:inline-block;width:30px;">PP</span>: Potongan Periodik<br>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      try {
        const data = JSON.parse(localStorage.getItem('dataTukinSusulan') || '{}');
        if (data) {
          const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || '-'; };
          set('NIDN', data.nidn);
          set('Nama', data.nama);
          set('NamaTTD', data.nama);
          set('Jabatan', data.jabatan);
          set('Kota', data.kota);
          set('Nomor_Surat', data.nomor);
          set('Nomor_Surat_2', data.nomor);
          set('Alamat_PTS', data.alamat);
          set('PeriodeBulan', data.bulan);
          // Auto print
          setTimeout(() => window.print(), 300);
        }
      } catch (e) {
        console.error(e);
      }
    });
  </script>
  </body>

</html>
