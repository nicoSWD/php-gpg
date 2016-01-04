php-gpg
=======

This is a fork of the php-gpg project, which has code quality (and partially due to this, security) issues. It is interoperable with the PGP standard.

Features/Limitations
--------------------

 * Supports encrypting with 2048-bit RSA keys.
 * Encrypted messages are integrity protected.
 
Usage
-----

```php
require 'vendor/autoload.php';

$gpg = new Certly\GPG\GPG();
$pubKey = new Certly\GPG\Public_Key($public_key_ascii);

echo $gpg->encrypt($pubKey,"ABCDEFGHIJKL");
```