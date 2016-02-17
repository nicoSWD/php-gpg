<?php

use nicoSWD\GPG\BDiv;

global $bs;
global $bx2;
global $bm;
global $bx;
global $bd;
global $bdm;

$bs = 28;
$bx2 = 1 << $bs;
$bm = $bx2 - 1;
$bx = $bx2 >> 1;
$bd = $bs >> 1;
$bdm = (1 << $bd) - 1;

/**
 * @param $s
 * @return array|int
 */
function mpi2b($s)
{
    global $bm;

    $bn = 1;
    $r = [0];
    $rn = 0;
    $sb = 256;
    $c = 0;
    $sn = strlen($s);

    if ($sn < 2) {
        return 0;
    }

    $len = ($sn - 2) * 8;
    $bits = ord($s[0]) * 256 + ord($s[1]);

    if ($bits > $len || $bits < $len - 8) {
        return 0;
    }

    for ($n = 0; $n < $len; $n++) {
        if (($sb <<= 1) > 255) {
            $sb = 1;
            $c = ord($s[--$sn]);
        }
        if ($bn > $bm) {
            $bn = 1;
            $r[++$rn] = 0;
        }
        if ($c & $sb) {
            $r[$rn] |= $bn;
        }
        $bn <<= 1;
    }

    return $r;
}

/**
 * @param $b
 * @return string
 */
function b2mpi($b)
{
    global $bs;
    global $bm;

    $bn = 1;
    $bc = 0;
    $r = [0];
    $rb = 1;
    $rn = 0;
    $bits = count($b) * $bs;
    $rr = '';

    for ($n = 0; $n < $bits; $n++) {
        if ($b[$bc] & $bn) {
            $r[$rn] |= $rb;
        }
        if (($rb <<= 1) > 255) {
            $rb = 1;
            $r[++$rn] = 0;
        }
        if (($bn <<= 1) > $bm) {
            $bn = 1;
            $bc++;
        }
    }

    while ($rn && $r[$rn] == 0) {
        $rn--;
    }

    $bn = 256;
    for ($bits = 8; $bits > 0; $bits--) {
        if ($r[$rn] & ($bn >>= 1)) {
            break;
        }
    }
    $bits += $rn * 8;

    $rr .= chr($bits / 256) . chr($bits % 256);
    if ($bits) {
        for ($n = $rn; $n >= 0; $n--) {
            $rr .= chr($r[$n]);
        }
    }

    return $rr;
}

/**
 * @param $xx
 * @param $y
 * @param $m
 * @return array
 */
function bmodexp($xx, $y, $m)
{
    global $bs;

    $r = [1];
    $x = array_merge((array)$xx);
    $n = count($m) * 2;
    $mu = array_fill(0, $n + 1, 0);

    $mu[$n--] = 1;
    for (; $n >= 0; $n--) {
        $mu[$n] = 0;
    }
    $dd = new BDiv($mu, $m);
    $mu = $dd->q;

    for ($n = 0; $n < count($y); $n++) {
        for ($a = 1, $an = 0; $an < $bs; $an++, $a <<= 1) {
            if ($y[$n] & $a) {
                $r = bmod2(bmul($r, $x), $m, $mu);
            }
            $x = bmod2(bmul($x, $x), $m, $mu);
        }
    }

    return $r;
}

/**
 * @param $i
 * @param $m
 * @return int
 */
function simplemod($i, $m) // returns the mod where m < 2^bd
{
    global $bd;
    global $bdm;

    $c = 0;

    for ($n = count($i) - 1; $n >= 0; $n--) {
        $v = $i[$n];
        $c = (($v >> $bd) + ($c << $bd)) % $m;
        $c = (($v & $bdm) + ($c << $bd)) % $m;
    }

    return $c;
}

/**
 * @param $p
 * @param $m
 * @return array
 */
function bmod($p, $m) // binary modulo
{
    global $bdm;

    if (count($m) == 1) {
        if (count($p) == 1) {
            return [$p[0] % $m[0]];
        }
        if ($m[0] < $bdm) {
            return [simplemod($p, $m[0])];
        }
    }

    $r = new BDiv($p, $m);

    return $r->mod;
}

