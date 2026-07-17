import sys

def fix_tbody(filepath, is_admin):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Find the else block for tbody
    target_start = '} else {\n                  for(let i=0;i<months.length;i++){'
    if target_start not in content:
        target_start = '} else {\n                for(let i=0;i<months.length;i++){'
    
    if target_start not in content:
        print(f"Failed to find target in {filepath}")
        return

    action_btn = ""
    if is_admin:
        action_btn = """<td class="text-center"><button class="btn btn-sm btn-icon edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" data-id="${data.transaksiIdBulanan[i]||''}" data-gaji="${gaji}" data-kotor="${data.kotorTpd[i]||0}" data-bersih="${data.bersihTpd[i]||0}" data-kotortkgb="${data.kotorTkgb[i]||0}" data-bersihtkgb="${data.bersihTkgb[i]||0}" data-pajaktkgb="${data.pajakTkgb[i]||0}" data-status="${st||''}" data-selisih="${s}" data-bulan="${months[i]}" data-tahun="${data.selectedYear}" ${!data.transaksiIdBulanan[i]?'disabled':''}><i class="bx bx-edit text-primary"></i></button></td>"""
    
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

    # Check if the sptjm logic is already injected right before target_start
    if "pajakTpdCol =" not in content[content.find(target_start)-1500:content.find(target_start)]:
        content = content.replace(target_start, sptjm_tbody + target_start)

    # Make sure we add action_btn to the original `else` branch (Semua filter) if admin!
    if is_admin:
        # For admin, the `else` branch data row ends with `</td><td class="text-center">${stH}</td></tr>`;`
        # We need to insert `{action_btn}` before `</tr>`;`
        import re
        
        # Using a very specific regex to find the data row generation in the else block
        # We find `stH}</td></tr>`;` and replace it
        if action_btn not in content:
            # We already put action_btn in sptjm, but not in `else`!
            pass
        
        # Let's just find the exact string for the `else` data row end
        # In the original file: `${pfx}${fmt(Math.abs(s))}</td><td class="text-center">${stH}</td></tr>`;`
        target_end_row = '</td><td class="text-center">${stH}</td></tr>`;'
        if target_end_row in content:
            # Wait, there are multiple matches! (sptjm now has it too, but we injected action_btn before it)
            # Actually we already injected `{action_btn}</tr>`;` in sptjm!
            # So the one without action_btn is the one in `else`!
            content = content.replace(target_end_row, f'</td><td class="text-center">${{stH}}</td>{action_btn}</tr>`;')
            
        # Also need to fix the `else` (Semua) Jumlah row to have end_cols
        # `<td colspan="2"></td><td colspan="2"></td></tr>`;`
        # Wait, the `else` Jumlah row in the original file: `<td colspan="2"></td><td colspan="2"></td></tr>`;`
        target_jumlah = '</td><td colspan="2"></td><td colspan="2"></td></tr>`;'
        if target_jumlah in content:
            content = content.replace(target_jumlah, f'</td><td colspan="2"></td><td colspan="2"></td>{end_cols}</tr>`;')
            
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print(f"Fixed tbody in {filepath}")

fix_tbody('d:/Kerjaan/Project/SPTJM_GITHUB/SPTJMv2.lldikti4/resources/views/admin/monitoring-pembayaran.blade.php', is_admin=True)
fix_tbody('d:/Kerjaan/Project/SPTJM_GITHUB/SPTJMv2.lldikti4/resources/views/dosen/monitoring-pembayaran.blade.php', is_admin=False)
