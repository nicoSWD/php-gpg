php-gpg
=======
[![Build Status](https://travis-ci.org/iangcarroll/php-gpg.svg?branch=master)](https://travis-ci.org/iangcarroll/php-gpg) [Documentation](https://certly-php-gpg.s3-website-us-east-1.amazonaws.com)

This is a fork of the php-gpg project, which has code quality (and partially due to this, security) issues. It can perform basic operations that are compatible with the PGP standard.

Features/Limitations
--------------------

 * Supports encrypting with 2048-bit RSA keys.
 * Encrypted messages are integrity protected.
 
Usage
-----

```php
require 'vendor/autoload.php';

$gpg = new Certly\GPG\GPG();
$pubKey = new Certly\GPG\Public_Key($aPublicKey);

echo $gpg->encrypt($pubKey,"ABCDEFGHIJKL");
```