<?php

namespace nicoSWD\GPG;

class BDiv
{
    /**
     * @var array
     */
    public $q;
    /**
     * @var array
     */
    public $mod;

    /**
     * BDiv constructor.
     *
     * @param $x
     * @param $y
     */
    public function __construct($x, $y)
    {
        global $bs;
        global $bm;

        $n = count($x) - 1;
        $t = count($y) - 1;
        $nmt = $n - $t;

        if ($n < $t || $n == $t && ($x[$n] < $y[$n] || $n > 0 && $x[$n] == $y[$n] && $x[$n - 1] < $y[$n - 1])) {
            $this->q = [0];
            $this->mod = [$x];

            return;
        }

        if ($n == $t && toppart($x, $t, 2) / toppart($y, $t, 2) < 4) {
            $qq = 0;
            for (; ;) {
                $xx = bsub($x, $y);
                if (count($xx) == 0) {
                    break;
                }
                $x = $xx;
                $qq++;
            }
            $this->q = [$qq];
            $this->mod = $x;

            return;
        }

        $shift2 = floor(log($y[$t]) / M_LN2) + 1;
        $shift = $bs - $shift2;
        if ($shift) {
            $x = array_merge((array) $x);
            $y = array_merge((array) $y);
            for ($i = $t; $i > 0; $i--) {
                $y[$i] = (($y[$i] << $shift) & $bm) | ($y[$i - 1] >> $shift2);
            }
            $y[0] = ($y[0] << $shift) & $bm;
            if ($x[$n] & (($bm << $shift2) & $bm)) {
                $x[++$n] = 0;
                $nmt++;
            }
            for ($i = $n; $i > 0; $i--) {
                $x[$i] = (($x[$i] << $shift) & $bm) | ($x[$i - 1] >> $shift2);
            }
            $x[0] = ($x[0] << $shift) & $bm;
        }

        $q = zeros($nmt + 1);
        $y2 = array_merge(zeros($nmt), (array) $y);
        for (; ;) {
            $x2 = bsub($x, $y2);
            if (count($x2) == 0) {
                break;
            }
            $q[$nmt]++;
            $x = $x2;
        }

        $yt = $y[$t];
        $top = toppart($y, $t, 2);
        for ($i = $n; $i > $t; $i--) {
            $m = $i - $t - 1;
            if ($i >= count($x)) {
                $q[$m] = 1;
            } elseif ($x[$i] == $yt) {
                $q[$m] = $bm;
            } else {
                $q[$m] = floor(toppart($x, $i, 2) / $yt);
            }

            $topx = toppart($x, $i, 3);
            while ($q[$m] * $top > $topx) {
                $q[$m]--;
            }

            $y2 = array_slice($y2, 1);
            $x2 = bsub($x, bmul([$q[$m]], $y2));
            if (count($x2) == 0) {
                $q[$m]--;
                $x2 = bsub($x, bmul([$q[$m]], $y2));
            }
            $x = $x2;
        }

        if ($shift) {
            for ($i = 0; $i < count($x) - 1; $i++) {
                $x[$i] = ($x[$i] >> $shift) | (($x[$i + 1] << $shift2) & $bm);
            }
            $x[count($x) - 1] >>= $shift;
        }
        $n = count($q);
        while ($n > 1 && $q[$n - 1] == 0) {
            $n--;
        }
        $this->q = array_slice($q, 0, $n);
        $n = count($x);
        while ($n > 1 && $x[$n - 1] == 0) {
            $n--;
        }
        $this->mod = array_slice($x, 0, $n);
    }
}
