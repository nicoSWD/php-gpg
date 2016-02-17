<?php

use nicoSWD\GPG\GPG;
use nicoSWD\GPG\PublicKey;

class EncryptTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return string
     */
    public function getTestKey()
    {
        return '-----BEGIN PGP PUBLIC KEY BLOCK-----
Version: GnuPG v1

mQENBFaKdnIBCACIVyq6yXMOyVygLLMQAc+xom2Mq/Ii0vRm8bh58iOI1kghhxe+
wGDU9FSWEG+msARTUZYYn6iPJLXxTj41i6XdcXdhZai93DR9/fUFGjbbJII6nQ8r
9jAe0tPK7CDOpiE78Kb5p56ViZ1MeOaRDTfzslfNibiU7mkLhCv3XS9jOx1uiCif
VfqIT+/tA3V2mMN071JLXUbfb3FSFA+4Fs0pJUHza5HbDW8nUHmYosNbtoVvj9fC
wiu/W3zOOVx+WI3FLV6cmE8U2UIX3i7SIrGlJDFOgx3vryuTvKRoIwu0lhbhz5qr
l4qYbL0+TQCK4aFqOGHz4894lc/mIbuCliZBABEBAAG0AIkBHAQQAQIABgUCVop2
cgAKCRARb0b/yznPfqZlB/4+dMVjzdG1nz7hxmg/O96iXOJGMctV+KyuKzUZeTqF
5JxCCpd66AKCfa15ZQRi4iSw6ULpc3QDPeytTf7mzULdk94/pH6f4Ass/0anxF0Z
qFgKsr+/5ZXTZ5lYvfu+ehNeHCBFCebsJAsgIMo697Ux3zo5IGbdSXCEWVJRhbDU
kjNnbiVxmaxAslZu5uQ87hTILa9VlhpIzQx3QGyBVZQr4UFEYP7WjQ0enOI2KINr
APKtNNO0x87pw+AnKs2gZ3vtR9CU59xaZe40XWNUeX6Dq8UXDjL5L6qPdFl4Ab4S
RtMRSAL/PdpISDpv0WgQzbjnlxZmRvCvwAxXnXl4Pa2A
=FEVo
-----END PGP PUBLIC KEY BLOCK-----';
    }

    public function test_Encrypt()
    {
        $public_key_ascii = $this->getTestKey();
        $gpg = new GPG();
        $pub_key = new PublicKey($public_key_ascii);
        $encrypted = $gpg->encrypt($pub_key, 'ABCDEF');

        $this->assertContains('-----BEGIN PGP MESSAGE-----', $encrypted);
        $this->assertContains('-----END PGP MESSAGE-----', $encrypted);
    }
}
