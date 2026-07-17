import sys

def add_pajak(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # The string to replace in the Jumlah row
    target = '''          <td class="text-end">{{ number_format($totalKotorTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalKotorTkgb,0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format($totalBersihTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalBersihTkgb,0,',','.') }}</td>@endif'''

    replacement = '''          <td class="text-end">{{ number_format($totalKotorTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalKotorTkgb,0,',','.') }}</td>@endif
          @if(($jenisTunjangan ?? 'semua') == 'sptjm')<td class="text-end">{{ number_format($totalPajakTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalPajakTkgb,0,',','.') }}</td>@endif @endif
          <td class="text-end">{{ number_format($totalBersihTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalBersihTkgb,0,',','.') }}</td>@endif'''

    content = content.replace(target, replacement)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print(f"Added Pajak to Jumlah row in {filepath}")

add_pajak('d:/Kerjaan/Project/SPTJM_GITHUB/SPTJMv2.lldikti4/resources/views/admin/monitoring-pembayaran.blade.php')
add_pajak('d:/Kerjaan/Project/SPTJM_GITHUB/SPTJMv2.lldikti4/resources/views/dosen/monitoring-pembayaran.blade.php')
