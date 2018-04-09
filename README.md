php-gpg
=======
This is a fork of the fork because everything still sucks. All unacceptable.

![packagist](https://img.shields.io/packagist/dt/nicoSWD/php-gpg.svg?maxAge=2592000)

Features/Limitations
--------------------

 * Supports encrypting with 2048-bit RSA keys.
 * Encrypted messages are integrity protected.
 
Usage
-----

```php
require 'vendor/autoload.php';

$gpg = new nicoSWD\GPG\GPG();
$pubKey = new nicoSWD\GPG\PublicKey($aPublicKey);

echo $gpg->encrypt($pubKey, 'ABCDEFGHIJKL');
```
