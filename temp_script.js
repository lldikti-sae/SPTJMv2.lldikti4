
>   <script>
      document.addEventListener('DOMContentLoaded', function() {
        const filterSelect = document.querySelector('select[name="tahun_versi"]');
        if (!filterSelect) return;
        const token = document.querySelector('input[name="_token"]')?.value;
        const nidnInput = document.getElementById('search_nidn');
        const startYearInput = document.getElementById('hidden_start_year');
        const endYearInput = document.getElementById('hidden_end_year');
        const fmt = n => Number(n).toLocaleString('id-ID',{maximumFractionDigits:0});
        const fmtDec = n => Number(n).toLocaleString('id-ID',{minimumFractionDigits:2, maximumFractionDigits:2});
        const statusCfg = {usulan:['bg-label-warning','Usulan'],proses:['bg-label-info','Proses'],kurang:['bg-label-dan
ger','Kurang'],lebih:['bg-label-secondary','Lebih'],selesai:['bg-label-success','Selesai']};
  
        filterSelect.addEventListener('change', function() {
          const tahun = this.value, nidn = nidnInput?.value||'', sy = startYearInput?.value||'', ey = 
endYearInput?.value||'';
          const exy = document.getElementById('export_tahun_versi'); if(exy) exy.value=tahun;
          const csy = document.getElementById('cetak_spt_tahun_versi'); if(csy) csy.value=tahun;
  
          // Tampilkan state loading agar tidak terasa "mentok"
          filterSelect.disabled = true;
          const originalText = filterSelect.options[filterSelect.selectedIndex].text;
  
          fetch("{{ route('dosen.monitoring-pembayaran.data') }}", {
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token},
            body: JSON.stringify({nidn,start_year:sy,end_year:ey,tahun_versi:tahun})
          }).then(r=>r.json()).then(data=>{
            filterSelect.disabled = false;
            if(!data.success){
                alert("Gagal memuat data: " + data.message);
                console.error(data.message);
                return;
            }
            const h=data.header||{};
  
            // Header
            const el1=document.getElementById('hdr-nidn'); if(el1) el1.value=(h.NIDN||'')+' - '+(h.Nama||'');
            const el2=document.getElementById('hdr-jabatan'); if(el2) el2.value=(h.JabatanSelected||'')+' - 
'+(h.Aktif==1?'Aktif':'Tidak Aktif');
            const el3=document.getElementById('hdr-pt'); if(el3) el3.value=(h.Kode_PT||'')+' - '+(h.PTS||'');
  
            // PNS badge
            const badge=document.getElementById('badge-jenis');
            if(badge){
              const j=(h.Jenis||'').toUpperCase(), pns=j.indexOf('PNS')!==-1&&j.indexOf('NON')===-1;
              badge.textContent=pns?'PNS':'Non-PNS';
              badge.className='badge '+(pns?'bg-label-primary':'bg-label-success');
              badge.style.cssText='font-size:13px;font-weight:700;padding:6px 14px;';
            }
            const sm=data.summary||{};
            const ek=document.getElementById('sum-kewajiban'); if(ek) ek.textContent='Rp '+fmt(sm.totalKewajiban||0);
            const ed=document.getElementById('sum-dibayar'); if(ed) ed.textContent='Rp '+fmt(sm.totalDibayar||0);
            const es=document.getElementById('sum-selisih');
            if(es){es.textContent='Rp '+fmt(sm.totalSelisih||0); 
es.className=(sm.totalSelisih||0)==0?'text-success':'text-danger';}
            const si=document.getElementById('sum-selisih-icon');
            if(si){si.className='avatar-initial rounded 
'+((sm.totalSelisih||0)==0?'bg-label-success':'bg-label-danger')+' me-2';}
  
            const 
sumTkgb=[...(data.kotorTkgb||[]),...(data.pajakTkgb||[]),...(data.bersihTkgb||[])].reduce((a,b)=>a+Number(b),0);
            let hasTkgb=sumTkgb!=0;
            if (currentJenis === 'semua') {
                hasTkgb = true;
            }
            const tbl=document.getElementById('mp-table');
            if(tbl) tbl.dataset.hasTkgb=hasTkgb?'1':'0';
  
            const thead=tbl?.querySelector('thead');
            if(thead){
              if (currentJenis === 'tukin') {
                  thead.innerHTML=`<tr><th class="text-center">Tahun</th><th class="text-center">Bulan</th><th 
class="text-center">Kode Usulan</th><th class="text-center">Gol/MK</th><th class="text-center">Gaji</th><th 
class="text-center">Nominal TUKIN</th><th class="text-center">Nominal Kinerja Dasar</th><th 
class="text-center">Nominal Kinerja Prestasi</th><th class="text-center">Potongan Periodik</th><th 
class="text-center">Nominal Bersih TPD</th><th class="text-center">Nilai Bersih TUKIN</th><th class="text-center">No 
SP2D</th><th class="text-center">Tgl SP2D</th><th class="text-center">Selisih</th><th 
class="text-center">Status</th></tr>`;
              } else {
                  thead.innerHTML=`<tr><th class="text-center">Tahun</th><th class="text-center">Bulan</th><th 
class="text-center">Kode Usulan</th><th class="text-center">Gol/MK</th><th class="text-center">Gaji</th><th 
class="text-center">Nominal SPTJM</th>${hasTkgb?'<th class="text-center tkgb-col">Nominal TUKIN</th>':''}<th 
class="text-center">Bersih SPTJM</th>${hasTkgb?'<th class="text-center tkgb-col">Bersih TUKIN</th>':''}<th 
class="text-center">NO SP2D</th><th class="text-center">TGL SP2D</th><th class="text-center">Selisih</th><th 
class="text-center">Status</th></tr>`;
              }
            }
  
            const tbody=tbl?.querySelector('tbody'); 
            if(tbody) {
              tbody.innerHTML='';
              const months=data.months||[], sb=data.selisihBulanan||[], stb=data.statusBulanan||[];
              const tkc=(v)=>hasTkgb?`<td class="text-end tkgb-col">${fmt(v)}</td>`:'';
  
              if (currentJenis === 'tukin') {
                  let totGaji=0, totTukin=0, totDasar=0, totPrestasi=0, totPotongan=0, totBersihTpd=0, 
totNilaiBersih=0;
                  for(let i=0;i<months.length;i++){
                      const st=stb[i];
                      let stH='-'; if(st&&statusCfg[st]) stH=`<span class="badge ${statusCfg[st][0]}" 
style="font-size:10px">${statusCfg[st][1]}</span>`; else if(st&&st.startsWith('kode:')) stH=`<span class="badge 
bg-label-secondary" style="font-size:10px">${st.substring(5)}</span>`;
                      
                      const gaji = data.gajiBulanan[i] ?? 0;
                      const nominalTukin = data.kotorTpd[i] ?? 0;
                      
                      const kinerjaDasar = nominalTukin * 0.6;
                      const kinerjaPrestasi = 0; // Tidak tersedia dari backend
                      const potongan = 0; // Tidak tersedia dari backend
                      const bersihTpdVal = 0; // Tidak tersedia (bersihTpd tertimpa nilai bersih tukin dari DB)
                      
                      let nilaiBersihTukin = 0;
                      if (nominalTukin > 0) {
                          nilaiBersihTukin = (kinerjaDasar + kinerjaPrestasi - potongan) - bersihTpdVal;
                      }
                      
                      totGaji += gaji; totTukin += nominalTukin; totDasar += kinerjaDasar;
                      totPrestasi += kinerjaPrestasi; totPotongan += potongan;
                      totBersihTpd += bersihTpdVal; totNilaiBersih += nilaiBersihTukin;
                      
                      tbody.innerHTML += `<tr><td 
class="text-center">${data.selectedYear||'-'}</td><td>${months[i]}</td><td 
class="text-center">${data.kodeUsulanBulanan[i]??'-'}</td><td class="text-center">${data.golonganBulanan[i]??'-'} - 
${data.tahunBulanan[i]??'-'}</td><td class="text-end">${fmt(gaji)}</td><td 
class="text-end">${fmt(nominalTukin)}</td><td class="text-end">${fmtDec(kinerjaDasar)}</td><td 
class="text-end">${fmtDec(kinerjaPrestasi)}</td><td class="text-end">${fmtDec(potongan)}</td><td 
class="text-end">${fmt(bersihTpdVal)}</td><td class="text-end">${fmt(nilaiBersihTukin)}</td><td class="text-center" 
style="font-size:11px">-</td><td class="text-center" style="font-size:11px">-</td><td class="text-end fw-bold 
text-success">0</td><td class="text-center">${stH}</td></tr>`;
                  }
                  tbody.innerHTML += `<tr class="fw-bold table-light"><td colspan="4" 
class="text-center">Jumlah</td><td class="text-end">${fmt(totGaji)}</td><td class="text-end">${fmt(totTukin)}</td><td 
class="text-end">${fmtDec(totDasar)}</td><td class="text-end">${fmtDec(totPrestasi)}</td><td 
class="text-end">${fmtDec(totPotongan)}</td><td class="text-end">${fmt(totBersihTpd)}</td><td 
class="text-end">${fmt(totNilaiBersih)}</td><td colspan="2"></td><td colspan="2"></td></tr>`;
              } else {
                  for(let i=0;i<months.length;i++){
                    const s=sb[i]||0, st=stb[i], sc=s<0?'text-end text-danger fw-bold':(s>0?'text-end text-success 
fw-bold':'text-end text-success fw-bold'), ss='';
                    const pfx=s<0?'-':(s>0?'+':'');
                    let stH='-'; if(st&&statusCfg[st]) stH=`<span class="badge ${statusCfg[st][0]}" 
style="font-size:10px">${statusCfg[st][1]}</span>`; else if(st&&st.startsWith('kode:')) stH=`<span class="badge 
bg-label-secondary" style="font-size:10px">${st.substring(5)}</span>`;
                    
                    let tglMain = data.tglSp2d[i] ?? '-';
                    if (tglMain !== '' && tglMain !== '-') {
                        const dMain = new Date(tglMain);
                        if(!isNaN(dMain)) {
                            const d = String(dMain.getDate()).padStart(2, '0');
                            const m = String(dMain.getMonth() + 1).padStart(2, '0');
                            const y = dMain.getFullYear();
                            tglMain = `${d}/${m}/${y}`;
                        }
                    }
  
                    const gaji = data.gajiBulanan[i]??0;
                    const kotorTpdCol = `<td class="text-end">${fmt(data.kotorTpd[i]??0)}</td>`;
                    const kotorTkgbCol = tkc(data.kotorTkgb[i]??0);
                    const pajakTpdCol = `<td class="text-end">${fmt(data.pajakTpd[i]??0)}</td>`;
                    const pajakTkgbCol = tkc(data.pajakTkgb[i]??0);
                    const bersihTpdCol = `<td class="text-end">${fmt(data.bersihTpd[i]??0)}</td>`;
                    const bersihTkgbCol = tkc(data.bersihTkgb[i]??0);
                    
                    let nomCols = kotorTpdCol + kotorTkgbCol + pajakTpdCol + pajakTkgbCol + bersihTpdCol + 
bersihTkgbCol;
      
                    tbody.innerHTML+=`<tr><td 
class="text-center">${data.selectedYear||'-'}</td><td>${months[i]}</td><td 
class="text-center">${data.kodeUsulanBulanan[i]??'-'}</td><td class="text-center">${data.golonganBulanan[i]??'-'} - 
${data.tahunBulanan[i]??'-'}</td><td class="text-end">${fmt(gaji)}</td>${nomCols}<td class="text-center" 
style="font-size:11px">${data.noSp2d[i]??'-'}</td><td class="text-center" style="font-size:11px">${tglMain}</td><td 
class="${sc}" style="${ss}">${pfx}${fmt(Math.abs(s))}</td><td class="text-center">${stH}</td></tr>`;
                  }
      
                  // Totals
                  const t=data.totals||{};
                  let sumCols = `<td class="text-end">${fmt(t.kotorTpd||0)}</td>${tkc(t.kotorTkgb||0)}<td 
class="text-end">${fmt(t.pajakTpd||0)}</td>${tkc(t.pajakTkgb||0)}<td 
class="text-end">${fmt(t.bersihTpd||0)}</td>${tkc(t.bersihTkgb||0)}`;
                  tbody.innerHTML+=`<tr class="fw-bold table-light"><td colspan="4" class="text-center">Jumlah</td><td 
class="text-end">${fmt(t.gaji||0)}</td>${sumCols}<td colspan="2"></td><td colspan="2"></td></tr>`;
      
                  // Pembayaran Kekurangan dan Pengembalian Kelebihan (Nilai Asli)
                  const sumOri = data.summaryOriginal || {};
                  const valKGrRow = sumOri.k_gross || 0;
                  const valKPjRow = sumOri.k_pajak || 0;
                  const valKNeRow = sumOri.k_net || 0;
                  const valLGrRow = sumOri.l_gross || 0;
                  const valLPjRow = sumOri.l_pajak || 0;
                  const valLNeRow = sumOri.l_net || 0;
                  
                  tbody.innerHTML+=`<tr class="fw-bold" style="background-color:#ffdcdc"><td colspan="4" 
class="text-center">Pembayaran Kekurangan</td><td></td><td class="text-end">${fmt(valKGrRow)}</td>${tkc(0)}<td 
class="text-end">${fmt(valKPjRow)}</td>${tkc(0)}<td class="text-end">${fmt(valKNeRow)}</td>${tkc(0)}<td 
colspan="2"></td><td colspan="2"></td></tr>`;
                  tbody.innerHTML+=`<tr class="fw-bold" style="background-color:#dbeafe"><td colspan="4" 
class="text-center">Pengembalian Kelebihan</td><td></td><td class="text-end">${fmt(valLGrRow)}</td>${tkc(0)}<td 
class="text-end">${fmt(valLPjRow)}</td>${tkc(0)}<td class="text-end">${fmt(valLNeRow)}</td>${tkc(0)}<td 
colspan="2"></td><td colspan="2"></td></tr>`;
      
                  // Total Akhir (Sisa)
                  const sumRekap = data.summaryRekap || {};
                  const valKGr = sumRekap.k_gross || 0;
                  const valKPj = sumRekap.k_pajak || 0;
                  const valKNe = sumRekap.k_net || 0;
                  const valLGr = sumRekap.l_gross || 0;
                  const valLPj = sumRekap.l_pajak || 0;
                  const valLNe = sumRekap.l_net || 0;
      
                  const jmKotorTpd = (t.kotorTpd||0) + (t.kotorTkgb||0);
                  const jmPajakTpd = (t.pajakTpd||0) + (t.pajakTkgb||0);
                  const jmBersihTpd = (t.bersihTpd||0) + (t.bersihTkgb||0);
                  
                  const taKotorTpd = jmKotorTpd + valKGrRow - valLGrRow;
                  const taPajakTpd = jmPajakTpd + valKPjRow - valLPjRow;
                  const taBersihTpd = jmBersihTpd + valKNeRow - valLNeRow;
                  tbody.innerHTML+=`<tr class="fw-bold" style="background-color:#d1fae5"><td colspan="4" 
class="text-center">Total Akhir</td><td class="text-end">${fmt(t.gaji||0)}</td><td 
class="text-end">${fmt(taKotorTpd)}</td>${tkc(0)}<td class="text-end">${fmt(taPajakTpd)}</td>${tkc(0)}<td 
class="text-end">${fmt(taBersihTpd)}</td>${tkc(0)}<td colspan="2"></td><td colspan="2"></td></tr>`;
              }
            }
  
            // UPDATE TABEL KEDUA (URAIAN PEMBAYARAN)
            const tbodyRiwayat = document.querySelector('#tabel-riwayat tbody');
            if (tbodyRiwayat) {
              tbodyRiwayat.innerHTML = '';
              const riwayatData = data.riwayatPembayaran || [];
              
              let totalUraianBersih = 0;
              let totalUraianNominal = 0;
              let totalUraianPajak = 0;
              
              if (riwayatData.length === 0) {
                tbodyRiwayat.innerHTML = '<tr><td colspan="8" class="text-center">Tidak ada data riwayat 
pembayaran</td></tr>';
              } else {
                riwayatData.forEach((item, index) => {
                  totalUraianBersih += parseFloat(item.bersih || 0);
                  totalUraianNominal += parseFloat(item.nominal || 0);
                  totalUraianPajak += parseFloat(item.pajak || 0);
                  
                  const tr = document.createElement('tr');
                  
                  let tglFormat = '-';
                  if(item.tanggal) {
                     const d = new Date(item.tanggal);
                     tglFormat = d.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'2-digit' 
}).replace(/ /g, '-');
                  }
  
                  tr.innerHTML = `
                    <td class="text-center">${index + 1}</td>
                    <td>${(() => {
                        let u = item.uraian_pembayaran ? item.uraian_pembayaran.charAt(0).toUpperCase() + 
item.uraian_pembayaran.slice(1) : '-';
                        const mn = 
['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];


