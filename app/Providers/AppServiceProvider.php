<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot()
  {
    View::composer('layouts.sections.menu.verticalMenuDosen', function ($view) {
      $jsonPath = resource_path('menu/verticalMenuDosen.json');
      if (is_file($jsonPath)) {
        $jsonContent = file_get_contents($jsonPath);
        $menuData = json_decode($jsonContent);
        $view->with('menuData', $menuData);
        return;
      }

      // Fallback to default menu if dosen menu json is missing
      $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
      $verticalMenuData = json_decode($verticalMenuJson);
      $view->with('menuData', [$verticalMenuData]);
    });

    View::composer('layouts.sections.menu.verticalMenuPic', function ($view) {
      $jsonPath = resource_path('menu/verticalMenuPic.json');
      $jsonContent = file_get_contents($jsonPath);
      $menuData = json_decode($jsonContent);

      $user = auth()->user();
      if ($user && $user->isPIC() && !empty($user->admin_permissions)) {
          $adminMenuPath = resource_path('menu/verticalMenu.json');
          if (file_exists($adminMenuPath)) {
              $adminMenuData = json_decode(file_get_contents($adminMenuPath));
              
              $permUrlMap = [
                  'data-dosen-lihat' => ['admin/data-dosen'],
                  'data-dosen-histori' => ['admin/histori-dosen'],
                  'data-dosen-monitoring' => ['admin/monitoring-usulan-dosen'],
                  'data-dosen-hapus' => ['admin/hapus-data-dosen-tidak-aktif'],
                  'skpp' => ['admin/skpp'],
                  'sisternas-cutoff' => ['admin/cutoff-sisternas'],
                  'proses-pengaturan' => ['admin/pengaturan-usulan'],
                  'proses-monitoring-usulan' => ['admin/usulan-sptjm'],
                  'proses-rekap-eligible' => ['admin/rekap-usulan-eligible'],
                  'proses-rekap-non-eligible' => ['admin/rekap-usulan-non-el'],
                  'rekap-pencairan' => ['admin/rekap-pencairan'],
                  'proses-laporan' => ['admin/laporan-keuangan'],
                  'monitoring-pembayaran' => ['admin/monitoring-pembayaran'],
                  'kekurangan-bayar' => ['admin/kekurangan-bayar'],
                  'monitoring-koreksi' => ['admin/koreksi'],
                  'sinkronisasi' => ['admin/sinkronisasi']
              ];
              
              $allowedUrls = [];
              foreach ($user->admin_permissions as $perm) {
                  if (isset($permUrlMap[$perm])) {
                      foreach ($permUrlMap[$perm] as $url) {
                          $allowedUrls[] = $url;
                      }
                  }
              }
              
              $additionalMenus = [];
              $findMenus = function($items) use (&$findMenus, $allowedUrls, &$additionalMenus) {
                  foreach ($items as $item) {
                      if (isset($item->url) && in_array($item->url, $allowedUrls)) {
                          // Change icon or use original
                          $additionalMenus[] = $item;
                      }
                      if (isset($item->submenu)) {
                          $findMenus($item->submenu);
                      }
                  }
              };
              
              if (isset($adminMenuData->menu)) {
                  $findMenus($adminMenuData->menu);
              }
              
              if (count($additionalMenus) > 0) {
                  $menuData[0]->menu[] = (object) ['menuHeader' => 'Akses Modul Admin'];
                  foreach ($additionalMenus as $am) {
                      $menuData[0]->menu[] = $am;
                  }
              }
          }
      }

      $view->with('menuData', $menuData);
    });

    View::composer('layouts.sections.menu.verticalMenuPts', function ($view) {
      $jsonPath = resource_path('menu/verticalMenuPts.json');
      $jsonContent = file_get_contents($jsonPath);
      $menuData = json_decode($jsonContent);

      // Cek apakah tanggal sekarang dalam rentang aktif
      $now = Carbon::now()->toDateString();
      $pengusulanAktif = DB::table('m_pengaturan_usulan')
        ->whereDate('tanggal_mulai', '<=', $now)
        ->whereDate('tanggal_selesai', '>=', $now)
        ->exists();

      // Filter menu 'Usulan Serdos' jika belum waktunya
      if (!$pengusulanAktif && isset($menuData[0]->menu)) {
        $menuData[0]->menu = array_filter($menuData[0]->menu, function ($item) {
          return $item->slug !== 'usulan';
        });

        // Reindex array setelah filter
        $menuData[0]->menu = array_values($menuData[0]->menu);
      }
      $view->with('menuData', $menuData);
    });

    View::composer('layouts.sections.menu.verticalMenuAuditor', function ($view) {
      $jsonPath = resource_path('menu/verticalMenuAuditor.json');
      if (is_file($jsonPath)) {
        $jsonContent = file_get_contents($jsonPath);
        $menuData = json_decode($jsonContent);
        $view->with('menuData', $menuData);
        return;
      }

      // Fallback: use default menu if auditor menu json is missing
      $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
      $verticalMenuData = json_decode($verticalMenuJson);
      $view->with('menuData', [$verticalMenuData]);
    });
  }
}
