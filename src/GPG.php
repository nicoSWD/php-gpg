<?php

namespace nicoSWD\GPG;

class GPG
{
    /**
     * @var int
     */
    private $width = 16;

    /**
     * @var string
     */
    private $version = '1.0.0';

    /**
     * @param PublicKey $pk
     * @param string    $plaintext
     * @param null      $versionHeader
     * @return string
     */
    public function encrypt(PublicKey $pk, $plaintext, $versionHeader = null)
    {
        $key_id = $pk->getKeyId();
        $public_key = $pk->getPublicKey();

        $session_key = Utility::s_random($this->width, 0);
        $key_id = Utility::hex2bin($key_id);
        $cp = $this->gpgSession($key_id, $session_key, $public_key) . $this->gpgData($session_key, $plaintext);

        $code = base64_encode($cp);
        $code = wordwrap($code, 64, "\n", 1);

        if ($versionHeader === null) {
            $versionHeader = 'Version: PHP-GPG v' . $this->version . "\n\n";
        } elseif (safeStrlen($versionHeader) > 0) {
            $versionHeader = 'Version: ' . $versionHeader . "\n\n";
        }

        return
            "-----BEGIN PGP MESSAGE-----\n" .
            $versionHeader .
            $code . "\n=" . base64_encode(Utility::crc24($cp)) .
            "\n-----END PGP MESSAGE-----\n";
    }

    /**
     * @param $key
     * @param $text
     * @return string
     */
    private function gpgEncrypt($key, $text)
    {
        $len = safeStrlen($text);
        $iblock = array_fill(0, $this->width, 0);
        $rblock = array_fill(0, $this->width, 0);

        $cipher = '';

        if ($len % $this->width) {
            for ($i = ($len % $this->width); $i < $this->width; $i++) {
                $text .= "\0";
            }
        }

        $ekey = new ExpandedKey($key);

        for ($i = 0; $i < $this->width; $i++) {
            $iblock[$i] = 0;
            $rblock[$i] = Utility::c_random();
        }

        $strLen = safeStrlen($text);

        for ($n = 0; $n < $strLen; $n += $this->width) {
            $iblock = AES::encrypt($iblock, $ekey);
            for ($i = 0; $i < $this->width; $i++) {
                $iblock[$i] ^= ord($text[$n + $i]);
                $cipher .= chr($iblock[$i]);
            }
        }

        return substr($cipher, 0, $len);
    }

    /**
     * @param $tag
     * @param $len
     * @return string
     */
    private function gpgHeader($tag, $len)
    {
        $h = '';
        if ($len < 0x100) {
            $h .= chr($tag);
            $h .= chr($len);
        } elseif ($len < 0x10000) {
            $tag += 1;
            $h .= chr($tag);
            $h .= $this->writeNumber($len, 2);
        } else {
            $tag += 2;
            $h .= chr($tag);
            $h .= $this->writeNumber($len, 4);
        }

        return $h;
    }

    /**
     * @param $n
     * @param $bytes
     * @return string
     */
    private function writeNumber($n, $bytes)
    {
        // credits for this function go to OpenPGP.js
        $b = '';
        for ($i = 0; $i < $bytes; $i++) {
            $b .= chr(($n >> (8 * ($bytes - $i - 1))) & 0xff);
        }

        return $b;
    }

    /**
     * @param $key_id
     * @param $session_key
     * @param $public_key
     * @return string
     */
    private function gpgSession($key_id, $session_key, $public_key)
    {
        $s = base64_decode($public_key);
        $l = floor((ord($s[0]) * 256 + ord($s[1]) + 7) / 8);
        $mod = mpi2b(substr($s, 0, $l + 2));
        $exp = mpi2b(substr($s, $l + 2));

        $c = 0;
        $lsk = strlen($session_key);
        for ($i = 0; $i < $lsk; $i++) {
            $c += ord($session_key[$i]);
        }
        $c &= 0xffff;

        $lm = ($l - 2) * 8 + 2;
        $m = chr($lm / 256) . chr($lm % 256) .
            chr(2) . Utility::s_random($l - $lsk - 6, 1) . "\0" .
            chr(7) . $session_key .
            chr($c / 256) . chr($c & 0xff);

        $enc = b2mpi(bmodexp(mpi2b($m), $exp, $mod));

        return $this->gpgHeader(0x84, strlen($enc) + 10) . chr(3) . $key_id . chr(1) . $enc;
    }

    /**
     * @param $text
     * @return string
     */
    private function gpgLiteral($text)
    {
        if (strpos($text, "\r\n") === false) {
            $text = str_replace("\n", "\r\n", $text);
        }

        return chr(11 | 0xC0) . chr(255) . $this->writeNumber(safeStrlen($text) + 10, 4) . 't' . chr(4) . "file\0\0\0\0" . $text;
    }

    /**
     * @param $key
     * @param $text
     * @return string
     */
    private function gpgData($key, $text)
    {
        $prefix = Utility::s_random($this->width, 0);
        $prefix .= substr($prefix, -2);
        $mdc = "\xD3\x14" . hash('sha1', $prefix . $this->gpgLiteral($text) . "\xD3\x14", true);
        $enc = $this->gpgEncrypt($key, $prefix . $this->gpgLiteral($text) . $mdc);

        return chr(0x12 | 0xC0) . chr(255) . $this->writeNumber(1 + strlen($enc), 4) . chr(1) . $enc;
    }
}