/**
 * @param $x
 * @param $m
 * @param $mu
 * @return array
 */
function bmod2($x, $m, $mu)
{
    $xl = count($x) - (count($m) << 1);
    if ($xl > 0) {
        return bmod2(array_concat(array_slice($x, 0, $xl), bmod2(array_slice($x, $xl), $m, $mu)), $m, $mu);
    }

    $ml1 = count($m) + 1;
    $ml2 = count($m) - 1;

    $q3 = array_slice(bmul(array_slice($x, $ml2), $mu), $ml1);
    $r1 = array_slice($x, 0, $ml1);
    $r2 = array_slice(bmul($q3, $m), 0, $ml1);

    $r = bsub($r1, $r2);
    if (count($r) == 0) {
        $r1[$ml1] = 1;
        $r = bsub($r1, $r2);
    }
    for ($n = 0; ; $n++) {
        $rr = bsub($r, $m);
        if (count($rr) == 0) {
            break;
        }
        $r = $rr;
        if ($n >= 3) {
            return bmod2($r, $m, $mu);
        }
    }

    return $r;
}

/**
 * @param $x
 * @param $start
 * @param $len
 * @return int
 */
function toppart($x, $start, $len)
{
    global $bx2;

    $n = 0;
    while ($start >= 0 && $len-- > 0) {
        $n = $n * $bx2 + $x[$start--];
    }

    return $n;
}

/**
 * @param $n
 * @return array
 */
function zeros($n)
{
    $r = array_fill(0, $n, 0);
    while ($n-- > 0) {
        $r[$n] = 0;
    }

    return $r;
}

/**
 * @param $a
 * @param $b
 * @return array
 */
function bsub($a, $b)
{
    global $bs;
    global $bm;

    $al = count($a);
    $bl = count($b);

    if ($bl > $al) {
        return [];
    }
    if ($bl == $al) {
        if ($b[$bl - 1] > $a[$bl - 1]) {
            return [];
        }
        if ($bl == 1) {
            return [$a[0] - $b[0]];
        }
    }

    $r = array_fill(0, $al, 0);
    $c = 0;

    for ($n = 0; $n < $bl; $n++) {
        $c += $a[$n] - $b[$n];
        $r[$n] = $c & $bm;
        $c >>= $bs;
    }
    for (; $n < $al; $n++) {
        $c += $a[$n];
        $r[$n] = $c & $bm;
        $c >>= $bs;
    }
    if ($c) {
        return [];
    }

    if ($r[$n - 1]) {
        return $r;
    }
    while ($n > 1 && $r[$n - 1] == 0) {
        $n--;
    }

    return array_slice($r, 0, $n);
}

/**
 * @param $a
 * @param $b
 * @return array
 */
function bmul($a, $b)
{
    global $bs;
    global $bm;
    global $bd;
    global $bdm;

    $b = array_merge((array)$b, [0]);
    $al = count($a);
    $bl = count($b);

    $r = zeros($al + $bl + 1);

    for ($n = 0; $n < $al; $n++) {
        $aa = $a[$n];
        if ($aa) {
            $c = 0;
            $hh = $aa >> $bd;
            $h = $aa & $bdm;
            $m = $n;
            for ($nn = 0; $nn < $bl; $nn++, $m++) {
                $g = $b[$nn];
                $gg = $g >> $bd;
                $g = $g & $bdm;
                $ghh = $g * $hh + $h * $gg;
                $ghhb = $ghh >> $bd;
                $ghh &= $bdm;
                $c += $r[$m] + $h * $g + ($ghh << $bd);
                $r[$m] = $c & $bm;
                $c = ($c >> $bs) + $gg * $hh + $ghhb;
            }
        }
    }
    $n = count($r);

    if ($r[$n - 1]) {
        return $r;
    }
    while ($n > 1 && $r[$n - 1] == 0) {
        $n--;
    }

    return array_slice($r, 0, $n);
}

/**
 * @param $string
 * @return int
 */
function safeStrlen($string)
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($string, '8bit');
    }

    return strlen($string);
}
