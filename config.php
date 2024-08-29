<?php

$package = function(string $name, string $code, string $path){
    return [
        'name' => $name,
        'code' => $code,
        'path' => $path
    ];
};

return [
    $package('spvgg-auto-update', 'spvgg-auto-update', 'wp-content/plugins/spvgg-auto-update')
];