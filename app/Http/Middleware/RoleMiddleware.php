<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
  public function handle(Request $request, Closure $next, ...$roles)
  {
    $user = Auth::user();

    if (!$user) {
      abort(403, 'Kamu tidak ada akses ke halaman tersebut!');
    }

    if (empty($roles)) {
      abort(403, 'Kamu tidak ada akses ke halaman tersebut!');
    }

    $role = (string) ($user->role ?? '');

    if ($role == '' || !in_array($role, $roles, true)) {
      // UX: jika auditor membuka URL admin yang memang ia audit, arahkan ke halaman auditor (read-only)
      if ($role === 'auditor' && in_array('admin', $roles, true)) {
        if ($request->is('admin/data-dosen')) {
          return redirect()->route('auditor.data-dosen');
        }
        if ($request->is('admin/laporan-keuangan')) {
          return redirect()->route('auditor.laporan-keuangan');
        }
      }

      // UX: module role pic untuk akses ke modul admin
      if ($role === 'pic' && in_array('admin', $roles, true)) {
          $permissions = $user->admin_permissions ?? [];

          // Define route-to-permission mapping
          // Using prefixes, e.g., 'admin/skpp' matches all subroutes of skpp
          $permissionMap = [
              // Master Data
              'admin/daftar-pt' => 'data-perguruan-tinggi',
              'admin/master-dosen' => 'master-dosen',
              'admin/data-bank' => 'master-bank',
              'admin/data-grade' => 'master-grade',
              'admin/grade-serdos' => 'master-grade-serdos',
              'admin/data-pajak' => 'master-pajak',
              'admin/jabatan' => 'master-jabatan',
              'admin/status-keaktifan' => 'master-aktif',
              'admin/status-pegawai' => 'master-pegawai',
              'admin/status-perubahan' => 'master-perubahan',

              // Data Dosen
              'admin/data-dosen' => 'lihat-data-dosen',
              'admin/ubah-data-dosen' => 'lihat-data-dosen',
              'admin/perubahan-data-dosen' => 'lihat-data-dosen',
              'admin/histori-dosen' => 'histori-data-dosen',
              'admin/monitoring-usulan-dosen' => 'monitoring-data-dosen',
              'admin/hapus-data-dosen-tidak-aktif' => 'hapus-data-dosen',
              'admin/skpp' => 'skpp',

              // Data Sisternas
              'admin/data-sisternas' => 'sisternas-data',
              'admin/cutoff-sisternas' => 'sisternas-cutoff',
              'admin/periode-sisternas' => 'sisternas-cutoff',

              // Proses Pembayaran
              'admin/pengaturan-usulan' => 'proses-pengaturan',
              'admin/usulan-sptjm' => 'proses-monitoring-usulan',
              'admin/rekap-usulan-eligible' => 'proses-rekap-eligible',
              'admin/rekap-usulan-non-el' => 'proses-rekap-non-eligible',
              'admin/rekap-pencairan' => 'rekap-pencairan', // backward compatible label
              'admin/laporan-keuangan' => 'proses-laporan',

              // Monitoring
              'admin/monitoring-pembayaran' => 'monitoring-pembayaran',
              'admin/kekurangan-bayar' => 'kekurangan-bayar',
              'admin/koreksi' => 'monitoring-koreksi',
              'admin/sinkronisasi' => 'sinkronisasi',

              // Pengaturan
              'admin/pengguna-akun' => 'pengaturan-akun',
              'admin/hak-akses-pic' => 'pengaturan-hak-akses',
              'admin/tambah-versi' => 'pengaturan-versi',
              'admin/migrasi' => 'pengaturan-migrasi',
              'admin/background' => 'pengaturan-background',
              
              // Complain
              'admin/complain' => 'admin-complain',
          ];

          // Legacy support untuk permission lama 'data-dosen' yang mungkin belum diupdate
          foreach ($permissionMap as $routePrefix => $requiredPermission) {
              if (str_contains($request->url(), '/' . $routePrefix)) {
                  if (in_array($requiredPermission, $permissions) || (str_starts_with($requiredPermission, 'data-dosen') && in_array('data-dosen', $permissions))) {
                      return $next($request);
                  }
              }
          }
      }

      abort(403, 'Kamu tidak ada akses ke halaman tersebut!');
    }

    return $next($request);
  }
}
