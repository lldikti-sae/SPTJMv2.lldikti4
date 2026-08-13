<?php
$files = [
    'd:/Kerjaan/Project/SPTJM_GITHUB/SPTJMv2.lldikti4/app/Http/Controllers/UsulanTukinBerjalanController.php',
    'd:/Kerjaan/Project/SPTJM_GITHUB/SPTJMv2.lldikti4/app/Http/Controllers/UsulanTukinSusulanController.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // 1. Replace the table assignment logic
        $searchTableLogic = <<<EOT
			if (in_array(\$bulan, [1, 2])) {
				\$joinTable = ['table' => 'p_sister_genap as b', 'kode_pt' => 'b.kode_pt'];
			} elseif (in_array(\$bulan, [3, 4, 5, 6, 7, 8])) {
				\$joinTable = ['table' => 'p_sister_ganjil as b', 'kode_pt' => 'b.kode_pt'];
			} else {
				\$joinTable = ['table' => 'p_sister_genap as b', 'kode_pt' => 'b.kode_pt'];
			}
EOT;
        $searchTableLogicSusulan1 = <<<EOT
            if (in_array(\$bulan, [1, 2])) {
                \$joinTable = ['table' => 'p_sister_genap as b', 'kode_pt' => 'b.kode_pt'];
            } elseif (in_array(\$bulan, [3, 4, 5, 6, 7, 8])) {
                \$joinTable = ['table' => 'p_sister_ganjil as b', 'kode_pt' => 'b.kode_pt'];
            } else {
                \$joinTable = ['table' => 'p_sister_genap as b', 'kode_pt' => 'b.kode_pt'];
            }
EOT;
        $searchTableLogic2 = <<<EOT
			if (in_array(\$bulan, [1, 2])) {
				\$joinTable = ['table' => 'p_sister_genap as b'];
			} elseif (in_array(\$bulan, [3, 4, 5, 6, 7, 8])) {
				\$joinTable = ['table' => 'p_sister_ganjil as b'];
			} else {
				\$joinTable = ['table' => 'p_sister_genap as b'];
			}
EOT;
        $searchTableLogicSusulan2 = <<<EOT
            if (in_array(\$bulan, [1, 2])) {
                \$joinTable = ['table' => 'p_sister_genap as b'];
            } elseif (in_array(\$bulan, [3, 4, 5, 6, 7, 8])) {
                \$joinTable = ['table' => 'p_sister_ganjil as b'];
            } else {
                \$joinTable = ['table' => 'p_sister_genap as b'];
            }
EOT;

        $replaceTableLogic = <<<EOT
			if (in_array(\$bulan, [1, 2])) {
				\$joinTable = ['table' => 'p_sister_tukin as b', 'kode_pt' => 'b.kode_pt', 'periode' => 'Genap'];
			} elseif (in_array(\$bulan, [3, 4, 5, 6, 7, 8])) {
				\$joinTable = ['table' => 'p_sister_tukin as b', 'kode_pt' => 'b.kode_pt', 'periode' => 'Ganjil'];
			} else {
				\$joinTable = ['table' => 'p_sister_tukin as b', 'kode_pt' => 'b.kode_pt', 'periode' => 'Genap'];
			}
EOT;
        $replaceTableLogicSusulan1 = <<<EOT
            if (in_array(\$bulan, [1, 2])) {
                \$joinTable = ['table' => 'p_sister_tukin as b', 'kode_pt' => 'b.kode_pt', 'periode' => 'Genap'];
            } elseif (in_array(\$bulan, [3, 4, 5, 6, 7, 8])) {
                \$joinTable = ['table' => 'p_sister_tukin as b', 'kode_pt' => 'b.kode_pt', 'periode' => 'Ganjil'];
            } else {
                \$joinTable = ['table' => 'p_sister_tukin as b', 'kode_pt' => 'b.kode_pt', 'periode' => 'Genap'];
            }
EOT;
        $replaceTableLogic2 = <<<EOT
			if (in_array(\$bulan, [1, 2])) {
				\$joinTable = ['table' => 'p_sister_tukin as b', 'periode' => 'Genap'];
			} elseif (in_array(\$bulan, [3, 4, 5, 6, 7, 8])) {
				\$joinTable = ['table' => 'p_sister_tukin as b', 'periode' => 'Ganjil'];
			} else {
				\$joinTable = ['table' => 'p_sister_tukin as b', 'periode' => 'Genap'];
			}
EOT;
        $replaceTableLogicSusulan2 = <<<EOT
            if (in_array(\$bulan, [1, 2])) {
                \$joinTable = ['table' => 'p_sister_tukin as b', 'periode' => 'Genap'];
            } elseif (in_array(\$bulan, [3, 4, 5, 6, 7, 8])) {
                \$joinTable = ['table' => 'p_sister_tukin as b', 'periode' => 'Ganjil'];
            } else {
                \$joinTable = ['table' => 'p_sister_tukin as b', 'periode' => 'Genap'];
            }
EOT;

        $content = str_replace($searchTableLogic, $replaceTableLogic, $content);
        $content = str_replace($searchTableLogicSusulan1, $replaceTableLogicSusulan1, $content);
        $content = str_replace($searchTableLogic2, $replaceTableLogic2, $content);
        $content = str_replace($searchTableLogicSusulan2, $replaceTableLogicSusulan2, $content);

        // 2. Add 'use ($joinTable)' and the 'periode' where clause in leftJoin
        $searchLeftJoin = <<<EOT
				->leftJoin(\$joinTable['table'], function (\$join) {
					\$join->on(function (\$on) {
						\$on->where(function (\$q) {
							\$q->whereColumn('d.NIDN', '=', 'b.nidn')
								->whereRaw("TRIM(d.NIDN) != ''")
								->whereRaw("TRIM(d.NIDN) != '-'");
						})->orWhere(function (\$q) {
							\$q->whereColumn('d.NUPTK', '=', 'b.nuptk')
								->whereRaw("TRIM(d.NUPTK) != ''")
								->whereRaw("TRIM(d.NUPTK) != '-'");
						});
					});
				})
EOT;
        $searchLeftJoinSusulan = <<<EOT
                ->leftJoin(\$joinTable['table'], function (\$join) {
                    \$join->on(function (\$on) {
                        \$on->where(function (\$q) {
                            \$q->whereColumn('d.NIDN', '=', 'b.nidn')
                                ->whereRaw("TRIM(d.NIDN) != ''")
                                ->whereRaw("TRIM(d.NIDN) != '-'");
                        })->orWhere(function (\$q) {
                            \$q->whereColumn('d.NUPTK', '=', 'b.nuptk')
                                ->whereRaw("TRIM(d.NUPTK) != ''")
                                ->whereRaw("TRIM(d.NUPTK) != '-'");
                        });
                    });
                })
EOT;
        $replaceLeftJoin = <<<EOT
				->leftJoin(\$joinTable['table'], function (\$join) use (\$joinTable) {
					\$join->on(function (\$on) {
						\$on->where(function (\$q) {
							\$q->whereColumn('d.NIDN', '=', 'b.nidn')
								->whereRaw("TRIM(d.NIDN) != ''")
								->whereRaw("TRIM(d.NIDN) != '-'");
						})->orWhere(function (\$q) {
							\$q->whereColumn('d.NUPTK', '=', 'b.nuptk')
								->whereRaw("TRIM(d.NUPTK) != ''")
								->whereRaw("TRIM(d.NUPTK) != '-'");
						});
					});
					if (isset(\$joinTable['periode'])) {
						\$join->where('b.periode', '=', \$joinTable['periode']);
					}
				})
EOT;
        $replaceLeftJoinSusulan = <<<EOT
                ->leftJoin(\$joinTable['table'], function (\$join) use (\$joinTable) {
                    \$join->on(function (\$on) {
                        \$on->where(function (\$q) {
                            \$q->whereColumn('d.NIDN', '=', 'b.nidn')
                                ->whereRaw("TRIM(d.NIDN) != ''")
                                ->whereRaw("TRIM(d.NIDN) != '-'");
                        })->orWhere(function (\$q) {
                            \$q->whereColumn('d.NUPTK', '=', 'b.nuptk')
                                ->whereRaw("TRIM(d.NUPTK) != ''")
                                ->whereRaw("TRIM(d.NUPTK) != '-'");
                        });
                    });
                    if (isset(\$joinTable['periode'])) {
                        \$join->where('b.periode', '=', \$joinTable['periode']);
                    }
                })
EOT;
        $content = str_replace($searchLeftJoin, $replaceLeftJoin, $content);
        $content = str_replace($searchLeftJoinSusulan, $replaceLeftJoinSusulan, $content);

        file_put_contents($file, $content);
        echo "Processed: \$file\n";
    }
}
?>
