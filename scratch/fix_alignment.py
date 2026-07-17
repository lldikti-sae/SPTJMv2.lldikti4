import sys
import re

def fix_view(filepath, is_admin=False):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Fix colspan="4" + <td></td> to colspan="5" for Pembayaran Kekurangan
    content = content.replace('<td colspan="4" class="text-center">Pembayaran Kekurangan</td>\n          <td></td>',
                              '<td colspan="5" class="text-center">Pembayaran Kekurangan</td>')
    content = content.replace('<td colspan="4" class="text-center">Pembayaran Kekurangan</td><td></td>',
                              '<td colspan="5" class="text-center">Pembayaran Kekurangan</td>')

    # 2. Fix colspan="4" + <td></td> to colspan="5" for Pengembalian Kelebihan
    content = content.replace('<td colspan="4" class="text-center">Pengembalian Kelebihan</td>\n          <td></td>',
                              '<td colspan="5" class="text-center">Pengembalian Kelebihan</td>')
    content = content.replace('<td colspan="4" class="text-center">Pengembalian Kelebihan</td><td></td>',
                              '<td colspan="5" class="text-center">Pengembalian Kelebihan</td>')

    # 3. For Admin only, fix the right edge alignment of summary rows (Action column)
    if is_admin:
        # Static Blade table endcols
        content = content.replace("@if(($jenisTunjangan ?? 'semua') == 'tukin')<td colspan=\"7\"></td>@else<td colspan=\"2\"></td><td colspan=\"2\"></td>@endif",
                                  "@if(($jenisTunjangan ?? 'semua') == 'tukin')<td colspan=\"7\"></td><td></td>@else<td colspan=\"2\"></td><td colspan=\"2\"></td><td></td>@endif")
        
        # JS loadData() endCols for summary rows
        content = content.replace("const endCols = currentJenis === 'tukin' ? `<td colspan=\"7\"></td>` : `<td colspan=\"2\"></td><td colspan=\"2\"></td>`;",
                                  "const endCols = currentJenis === 'tukin' ? `<td colspan=\"7\"></td><td></td>` : `<td colspan=\"2\"></td><td colspan=\"2\"></td><td></td>`;")
        
        # Jumlah row in JS loadData() for sptjm
        # It currently has `<td class="d-none"></td>`, change to `<td></td>`
        content = content.replace("<td colspan=\"2\"></td><td colspan=\"2\"></td><td class=\"d-none\"></td></tr>",
                                  "<td colspan=\"2\"></td><td colspan=\"2\"></td><td></td></tr>")
        
        # Jumlah row in static Blade
        content = content.replace("<td colspan=\"2\"></td><td colspan=\"2\"></td><td class=\"d-none\"></td>\n        </tr>",
                                  "<td colspan=\"2\"></td><td colspan=\"2\"></td><td></td>\n        </tr>")

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print(f"Fixed {filepath}")

fix_view('d:/Kerjaan/Project/SPTJM_GITHUB/SPTJMv2.lldikti4/resources/views/admin/monitoring-pembayaran.blade.php', is_admin=True)
fix_view('d:/Kerjaan/Project/SPTJM_GITHUB/SPTJMv2.lldikti4/resources/views/dosen/monitoring-pembayaran.blade.php', is_admin=False)
