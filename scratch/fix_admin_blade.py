import sys

with open('d:/Kerjaan/Project/SPTJM_GITHUB/SPTJMv2.lldikti4/resources/views/admin/monitoring-pembayaran.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Fix the missing @else block in the Blade HTML for admin
html_target = '''        <tr class="fw-bold table-light">
          <td colspan="4" class="text-center">Jumlah</td>
          <td class="text-end">{{ number_format($totalGaji,0,',','.') }}</td>
          <td class="text-end">{{ number_format($totalKotorTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalKotorTkgb,0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format($totalBersihTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalBersihTkgb,0,',','.') }}</td>@endif
          <td colspan="2"></td><td colspan="2"></td>
        </tr>
      @endif

        @php
           // Mengambil selisih asli TANPA dikurangi pembayaran dan tanpa netting'''

html_replacement = '''        <tr class="fw-bold table-light">
          <td colspan="4" class="text-center">Jumlah</td>
          <td class="text-end">{{ number_format($totalGaji,0,',','.') }}</td>
          <td class="text-end">{{ number_format($totalKotorTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalKotorTkgb,0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format($totalBersihTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalBersihTkgb,0,',','.') }}</td>@endif
          <td colspan="2"></td><td colspan="2"></td>
        </tr>
      @else
      <thead>
        <tr>
          <th class="text-center align-middle">Tahun</th>
          <th class="text-center align-middle">Bulan</th>
          <th class="text-center align-middle">Kode Usulan</th>
          <th class="text-center align-middle">Gol/MK</th>
          <th class="text-center align-middle">Gaji</th>
          <th class="text-center align-middle">Nominal SPTJM</th>
          @if($hasTkgb)<th class="text-center align-middle tkgb-col">Nominal TUKIN</th>@endif
          <th class="text-center align-middle">Bersih SPTJM</th>
          @if($hasTkgb)<th class="text-center align-middle tkgb-col">Bersih TUKIN</th>@endif
          <th class="text-center align-middle">NO SP2D</th>
          <th class="text-center align-middle">TGL SP2D</th>
          <th class="text-center align-middle">Selisih</th>
          <th class="text-center align-middle">Status</th>
          <th class="text-center align-middle"><i class="bx bx-cog"></i></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($months as $index => $month)
        @php $sel = $selisihBulanan[$index] ?? 0; $st = $statusBulanan[$index] ?? null; @endphp
        <tr>
          <td class="text-center">{{ $selectedYear ?? '-' }}</td>
          <td>{{ $month }}</td>
          <td class="text-center">{{ $kodeUsulanBulanan[$index] ?? '-' }}</td>
          <td class="text-center">{{ $golonganBulanan[$index] ?? '-' }} - {{ $tahunBulanan[$index] ?? '-' }}</td>
          <td class="text-end">{{ number_format($gajiBulanan[$index] ?? 0,0,',','.') }}</td>
          <td class="text-end">{{ number_format($kotorTpd[$index] ?? 0,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($kotorTkgb[$index] ?? 0,0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format($bersihTpd[$index] ?? 0,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($bersihTkgb[$index] ?? 0,0,',','.') }}</td>@endif
          <td class="text-center" style="font-size:11px;">{{ $noSp2d[$index] ?? '-' }}</td>
          @php
             $tglSp2dStr = $tglSp2d[$index] ?? '-';
             if ($tglSp2dStr !== '' && $tglSp2dStr !== '-') {
                 try { $tglSp2dStr = \Carbon\Carbon::parse($tglSp2dStr)->format('d/m/Y'); } catch(\Exception $e) {}
             }
          @endphp
          <td class="text-center" style="font-size:11px;">{{ $tglSp2dStr }}</td>
          <td class="text-end fw-bold {{ $sel < 0 ? 'text-danger' : ($sel > 0 ? 'text-success' : 'text-success') }}">{{ $sel < 0 ? '-' : ($sel > 0 ? '+' : '') }}{{ number_format(abs($sel),0,',','.') }}</td>
          <td class="text-center">@if($st && isset($statusMap[$st]))<span class="badge {{ $statusMap[$st][0] }}" style="font-size:10px;">{{ $statusMap[$st][1] }}</span>@elseif($st && str_starts_with($st, 'kode:'))<span class="badge bg-label-secondary" style="font-size:10px;">{{ substr($st, 5) }}</span>@else - @endif</td>
          <td class="text-center">
             <button class="btn btn-sm btn-icon edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" 
               data-id="{{ $transaksiIdBulanan[$index] ?? '' }}" data-gaji="{{ $gajiBulanan[$index] ?? 0 }}"
               data-kotor="{{ $kotorTpd[$index] ?? 0 }}" data-bersih="{{ $bersihTpd[$index] ?? 0 }}"
               data-kotortkgb="{{ $kotorTkgb[$index] ?? 0 }}" data-bersihtkgb="{{ $bersihTkgb[$index] ?? 0 }}"
               data-pajaktkgb="{{ $pajakTkgb[$index] ?? 0 }}"
               data-status="{{ $st ?? '' }}" data-selisih="{{ $sel }}"
               data-bulan="{{ $month }}" data-tahun="{{ $selectedYear }}" {{ empty($transaksiIdBulanan[$index]) ? 'disabled' : '' }}>
               <i class="bx bx-edit text-primary"></i>
             </button>
          </td>
        </tr>
        @endforeach
        <tr class="fw-bold table-light">
          <td colspan="4" class="text-center">Jumlah</td>
          <td class="text-end">{{ number_format($totalGaji,0,',','.') }}</td>
          <td class="text-end">{{ number_format($totalKotorTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalKotorTkgb,0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format($totalBersihTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalBersihTkgb,0,',','.') }}</td>@endif
          <td colspan="2"></td><td colspan="2"></td><td class="d-none"></td>
        </tr>
      @endif

        @php
           // Mengambil selisih asli TANPA dikurangi pembayaran dan tanpa netting'''

content = content.replace(html_target, html_replacement)

with open('d:/Kerjaan/Project/SPTJM_GITHUB/SPTJMv2.lldikti4/resources/views/admin/monitoring-pembayaran.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Done fixing admin view HTML")
