<?php

$package = function(string $name, string $code, string $path){
    return [
        'name' => $name,
        'code' => $code,
        'path' => $path
    ];
};

return [
    'spg-auto-updater' => $package('spvgg-auto-update', 'spvgg-auto-update', 'wp-content/plugins/spvgg-auto-update')
];