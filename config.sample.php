<?php

$package = function(string $name, string $code, string $path){
    return [
        'name' => $name,
        'code' => $code,
        'path' => $path
    ];
};

return [
    'spg-auto-updater' => $package('spvgg-auto-update', '$2y$10$VvhzkAHyJa4aJtCMKsEil.twNDcuZjcxszoPFuPSoJI24f2KIgKtW', 'wp-content/plugins/spvgg-auto-update') 
    // Key: 66d0f6c5700f866d0f6c5704e06.13311890
];