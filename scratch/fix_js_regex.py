import re

def fix_js(filepath, is_admin):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Fix thead
    thead_pattern = r"(if\s*\(currentJenis\s*===\s*'tukin'\)\s*\{\s*thead\.innerHTML=.*?\}\s*else\s*\{)(\s*thead\.innerHTML=.*?;)(\s*\})"
    
    # Action header for sptjm and semua if admin
    action_th = '<th class="text-center align-middle"><i class="bx bx-cog"></i></th>' if is_admin else ''
    
    def thead_repl(m):
        # We inject the else if branch right before the else branch
        sptjm_thead = f"""}} else if (currentJenis === 'sptjm') {{
                  const nc=hasTkgb?6:3;
                  thead.innerHTML=`<tr><th rowspan="2" class="text-center align-middle">Tahun</th><th rowspan="2" class="text-center align-middle">Bulan</th><th rowspan="2" class="text-center align-middle">Kode Usulan</th><th rowspan="2" class="text-center align-middle">Gol/MK</th><th rowspan="2" class="text-center align-middle">Gaji</th><th colspan="${{nc}}" class="text-center align-middle">Nominal</th><th rowspan="2" class="text-center align-middle">NO SP2D</th><th rowspan="2" class="text-center align-middle">TGL SP2D</th><th rowspan="2" class="text-center align-middle">Selisih</th><th rowspan="2" class="text-center align-middle">Status</th>{action_th}</tr><tr><th class="text-center align-middle">Kotor TPD</th>${{hasTkgb?'<th class="text-center align-middle tkgb-col">Kotor TKGB</th>':''}}<th class="text-center align-middle">Pajak TPD</th>${{hasTkgb?'<th class="text-center align-middle tkgb-col">Pajak TKGB</th>':''}}<th class="text-center align-middle">Bersih TPD</th>${{hasTkgb?'<th class="text-center align-middle tkgb-col">Bersih TKGB</th>':''}}</tr>`;
              """
        
        # We need to make sure the original 'else' (semua) also has action_th for admin
        original_else = m.group(2)
        if is_admin and '<i class="bx bx-cog"></i>' not in original_else:
            original_else = original_else.replace('</tr>`;', f'{action_th}</tr>`;')
            
        return m.group(1).replace('} else {', sptjm_thead + '} else {') + original_else + m.group(3)

    if "else if (currentJenis === 'sptjm')" not in content:
        content = re.sub(thead_pattern, thead_repl, content, flags=re.DOTALL)
        print("Replaced thead in", filepath)

    # Fix tbody (We will just find the `else {` block corresponding to currentJenis)
    # The `else` block for tbody usually loops over months
    tbody_pattern = r"(\}\s*else\s*\{\s*for\(let i=0;i<months\.length;i\+\+\).*?\}[\s\S]*?)(// Pembayaran Kekurangan dan Pengembalian Kelebihan)"
    
    def tbody_repl(m):
        original_else = m.group(1)
        action_btn = ""
        if is_admin:
            action_btn = """<td class="text-center"><button class="btn btn-sm btn-icon edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" data-id="${data.transaksiIdBulanan[i]||''}" data-gaji="${gaji}" data-kotor="${data.kotorTpd[i]||0}" data-bersih="${data.bersihTpd[i]||0}" data-kotortkgb="${data.kotorTkgb[i]||0}" data-bersihtkgb="${data.bersihTkgb[i]||0}" data-pajaktkgb="${data.pajakTkgb[i]||0}" data-status="${st||''}" data-selisih="${s}" data-bulan="${months[i]}" data-tahun="${data.selectedYear}" ${!data.transaksiIdBulanan[i]?'disabled':''}><i class="bx bx-edit text-primary"></i></button></td>"""
            
            # Ensure action btn is in original_else
            if '<i class="bx bx-edit' not in original_else:
                original_else = original_else.replace('</td></tr>`;', f'</td>{action_btn}</tr>`;')

        end_cols = '<td></td>' if is_admin else ''

        sptjm_tbody = f"""}} else if (currentJenis === 'sptjm') {{
                for(let i=0;i<months.length;i++){{
                  const st=stb[i];
                  let stH='-'; if(st&&statusCfg[st]) stH=`<span class="badge ${{statusCfg[st][0]}}" style="font-size:10px">${{statusCfg[st][1]}}</span>`; else if(st&&st.startsWith('kode:')) stH=`<span class="badge bg-label-secondary" style="font-size:10px">${{st.substring(5)}}</span>`;
                  const pfx=sb[i]<0?'-':(sb[i]>0?'+':''), s=sb[i]||0, sc=s<0?'text-danger fw-bold':(s>0?'text-success fw-bold':'text-success fw-bold');
                  const ss='font-size:12px; font-weight:700; font-family:"Roboto",sans-serif; letter-spacing:0.3px;';
                  let tglMain=data.tglSp2d[i]??'-';
                  if(tglMain!=='-'){{ try{{ const pd=new Date(tglMain); if(!isNaN(pd)) tglMain=('0'+pd.getDate()).slice(-2)+'/'+('0'+(pd.getMonth()+1)).slice(-2)+'/'+pd.getFullYear(); }}catch(e){{}} }}
                  
                  const gaji = data.gajiBulanan[i]??0;
                  const kotorTpdCol = `<td class="text-end">${{fmt(data.kotorTpd[i]??0)}}</td>`;
                  const kotorTkgbCol = tkc(data.kotorTkgb[i]??0);
                  const pajakTpdCol = `<td class="text-end">${{fmt(data.pajakTpd[i]??0)}}</td>`;
                  const pajakTkgbCol = tkc(data.pajakTkgb[i]??0);
                  const bersihTpdCol = `<td class="text-end">${{fmt(data.bersihTpd[i]??0)}}</td>`;
                  const bersihTkgbCol = tkc(data.bersihTkgb[i]??0);
                  
                  let nomCols = kotorTpdCol + kotorTkgbCol + pajakTpdCol + pajakTkgbCol + bersihTpdCol + bersihTkgbCol;
                  
                  tbody.innerHTML+=`<tr><td class="text-center">${{data.selectedYear||'-'}}</td><td>${{months[i]}}</td><td class="text-center">${{data.kodeUsulanBulanan[i]??'-'}}</td><td class="text-center">${{data.golonganBulanan[i]??'-'}} - ${{data.tahunBulanan[i]??'-'}}</td><td class="text-end">${{fmt(gaji)}}</td>${{nomCols}}<td class="text-center" style="font-size:11px">${{data.noSp2d[i]??'-'}}</td><td class="text-center" style="font-size:11px">${{tglMain}}</td><td class="${{sc}}" style="${{ss}}">${{pfx}}${{fmt(Math.abs(s))}}</td><td class="text-center">${{stH}}</td>{action_btn}</tr>`;
                }}
    
                // Totals
                const t=data.totals||{{}};
                let sumCols = `<td class="text-end">${{fmt(t.kotorTpd||0)}}</td>${{tkc(t.kotorTkgb||0)}}<td class="text-end">${{fmt(t.pajakTpd||0)}}</td>${{tkc(t.pajakTkgb||0)}}<td class="text-end">${{fmt(t.bersihTpd||0)}}</td>${{tkc(t.bersihTkgb||0)}}`;
                tbody.innerHTML+=`<tr class="fw-bold table-light"><td colspan="4" class="text-center">Jumlah</td><td class="text-end">${{fmt(t.gaji||0)}}</td>${{sumCols}}<td colspan="2"></td><td colspan="2"></td>{end_cols}</tr>`;
              """

        return original_else.replace('} else {', sptjm_tbody + '} else {') + m.group(2)

    if 'sptjm_tbody' not in content and 'pajakTpdCol' not in content:
        content = re.sub(tbody_pattern, tbody_repl, content, flags=re.DOTALL)
        print("Replaced tbody in", filepath)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

fix_js('d:/Kerjaan/Project/SPTJM_GITHUB/SPTJMv2.lldikti4/resources/views/admin/monitoring-pembayaran.blade.php', is_admin=True)
fix_js('d:/Kerjaan/Project/SPTJM_GITHUB/SPTJMv2.lldikti4/resources/views/dosen/monitoring-pembayaran.blade.php', is_admin=False)
