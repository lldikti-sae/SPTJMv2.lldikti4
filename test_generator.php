<?php
require __DIR__.'/vendor/autoload.php';
require __DIR__.'/bootstrap/app.php';

use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class TestExport implements FromGenerator, WithHeadings {
    public function generator(): \Generator {
        for ($i = 0; $i < 10; $i++) {
            yield ["Row " . $i];
        }
    }
    public function headings(): array { return ["Col"]; }
}

try {
    Excel::store(new TestExport(), 'test.xlsx', 'local');
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
