<?php
\ = \DB::table('t_kekurangan')->whereNotNull('rekap_id')->count();
\ = \DB::table('t_kekurangan')->whereNull('rekap_id')->count();
echo "rekap_id IS NOT NULL: " . \ . "\n";
echo "rekap_id IS NULL: " . \ . "\n";
