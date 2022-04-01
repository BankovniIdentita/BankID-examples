<?php

declare(strict_types=1);

namespace App;

use BankId\OIDC\Tools\KeyGenerator;

require 'vendor/autoload.php';

$keyGenerator = new KeyGenerator();

$key = $keyGenerator->generate();
$publicKey = $key->toPublic();

//save $key in the way you prefer in order to expose an array of these keys under /.well-known/jwks
//because BankId will attend your site in order to obtain the keys to verify the signatures of your requests

//here in the example the key will be dumped as 'key.json'

file_put_contents('./key.json', json_encode($key));
file_put_contents('./publickey.json', json_encode($publicKey));


