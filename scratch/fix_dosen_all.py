import sys
with open('d:/Kerjaan/Project/SPTJM_GITHUB/SPTJMv2.lldikti4/resources/views/dosen/monitoring-pembayaran.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Fix the missing @else block in the Blade HTML
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
        </tr>
        @endforeach
        <tr class="fw-bold table-light">
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

content = content.replace(html_target, html_replacement)


# 2. Fix JS thead
js_thead_target = '''            if(thead){
              if (currentJenis === 'tukin') {
                  thead.innerHTML=`<tr><th class="text-center">Tahun</th><th class="text-center">Bulan</th><th class="text-center">Kode Usulan</th><th class="text-center">Gol/MK</th><th class="text-center">Gaji</th><th class="text-center">Tunj. Dasar</th><th class="text-center">Tunj. Prestasi</th><th class="text-center">Potongan</th><th class="text-center">Bersih TPD</th><th class="text-center">Bersih Serdos</th><th class="text-center">Nilai Bersih</th><th class="text-center">NO SP2D</th><th class="text-center">Tgl SP2D</th><th class="text-center">Selisih</th><th class="text-center">Status</th></tr>`;
              } else {
                  thead.innerHTML=`<tr><th class="text-center">Tahun</th><th class="text-center">Bulan</th><th class="text-center">Kode Usulan</th><th class="text-center">Gol/MK</th><th class="text-center">Gaji</th><th class="text-center">Nominal SPTJM</th>${hasTkgb?'<th class="text-center tkgb-col">Nominal TUKIN</th>':''}<th class="text-center">Bersih SPTJM</th>${hasTkgb?'<th class="text-center tkgb-col">Bersih TUKIN</th>':''}<th class="text-center">NO SP2D</th><th class="text-center">TGL SP2D</th><th class="text-center">Selisih</th><th class="text-center">Status</th></tr>`;
              }
            }'''

js_thead_replacement = '''            if(thead){
              if (currentJenis === 'tukin') {
                  thead.innerHTML=`<tr><th class="text-center">Tahun</th><th class="text-center">Bulan</th><th class="text-center">Kode Usulan</th><th class="text-center">Gol/MK</th><th class="text-center">Gaji</th><th class="text-center">Tunj. Dasar</th><th class="text-center">Tunj. Prestasi</th><th class="text-center">Potongan</th><th class="text-center">Bersih TPD</th><th class="text-center">Bersih Serdos</th><th class="text-center">Nilai Bersih</th><th class="text-center">NO SP2D</th><th class="text-center">Tgl SP2D</th><th class="text-center">Selisih</th><th class="text-center">Status</th></tr>`;
              } else if (currentJenis === 'sptjm') {
                  const nc=hasTkgb?6:3;
                  thead.innerHTML=`<tr><th rowspan="2" class="text-center align-middle">Tahun</th><th rowspan="2" class="text-center align-middle">Bulan</th><th rowspan="2" class="text-center align-middle">Kode Usulan</th><th rowspan="2" class="text-center align-middle">Gol/MK</th><th rowspan="2" class="text-center align-middle">Gaji</th><th colspan="${nc}" class="text-center align-middle">Nominal</th><th rowspan="2" class="text-center align-middle">NO SP2D</th><th rowspan="2" class="text-center align-middle">TGL SP2D</th><th rowspan="2" class="text-center align-middle">Selisih</th><th rowspan="2" class="text-center align-middle">Status</th></tr><tr><th class="text-center align-middle">Kotor TPD</th>${hasTkgb?'<th class="text-center align-middle tkgb-col">Kotor TKGB</th>':''}<th class="text-center align-middle">Pajak TPD</th>${hasTkgb?'<th class="text-center align-middle tkgb-col">Pajak TKGB</th>':''}<th class="text-center align-middle">Bersih TPD</th>${hasTkgb?'<th class="text-center align-middle tkgb-col">Bersih TKGB</th>':''}</tr>`;
              } else {
                  thead.innerHTML=`<tr><th class="text-center align-middle">Tahun</th><th class="text-center align-middle">Bulan</th><th class="text-center align-middle">Kode Usulan</th><th class="text-center align-middle">Gol/MK</th><th class="text-center align-middle">Gaji</th><th class="text-center align-middle">Nominal SPTJM</th>${hasTkgb?'<th class="text-center align-middle tkgb-col">Nominal TUKIN</th>':''}<th class="text-center align-middle">Bersih SPTJM</th>${hasTkgb?'<th class="text-center align-middle tkgb-col">Bersih TUKIN</th>':''}<th class="text-center align-middle">NO SP2D</th><th class="text-center align-middle">TGL SP2D</th><th class="text-center align-middle">Selisih</th><th class="text-center align-middle">Status</th></tr>`;
              }
            }'''

content = content.replace(js_thead_target, js_thead_replacement)


# 3. Fix JS tbody
js_tbody_target = '''              } else {
                for(let i=0;i<months.length;i++){
                  const st=stb[i];
                  let stH='-'; if(st&&statusCfg[st]) stH=`<span class="badge ${statusCfg[st][0]}" style="font-size:10px">${statusCfg[st][1]}</span>`; else if(st&&st.startsWith('kode:')) stH=`<span class="badge bg-label-secondary" style="font-size:10px">${st.substring(5)}</span>`;
                  const pfx=sb[i]<0?'-':(sb[i]>0?'+':''), s=sb[i]||0, sc=s<0?'text-danger fw-bold':(s>0?'text-success fw-bold':'text-success fw-bold');
                  const ss='font-size:12px; font-weight:700; font-family:"Roboto",sans-serif; letter-spacing:0.3px;';
                  let tglMain=data.tglSp2d[i]??'-';
                  if(tglMain!=='-'){ try{ const pd=new Date(tglMain); if(!isNaN(pd)) tglMain=('0'+pd.getDate()).slice(-2)+'/'+('0'+(pd.getMonth()+1)).slice(-2)+'/'+pd.getFullYear(); }catch(e){} }
                  
                  const gaji = data.gajiBulanan[i]??0;
                  const kotorTpdCol = `<td class="text-end">${fmt(data.kotorTpd[i]??0)}</td>`;
                  const kotorTkgbCol = tkc(data.kotorTkgb[i]??0);
                  const pajakTpdCol = `<td class="text-end">${fmt(data.pajakTpd[i]??0)}</td>`;
                  const pajakTkgbCol = tkc(data.pajakTkgb[i]??0);
                  const bersihTpdCol = `<td class="text-end">${fmt(data.bersihTpd[i]??0)}</td>`;
                  const bersihTkgbCol = tkc(data.bersihTkgb[i]??0);
                  
                  let nomCols = kotorTpdCol + kotorTkgbCol + pajakTpdCol + pajakTkgbCol + bersihTpdCol + bersihTkgbCol;
    
                  tbody.innerHTML+=`<tr><td class="text-center">${data.selectedYear||'-'}</td><td>${months[i]}</td><td class="text-center">${data.kodeUsulanBulanan[i]??'-'}</td><td class="text-center">${data.golonganBulanan[i]??'-'} - ${data.tahunBulanan[i]??'-'}</td><td class="text-end">${fmt(gaji)}</td>${nomCols}<td class="text-center" style="font-size:11px">${data.noSp2d[i]??'-'}</td><td class="text-center" style="font-size:11px">${tglMain}</td><td class="${sc}" style="${ss}">${pfx}${fmt(Math.abs(s))}</td><td class="text-center">${stH}</td></tr>`;
                }
    
                // Totals
                const t=data.totals||{};
                let sumCols = `<td class="text-end">${fmt(t.kotorTpd||0)}</td>${tkc(t.kotorTkgb||0)}<td class="text-end">${fmt(t.pajakTpd||0)}</td>${tkc(t.pajakTkgb||0)}<td class="text-end">${fmt(t.bersihTpd||0)}</td>${tkc(t.bersihTkgb||0)}`;
                tbody.innerHTML+=`<tr class="fw-bold table-light"><td colspan="4" class="text-center">Jumlah</td><td class="text-end">${fmt(t.gaji||0)}</td>${sumCols}<td colspan="2"></td><td colspan="2"></td></tr>`;
    
                // Pembayaran Kekurangan dan Pengembalian Kelebihan (Nilai Asli)
                const sumOri = data.summaryOriginal || {};
                const valKGrRow = sumOri.k_gross || 0;
                const valKPjRow = sumOri.k_pajak || 0;
                const valKNeRow = sumOri.k_net || 0;
                
                const valLGrRow = sumOri.l_gross || 0;
                const valLPjRow = sumOri.l_pajak || 0;
                const valLNeRow = sumOri.l_net || 0;

                tbody.innerHTML+=`<tr class="fw-bold" style="background-color:#ffdcdc"><td colspan="4" class="text-center">Pembayaran Kekurangan</td><td></td><td class="text-end">${fmt(valKGrRow)}</td>${tkc(0)}<td class="text-end">${fmt(valKPjRow)}</td>${tkc(0)}<td class="text-end">${fmt(valKNeRow)}</td>${tkc(0)}<td colspan="2"></td><td colspan="2"></td></tr>`;
                tbody.innerHTML+=`<tr class="fw-bold" style="background-color:#dbeafe"><td colspan="4" class="text-center">Pengembalian Kelebihan</td><td></td><td class="text-end">${fmt(valLGrRow)}</td>${tkc(0)}<td class="text-end">${fmt(valLPjRow)}</td>${tkc(0)}<td class="text-end">${fmt(valLNeRow)}</td>${tkc(0)}<td colspan="2"></td><td colspan="2"></td></tr>`;
    
                // Total Akhir (Sisa)
                const jmKotorTpd = (t.kotorTpd||0) + (t.kotorTkgb||0);
                const jmPajakTpd = (t.pajakTpd||0) + (t.pajakTkgb||0);
                const jmBersihTpd = (t.bersihTpd||0) + (t.bersihTkgb||0);
                
                const taKotorTpd = jmKotorTpd + valKGrRow - valLGrRow;
                const taPajakTpd = jmPajakTpd + valKPjRow - valLPjRow;
                const taBersihTpd = jmBersihTpd + valKNeRow - valLNeRow;
                tbody.innerHTML+=`<tr class="fw-bold" style="background-color:#d1fae5"><td colspan="4" class="text-center">Total Akhir</td><td class="text-end">${fmt(t.gaji||0)}</td><td class="text-end">${fmt(taKotorTpd)}</td>${tkc(0)}<td class="text-end">${fmt(taPajakTpd)}</td>${tkc(0)}<td class="text-end">${fmt(taBersihTpd)}</td>${tkc(0)}<td colspan="2"></td><td colspan="2"></td></tr>`;
            }'''

js_tbody_replacement = '''              } else if (currentJenis === 'sptjm') {
                for(let i=0;i<months.length;i++){
                  const st=stb[i];
                  let stH='-'; if(st&&statusCfg[st]) stH=`<span class="badge ${statusCfg[st][0]}" style="font-size:10px">${statusCfg[st][1]}</span>`; else if(st&&st.startsWith('kode:')) stH=`<span class="badge bg-label-secondary" style="font-size:10px">${st.substring(5)}</span>`;
                  const pfx=sb[i]<0?'-':(sb[i]>0?'+':''), s=sb[i]||0, sc=s<0?'text-danger fw-bold':(s>0?'text-success fw-bold':'text-success fw-bold');
                  const ss='font-size:12px; font-weight:700; font-family:"Roboto",sans-serif; letter-spacing:0.3px;';
                  let tglMain=data.tglSp2d[i]??'-';
                  if(tglMain!=='-'){ try{ const pd=new Date(tglMain); if(!isNaN(pd)) tglMain=('0'+pd.getDate()).slice(-2)+'/'+('0'+(pd.getMonth()+1)).slice(-2)+'/'+pd.getFullYear(); }catch(e){} }
                  
                  const gaji = data.gajiBulanan[i]??0;
                  const kotorTpdCol = `<td class="text-end">${fmt(data.kotorTpd[i]??0)}</td>`;
                  const kotorTkgbCol = tkc(data.kotorTkgb[i]??0);
                  const pajakTpdCol = `<td class="text-end">${fmt(data.pajakTpd[i]??0)}</td>`;
                  const pajakTkgbCol = tkc(data.pajakTkgb[i]??0);
                  const bersihTpdCol = `<td class="text-end">${fmt(data.bersihTpd[i]??0)}</td>`;
                  const bersihTkgbCol = tkc(data.bersihTkgb[i]??0);
                  
                  let nomCols = kotorTpdCol + kotorTkgbCol + pajakTpdCol + pajakTkgbCol + bersihTpdCol + bersihTkgbCol;
    
                  tbody.innerHTML+=`<tr><td class="text-center">${data.selectedYear||'-'}</td><td>${months[i]}</td><td class="text-center">${data.kodeUsulanBulanan[i]??'-'}</td><td class="text-center">${data.golonganBulanan[i]??'-'} - ${data.tahunBulanan[i]??'-'}</td><td class="text-end">${fmt(gaji)}</td>${nomCols}<td class="text-center" style="font-size:11px">${data.noSp2d[i]??'-'}</td><td class="text-center" style="font-size:11px">${tglMain}</td><td class="${sc}" style="${ss}">${pfx}${fmt(Math.abs(s))}</td><td class="text-center">${stH}</td></tr>`;
                }
    
                // Totals
                const t=data.totals||{};
                let sumCols = `<td class="text-end">${fmt(t.kotorTpd||0)}</td>${tkc(t.kotorTkgb||0)}<td class="text-end">${fmt(t.pajakTpd||0)}</td>${tkc(t.pajakTkgb||0)}<td class="text-end">${fmt(t.bersihTpd||0)}</td>${tkc(t.bersihTkgb||0)}`;
                tbody.innerHTML+=`<tr class="fw-bold table-light"><td colspan="4" class="text-center">Jumlah</td><td class="text-end">${fmt(t.gaji||0)}</td>${sumCols}<td colspan="2"></td><td colspan="2"></td></tr>`;
    
                // Pembayaran Kekurangan dan Pengembalian Kelebihan (Nilai Asli)
                const sumOri = data.summaryOriginal || {};
                const valKGrRow = sumOri.k_gross || 0;
                const valKPjRow = sumOri.k_pajak || 0;
                const valKNeRow = sumOri.k_net || 0;
                
                const valLGrRow = sumOri.l_gross || 0;
                const valLPjRow = sumOri.l_pajak || 0;
                const valLNeRow = sumOri.l_net || 0;

                const pjKurang = `<td class="text-end">${fmt(valKPjRow)}</td>${tkc(0)}`;
                tbody.innerHTML+=`<tr class="fw-bold" style="background-color:#ffdcdc"><td colspan="4" class="text-center">Pembayaran Kekurangan</td><td></td><td class="text-end">${fmt(valKGrRow)}</td>${tkc(0)}${pjKurang}<td class="text-end">${fmt(valKNeRow)}</td>${tkc(0)}<td colspan="2"></td><td colspan="2"></td></tr>`;
                const pjLebih = `<td class="text-end">${fmt(valLPjRow)}</td>${tkc(0)}`;
                tbody.innerHTML+=`<tr class="fw-bold" style="background-color:#dbeafe"><td colspan="4" class="text-center">Pengembalian Kelebihan</td><td></td><td class="text-end">${fmt(valLGrRow)}</td>${tkc(0)}${pjLebih}<td class="text-end">${fmt(valLNeRow)}</td>${tkc(0)}<td colspan="2"></td><td colspan="2"></td></tr>`;
    
                // Total Akhir (Sisa)
                const jmKotorTpd = (t.kotorTpd||0) + (t.kotorTkgb||0);
                const jmPajakTpd = (t.pajakTpd||0) + (t.pajakTkgb||0);
                const jmBersihTpd = (t.bersihTpd||0) + (t.bersihTkgb||0);
                
                const taKotorTpd = jmKotorTpd + valKGrRow - valLGrRow;
                const taPajakTpd = jmPajakTpd + valKPjRow - valLPjRow;
                const taBersihTpd = jmBersihTpd + valKNeRow - valLNeRow;
                const pjTa = `<td class="text-end">${fmt(taPajakTpd)}</td>${tkc(0)}`;
                tbody.innerHTML+=`<tr class="fw-bold" style="background-color:#d1fae5"><td colspan="4" class="text-center">Total Akhir</td><td class="text-end">${fmt(t.gaji||0)}</td><td class="text-end">${fmt(taKotorTpd)}</td>${tkc(0)}${pjTa}<td class="text-end">${fmt(taBersihTpd)}</td>${tkc(0)}<td colspan="2"></td><td colspan="2"></td></tr>`;
              } else {
                for(let i=0;i<months.length;i++){
                  const st=stb[i];
                  let stH='-'; if(st&&statusCfg[st]) stH=`<span class="badge ${statusCfg[st][0]}" style="font-size:10px">${statusCfg[st][1]}</span>`; else if(st&&st.startsWith('kode:')) stH=`<span class="badge bg-label-secondary" style="font-size:10px">${st.substring(5)}</span>`;
                  const pfx=sb[i]<0?'-':(sb[i]>0?'+':''), s=sb[i]||0, sc=s<0?'text-danger fw-bold':(s>0?'text-success fw-bold':'text-success fw-bold');
                  const ss='font-size:12px; font-weight:700; font-family:"Roboto",sans-serif; letter-spacing:0.3px;';
                  let tglMain=data.tglSp2d[i]??'-';
                  if(tglMain!=='-'){ try{ const pd=new Date(tglMain); if(!isNaN(pd)) tglMain=('0'+pd.getDate()).slice(-2)+'/'+('0'+(pd.getMonth()+1)).slice(-2)+'/'+pd.getFullYear(); }catch(e){} }
                  
                  const gaji = data.gajiBulanan[i]??0;
                  const kotorTpdCol = `<td class="text-end">${fmt(data.kotorTpd[i]??0)}</td>`;
                  const kotorTkgbCol = tkc(data.kotorTkgb[i]??0);
                  const bersihTpdCol = `<td class="text-end">${fmt(data.bersihTpd[i]??0)}</td>`;
                  const bersihTkgbCol = tkc(data.bersihTkgb[i]??0);
                  
                  let nomCols = kotorTpdCol + kotorTkgbCol + bersihTpdCol + bersihTkgbCol;
    
                  tbody.innerHTML+=`<tr><td class="text-center">${data.selectedYear||'-'}</td><td>${months[i]}</td><td class="text-center">${data.kodeUsulanBulanan[i]??'-'}</td><td class="text-center">${data.golonganBulanan[i]??'-'} - ${data.tahunBulanan[i]??'-'}</td><td class="text-end">${fmt(gaji)}</td>${nomCols}<td class="text-center" style="font-size:11px">${data.noSp2d[i]??'-'}</td><td class="text-center" style="font-size:11px">${tglMain}</td><td class="${sc}" style="${ss}">${pfx}${fmt(Math.abs(s))}</td><td class="text-center">${stH}</td></tr>`;
                }
    
                // Totals
                const t=data.totals||{};
                let sumCols = `<td class="text-end">${fmt(t.kotorTpd||0)}</td>${tkc(t.kotorTkgb||0)}<td class="text-end">${fmt(t.bersihTpd||0)}</td>${tkc(t.bersihTkgb||0)}`;
                tbody.innerHTML+=`<tr class="fw-bold table-light"><td colspan="4" class="text-center">Jumlah</td><td class="text-end">${fmt(t.gaji||0)}</td>${sumCols}<td colspan="2"></td><td colspan="2"></td></tr>`;
    
                // Pembayaran Kekurangan dan Pengembalian Kelebihan (Nilai Asli)
                const sumOri = data.summaryOriginal || {};
                const valKGrRow = sumOri.k_gross || 0;
                const valKNeRow = sumOri.k_net || 0;
                
                const valLGrRow = sumOri.l_gross || 0;
                const valLNeRow = sumOri.l_net || 0;

                tbody.innerHTML+=`<tr class="fw-bold" style="background-color:#ffdcdc"><td colspan="4" class="text-center">Pembayaran Kekurangan</td><td></td><td class="text-end">${fmt(valKGrRow)}</td>${tkc(0)}<td class="text-end">${fmt(valKNeRow)}</td>${tkc(0)}<td colspan="2"></td><td colspan="2"></td></tr>`;
                tbody.innerHTML+=`<tr class="fw-bold" style="background-color:#dbeafe"><td colspan="4" class="text-center">Pengembalian Kelebihan</td><td></td><td class="text-end">${fmt(valLGrRow)}</td>${tkc(0)}<td class="text-end">${fmt(valLNeRow)}</td>${tkc(0)}<td colspan="2"></td><td colspan="2"></td></tr>`;
    
                // Total Akhir (Sisa)
                const jmKotorTpd = (t.kotorTpd||0) + (t.kotorTkgb||0);
                const jmBersihTpd = (t.bersihTpd||0) + (t.bersihTkgb||0);
                
                const taKotorTpd = jmKotorTpd + valKGrRow - valLGrRow;
                const taBersihTpd = jmBersihTpd + valKNeRow - valLNeRow;
                tbody.innerHTML+=`<tr class="fw-bold" style="background-color:#d1fae5"><td colspan="4" class="text-center">Total Akhir</td><td class="text-end">${fmt(t.gaji||0)}</td><td class="text-end">${fmt(taKotorTpd)}</td>${tkc(0)}<td class="text-end">${fmt(taBersihTpd)}</td>${tkc(0)}<td colspan="2"></td><td colspan="2"></td></tr>`;
              }'''

content = content.replace(js_tbody_target, js_tbody_replacement)

with open('d:/Kerjaan/Project/SPTJM_GITHUB/SPTJMv2.lldikti4/resources/views/dosen/monitoring-pembayaran.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Done fixing dosen view")
