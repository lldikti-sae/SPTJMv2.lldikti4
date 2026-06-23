<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class ErrorAlias
{
    /**
     * Generate an alias code + friendly message for end users.
     *
     * @return array{code: string, message: string}
     */
    public static function fromThrowable(\Throwable $e, string $scope = 'ERR'): array
    {
        $scope = strtoupper(trim($scope)) ?: 'ERR';
        $uuid = str_replace('-', '', (string) Str::uuid());
        $code = $scope . '-' . strtoupper(substr($uuid, 0, 10));

        $debugMsg = $e->getMessage() . " | " . $e->getFile() . ":" . $e->getLine();

        \Illuminate\Support\Facades\Log::error('ErrorAlias generated: ' . $code, [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return [
            'code' => $code,
            'message' => 'Terjadi kesalahan sistem. Silakan coba lagi. (Kode: ' . $code . ') DETAIL: ' . $debugMsg,
        ];
    }
}
