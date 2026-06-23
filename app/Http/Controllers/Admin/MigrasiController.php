<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ErrorAlias;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class MigrasiController extends Controller
{
    public function index()
    {
        $definitions = $this->getDatasetDefinitions();
        $datasets = [];
        foreach ($definitions as $key => $config) {
            $datasets[$key] = [
                'label' => $config['label'],
                'table' => $config['table'],
                'expectedCount' => count($config['columns']),
            ];
        }

        return view('admin.migrasi', compact('datasets'));
    }

    /**
     * Normalize CSV cell value: convert empty strings and literal 'NULL' to PHP null.
     */
    private function normalizeCell($value)
    {
      if ($value === null) return null;
      if (is_string($value)) {
          // strip BOM if somehow present and trim whitespace/quotes
          $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
          $trimmed = trim($value, " \t\n\r\0\x0B\"'");
          if ($trimmed === '') return null;
          if (strcasecmp($trimmed, 'NULL') === 0) return null;
          // Fix invalid UTF-8 characters (like Windows-1252 smart quotes from Excel)
          $trimmed = mb_convert_encoding($trimmed, 'UTF-8', 'UTF-8, Windows-1252, ISO-8859-1');
          return $trimmed;
      }
      return $value;
    }

    /**
     * Normalize koreksi cell with 3-state intent:
     * - blank / missing => do not update column
     * - literal "NULL" (case-insensitive) => update column to SQL NULL
     * - otherwise => update column to trimmed value
     *
     * Returns: [bool $shouldUpdate, mixed $normalizedValue]
     */
    private function normalizeKoreksiCell($value): array
    {
        if ($value === null) {
            return [false, null];
        }

        if (is_string($value)) {
            $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
            $trimmed = trim($value, " \t\n\r\0\x0B\"'");
            if ($trimmed === '') {
                return [false, null];
            }
            if (strcasecmp($trimmed, 'NULL') === 0) {
                return [true, null];
            }
            return [true, $trimmed];
        }

        // Non-string (numbers, etc.) => update as-is
        return [true, $value];
    }

    /**
     * Normalize masa kerja (Tahun1..Tahun12) to an integer year count.
     * Returns null when value is not a valid numeric year count.
     */
    private function normalizeMasaKerjaYear($value): ?int
    {
        if ($value === null) {
            return null;
        }

        // Accept numeric types directly
        if (is_int($value)) {
            $v = $value;
        } elseif (is_float($value)) {
            $v = (int) round($value);
        } else {
            $s = is_string($value) ? trim($value) : trim((string) $value);
            if ($s === '') {
                return null;
            }

            // handle common decimal comma
            if (preg_match('/^\d{1,3},0+$/', $s)) {
                $s = str_replace(',', '.', $s);
            }

            // allow 19, 19.0, 19.00
            if (preg_match('/^\d{1,3}(?:\.0+)?$/', $s) !== 1) {
                return null;
            }
            $v = (int) floor((float) $s);
        }

        // guardrails: masa kerja typically 0..99
        if ($v < 0 || $v > 99) {
            return null;
        }
        return $v;
    }

    /**
     * Load MySQL column metadata for a table (data type, lengths/precision) via information_schema.
     * Returns map keyed by lowercased column name.
     */
    private function loadTableColumnMeta(string $table): array
    {
        try {
            $dbName = DB::connection()->getDatabaseName();
            $rows = DB::select(
                'SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE '
                . 'FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
                [$dbName, $table]
            );

            $meta = [];
            foreach ($rows as $r) {
                $name = strtolower((string) ($r->COLUMN_NAME ?? ''));
                if ($name === '') {
                    continue;
                }
                $meta[$name] = [
                    'data_type' => strtolower((string) ($r->DATA_TYPE ?? '')),
                    'char_len' => $r->CHARACTER_MAXIMUM_LENGTH !== null ? (int) $r->CHARACTER_MAXIMUM_LENGTH : null,
                    'num_precision' => $r->NUMERIC_PRECISION !== null ? (int) $r->NUMERIC_PRECISION : null,
                    'num_scale' => $r->NUMERIC_SCALE !== null ? (int) $r->NUMERIC_SCALE : null,
                ];
            }
            return $meta;
        } catch (\Throwable $e) {
            // If information_schema is not accessible, fall back to minimal coercion.
            return [];
        }
    }

    /**
     * Coerce a koreksi value to match the destination column type/length.
     * Returns [bool $ok, mixed $value, bool $warned]
     */
    private function coerceValueForColumn(string $columnName, $value, array $columnMeta): array
    {
        // null is always acceptable
        if ($value === null) {
            return [true, null, false];
        }

        // Special rule: Tahun1..Tahun12 is masa kerja (numeric but stored as string(2)).
        if (preg_match('/^Tahun(1[0-2]|[1-9])$/', $columnName) === 1) {
            $mk = $this->normalizeMasaKerjaYear($value);
            if ($mk === null) {
                return [false, null, true];
            }
            return [true, $mk, false];
        }

        $meta = $columnMeta[strtolower($columnName)] ?? null;
        if (!$meta) {
            // Unknown meta: keep as trimmed string for scalars, otherwise as-is
            if (is_string($value)) {
                return [true, trim($value), false];
            }
            if (is_scalar($value)) {
                return [true, $value, false];
            }
            return [false, null, true];
        }

        $type = $meta['data_type'] ?? '';
        $charLen = $meta['char_len'] ?? null;

        // numeric coercion helpers
        $parseNumber = function ($v) {
            if (is_int($v) || is_float($v)) {
                return (string) $v;
            }
            $s = is_string($v) ? trim($v) : trim((string) $v);
            if ($s === '') return null;
            // remove currency/text
            $s = preg_replace('/[^0-9,\.\-]/', '', $s);
            if ($s === '' || $s === '-' || $s === '.' || $s === ',') return null;
            // Indonesian format: 1.234,56
            if (strpos($s, ',') !== false && strpos($s, '.') !== false) {
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            } elseif (strpos($s, ',') !== false && strpos($s, '.') === false) {
                $s = str_replace(',', '.', $s);
            }
            if (!is_numeric($s)) return null;
            return $s;
        };

        if (in_array($type, ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'], true)) {
            $n = $parseNumber($value);
            if ($n === null) return [false, null, true];
            return [true, (int) round((float) $n), false];
        }

        if (in_array($type, ['decimal', 'numeric', 'float', 'double', 'real'], true)) {
            $n = $parseNumber($value);
            if ($n === null) return [false, null, true];
            return [true, $n, false];
        }

        // default: treat as string and enforce max length if available
        $s = is_string($value) ? $value : (is_scalar($value) ? (string) $value : null);
        if ($s === null) {
            return [false, null, true];
        }
        $s = trim($s);
        if ($charLen !== null && $charLen > 0) {
            if (mb_strlen($s, 'UTF-8') > $charLen) {
                $s = mb_substr($s, 0, $charLen, 'UTF-8');
                return [true, $s, true];
            }
        }
        return [true, $s, false];
    }

    /**
     * Try to normalize a variety of human-friendly date strings into
     * a MySQL DATETIME string (Y-m-d H:i:s). Returns null when unable to parse.
     */
    private function normalizeDateValue($value)
    {
        // Excel numeric date serial (common for XLSX when readDataOnly=true)
        if (is_numeric($value)) {
            try {
                $dt = ExcelDate::excelToDateTimeObject((float)$value);
                return $dt->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                // ignore and try other parsing
            }
        }

        $v = $this->normalizeCell($value);
        if ($v === null) return null;

        // Replace common Indonesian month names to English to help parsing
        $monthMap = [
            'Januari' => 'January', 'Februari' => 'February', 'Maret' => 'March', 'April' => 'April',
            'Mei' => 'May', 'Juni' => 'June', 'Juli' => 'July', 'Agustus' => 'August',
            'September' => 'September', 'Oktober' => 'October', 'November' => 'November', 'Desember' => 'December',
            // common abbreviations
            'Jan' => 'Jan', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Apr', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Ags' => 'Aug', 'Agt' => 'Aug', 'Agu' => 'Aug', 'Des' => 'Dec'
        ];

        $search = array_keys($monthMap);
        $replace = array_values($monthMap);
        $v2 = str_ireplace($search, $replace, $v);
        $v2 = preg_replace('/\s+/', ' ', trim($v2));

        $formats = [
            'd/m/Y H:i', 'd/m/Y H:i:s', 'd/m/Y',
            'd-m-Y H:i', 'd-m-Y H:i:s', 'd-m-Y', 'd-m-y',
            'd-M-y', 'd-M-Y', 'd M Y', 'd F Y',
            'd M Y H:i', 'd M Y H:i:s',
            'Y-m-d H:i:s', 'Y-m-d'
        ];

        foreach ($formats as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $v2);
                if ($dt !== false) {
                    // ensure time component
                    if (strpos($fmt, 'H') === false) {
                        $dt->setTime(0, 0, 0);
                    }
                    return $dt->format('Y-m-d H:i:s');
                }
            } catch (\Exception $e) {
                // ignore and try next
            }
        }

        // Last resort: try PHP's strtotime which is fairly flexible
        $ts = strtotime($v2);
        if ($ts !== false && $ts !== -1) {
            return date('Y-m-d H:i:s', $ts);
        }

        return null;
    }

    public function import(Request $request)
    {
        // Hilangkan batas waktu eksekusi agar proses import besar tidak terhenti
        @set_time_limit(0);

        // Hindari memory bloat untuk proses besar
        DB::disableQueryLog();

        $validator = Validator::make($request->all(), [
            'dataset' => 'required|in:s_transaksi_2,q_sptjm',
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:1048576',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $dataset = $request->input('dataset');
        $config = $this->getDatasetConfig($dataset);
        if (!$config) {
            return back()->with('error', 'Dataset tidak dikenal.');
        }

        $file = $request->file('file');
        $path = $file->getRealPath();

        $ext = strtolower((string) $file->getClientOriginalExtension());
        if (in_array($ext, ['xlsx', 'xls'], true)) {
            $config = $this->getDatasetConfig($dataset);
            if (!$config) {
                return back()->with('error', 'Dataset tidak dikenal.');
            }

            try {
                $reader = ($ext === 'xls') ? new Xls() : new Xlsx();
            } catch (\Throwable $e) {
                $alias = ErrorAlias::fromThrowable($e, 'ADM-MIGRASI');
                Log::error('MigrasiController: failed to initialize Excel reader', [
                    'alias' => $alias['code'],
                    'dataset' => $dataset,
                    'ext' => $ext,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return back()->with('error', 'Tidak bisa membaca file Excel. (Kode: ' . $alias['code'] . ')')->withInput();
            }

            // Read in chunks for better memory usage
            $reader->setReadDataOnly(true);
            if (method_exists($reader, 'setPreCalculateFormulas')) {
                $reader->setPreCalculateFormulas(false);
            }

            $sheetInfos = [];
            try {
                $sheetInfos = $reader->listWorksheetInfo($path);
            } catch (\Throwable $e) {
                $alias = ErrorAlias::fromThrowable($e, 'ADM-MIGRASI');
                Log::error('MigrasiController: failed to read worksheet info', [
                    'alias' => $alias['code'],
                    'dataset' => $dataset,
                    'ext' => $ext,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return back()->with('error', 'Tidak bisa membaca informasi worksheet Excel. (Kode: ' . $alias['code'] . ')')->withInput();
            }

            if (empty($sheetInfos)) {
                return back()->with('error', 'File Excel tidak memiliki worksheet yang bisa dibaca.')->withInput();
            }

            $sheetInfo = $sheetInfos[0];
            $worksheetName = $sheetInfo['worksheetName'] ?? null;
            $lastColumn = $sheetInfo['lastColumnLetter'] ?? 'A';
            $totalRows = (int)($sheetInfo['totalRows'] ?? 0);

            if ($worksheetName) {
                $reader->setLoadSheetsOnly([$worksheetName]);
            }

            // Helper read filter (mutable)
            $chunkFilter = new class implements IReadFilter {
                private int $startRow = 1;
                private int $endRow = 1;

                public function setRows(int $startRow, int $chunkSize): void
                {
                    $this->startRow = $startRow;
                    $this->endRow = $startRow + $chunkSize - 1;
                }

                public function readCell($column, $row, $worksheetName = ''): bool
                {
                    return $row >= $this->startRow && $row <= $this->endRow;
                }
            };

            $reader->setReadFilter($chunkFilter);

            // Read header (row 1)
            $chunkFilter->setRows(1, 1);
            try {
                $spreadsheet = $reader->load($path);
                $sheet = $worksheetName ? ($spreadsheet->getSheetByName($worksheetName) ?? $spreadsheet->getActiveSheet()) : $spreadsheet->getActiveSheet();
                $headerRow = $sheet->rangeToArray("A1:{$lastColumn}1", null, true, true, false)[0] ?? [];
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
            } catch (\Throwable $e) {
                $alias = ErrorAlias::fromThrowable($e, 'ADM-MIGRASI');
                Log::error('MigrasiController: failed to read Excel header', [
                    'alias' => $alias['code'],
                    'dataset' => $dataset,
                    'ext' => $ext,
                    'worksheet' => $worksheetName,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return back()->with('error', 'Header Excel tidak terbaca. (Kode: ' . $alias['code'] . ')')->withInput();
            }

            $header = array_map(function ($h) {
                $h = preg_replace('/^\xEF\xBB\xBF/', '', (string)$h);
                return trim($h, " \t\n\r\0\x0B\"'");
            }, $headerRow);

            // Remove trailing empty header cells
            while (!empty($header) && ($header[count($header) - 1] === '' || $header[count($header) - 1] === null)) {
                array_pop($header);
            }

            // If source contains a header named 'NO' (case-insensitive), ignore that column.
            $origHeader = $header;
            $skipIndexes = [];
            foreach ($origHeader as $i => $h) {
                if (strtolower((string)$h) === 'no') {
                    $skipIndexes[] = $i;
                }
            }
            if (!empty($skipIndexes)) {
                $filtered = [];
                $colIndices = [];
                foreach ($origHeader as $i => $h) {
                    if (!in_array($i, $skipIndexes, true)) {
                        $filtered[] = $h;
                        $colIndices[] = $i;
                    }
                }
                $header = array_values($filtered);
            } else {
                // no skip => all indices are sequential
                $colIndices = array_keys($origHeader);
            }

            $columns = $config['columns'];

            $missing = array_values(array_diff($columns, $header));
            $extra = array_values(array_diff($header, $columns));
            $expectedCount = count($columns);
            $foundCount = count($header);

            if (!empty($missing) || !empty($extra) || $expectedCount !== $foundCount) {
                return back()->with([
                    'headerMismatch' => true,
                    'missingColumns' => $missing,
                    'extraColumns' => $extra,
                    'expectedCount' => $expectedCount,
                    'foundCount' => $foundCount,
                    'datasetLabel' => $config['label'],
                ])->withInput();
            }

            if ($totalRows < 2) {
                return back()->with('error', 'Tidak ada data setelah header.')->withInput();
            }

            $inserted = 0;
            try {
                // Excel chunk/batch: gunakan angka lebih besar agar tidak terlalu banyak reload file
                $chunkSize = (int)($config['excel_chunk'] ?? 1000);
                if ($chunkSize < 1) {
                    $chunkSize = 1000;
                }

                $batchInsertSize = (int)($config['excel_batch'] ?? 500);
                if ($batchInsertSize < 1) {
                    $batchInsertSize = 500;
                }

                for ($startRow = 2; $startRow <= $totalRows; $startRow += $chunkSize) {
                    $currentChunkSize = min($chunkSize, $totalRows - $startRow + 1);
                    $endRow = $startRow + $currentChunkSize - 1;

                    $chunkFilter->setRows($startRow, $currentChunkSize);

                    $spreadsheet = $reader->load($path);
                    $sheet = $worksheetName ? ($spreadsheet->getSheetByName($worksheetName) ?? $spreadsheet->getActiveSheet()) : $spreadsheet->getActiveSheet();
                    $rows = $sheet->rangeToArray("A{$startRow}:{$lastColumn}{$endRow}", null, true, true, false);
                    $spreadsheet->disconnectWorksheets();
                    unset($spreadsheet);

                    $batch = [];
                    foreach ($rows as $rowValues) {
                        // Skip empty rows
                        $nonEmpty = false;
                        foreach ($rowValues as $v) {
                            if ($v !== null && $v !== '') {
                                $nonEmpty = true;
                                break;
                            }
                        }
                        if (!$nonEmpty) {
                            continue;
                        }

                        $rowAssoc = [];
                        // Use original indices (skip 'NO' columns if present)
                        foreach ($colIndices as $k => $origIdx) {
                            $col = $origHeader[$origIdx];
                            $cell = $rowValues[$origIdx] ?? null;
                            // normalize DateTime-like values coming from Excel
                            if ($cell instanceof \DateTimeInterface) {
                                $cell = $cell->format('Y-m-d H:i:s');
                            }
                            $rowAssoc[$col] = $this->normalizeCell($cell);
                        }

                        $item = [];
                        foreach ($columns as $col) {
                            $val = array_key_exists($col, $rowAssoc) ? $this->normalizeCell($rowAssoc[$col]) : null;
                            if ($dataset === 'q_sptjm' && in_array($col, ['tanggal_usulan', 'created_at', 'updated_at'])) {
                                $item[$col] = $this->normalizeDateValue($val);
                            } else {
                                $item[$col] = $val;
                            }
                        }

                        $batch[] = $item;
                    }

                    if (!empty($batch)) {
                        foreach (array_chunk($batch, $batchInsertSize) as $subBatch) {
                            DB::table($config['table'])->insert($subBatch);
                            $inserted += count($subBatch);
                        }
                    }
                }
            } catch (\Throwable $e) {
                $alias = ErrorAlias::fromThrowable($e, 'ADM-MIGRASI');
                Log::error('MigrasiController: Excel import failed', [
                    'alias' => $alias['code'],
                    'dataset' => $dataset,
                    'ext' => $ext,
                    'inserted' => $inserted,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return back()->with('error', 'Gagal import. (Kode: ' . $alias['code'] . ')')
                    ->with('inserted', $inserted)
                    ->with('failed', 0)
                    ->with('datasetLabel', $config['label'])
                    ->withInput();
            }

            if ($inserted < 1) {
                return back()->with('error', 'Tidak ada baris data yang berhasil dibaca dari Excel.')->withInput();
            }

            return back()->with('success', "Berhasil import $inserted baris ke {$config['label']}.");
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            return back()->with('error', 'Tidak bisa membaca file CSV.')->withInput();
        }

        // Baca baris pertama mentah untuk deteksi delimiter dan BOM
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return back()->with('error', 'Header CSV tidak terbaca.')->withInput();
        }

        // Hilangkan BOM jika ada
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);

        // Deteksi delimiter: pilih yang jumlahnya lebih banyak di baris pertama
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        // Parse header dari baris pertama menggunakan delimiter terdeteksi
        $header = str_getcsv($firstLine, $delimiter);
        $header = array_map(function ($h) {
            // trim spasi dan kutip, serta sisa BOM pada sel pertama jika ada
            $h = preg_replace('/^\xEF\xBB\xBF/', '', (string)$h);
            return trim($h, " \t\n\r\0\x0B\"'" );
        }, $header);

        // If CSV contains a 'NO' column, ignore it (do not treat as extra)
        $origHeader = $header;
        $skipIndexes = [];
        foreach ($origHeader as $i => $h) {
            if (strtolower((string)$h) === 'no') {
                $skipIndexes[] = $i;
            }
        }
        if (!empty($skipIndexes)) {
            $filtered = [];
            $colIndices = [];
            foreach ($origHeader as $i => $h) {
                if (!in_array($i, $skipIndexes, true)) {
                    $filtered[] = $h;
                    $colIndices[] = $i;
                }
            }
            $header = array_values($filtered);
        } else {
            $colIndices = array_keys($origHeader);
        }

        $columns = $config['columns'];

        // Validasi jumlah dan penamaan kolom: harus sama persis (tanpa memperhatikan urutan)
        $missing = array_values(array_diff($columns, $header));
        $extra = array_values(array_diff($header, $columns));
        $expectedCount = count($columns);
        $foundCount = count($header);

        if (!empty($missing) || !empty($extra) || $expectedCount !== $foundCount) {
            fclose($handle);
            return back()->with([
                'headerMismatch' => true,
                'missingColumns' => $missing,
                'extraColumns' => $extra,
                'expectedCount' => $expectedCount,
                'foundCount' => $foundCount,
                'datasetLabel' => $config['label'],
            ])->withInput();
        }

        // Lanjut baca baris data setelah header
        $rows = [];
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row = [];
            // Respect original indices in case we skipped 'NO' column(s)
            foreach ($colIndices as $k => $origIdx) {
                $col = $origHeader[$origIdx];
                $row[$col] = $this->normalizeCell($data[$origIdx] ?? null);
            }
            $rows[] = $row;
        }
        fclose($handle);

        if (empty($rows)) {
            return back()->with('error', 'Tidak ada data setelah header CSV.')->withInput();
        }

        // Siapkan batch insert dengan mapping kolom yang cocok antara header dan tabel
        $batch = [];
        foreach ($rows as $r) {
            $item = [];
            foreach ($columns as $col) {
                $val = array_key_exists($col, $r) ? $this->normalizeCell($r[$col]) : null;
                // For q_sptjm dataset, attempt to normalize date/time fields to MySQL DATETIME
                if ($dataset === 'q_sptjm' && in_array($col, ['tanggal_usulan', 'created_at', 'updated_at'])) {
                    $item[$col] = $this->normalizeDateValue($val);
                } else {
                    $item[$col] = $val;
                }
            }
            $batch[] = $item;
        }

        // Insert secara chunk untuk efisiensi
        $inserted = 0;
        DB::beginTransaction();
        try {
            foreach (array_chunk($batch, $config['chunk']) as $chunk) {
                DB::table($config['table'])->insert($chunk);
                $inserted += count($chunk);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $failed = count($batch) - $inserted;
            $alias = ErrorAlias::fromThrowable($e, 'ADM-MIGRASI');
            Log::error('MigrasiController: CSV import failed', [
                'alias' => $alias['code'],
                'dataset' => $dataset,
                'inserted' => $inserted,
                'failed' => $failed,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Gagal import. (Kode: ' . $alias['code'] . ')')
                ->with('inserted', $inserted)
                ->with('failed', $failed)
                ->with('datasetLabel', $config['label'])
                ->withInput();
        }

        return back()->with('success', "Berhasil import $inserted baris ke {$config['label']}.");
    }

    /**
     * Verify password to unlock migrasi page for current session.
     * Expects POST { password } and returns JSON { success: bool, message: string }
     */
    
    
    public function unlock(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not authenticated.'], 401);
        }

        // Accept the fixed migration password 'lldikti4'
        $input = (string) $request->input('password');
        if ($input === 'lldikti4') {
            // set server-side session flag that persists until logout
            session(['migrasi_unlocked' => true]);
            return response()->json(['success' => true, 'message' => 'Akses migrasi diberikan.']);
        }

        return response()->json(['success' => false, 'message' => 'Password salah.'], 422);
    }

    /**
     * Koreksi data berdasarkan CSV (update baris existing) menggunakan NIDN sebagai key.
     * Hanya kolom yang ada di header dan dikenali tabel yang akan di-update.
     */
    public function koreksi(Request $request)
    {
        // Hilangkan batas waktu eksekusi agar proses koreksi besar tidak terhenti
        @set_time_limit(0);

        // Hindari memory bloat untuk proses besar
        DB::disableQueryLog();

        $validator = Validator::make($request->all(), [
            'dataset_koreksi' => 'required|in:s_transaksi_2,q_sptjm',
            'file' => 'required|file|mimes:csv,txt|max:1048576',
            'koreksi_key' => 'nullable|in:nidn,nuptk,id_usulan',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $dataset = $request->input('dataset_koreksi');
        $keyType = $request->input('koreksi_key', 'nidn');
        $config = $this->getDatasetConfig($dataset);
        if (!$config) {
            return back()->with('error', 'Dataset tidak dikenal.')->withInput();
        }

        $columnMeta = $this->loadTableColumnMeta($config['table']);

        $tahunAktif = session('tahun');
        if (!$tahunAktif) {
            return back()->with('error', 'Tahun versi tidak ditemukan di sesi. Silakan login ulang.')->withInput();
        }

        $file = $request->file('file');
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if (!$handle) {
            return back()->with('error', 'Tidak bisa membaca file CSV.')->withInput();
        }

        // Baca baris pertama untuk header dan deteksi delimiter
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return back()->with('error', 'Header CSV tidak terbaca.')->withInput();
        }

        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        $header = str_getcsv($firstLine, $delimiter);
        $header = array_map(function ($h) {
            $h = preg_replace('/^\xEF\xBB\xBF/', '', (string)$h);
            return trim($h, " \t\n\r\0\x0B\"'" );
        }, $header);

        $columns = $config['columns'];

        // Bangun mapping header index -> nama kolom tabel (case-insensitive)
        $columnMap = [];
        $nidnIndex = null;
        $nuptkIndex = null;
        $nidnColumn = null;
        $nuptkColumn = null;
        $idUsulanIndex = null;
        $idUsulanColumn = null;
        $bulanIndex = null;
        $bulanColumn = null;
        foreach ($header as $i => $nameRaw) {
            $nameNorm = strtolower($nameRaw);
            foreach ($columns as $col) {
                if (strtolower($col) === $nameNorm) {
                    $columnMap[$i] = $col;
                    if ($nameNorm === 'nidn') {
                        $nidnIndex = $i;
                        $nidnColumn = $col; // nama kolom di tabel (bisa 'NIDN' atau 'nidn')
                    }
                    if ($nameNorm === 'nuptk') {
                        $nuptkIndex = $i;
                        $nuptkColumn = $col;
                    }
                    if ($nameNorm === 'id_usulan') {
                        $idUsulanIndex = $i;
                        $idUsulanColumn = $col;
                    }
                    if ($nameNorm === 'bulan') {
                        $bulanIndex = $i;
                        $bulanColumn = $col;
                    }
                    break;
                }
            }
        }

        // Untuk s_transaksi_2, koreksi boleh berdasarkan NIDN atau NUPTK — pakai pilihan user
        if ($dataset === 's_transaksi_2') {
            if ($keyType === 'nidn') {
                if ($nidnIndex === null || $nidnColumn === null) {
                    fclose($handle);
                    return back()->with('error', 'File CSV koreksi harus memiliki kolom NIDN/nidn yang sesuai dengan tabel ketika pilihan kunci adalah NIDN.')
                        ->withInput();
                }
            } else { // nuptk
                if ($nuptkIndex === null || $nuptkColumn === null) {
                    fclose($handle);
                    return back()->with('error', 'File CSV koreksi harus memiliki kolom NUPTK/nuptk yang sesuai dengan tabel ketika pilihan kunci adalah NUPTK.')
                        ->withInput();
                }
            }
        } elseif ($dataset === 'q_sptjm') {
            if ($keyType === 'id_usulan') {
                if ($idUsulanIndex === null || $idUsulanColumn === null) {
                    fclose($handle);
                    return back()->with('error', 'File CSV koreksi harus memiliki kolom ID Usulan/id_usulan yang sesuai dengan tabel ketika pilihan kunci adalah ID Usulan.')
                        ->withInput();
                }
            } else { // default to nidn for q_sptjm
                if ($nidnIndex === null || $nidnColumn === null) {
                    fclose($handle);
                    return back()->with('error', 'File CSV koreksi harus memiliki kolom NIDN/nidn yang sesuai dengan tabel.')
                        ->withInput();
                }
            }
        } elseif ($nidnIndex === null || $nidnColumn === null) {
            fclose($handle);
            return back()->with('error', 'File CSV koreksi harus memiliki kolom NIDN/nidn yang sesuai dengan tabel.')
                ->withInput();
        }

        // Pastikan ada kolom lain selain kolom key untuk dikoreksi
        // - s_transaksi_2: exclude NIDN/NUPTK
        // - q_sptjm: exclude NIDN; jika file punya id_usulan, exclude juga id_usulan (lebih aman, jangan ubah PK)
        // Build map of header-index => table column for updateable columns.
        // For s_transaksi_2 we should exclude only the user-selected key (nidn or nuptk),
        // so that the other identifier can be updated (e.g. change NUPTK based on NIDN).
        $updateableMap = array_filter($columnMap, function ($col, $idx) use ($nidnIndex, $nuptkIndex, $dataset, $idUsulanIndex, $bulanIndex, $keyType) {
            if ($dataset === 's_transaksi_2') {
                if ($keyType === 'nidn') {
                    if ($nidnIndex !== null && $idx === $nidnIndex) return false;
                } else { // keyType == 'nuptk'
                    if ($nuptkIndex !== null && $idx === $nuptkIndex) return false;
                }
            } else {
                // q_sptjm
                if ($keyType === 'id_usulan') {
                    if ($idUsulanIndex !== null && $idx === $idUsulanIndex) return false;
                } else {
                    if ($nidnIndex !== null && $idx === $nidnIndex) return false;
                    if ($bulanIndex !== null && $idx === $bulanIndex) {
                        if ($idUsulanIndex === null) return false;
                    }
                }
            }
            return true;
        }, ARRAY_FILTER_USE_BOTH);

        if (empty($updateableMap)) {
            fclose($handle);
            return back()->with('error', 'File CSV tidak memiliki kolom lain selain NIDN untuk dikoreksi.')
                ->withInput();
        }

        // Set untuk menghindari duplikasi (lebih efisien daripada array_unique)
        $updatedIdSet = [];
        $notUpdatedIdSet = [];
        $notFoundIdSet = [];
        $invalidCellCount = 0;
        $invalidCellSamples = [];

        // Optimasi koreksi besar: chunk lebih besar untuk mengurangi overhead per-batch.
        $chunkSize = (int)($config['chunk'] ?? 100);
        if ($chunkSize < 1) {
            $chunkSize = 100;
        }
        if ($dataset === 's_transaksi_2') {
            $chunkSize = max(1000, $chunkSize);
        }

        // Untuk s_transaksi_2: preload mapping identifier -> primary key (No) sekali saja.
        // Ini menghindari query per-baris (yang lambat tanpa index).
        $idToPk = [];
        if ($dataset === 's_transaksi_2') {
            $keyColumn = ($keyType === 'nidn') ? 'NIDN' : 'NUPTK';
            foreach (
                DB::table($config['table'])
                    ->select(['No', $keyColumn])
                    ->where('Tahun_Versi', $tahunAktif)
                    ->cursor()
                as $r
            ) {
                $pk = $r->No ?? null;
                if ($pk === null) {
                    continue;
                }
                $identifier = isset($r->{$keyColumn}) ? trim((string) $r->{$keyColumn}) : '';
                if ($identifier === '') {
                    continue;
                }
                if (!isset($idToPk[$identifier])) {
                    $idToPk[$identifier] = $pk;
                }
            }
        }

        // Untuk q_sptjm: optimasi batch update paling aman jika key unik.
        // Prefer key = id_usulan (jika tersedia di CSV), jika tidak ada maka gunakan key komposit nidn+bulan.
        $qKeyStrategy = null; // 'id_usulan' | 'nidn_bulan'
        $qKeyToPk = []; // [nidn|bulan => id_usulan]
        if ($dataset === 'q_sptjm') {
            if ($idUsulanIndex !== null && $idUsulanColumn !== null) {
                $qKeyStrategy = 'id_usulan';
            } elseif ($nidnIndex !== null && $nidnColumn !== null && $bulanIndex !== null && $bulanColumn !== null) {
                $qKeyStrategy = 'nidn_bulan';
                foreach (
                    DB::table($config['table'])
                        ->select([$idUsulanColumn ?? 'id_usulan', $nidnColumn, $bulanColumn])
                        ->where('tahun', $tahunAktif)
                        ->cursor()
                    as $r
                ) {
                    $pk = $r->{$idUsulanColumn ?? 'id_usulan'} ?? null;
                    if ($pk === null) {
                        continue;
                    }
                    $nidnDb = isset($r->{$nidnColumn}) ? trim((string) $r->{$nidnColumn}) : '';
                    $bulanDb = isset($r->{$bulanColumn}) ? trim((string) $r->{$bulanColumn}) : '';
                    if ($nidnDb === '' || $bulanDb === '') {
                        continue;
                    }
                    $k = $nidnDb . '|' . $bulanDb;
                    if (!isset($qKeyToPk[$k])) {
                        $qKeyToPk[$k] = $pk;
                    }
                }
            }
        }

        $batchRows = [];

        $processBatch = function () use (
            &$batchRows,
            $dataset,
            $config,
            $nidnIndex,
            $nuptkIndex,
            $nidnColumn,
            $nuptkColumn,
            $idUsulanIndex,
            $idUsulanColumn,
            $bulanIndex,
            $bulanColumn,
            $updateableMap,
            &$updatedIdSet,
            &$notUpdatedIdSet,
            &$notFoundIdSet,
            &$invalidCellCount,
            &$invalidCellSamples,
            $keyType,
            $tahunAktif,
            $idToPk,
            $qKeyStrategy,
            $qKeyToPk,
            $columnMeta
        ) {
            if (empty($batchRows)) {
                return;
            }

            // Fast path untuk s_transaksi_2: batch compare + single UPDATE (CASE).
            if ($dataset === 's_transaksi_2') {
                $updatesByPk = []; // [pk => ['identifier' => string, 'data' => [...]]]

                foreach ($batchRows as $data) {
                    if (!is_array($data) || count(array_filter($data, function ($v) {
                        return $v !== null && $v !== '';
                    })) === 0) {
                        continue;
                    }

                    $nidnValue = ($nidnIndex !== null) ? $this->normalizeCell($data[$nidnIndex] ?? null) : null;
                    $nuptkValue = ($nuptkIndex !== null) ? $this->normalizeCell($data[$nuptkIndex] ?? null) : null;
                    $identifier = ($keyType === 'nidn') ? $nidnValue : $nuptkValue;

                    if ($identifier === null || $identifier === '') {
                        continue;
                    }

                    $pk = $idToPk[$identifier] ?? null;
                    if ($pk === null) {
                        $notFoundIdSet[$identifier] = true;
                        continue;
                    }

                    $updateData = [];
                    foreach ($updateableMap as $idx => $colName) {
                        [$shouldUpdate, $value] = $this->normalizeKoreksiCell($data[$idx] ?? null);
                        if (!$shouldUpdate) {
                            continue;
                        }

                        [$ok, $coerced, $warned] = $this->coerceValueForColumn((string) $colName, $value, $columnMeta);
                        if (!$ok) {
                            $invalidCellCount++;
                            if (count($invalidCellSamples) < 20) {
                                $invalidCellSamples[] = [
                                    'key' => $identifier,
                                    'column' => $colName,
                                    'value' => is_scalar($value) ? (string) $value : gettype($value),
                                ];
                            }
                            continue;
                        }
                        if ($warned) {
                            $invalidCellCount++;
                            if (count($invalidCellSamples) < 20) {
                                $invalidCellSamples[] = [
                                    'key' => $identifier,
                                    'column' => $colName,
                                    'value' => is_scalar($value) ? (string) $value : gettype($value),
                                ];
                            }
                        }

                        $updateData[$colName] = $coerced;
                    }

                    if (empty($updateData)) {
                        $notUpdatedIdSet[$identifier] = true;
                        continue;
                    }

                    // Jika ada duplikasi key di CSV, baris terakhir menang.
                    $updatesByPk[$pk] = [
                        'identifier' => $identifier,
                        'data' => $updateData,
                    ];
                }

                if (empty($updatesByPk)) {
                    $batchRows = [];
                    return;
                }

                // Ambil nilai existing untuk kolom-kolom yang akan diubah agar:
                // 1) bisa skip no-op update, 2) logging lebih akurat.
                $pkList = array_values(array_keys($updatesByPk));
                $unionCols = [];
                foreach ($updatesByPk as $payload) {
                    foreach ($payload['data'] as $col => $_val) {
                        $unionCols[$col] = true;
                    }
                }
                $unionCols = array_values(array_keys($unionCols));

                $currentRows = DB::table($config['table'])
                    ->select(array_merge(['No'], $unionCols))
                    ->where('Tahun_Versi', $tahunAktif)
                    ->whereIn('No', $pkList)
                    ->get();

                $currentByPk = [];
                foreach ($currentRows as $r) {
                    $currentByPk[$r->No] = (array) $r;
                }

                $toApply = []; // [pk => payload]
                foreach ($updatesByPk as $pk => $payload) {
                    $identifier = $payload['identifier'];
                    $cur = $currentByPk[$pk] ?? null;
                    if ($cur === null) {
                        $notFoundIdSet[$identifier] = true;
                        continue;
                    }

                    $needsUpdate = false;
                    foreach ($payload['data'] as $col => $newVal) {
                        $curVal = $cur[$col] ?? null;
                        $curCmp = ($curVal === null) ? null : (is_string($curVal) ? trim($curVal) : (string) $curVal);
                        $newCmp = ($newVal === null) ? null : (is_string($newVal) ? trim($newVal) : (string) $newVal);
                        if ($curCmp !== $newCmp) {
                            $needsUpdate = true;
                            break;
                        }
                    }

                    if ($needsUpdate) {
                        $toApply[$pk] = $payload;
                    } else {
                        $notUpdatedIdSet[$identifier] = true;
                    }
                }

                if (!empty($toApply)) {
                    $setClauses = [];
                    $bindings = [];

                    $applyCols = [];
                    foreach ($toApply as $payload) {
                        foreach ($payload['data'] as $col => $_v) {
                            $applyCols[$col] = true;
                        }
                    }
                    $applyCols = array_values(array_keys($applyCols));

                    foreach ($applyCols as $col) {
                        $caseSql = "`{$col}` = CASE `No`";
                        $has = false;
                        foreach ($toApply as $pk => $payload) {
                            if (!array_key_exists($col, $payload['data'])) {
                                continue;
                            }
                            $has = true;
                            $caseSql .= ' WHEN ? THEN ?';
                            $bindings[] = $pk;
                            $bindings[] = $payload['data'][$col];
                        }
                        if ($has) {
                            $caseSql .= " ELSE `{$col}` END";
                            $setClauses[] = $caseSql;
                        }
                    }

                    $applyPkList = array_values(array_keys($toApply));
                    $inPlaceholders = implode(',', array_fill(0, count($applyPkList), '?'));
                    foreach ($applyPkList as $pk) {
                        $bindings[] = $pk;
                    }
                    $bindings[] = $tahunAktif;

                    $sql = 'UPDATE ' . $config['table'] . ' SET ' . implode(', ', $setClauses)
                        . ' WHERE `No` IN (' . $inPlaceholders . ') AND `Tahun_Versi` = ?';

                    DB::beginTransaction();
                    try {
                        DB::update($sql, $bindings);
                        foreach ($toApply as $payload) {
                            $updatedIdSet[$payload['identifier']] = true;
                        }
                        DB::commit();
                    } catch (\Throwable $e) {
                        DB::rollBack();
                        throw $e;
                    }
                }

                $batchRows = [];
                return;
            }

            // Fast path untuk q_sptjm: batch compare + single UPDATE (CASE).
            if ($dataset === 'q_sptjm' && $qKeyStrategy !== null) {
                $pkCol = $idUsulanColumn ?? 'id_usulan';
                $updatesByPk = []; // [pk => ['identifier' => string, 'data' => [...]]]

                foreach ($batchRows as $data) {
                    if (!is_array($data) || count(array_filter($data, function ($v) {
                        return $v !== null && $v !== '';
                    })) === 0) {
                        continue;
                    }

                    $nidnValue = ($nidnIndex !== null) ? $this->normalizeCell($data[$nidnIndex] ?? null) : null;
                    if ($nidnValue === null || $nidnValue === '') {
                        continue;
                    }

                    $pk = null;
                    if ($qKeyStrategy === 'id_usulan') {
                        $pk = ($idUsulanIndex !== null) ? $this->normalizeCell($data[$idUsulanIndex] ?? null) : null;
                    } else { // nidn_bulan
                        $bulanValue = ($bulanIndex !== null) ? $this->normalizeCell($data[$bulanIndex] ?? null) : null;
                        if ($bulanValue === null || $bulanValue === '') {
                            $notFoundIdSet[$nidnValue] = true;
                            continue;
                        }
                        $k = $nidnValue . '|' . (string) $bulanValue;
                        $pk = $qKeyToPk[$k] ?? null;
                    }

                    if ($pk === null || $pk === '') {
                        $notFoundIdSet[$nidnValue] = true;
                        continue;
                    }

                    $updateData = [];
                    foreach ($updateableMap as $idx => $colName) {
                        [$shouldUpdate, $value] = $this->normalizeKoreksiCell($data[$idx] ?? null);
                        if (!$shouldUpdate) {
                            continue;
                        }
                        if (in_array($colName, ['tanggal_usulan', 'created_at', 'updated_at'])) {
                            if ($value !== null) {
                                $value = $this->normalizeDateValue($value);
                                if ($value === null) {
                                    continue;
                                }
                            }
                        }

                        [$ok, $coerced, $warned] = $this->coerceValueForColumn((string) $colName, $value, $columnMeta);
                        if (!$ok) {
                            $invalidCellCount++;
                            if (count($invalidCellSamples) < 20) {
                                $invalidCellSamples[] = [
                                    'key' => $nidnValue,
                                    'column' => $colName,
                                    'value' => is_scalar($value) ? (string) $value : gettype($value),
                                ];
                            }
                            continue;
                        }
                        if ($warned) {
                            $invalidCellCount++;
                            if (count($invalidCellSamples) < 20) {
                                $invalidCellSamples[] = [
                                    'key' => $nidnValue,
                                    'column' => $colName,
                                    'value' => is_scalar($value) ? (string) $value : gettype($value),
                                ];
                            }
                        }
                        $updateData[$colName] = $coerced;
                    }

                    if (empty($updateData)) {
                        $notUpdatedIdSet[$nidnValue] = true;
                        continue;
                    }

                    // Duplikasi key di CSV: baris terakhir menang
                    $updatesByPk[$pk] = [
                        'identifier' => $nidnValue,
                        'data' => $updateData,
                    ];
                }

                if (empty($updatesByPk)) {
                    $batchRows = [];
                    return;
                }

                $pkList = array_values(array_keys($updatesByPk));
                $unionCols = [];
                foreach ($updatesByPk as $payload) {
                    foreach ($payload['data'] as $col => $_val) {
                        $unionCols[$col] = true;
                    }
                }
                $unionCols = array_values(array_keys($unionCols));

                $currentRows = DB::table($config['table'])
                    ->select(array_merge([$pkCol], $unionCols))
                    ->where('tahun', $tahunAktif)
                    ->whereIn($pkCol, $pkList)
                    ->get();

                $currentByPk = [];
                foreach ($currentRows as $r) {
                    $pkVal = $r->{$pkCol};
                    $currentByPk[$pkVal] = (array) $r;
                }

                $toApply = [];
                foreach ($updatesByPk as $pk => $payload) {
                    $identifier = $payload['identifier'];
                    $cur = $currentByPk[$pk] ?? null;
                    if ($cur === null) {
                        $notFoundIdSet[$identifier] = true;
                        continue;
                    }

                    $needsUpdate = false;
                    foreach ($payload['data'] as $col => $newVal) {
                        $curVal = $cur[$col] ?? null;
                        $curCmp = ($curVal === null) ? null : (is_string($curVal) ? trim($curVal) : (string) $curVal);
                        $newCmp = ($newVal === null) ? null : (is_string($newVal) ? trim($newVal) : (string) $newVal);
                        if ($curCmp !== $newCmp) {
                            $needsUpdate = true;
                            break;
                        }
                    }

                    if ($needsUpdate) {
                        $toApply[$pk] = $payload;
                    } else {
                        $notUpdatedIdSet[$identifier] = true;
                    }
                }

                if (!empty($toApply)) {
                    $setClauses = [];
                    $bindings = [];

                    $applyCols = [];
                    foreach ($toApply as $payload) {
                        foreach ($payload['data'] as $col => $_v) {
                            $applyCols[$col] = true;
                        }
                    }
                    $applyCols = array_values(array_keys($applyCols));

                    foreach ($applyCols as $col) {
                        $caseSql = "`{$col}` = CASE `{$pkCol}`";
                        $has = false;
                        foreach ($toApply as $pk => $payload) {
                            if (!array_key_exists($col, $payload['data'])) {
                                continue;
                            }
                            $has = true;
                            $caseSql .= ' WHEN ? THEN ?';
                            $bindings[] = $pk;
                            $bindings[] = $payload['data'][$col];
                        }
                        if ($has) {
                            $caseSql .= " ELSE `{$col}` END";
                            $setClauses[] = $caseSql;
                        }
                    }

                    $applyPkList = array_values(array_keys($toApply));
                    $inPlaceholders = implode(',', array_fill(0, count($applyPkList), '?'));
                    foreach ($applyPkList as $pk) {
                        $bindings[] = $pk;
                    }
                    $bindings[] = $tahunAktif;

                    $sql = 'UPDATE ' . $config['table'] . ' SET ' . implode(', ', $setClauses)
                        . ' WHERE `' . $pkCol . '` IN (' . $inPlaceholders . ') AND `tahun` = ?';

                    DB::beginTransaction();
                    try {
                        DB::update($sql, $bindings);
                        foreach ($toApply as $payload) {
                            $updatedIdSet[$payload['identifier']] = true;
                        }
                        DB::commit();
                    } catch (\Throwable $e) {
                        DB::rollBack();
                        throw $e;
                    }
                }

                $batchRows = [];
                return;
            }

            // Default path: dataset selain s_transaksi_2 tetap pakai per-row update (lebih sederhana).
            DB::beginTransaction();
            try {
                foreach ($batchRows as $data) {
                    if (!is_array($data) || count(array_filter($data, function ($v) {
                        return $v !== null && $v !== '';
                    })) === 0) {
                        continue; // lewati baris kosong
                    }

                    $nidnValue = null;
                    $nuptkValue = null;
                    if ($nidnIndex !== null) {
                        $nidnValue = $this->normalizeCell($data[$nidnIndex] ?? null);
                    }
                    if ($nuptkIndex !== null) {
                        $nuptkValue = $this->normalizeCell($data[$nuptkIndex] ?? null);
                    }

                    // identifier berdasarkan pilihan kunci pengguna
                    if ($keyType === 'nidn') {
                        $identifier = $nidnValue;
                    } else {
                        $identifier = $nuptkValue;
                    }

                    if ($identifier === null || $identifier === '') {
                        continue;
                    }

                    $updateData = [];
                    foreach ($updateableMap as $idx => $colName) {
                        // Blank => skip update; literal 'NULL' => set SQL NULL
                        [$shouldUpdate, $value] = $this->normalizeKoreksiCell($data[$idx] ?? null);
                        if (!$shouldUpdate) {
                            continue;
                        }

                        [$ok, $coerced, $warned] = $this->coerceValueForColumn((string) $colName, $value, $columnMeta);
                        if (!$ok) {
                            $invalidCellCount++;
                            if (count($invalidCellSamples) < 20) {
                                $invalidCellSamples[] = [
                                    'key' => (string) $identifier,
                                    'column' => $colName,
                                    'value' => is_scalar($value) ? (string) $value : gettype($value),
                                ];
                            }
                            continue;
                        }
                        if ($warned) {
                            $invalidCellCount++;
                            if (count($invalidCellSamples) < 20) {
                                $invalidCellSamples[] = [
                                    'key' => (string) $identifier,
                                    'column' => $colName,
                                    'value' => is_scalar($value) ? (string) $value : gettype($value),
                                ];
                            }
                        }

                        // Samakan normalisasi tanggal seperti proses import untuk dataset q_sptjm
                        if ($dataset === 'q_sptjm' && in_array($colName, ['tanggal_usulan', 'created_at', 'updated_at'])) {
                            // Jika explicit NULL, set null; kalau bukan null, baru coba parsing
                            if ($value !== null) {
                                $value = $this->normalizeDateValue($value);
                                if ($value === null) {
                                    continue;
                                }
                            }
                        }

                        $updateData[$colName] = $coerced;
                    }

                    if (empty($updateData)) {
                        $notUpdatedIdSet[$identifier] = true;
                        continue;
                    }

                    $affected = 0;
                    if ($dataset === 'q_sptjm') {
                        $affected = DB::table($config['table'])
                            ->where($nidnColumn, $identifier)
                            ->where('tahun', $tahunAktif)
                            ->update($updateData);
                    } else {
                        $affected = DB::table($config['table'])
                            ->where($nidnColumn, $identifier)
                            ->update($updateData);
                    }

                    if ($affected > 0) {
                        $updatedIdSet[$identifier] = true;
                        continue;
                    }

                    $existsQuery = DB::table($config['table'])->where($nidnColumn, $identifier);
                    if ($dataset === 'q_sptjm') {
                        $existsQuery->where('tahun', $tahunAktif);
                    }
                    if ($existsQuery->exists()) {
                        $notUpdatedIdSet[$identifier] = true;
                    } else {
                        $notFoundIdSet[$identifier] = true;
                    }
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            } finally {
                $batchRows = [];
            }
        };

        try {
            while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                $batchRows[] = $data;

                if (count($batchRows) >= $chunkSize) {
                    $processBatch();
                }
            }

            // flush sisa batch
            $processBatch();
            fclose($handle);
        } catch (\Throwable $e) {
            fclose($handle);
            $alias = ErrorAlias::fromThrowable($e, 'ADM-MIGRASI');
            Log::error('MigrasiController: koreksi failed', [
                'alias' => $alias['code'],
                'dataset' => $dataset,
                'keyType' => $keyType,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Gagal koreksi. (Kode: ' . $alias['code'] . ')')->withInput();
        }

        $updatedNidn = array_values(array_keys($updatedIdSet));
        $notUpdatedNidn = array_values(array_keys($notUpdatedIdSet));
        $notFoundNidn = array_values(array_keys($notFoundIdSet));

        $msg = 'Koreksi selesai. ';
        $msg .= 'Data ter-update: ' . count($updatedNidn) . ' baris kunci (NIDN/NUPTK/nidn)';
        if (!empty($notUpdatedNidn)) {
            $msg .= ' | Ada tetapi tidak diperbarui: ' . count($notUpdatedNidn) . ' data.';
        }
        if (!empty($notFoundNidn)) {
            $msg .= ' | Tidak ditemukan di tabel: ' . count($notFoundNidn) . ' data.';
        }
        if ($invalidCellCount > 0) {
            $msg .= ' | Peringatan: ' . $invalidCellCount . ' nilai tidak valid (contoh di daftar).';
        }

        return back()
            ->with('success', $msg)
            ->with('koreksiUpdatedNidn', $updatedNidn)
            ->with('koreksiNotUpdatedNidn', $notUpdatedNidn)
            ->with('koreksiNotFoundNidn', $notFoundNidn)
            ->with('koreksiInvalidCells', $invalidCellSamples);
    }

    private function getDatasetDefinitions(): array
    {
        return [
            's_transaksi_2' => [
                'table' => 's_transaksi_2',
                'label' => 's_transaksi_2',
                'chunk' => 100,
                // Optimized for large XLSX uploads
                'excel_chunk' => 1000,
                'excel_batch' => 500,
                'columns' => [
                    'NIDN','NUPTK','NIK','Nama','TTL','Tanggal_Lahir','Usia','Sertifikat_Dosen','Tahun_Lulus','PTS','Kode_PT','Jenis','TMT_JAD_Pertama','Inpassing','TMT_Inpassing_Akhir','TMT_JAD_Akhir','Jabatan1','Jabatan2','Jabatan3','Jabatan4','Jabatan5','Jabatan6','Jabatan7','Jabatan8','Jabatan9','Jabatan10','Jabatan11','Jabatan12','Gol1','Gol2','Gol3','Gol4','Gol5','Gol6','Gol7','Gol8','Gol9','Gol10','Gol11','Gol12','Tahun1','Tahun2','Tahun3','Tahun4','Tahun5','Tahun6','Tahun7','Tahun8','Tahun9','Tahun10','Tahun11','Tahun12','NPWP','No_Rek','No_Rekening','Nama_Pegawai','Nama_Rekening','Nama_Penerima','Bank','Biaya_Per_Bulan','Aktif','Keterangan','Eligible_span','Tanggal_Update_Terakhir','Pemegang_Wilayah','Gaji1','Gaji2','Gaji3','Gaji4','Gaji5','Gaji6','Gaji7','Gaji8','Gaji9','Gaji10','Gaji11','Gaji12','KodeUsulan1','KodeUsulan2','KodeUsulan3','KodeUsulan4','KodeUsulan5','KodeUsulan6','KodeUsulan7','KodeUsulan8','KodeUsulan9','KodeUsulan10','KodeUsulan11','KodeUsulan12','Jan','Feb','Mar','Apr','May','Jun','Jul','Ags','Sep','Okt','Nov','Des','TPD1','TKGB1','TPD2','TKGB2','TPD3','TKGB3','TPD4','TKGB4','TPD5','TKGB5','TPD6','TKGB6','TPD7','TKGB7','TPD8','TKGB8','TPD9','TKGB9','TPD10','TKGB10','TPD11','TKGB11','TPD12','TKGB12','pajakTPD1','pajakTKGB1','pajakTPD2','pajakTKGB2','pajakTPD3','pajakTKGB3','pajakTPD4','pajakTKGB4','pajakTPD5','pajakTKGB5','pajakTPD6','pajakTKGB6','pajakTPD7','pajakTKGB7','pajakTPD8','pajakTKGB8','pajakTPD9','pajakTKGB9','pajakTPD10','pajakTKGB10','pajakTPD11','pajakTKGB11','pajakTPD12','pajakTKGB12','nilaiPajakTPD1','nilaiPajakTKGB1','nilaiPajakTPD2','nilaiPajakTKGB2','nilaiPajakTPD3','nilaiPajakTKGB3','nilaiPajakTPD4','nilaiPajakTKGB4','nilaiPajakTPD5','nilaiPajakTKGB5','nilaiPajakTPD6','nilaiPajakTKGB6','nilaiPajakTPD7','nilaiPajakTKGB7','nilaiPajakTPD8','nilaiPajakTKGB8','nilaiPajakTPD9','nilaiPajakTKGB9','nilaiPajakTPD10','nilaiPajakTKGB10','nilaiPajakTPD11','nilaiPajakTKGB11','nilaiPajakTPD12','nilaiPajakTKGB12','bersihTPD1','bersihTKGB1','bersihTPD2','bersihTKGB2','bersihTPD3','bersihTKGB3','bersihTPD4','bersihTKGB4','bersihTPD5','bersihTKGB5','bersihTPD6','bersihTKGB6','bersihTPD7','bersihTKGB7','bersihTPD8','bersihTKGB8','bersihTPD9','bersihTKGB9','bersihTPD10','bersihTKGB10','bersihTPD11','bersihTKGB11','bersihTPD12','bersihTKGB12','No_sp2d_1','No_sp2d_2','No_sp2d_3','No_sp2d_4','No_sp2d_5','No_sp2d_6','No_sp2d_7','No_sp2d_8','No_sp2d_9','No_sp2d_10','No_sp2d_11','No_sp2d_12','Tgl_sp2d_1','Tgl_sp2d_2','Tgl_sp2d_3','Tgl_sp2d_4','Tgl_sp2d_5','Tgl_sp2d_6','Tgl_sp2d_7','Tgl_sp2d_8','Tgl_sp2d_9','Tgl_sp2d_10','Tgl_sp2d_11','Tgl_sp2d_12','JmlTPD_Selisih','JmlTKGB_Selisih','Pajak_TPD_Selisih','Pajak_TKGB_Selisih','Bersih_TPD_Selisih','Bersih_TKGB_Selisih','No_SPM_TPD','No_SPM_TKGB','TglTPD','TglTKGB','Pengguna','Tahun_Versi'
                ],
            ],
            'q_sptjm' => [
                'table' => 'q_sptjm',
                'label' => 'q_sptjm',
                'chunk' => 100,
                // Optimized for large XLSX uploads
                'excel_chunk' => 1000,
                'excel_batch' => 500,
                'columns' => [
                    'id_usulan','tanggal_usulan','kode_pts','nama_pts','bulan','tahun','nidn','nuptk','nama','jabatan','kota','nomor_surat','alamat_pts','wilayah','password','aktif','file','status','alasan_penolakan','created_at','updated_at'
                ],
            ],
        ];
    }

    private function getDatasetConfig(string $dataset): ?array
    {
        $datasets = $this->getDatasetDefinitions();

        return $datasets[$dataset] ?? null;
    }
}
