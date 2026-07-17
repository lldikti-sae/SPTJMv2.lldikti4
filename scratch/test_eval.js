const fs = require('fs');
const data = JSON.parse(fs.readFileSync('scratch/test_ajax.json', 'utf8'));

// Mocking some globals
const document = {
    getElementById: (id) => ({ value: '', textContent: '', className: '', style: { cssText: '' }, dataset: {} }),
    querySelector: (sel) => ({ value: '', innerHTML: '', appendChild: () => {} }),
    querySelectorAll: () => [],
    createElement: () => ({ innerHTML: '', className: '', style: { backgroundColor: '' } })
};
const fmt = (num) => String(num);
const tkc = (v) => '';
const statusCfg = {};
const currentJenis = 'sptjm';

let hasTkgb = false;
if (data.kotorTkgb && data.kotorTkgb.some(v => parseFloat(v||0)>0)) {
    hasTkgb = true;
}
if (data.pajakTkgb && data.pajakTkgb.some(v => parseFloat(v||0)>0)) {
    hasTkgb = true;
}
if (data.bersihTkgb && data.bersihTkgb.some(v => parseFloat(v||0)>0)) {
    hasTkgb = true;
}

try {
    const h = data.header || {};
    const sm = data.summary || {};
    const months = data.months || [], sb = data.selisihBulanan || [], stb = data.statusBulanan || [];
    
    // Extracted block
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
    }

    // Totals
    const tSptjm=data.totals||{};
    let sumCols = `<td class="text-end">${fmt(tSptjm.kotorTpd||0)}</td>${tkc(tSptjm.kotorTkgb||0)}<td class="text-end">${fmt(tSptjm.pajakTpd||0)}</td>${tkc(tSptjm.pajakTkgb||0)}<td class="text-end">${fmt(tSptjm.bersihTpd||0)}</td>${tkc(tSptjm.bersihTkgb||0)}`;

    const sumOri = data.summaryOriginal || {};
    const valKGrRow = sumOri.k_gross || 0;
    const valKPjRow = sumOri.k_pajak || 0;
    const valKNeRow = sumOri.k_net || 0;
    const valLGrRow = sumOri.l_gross || 0;
    const valLPjRow = sumOri.l_pajak || 0;
    const valLNeRow = sumOri.l_net || 0;
    
    const endCols = currentJenis === 'tukin' ? '<td colspan="8"></td>' : '<td colspan="2"></td><td colspan="2"></td>';
    const pjKurang = currentJenis === 'sptjm' ? `<td class="text-end">${fmt(valKPjRow)}</td>${tkc(0)}` : '';
    const pjLebih = currentJenis === 'sptjm' ? `<td class="text-end">${fmt(valLPjRow)}</td>${tkc(0)}` : '';

    const tTotals = data.totals || {};
    const jmKotorTpd = (tTotals.kotorTpd||0) + (tTotals.kotorTkgb||0);
    const jmPajakTpd = (tTotals.pajakTpd||0) + (tTotals.pajakTkgb||0);
    const jmBersihTpd = (tTotals.bersihTpd||0) + (tTotals.bersihTkgb||0);
    
    const taKotorTpd = jmKotorTpd + valKGrRow - valLGrRow;
    const taPajakTpd = jmPajakTpd + valKPjRow - valLPjRow;
    const taBersihTpd = jmBersihTpd + valKNeRow - valLNeRow;

    const pjTa = currentJenis === 'sptjm' ? `<td class="text-end">${fmt(taPajakTpd)}</td>${tkc(0)}` : '';

    // Tabel 2
    const riwayatData = data.riwayatPembayaran || [];
    riwayatData.forEach((item, index) => {
        let u = item.uraian_pembayaran ? item.uraian_pembayaran.charAt(0).toUpperCase() + item.uraian_pembayaran.slice(1) : '-';
        // ...
    });

    const sumOriU = data.summaryOriginal || {};
    const nettingText = '';
    
    const kGross = sumOriU.k_gross || 0;
    const kPajak = sumOriU.k_pajak || 0;
    const kNet = sumOriU.k_net || 0;
    const lGross = sumOriU.l_gross || 0;
    const lPajak = sumOriU.l_pajak || 0;
    const lNet = sumOriU.l_net || 0;

    const sumKotorTpd = (data.kotorTpd || []).reduce((a,b)=>a+parseFloat(b||0), 0);
    const sumKotorTkgb = (data.kotorTkgb || []).reduce((a,b)=>a+parseFloat(b||0), 0);
    const sumPajakTpd = (data.pajakTpd || []).reduce((a,b)=>a+parseFloat(b||0), 0);
    const sumPajakTkgb = (data.pajakTkgb || []).reduce((a,b)=>a+parseFloat(b||0), 0);
    const sumBersihTpd = (data.bersihTpd || []).reduce((a,b)=>a+parseFloat(b||0), 0);
    const sumBersihTkgb = (data.bersihTkgb || []).reduce((a,b)=>a+parseFloat(b||0), 0);

    const totalAkhirGross = sumKotorTpd + sumKotorTkgb + kGross - lGross;
    const totalAkhirPajak = sumPajakTpd + sumPajakTkgb + kPajak - lPajak;
    const totalAkhirNet = sumBersihTpd + sumBersihTkgb + kNet - lNet;

    console.log("Success! No errors.");
} catch (err) {
    console.error("ERROR CAUGHT:", err);
}
