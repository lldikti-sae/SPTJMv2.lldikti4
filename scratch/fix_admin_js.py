import sys
with open('d:/Kerjaan/Project/SPTJM_GITHUB/SPTJMv2.lldikti4/resources/views/admin/monitoring-pembayaran.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

js_thead_target = '''            if(thead){
              if (currentJenis === 'tukin') {
                  thead.innerHTML=`<tr><th class="text-center">Tahun</th><th class="text-center col-bulan">Bulan</th><th class="text-center">Kode Usulan</th><th class="text-center">Jabatan akademik</th><th class="text-center">Nominal TUKIN</th><th class="text-center">Nominal Diterima</th><th class="text-center">Nominal kinerja dasar</th><th class="text-center">Nominal Kinerja Prestasi</th><th class="text-center">Potongan Periodik</th><th class="text-center">Nominal Bersih TPD</th><th class="text-center">NilaiBersih TUKIN</th><th class="text-center">NO SP2D</th><th class="text-center">TGL SP2D</th><th class="text-center">Selisih</th><th class="text-center">Status</th></tr>`;
              } else {
                  thead.innerHTML=`<tr><th class="text-center">Tahun</th><th class="text-center">Bulan</th><th class="text-center">Kode Usulan</th><th class="text-center">Gol/MK</th><th class="text-center">Gaji</th><th class="text-center">Nominal SPTJM</th>${hasTkgb?'<th class="text-center tkgb-col">Nominal TUKIN</th>':''}<th class="text-center">Bersih SPTJM</th>${hasTkgb?'<th class="text-center tkgb-col">Bersih TUKIN</th>':''}<th class="text-center">NO SP2D</th><th class="text-center">TGL SP2D</th><th class="text-center">Selisih</th><th class="text-center">Status</th></tr>`;
              }
            }'''

js_thead_replacement = '''            if(thead){
              if (currentJenis === 'tukin') {
                  thead.innerHTML=`<tr><th class="text-center">Tahun</th><th class="text-center col-bulan">Bulan</th><th class="text-center">Kode Usulan</th><th class="text-center">Jabatan akademik</th><th class="text-center">Nominal TUKIN</th><th class="text-center">Nominal Diterima</th><th class="text-center">Nominal kinerja dasar</th><th class="text-center">Nominal Kinerja Prestasi</th><th class="text-center">Potongan Periodik</th><th class="text-center">Nominal Bersih TPD</th><th class="text-center">NilaiBersih TUKIN</th><th class="text-center">NO SP2D</th><th class="text-center">TGL SP2D</th><th class="text-center">Selisih</th><th class="text-center">Status</th></tr>`;
              } else if (currentJenis === 'sptjm') {
                  const nc=hasTkgb?6:3;
                  thead.innerHTML=`<tr><th rowspan="2" class="text-center align-middle">Tahun</th><th rowspan="2" class="text-center align-middle">Bulan</th><th rowspan="2" class="text-center align-middle">Kode Usulan</th><th rowspan="2" class="text-center align-middle">Gol/MK</th><th rowspan="2" class="text-center align-middle">Gaji</th><th colspan="${nc}" class="text-center align-middle">Nominal</th><th rowspan="2" class="text-center align-middle">NO SP2D</th><th rowspan="2" class="text-center align-middle">TGL SP2D</th><th rowspan="2" class="text-center align-middle">Selisih</th><th rowspan="2" class="text-center align-middle">Status</th><th rowspan="2" class="text-center align-middle"><i class="bx bx-cog"></i></th></tr><tr><th class="text-center align-middle">Kotor TPD</th>${hasTkgb?'<th class="text-center align-middle tkgb-col">Kotor TKGB</th>':''}<th class="text-center align-middle">Pajak TPD</th>${hasTkgb?'<th class="text-center align-middle tkgb-col">Pajak TKGB</th>':''}<th class="text-center align-middle">Bersih TPD</th>${hasTkgb?'<th class="text-center align-middle tkgb-col">Bersih TKGB</th>':''}</tr>`;
              } else {
                  thead.innerHTML=`<tr><th class="text-center align-middle">Tahun</th><th class="text-center align-middle">Bulan</th><th class="text-center align-middle">Kode Usulan</th><th class="text-center align-middle">Gol/MK</th><th class="text-center align-middle">Gaji</th><th class="text-center align-middle">Nominal SPTJM</th>${hasTkgb?'<th class="text-center align-middle tkgb-col">Nominal TUKIN</th>':''}<th class="text-center align-middle">Bersih SPTJM</th>${hasTkgb?'<th class="text-center align-middle tkgb-col">Bersih TUKIN</th>':''}<th class="text-center align-middle">NO SP2D</th><th class="text-center align-middle">TGL SP2D</th><th class="text-center align-middle">Selisih</th><th class="text-center align-middle">Status</th><th class="text-center align-middle"><i class="bx bx-cog"></i></th></tr>`;
              }
            }'''

content = content.replace(js_thead_target, js_thead_replacement)


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
                  
                  let actionBtn = `<td class="text-center"><button class="btn btn-sm btn-icon edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" data-id="${data.transaksiIdBulanan[i]||''}" data-gaji="${gaji}" data-kotor="${data.kotorTpd[i]||0}" data-bersih="${data.bersihTpd[i]||0}" data-kotortkgb="${data.kotorTkgb[i]||0}" data-bersihtkgb="${data.bersihTkgb[i]||0}" data-pajaktkgb="${data.pajakTkgb[i]||0}" data-status="${st||''}" data-selisih="${s}" data-bulan="${months[i]}" data-tahun="${data.selectedYear}" ${!data.transaksiIdBulanan[i]?'disabled':''}><i class="bx bx-edit text-primary"></i></button></td>`;
    
                  tbody.innerHTML+=`<tr><td class="text-center">${data.selectedYear||'-'}</td><td>${months[i]}</td><td class="text-center">${data.kodeUsulanBulanan[i]??'-'}</td><td class="text-center">${data.golonganBulanan[i]??'-'} - ${data.tahunBulanan[i]??'-'}</td><td class="text-end">${fmt(gaji)}</td>${nomCols}<td class="text-center" style="font-size:11px">${data.noSp2d[i]??'-'}</td><td class="text-center" style="font-size:11px">${tglMain}</td><td class="${sc}" style="${ss}">${pfx}${fmt(Math.abs(s))}</td><td class="text-center">${stH}</td>${actionBtn}</tr>`;
                }
    
                // Totals
                const t=data.totals||{};
                let sumCols = `<td class="text-end">${fmt(t.kotorTpd||0)}</td>${tkc(t.kotorTkgb||0)}<td class="text-end">${fmt(t.pajakTpd||0)}</td>${tkc(t.pajakTkgb||0)}<td class="text-end">${fmt(t.bersihTpd||0)}</td>${tkc(t.bersihTkgb||0)}`;
                tbody.innerHTML+=`<tr class="fw-bold table-light"><td colspan="4" class="text-center">Jumlah</td><td class="text-end">${fmt(t.gaji||0)}</td>${sumCols}<td colspan="2"></td><td colspan="2"></td><td class="d-none"></td></tr>`;
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
                  
                  let actionBtn = `<td class="text-center"><button class="btn btn-sm btn-icon edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" data-id="${data.transaksiIdBulanan[i]||''}" data-gaji="${gaji}" data-kotor="${data.kotorTpd[i]||0}" data-bersih="${data.bersihTpd[i]||0}" data-kotortkgb="${data.kotorTkgb[i]||0}" data-bersihtkgb="${data.bersihTkgb[i]||0}" data-pajaktkgb="${data.pajakTkgb[i]||0}" data-status="${st||''}" data-selisih="${s}" data-bulan="${months[i]}" data-tahun="${data.selectedYear}" ${!data.transaksiIdBulanan[i]?'disabled':''}><i class="bx bx-edit text-primary"></i></button></td>`;
    
                  tbody.innerHTML+=`<tr><td class="text-center">${data.selectedYear||'-'}</td><td>${months[i]}</td><td class="text-center">${data.kodeUsulanBulanan[i]??'-'}</td><td class="text-center">${data.golonganBulanan[i]??'-'} - ${data.tahunBulanan[i]??'-'}</td><td class="text-end">${fmt(gaji)}</td>${nomCols}<td class="text-center" style="font-size:11px">${data.noSp2d[i]??'-'}</td><td class="text-center" style="font-size:11px">${tglMain}</td><td class="${sc}" style="${ss}">${pfx}${fmt(Math.abs(s))}</td><td class="text-center">${stH}</td>${actionBtn}</tr>`;
                }
    
                // Totals
                const t=data.totals||{};
                let sumCols = `<td class="text-end">${fmt(t.kotorTpd||0)}</td>${tkc(t.kotorTkgb||0)}<td class="text-end">${fmt(t.bersihTpd||0)}</td>${tkc(t.bersihTkgb||0)}`;
                tbody.innerHTML+=`<tr class="fw-bold table-light"><td colspan="4" class="text-center">Jumlah</td><td class="text-end">${fmt(t.gaji||0)}</td>${sumCols}<td colspan="2"></td><td colspan="2"></td><td class="d-none"></td></tr>`;
            }'''

content = content.replace(js_tbody_target, js_tbody_replacement)

with open('d:/Kerjaan/Project/SPTJM_GITHUB/SPTJMv2.lldikti4/resources/views/admin/monitoring-pembayaran.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Done fixing admin view JS")
