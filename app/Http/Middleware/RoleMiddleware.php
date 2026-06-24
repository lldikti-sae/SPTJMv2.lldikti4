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
          $path = $request->path(); // e.g., 'admin/skpp'

          // Define route-to-permission mapping
          // Using prefixes, e.g., 'admin/skpp' matches all subroutes of skpp
          $permissionMap = [
              'admin/skpp' => 'skpp',
              'admin/kekurangan-bayar' => 'kekurangan-bayar',
              'admin/master-dosen' => 'data-dosen',
              'admin/data-dosen' => 'data-dosen',
              'admin/ubah-data-dosen' => 'data-dosen',
              'admin/perubahan-data-dosen' => 'data-dosen',
              'admin/rekap-pencairan' => 'rekap-pencairan',
              'admin/sinkronisasi' => 'sinkronisasi',
              'admin/data-sisternas' => 'data-sisternas',
              'admin/cutoff-sisternas' => 'data-sisternas',
          ];

          foreach ($permissionMap as $routePrefix => $requiredPermission) {
              // Exact match or subroute match
              if ($path === $routePrefix || str_starts_with($path, $routePrefix . '/')) {
                  if (in_array($requiredPermission, $permissions)) {
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
