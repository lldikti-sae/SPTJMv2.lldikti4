<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComplainPicController extends ComplainAdminController
{
    private function picEmail(): string
    {
        $user = Auth::guard('web')->user();
        return trim((string) ($user->email ?? ''));
    }

    private function assertPicCanAccessComplain(int $complainId): void
    {
        $email = $this->picEmail();
        if ($email === '') {
            abort(403);
        }

        $row = DB::table('i_complain')->where('id', $complainId)->first();
        if (!$row) {
            abort(404);
        }

        $tahunVersi = (int) session('tahun');
        if ($tahunVersi <= 0) {
            // fall back: allow check without year constraint when session year is missing
            $tahunVersi = 0;
        }

        $identifier = trim((string) ($row->nidn ?? ''));
        if ($identifier === '') {
            $identifier = trim((string) ($row->nuptk ?? ''));
        }

        if ($identifier === '') {
            // no identifier -> cannot determine wilayah -> deny
            abort(403);
        }

        $q = DB::table('s_transaksi_2 as t')
            ->where(function ($w) use ($identifier) {
                $w->where('t.NIDN', $identifier)->orWhere('t.NUPTK', $identifier);
            })
            ->whereRaw('TRIM(t.Pemegang_Wilayah) = ?', [$email]);

        if ($tahunVersi > 0) {
            $q->where('t.Tahun_Versi', $tahunVersi);
        }

        if (!$q->exists()) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $email = $this->picEmail();
        if ($email === '') {
            abort(403);
        }

        if ($request->ajax()) {
            $draw = (int) $request->input('draw', 0);

            try {
                $start = (int) $request->input('start', 0);
                $length = (int) $request->input('length', 10);
                $searchValue = (string) $request->input('search.value', '');

                $columnsMap = [
                    0 => 'id',
                    1 => 'pelapor_tipe',
                    2 => 'kode_pts',
                    3 => 'c.nidn',
                    4 => 'c.nuptk',
                    5 => 'pic',
                    6 => 'nama_pelapor',
                    7 => 'c.judul',
                    8 => 'c.status',
                    9 => 'c.handled_at',
                    10 => 'c.created_at',
                ];

                $order = $request->input('order', []);
                $orderColIndex = (int) data_get($order, '0.column', 0);
                $orderDir = (string) data_get($order, '0.dir', 'desc');
                $orderDir = in_array($orderDir, ['asc', 'desc'], true) ? $orderDir : 'desc';
                $orderColumn = $columnsMap[$orderColIndex] ?? 'id';

                $tahunVersi = (int) session('tahun');

                $tahunSession = (int) session('tahun');
                $startOfYear = null;
                $startOfNextYear = null;
                if ($tahunSession > 0) {
                    $startOfYear = Carbon::create($tahunSession, 1, 1, 0, 0, 0);
                    $startOfNextYear = Carbon::create($tahunSession + 1, 1, 1, 0, 0, 0);
                }

                // Best practice: avoid correlated subquery against big table.
                // Build allowed identifiers for this PIC once, then join to i_complain.
                $allowedNidn = DB::table('s_transaksi_2 as t')
                    ->selectRaw('t.NIDN as nidn')
                    ->whereNotNull('t.NIDN')
                    ->where('t.NIDN', '<>', '')
                    ->whereRaw('TRIM(t.Pemegang_Wilayah) = ?', [$email])
                    ->when($tahunVersi > 0, function ($q) use ($tahunVersi) {
                        $q->where('t.Tahun_Versi', '=', $tahunVersi);
                    })
                    ->groupBy('t.NIDN');

                $allowedNuptk = DB::table('s_transaksi_2 as t')
                    ->selectRaw('t.NUPTK as nuptk')
                    ->whereNotNull('t.NUPTK')
                    ->where('t.NUPTK', '<>', '')
                    ->whereRaw('TRIM(t.Pemegang_Wilayah) = ?', [$email])
                    ->when($tahunVersi > 0, function ($q) use ($tahunVersi) {
                        $q->where('t.Tahun_Versi', '=', $tahunVersi);
                    })
                    ->groupBy('t.NUPTK');

                $statusFilter = (string) $request->input('status', '');

                // Data query (PIC column is constant for this view, avoid correlated subquery)
                $baseQuery = DB::table('i_complain as c')
                    ->leftJoin('a_dosen as d', 'c.dosen_id', '=', 'd.id')
                    ->leftJoin('a_pts as p', 'c.pts_id', '=', 'p.id')
                    ->leftJoinSub($allowedNidn, 'an', function ($join) {
                        $join->on('an.nidn', '=', 'c.nidn');
                    })
                    ->leftJoinSub($allowedNuptk, 'au', function ($join) {
                        $join->on('au.nuptk', '=', 'c.nuptk');
                    })
                    ->select([
                        'c.id',
                        'c.pelapor_tipe',
                        'c.kode_pts',
                        'c.nidn',
                        'c.nuptk',
                        'c.judul',
                        'c.status',
                        'c.handled_at',
                        'c.created_at',
                        DB::raw('COALESCE(d.nama_dosen, p.nama_pts) as nama_pelapor'),
                    ])
                    ->where(function ($q) {
                        $q->whereIn('c.pelapor_tipe', ['pts', 'dosen'])
                          ->orWhere(function ($subq) {
                              $subq->where('c.pelapor_tipe', 'admin')
                                   ->whereIn('c.jenis_pengajuan', ['Surat Keterangan', 'Surat SKPP'])
                                   ->whereNotNull('c.lampiran');
                          });
                    })
                    ->selectRaw('? as pic', [$email]);

                // Enforce PIC scope via joined identifier lists
                $baseQuery->where(function ($q) {
                    $q->whereNotNull('an.nidn')->orWhereNotNull('au.nuptk');
                });

                if ($startOfYear && $startOfNextYear) {
                    $baseQuery->where('c.created_at', '>=', $startOfYear)
                        ->where('c.created_at', '<', $startOfNextYear);
                }

                if (trim($searchValue) !== '') {
                    $baseQuery->where(function ($q) use ($searchValue) {
                        $q->where('c.pelapor_tipe', 'like', "%{$searchValue}%")
                            ->orWhere('c.kode_pts', 'like', "%{$searchValue}%")
                            ->orWhere('c.nidn', 'like', "%{$searchValue}%")
                            ->orWhere('c.nuptk', 'like', "%{$searchValue}%")
                            ->orWhere('d.nama_dosen', 'like', "%{$searchValue}%")
                            ->orWhere('p.nama_pts', 'like', "%{$searchValue}%")
                            ->orWhere('c.judul', 'like', "%{$searchValue}%")
                            ->orWhere('c.pesan', 'like', "%{$searchValue}%")
                            ->orWhere('c.status', 'like', "%{$searchValue}%");
                    });
                }

                if ($statusFilter !== '') {
                    $allowed = ['open', 'setuju', 'tolak', 'menunggu_konfirmasi'];
                    if (in_array($statusFilter, $allowed, true)) {
                        if ($statusFilter === 'open') {
                            $baseQuery->whereIn('c.status', ['open', 'menunggu_konfirmasi']);
                        } else {
                            $baseQuery->where('c.status', $statusFilter);
                        }
                    }
                }

                // Total count (lighter query)
                $recordsTotalQuery = DB::table('i_complain as c')
                    ->leftJoinSub($allowedNidn, 'an', function ($join) {
                        $join->on('an.nidn', '=', 'c.nidn');
                    })
                    ->leftJoinSub($allowedNuptk, 'au', function ($join) {
                        $join->on('au.nuptk', '=', 'c.nuptk');
                    })
                    ->where(function ($q) {
                        $q->whereNotNull('an.nidn')->orWhereNotNull('au.nuptk');
                    })
                    ->where(function ($q) {
                        $q->whereIn('c.pelapor_tipe', ['pts', 'dosen'])
                          ->orWhere(function ($subq) {
                              $subq->where('c.pelapor_tipe', 'admin')
                                   ->whereIn('c.jenis_pengajuan', ['Surat Keterangan', 'Surat SKPP'])
                                   ->whereNotNull('c.lampiran');
                          });
                    });
                if ($startOfYear && $startOfNextYear) {
                    $recordsTotalQuery->where('c.created_at', '>=', $startOfYear)
                        ->where('c.created_at', '<', $startOfNextYear);
                }
                $recordsTotal = (int) $recordsTotalQuery->distinct('c.id')->count('c.id');

                // Filtered count (lighter than counting the full data-select query)
                $filteredCountQuery = DB::table('i_complain as c')
                    ->leftJoinSub($allowedNidn, 'an', function ($join) {
                        $join->on('an.nidn', '=', 'c.nidn');
                    })
                    ->leftJoinSub($allowedNuptk, 'au', function ($join) {
                        $join->on('au.nuptk', '=', 'c.nuptk');
                    })
                    ->where(function ($q) {
                        $q->whereNotNull('an.nidn')->orWhereNotNull('au.nuptk');
                    })
                    ->where(function ($q) {
                        $q->whereIn('c.pelapor_tipe', ['pts', 'dosen'])
                          ->orWhere(function ($subq) {
                              $subq->where('c.pelapor_tipe', 'admin')
                                   ->whereIn('c.jenis_pengajuan', ['Surat Keterangan', 'Surat SKPP'])
                                   ->whereNotNull('c.lampiran');
                          });
                    });
                if (trim($searchValue) !== '') {
                    $filteredCountQuery->leftJoin('a_dosen as d', 'c.dosen_id', '=', 'd.id')
                        ->leftJoin('a_pts as p', 'c.pts_id', '=', 'p.id');
                }
                if ($startOfYear && $startOfNextYear) {
                    $filteredCountQuery->where('c.created_at', '>=', $startOfYear)
                        ->where('c.created_at', '<', $startOfNextYear);
                }
                if (trim($searchValue) !== '') {
                    $filteredCountQuery->where(function ($q) use ($searchValue) {
                        $q->where('c.pelapor_tipe', 'like', "%{$searchValue}%")
                            ->orWhere('c.kode_pts', 'like', "%{$searchValue}%")
                            ->orWhere('c.nidn', 'like', "%{$searchValue}%")
                            ->orWhere('c.nuptk', 'like', "%{$searchValue}%")
                            ->orWhere('d.nama_dosen', 'like', "%{$searchValue}%")
                            ->orWhere('p.nama_pts', 'like', "%{$searchValue}%")
                            ->orWhere('c.judul', 'like', "%{$searchValue}%")
                            ->orWhere('c.pesan', 'like', "%{$searchValue}%")
                            ->orWhere('c.status', 'like', "%{$searchValue}%");
                    });
                }
                if ($statusFilter !== '') {
                    $allowed = ['open', 'setuju', 'tolak', 'menunggu_konfirmasi'];
                    if (in_array($statusFilter, $allowed, true)) {
                        if ($statusFilter === 'open') {
                            $filteredCountQuery->whereIn('c.status', ['open', 'menunggu_konfirmasi']);
                        } else {
                            $filteredCountQuery->where('c.status', $statusFilter);
                        }
                    }
                }
                $recordsFiltered = (int) $filteredCountQuery->distinct('c.id')->count('c.id');

                $baseQuery->orderBy($orderColumn, $orderDir)->orderByDesc('id');
                if ($length !== -1) {
                    $baseQuery->offset($start)->limit($length);
                }

                $rows = $baseQuery->get();

                $data = $rows->map(function ($r) {
                    $status = (string) ($r->status ?? 'open');
                    $badge = 'bg-label-secondary';
                    if ($status === 'open') $badge = 'bg-label-warning';
                    if ($status === 'menunggu_konfirmasi') $badge = 'bg-label-info';
                    if ($status === 'setuju') $badge = 'bg-label-success';
                    if ($status === 'tolak') $badge = 'bg-label-danger';

                    $replyBtn = '<button type="button" class="sptjm-icon-btn sptjm-btn-edit reply-complain" data-id="' . $r->id . '" title="Tanggapi"><i class="bx bx-message-square-dots"></i></button>';

                    $fullTitle = (string) ($r->judul ?? '');
                    $words = preg_split('/\s+/', trim($fullTitle));
                    if (is_array($words) && count($words) > 5) {
                        $shortTitle = implode(' ', array_slice($words, 0, 5)) . ' .....';
                    } else {
                        $shortTitle = $fullTitle;
                    }

                    $handledAt = null;
                    $createdAt = null;
                    try {
                        $handledAt = $r->handled_at ? Carbon::parse($r->handled_at)->setTimezone('Asia/Jakarta')->format('d-m-Y H:i') : '-';
                    } catch (\Throwable $e) {
                        $handledAt = $r->handled_at ?? '-';
                    }
                    try {
                        $createdAt = $r->created_at ? Carbon::parse($r->created_at)->setTimezone('Asia/Jakarta')->format('d-m-Y H:i') : '-';
                    } catch (\Throwable $e) {
                        $createdAt = $r->created_at ?? '-';
                    }

                    return [
                        'id' => $r->id,
                        'pelapor_tipe' => strtoupper($r->pelapor_tipe),
                        'kode_pts' => $r->kode_pts,
                        'nidn' => $r->nidn ?: '-',
                        'nuptk' => $r->nuptk ?: '-',
                        'pic' => $r->pic ?: '-',
                        'nama' => $r->nama_pelapor ?: '-',
                        'judul' => $shortTitle,
                        'status' => '<span class="badge ' . $badge . '">' . strtoupper(str_replace('_', ' ', $status)) . '</span>',
                        'handled_at' => $handledAt,
                        'created_at' => $createdAt,
                        'aksi' => $replyBtn,
                    ];
                });

                $pendingCountQuery = DB::table('i_complain as c')
                    ->leftJoinSub($allowedNidn, 'an', function ($join) {
                        $join->on('an.nidn', '=', 'c.nidn');
                    })
                    ->leftJoinSub($allowedNuptk, 'au', function ($join) {
                        $join->on('au.nuptk', '=', 'c.nuptk');
                    })
                    ->where(function ($q) {
                        $q->whereNotNull('an.nidn')->orWhereNotNull('au.nuptk');
                    })
                    ->where(function ($q) {
                        $q->whereIn('c.pelapor_tipe', ['pts', 'dosen'])
                          ->orWhere(function ($subq) {
                              $subq->where('c.pelapor_tipe', 'admin')
                                   ->whereIn('c.jenis_pengajuan', ['Surat Keterangan', 'Surat SKPP'])
                                   ->whereNotNull('c.lampiran');
                          });
                    })
                    ->whereIn('c.status', ['open', 'menunggu_konfirmasi']);
                if ($startOfYear && $startOfNextYear) {
                    $pendingCountQuery->where('c.created_at', '>=', $startOfYear)
                        ->where('c.created_at', '<', $startOfNextYear);
                }
                $pendingCount = (int) $pendingCountQuery->distinct('c.id')->count('c.id');

                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $recordsFiltered,
                    'pendingCount' => $pendingCount,
                    'data' => $data,
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]);
            }
        }

        return view('pic.complain');
    }

    public function show($id)
    {
        $this->assertPicCanAccessComplain((int) $id);
        
        // Ubah status menjadi 'open' secara otomatis jika PIC membuka detail pengaduan SKPP
        $complain = DB::table('i_complain')->where('id', $id)->first();
        if ($complain && in_array($complain->jenis_pengajuan, ['Surat Keterangan', 'Surat SKPP']) && $complain->status === 'menunggu_konfirmasi') {
            DB::table('i_complain')->where('id', $id)->update(['status' => 'open']);
        }

        return parent::show($id);
    }

    public function update(Request $request, $id)
    {
        $this->assertPicCanAccessComplain((int) $id);
        return parent::update($request, $id);
    }
}
