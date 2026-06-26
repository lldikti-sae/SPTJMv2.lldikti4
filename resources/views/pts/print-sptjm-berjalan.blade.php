<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <title>Print SPTJM Berjalan</title>
  <style>
    body {
      font-family: "Times New Roman", serif;
      margin: 40px;
      font-size: 12pt;
    }

    .center {
      text-align: center;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th,
    td {
      border: 1px solid #888;
      padding: 8px;
      text-align: left;
    }

    th {
      background-color: #e5e5e5;
    }

    .mt-5 {
      margin-top: 50px;
    }

    .ttd {
      float: right;
      text-align: left;
      margin-top: 50px;
    }

    /* Cetak jadi 2 halaman */
    .page-break {
      page-break-before: always;
    }

    @media print {
      .page-break {
        display: block;
        page-break-before: always;
      }
    }
  </style>
</head>

<body>
  <!-- Halaman 1 -->
  <br><br><br><br><br>
  <div class="center" style="margin-top: 100px;">
    <h3 style="margin-bottom: 0; line-height: 1;"><u>SURAT PERNYATAAN TANGGUNG JAWAB MUTLAK</u></h3>
    <p style="margin-top: 0;">Nomor: <span id="nomorSurat"></span></p>
  </div>
  <br>

  <p>Yang bertanda tangan di bawah ini:</p>

  <p>
    Nama : <span id="namaPimpinan"></span><br />
    Jabatan: <span id="jabatanPimpinan"></span><br />
    PTS : <span>{{ $pts }}</span><br />
  </p>

  <p>Menyatakan dengan sesungguhnya bahwa:</p>

  <ol style="text-align: justify;">
    <li style="margin-bottom: 5px;">
      Nama-nama dosen sebagaimana daftar terlampir telah nyata melaksanakan tugas sesuai dengan Kontrak Beban
      Kerja Dosen yang telah disepakati dengan beban kerja sesuai Peraturan Pemerintah Nomor 37 Tahun 2009.
    </li>
    <li style="margin-bottom: 5px;">
      Apabila dikemudian hari terdapat kekeliruan sehingga mengakibatkan kesalahan/kelebihan pembayaran
      gaji/tunjangan sertifikasi dosen, saya bersedia mengembalikan kelebihan ke Kas Negara.
    </li>
  </ol>

  <p>Demikian pernyataan ini saya buat dengan sebenar-benarnya.</p>

  <div class="ttd">
    <span id="kotaPimpinan"></span>, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}<br>
    Pembuat Pernyataan,<br><br><br><br><br><br><br><br><br>
    <strong><u><span id="namaPimpinan1"></span></u></strong><br />
    NIDN: <span id="nidnPimpinan"></span>
  </div>

  <!-- Halaman 2 -->
  <div class="page-break"></div>
  <br><br><br><br><br><br><br><br><br><br>
  <div class="mt-5">
    @php
    use Carbon\Carbon;
    $bulanSekarang = $bulan;
    $tahunSekarang = Carbon::now()->year;
    $namaBulan = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember',
    ];
    @endphp

    Lampiran SPTJM Nomor: <span id="nomorSurat1"></span><br />
    Kode PTS: {{ $kode_pts }}<br />
    Nama PTS: {{ $pts }}<br />
    Laporan Bulan: {{ $namaBulan[$bulanSekarang] }} {{ $tahunSekarang }}<br />
  </div>

  <h4>Daftar Nama Dosen PNS:</h4>
  <table>
    <colgroup>
      <col style="width: 5%">
      <col style="width: 10%">
      <col style="width: 10%">
      <col style="width: 25%">
      <col style="width: 12%">
      <col style="width: 12%">
      <col style="width: 13%">
      <col style="width: 13%">
    </colgroup>
    <thead>
      <tr>
        <th>No</th>
        <th>NIDN</th>
        <th>NUPTK</th>
        <th>Nama Dosen</th>
        <th>Golongan</th>
        <th>Masa Kerja</th>
        <th>Jabatan</th>
        <th>BKD</th>
      </tr>
    </thead>
    <tbody>
      @php $no = 1; @endphp
      @foreach ($dosenListPNS as $dosen)
      <tr>
        <td>{{ $no++ }}</td>
        <td>{{ $dosen->nidn }}</td>
        <td>{{ $dosen->nuptk ?? '-' }}</td>
        <td>{{ $dosen->nama }}</td>
        <td>{{ $dosen->gol ?? '-' }}</td>
        <td>{{ $dosen->tahun ?? '-' }}</td>
        <td>{{ $dosen->jabatan ?? '-' }}</td>
        <td>{{ $dosen->kesimpulan_bkd ?? '-' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <h4>Daftar Nama Dosen NON PNS:</h4>
  <table>
    <colgroup>
      <col style="width: 5%">
      <col style="width: 12%">
      <col style="width: 25%">
      <col style="width: 14%">
      <col style="width: 14%">
      <col style="width: 15%">
      <col style="width: 15%">
    </colgroup>
    <thead>
      <tr>
        <th>No</th>
        <th>NIDN</th>
        <th>NUPTK</th>
        <th>Nama Dosen</th>
        <th>Golongan</th>
        <th>Masa Kerja</th>
        <th>Jabatan</th>
        <th>BKD</th>
      </tr>
    </thead>
    <tbody>
      @php $no = 1; @endphp
      @foreach ($dosenListNonPNS as $dosen)
      <tr>
        <td>{{ $no++ }}</td>
        <td>{{ $dosen->nidn }}</td>
        <td>{{ $dosen->nuptk ?? '-' }}</td>
        <td>{{ $dosen->nama }}</td>
        <td>{{ $dosen->gol ?? '-' }}</td>
        <td>{{ $dosen->tahun ?? '-' }}</td>
        <td>{{ $dosen->jabatan ?? '-' }}</td>
        <td>{{ $dosen->kesimpulan_bkd ?? '-' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="ttd">
    <span id="kotaPimpinan2"></span>, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}<br>
    Pembuat Pernyataan,<br><br><br><br><br><br><br><br><br>
    <strong><u><span id="namaPimpinan2"></span></u></strong><br />
    NIDN:<span id="nidnPimpinan2"></span>
  </div>

  <script>
    window.onload = function() {
      const data = JSON.parse(localStorage.getItem('dataSPTJM'));

      if (data) {
        document.getElementById('namaPimpinan').innerText = data.nama;
        document.getElementById('namaPimpinan1').innerText = data.nama;
        document.getElementById('jabatanPimpinan').innerText = data.jabatan;
        document.getElementById('nidnPimpinan').innerText = data.nidn;
        document.getElementById('kotaPimpinan').innerText = data.kota;
        document.getElementById('nomorSurat').innerText = data.nomor;
        document.getElementById('nomorSurat1').innerText = data.nomor;
        document.getElementById('kotaPimpinan2').innerText = data.kota;
        document.getElementById('namaPimpinan2').innerText = data.nama;
        document.getElementById('nidnPimpinan2').innerText = data.nidn;
      }
    };
  </script>

</body>

</html>