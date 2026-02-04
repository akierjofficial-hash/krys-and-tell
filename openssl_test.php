<?php
echo OPENSSL_VERSION_TEXT, PHP_EOL;

$config = [
  "private_key_type" => OPENSSL_KEYTYPE_EC,
  "curve_name"       => "prime256v1",
];

$k = openssl_pkey_new($config);
var_dump($k !== false);

while ($e = openssl_error_string()) {
  echo "OpenSSL: $e", PHP_EOL;
}
