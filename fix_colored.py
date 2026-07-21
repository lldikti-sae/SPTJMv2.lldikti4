import os
import re

files = [
    'resources/views/admin/monitoring-pembayaran.blade.php',
    'resources/views/pic/monitoring-pembayaran.blade.php',
    'resources/views/dosen/monitoring-pembayaran.blade.php',
    'resources/views/pts/monitoring-pembayaran.blade.php'
]

colored_rows_js = r'''
            let rowKurang = '';
            let rowLebih = '';
            let rowAkhir = '';
            
            if (currentJenis === 'tukin') {
                rowKurang = \<td colspan=\"4\" class=\"text-center\">Pembayaran Kekurangan</td><td></td><td class=\"text-end\">\</td><td class=\"text-end\">\</td><td colspan=\"8\"></td>\;
                rowLebih = \<td colspan=\"4\" class=\"text-center\">Pengembalian Kelebihan</td><td></td><td class=\"text-end\">\</td><td class=\"text-end\">\</td><td colspan=\"8\"></td>\;
                rowAkhir = \<td colspan=\"4\" class=\"text-center\">Total Akhir</td><td class=\"text-end\">\</td><td class=\"text-end\">\</td><td class=\"text-end\">\</td><td colspan=\"8\"></td>\;
            } else if (currentJenis === 'sptjm') {
                rowKurang = \<td colspan=\"4\" class=\"text-center\">Pembayaran Kekurangan</td><td></td><td class=\"text-end\">\</td><td class=\"text-end\">\</td>\\<td class=\"text-end\">\</td>\<td colspan=\"2\"></td><td colspan=\"2\"></td>\;
                rowLebih = \<td colspan=\"4\" class=\"text-center\">Pengembalian Kelebihan</td><td></td><td class=\"text-end\">\</td><td class=\"text-end\">\</td>\\<td class=\"text-end\">\</td>\<td colspan=\"2\"></td><td colspan=\"2\"></td>\;
                rowAkhir = \<td colspan=\"4\" class=\"text-center\">Total Akhir</td><td class=\"text-end\">\</td><td class=\"text-end\">\</td><td class=\"text-end\">\</td>\\<td class=\"text-end\">\</td>\<td colspan=\"2\"></td><td colspan=\"2\"></td>\;
            } else {
                rowKurang = \<td colspan=\"3\" class=\"text-center\">Pembayaran Kekurangan</td><td></td><td class=\"text-end\">\</td>\<td class=\"text-end\">\</td>\<td colspan=\"2\"></td><td colspan=\"2\"></td>\;
                rowLebih = \<td colspan=\"3\" class=\"text-center\">Pengembalian Kelebihan</td><td></td><td class=\"text-end\">\</td>\<td class=\"text-end\">\</td>\<td colspan=\"2\"></td><td colspan=\"2\"></td>\;
                rowAkhir = \<td colspan=\"3\" class=\"text-center\">Total Akhir</td><td class=\"text-end\">\</td><td class=\"text-end\">\</td>\<td class=\"text-end\">\</td>\<td colspan=\"2\"></td><td colspan=\"2\"></td>\;
            }
            
            tbody.innerHTML+=\<tr class=\"fw-bold\" style=\"background-color:#ffdcdc\">\</tr>\;
            tbody.innerHTML+=\<tr class=\"fw-bold\" style=\"background-color:#dbeafe\">\</tr>\;
            tbody.innerHTML+=\<tr class=\"fw-bold\" style=\"background-color:#d1fae5\">\</tr>\;
          }
'''

for file in files:
    if os.path.exists(file):
        with open(file, 'r', encoding='utf-8') as f:
            content = f.read()
        
        pattern = re.compile(r'let sumColspanVal = currentJenis === \'semua\' \? 3 : 4;.*?tbody\.innerHTML\+=<tr class=\"fw-bold\" style=\"background-color:#d1fae5\".*?</tr>;\s*}\s*', re.DOTALL)
        
        # also match pic/pts pattern which is slightly different if they don't have sumColspanVal
        pattern_pic = re.compile(r'let pjKurang = currentJenis === \'sptjm\'.*?tbody\.innerHTML\+=<tr class=\"fw-bold\" style=\"background-color:#d1fae5\".*?</tr>;\s*}\s*', re.DOTALL)
        
        if pattern.search(content):
            content = pattern.sub(colored_rows_js + '\n', content)
            print(f\"Replaced colored rows (pattern 1) in {file}\")
        elif pattern_pic.search(content):
            content = pattern_pic.sub(colored_rows_js + '\n', content)
            print(f\"Replaced colored rows (pattern 2) in {file}\")
        else:
            print(f\"Could not find colored rows block in {file}\")
            
        with open(file, 'w', encoding='utf-8') as f:
            f.write(content)
