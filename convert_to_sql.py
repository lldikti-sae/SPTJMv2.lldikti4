import csv

input_file = r'D:\Kerjaan\Project\SPTJM_GITHUB\SPTJMv2.lldikti4\public\dokumen\Laporan_Validasi_Database_Clean.csv'
output_file = r'D:\Kerjaan\Project\SPTJM_GITHUB\SPTJMv2.lldikti4\public\dokumen\import_histori.sql'

with open(input_file, mode='r', encoding='utf-8') as infile, open(output_file, mode='w', encoding='utf-8') as outfile:
    reader = csv.reader(infile, delimiter=';')
    headers = next(reader)
    
    for row in reader:
        if not row: continue
        values = []
        for val in row:
            if val == r'\N' or val == '':
                values.append('NULL')
            else:
                val_escaped = val.replace("'", "''")
                values.append(f"'{val_escaped}'")
                
        values_str = ', '.join(values)
        sql = f'INSERT INTO j_histori_dosen VALUES ({values_str});\n'
        outfile.write(sql)
print('Done!')
