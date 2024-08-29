<?php

$key = uniqid(uniqid(), true);
$hash = password_hash($key, PASSWORD_DEFAULT);

echo "Key: $key\n";
echo "Hash: $hash\n";
?>