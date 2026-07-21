<?php
$data = json_decode(file_get_contents('api_response2.json'), true);
if (!$data) {
    echo "Not a valid JSON!\n";
    echo substr(file_get_contents('api_response2.json'), 0, 500);
} else {
    print_r($data['noSp2d'] ?? 'no noSp2d key');
    echo "\n";
    print_r($data['kotorTpd'] ?? 'no kotorTpd key');
}
