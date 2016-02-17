php-gpg
=======
This is a fork of the fork because everything still sucks.

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
