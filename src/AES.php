<?php

namespace nicoSWD\GPG;

class AES
{
    /**
     * @param $block
     * @param $ctx
     * @return array
     */
    public static function encrypt($block, $ctx)
    {
        $T1 = Cipher::$T1;
        $T2 = Cipher::$T2;
        $T3 = Cipher::$T3;
        $T4 = Cipher::$T4;

        $b = Utility::pack_octets($block);
        $rounds = $ctx->rounds;
        $b0 = $b[0];
        $b1 = $b[1];
        $b2 = $b[2];
        $b3 = $b[3];

        for ($r = 0; $r < $rounds - 1; $r++) {
            $t0 = $b0 ^ $ctx->rk[$r][0];
            $t1 = $b1 ^ $ctx->rk[$r][1];
            $t2 = $b2 ^ $ctx->rk[$r][2];
            $t3 = $b3 ^ $ctx->rk[$r][3];

            $b0 = $T1[$t0 & 255] ^ $T2[($t1 >> 8) & 255] ^ $T3[($t2 >> 16) & 255] ^ $T4[Utility::zshift($t3, 24)];
            $b1 = $T1[$t1 & 255] ^ $T2[($t2 >> 8) & 255] ^ $T3[($t3 >> 16) & 255] ^ $T4[Utility::zshift($t0, 24)];
            $b2 = $T1[$t2 & 255] ^ $T2[($t3 >> 8) & 255] ^ $T3[($t0 >> 16) & 255] ^ $T4[Utility::zshift($t1, 24)];
            $b3 = $T1[$t3 & 255] ^ $T2[($t0 >> 8) & 255] ^ $T3[($t1 >> 16) & 255] ^ $T4[Utility::zshift($t2, 24)];
        }

        $r = $rounds - 1;

        $t0 = $b0 ^ $ctx->rk[$r][0];
        $t1 = $b1 ^ $ctx->rk[$r][1];
        $t2 = $b2 ^ $ctx->rk[$r][2];
        $t3 = $b3 ^ $ctx->rk[$r][3];

        $b[0] = Cipher::F1($t0, $t1, $t2, $t3) ^ $ctx->rk[$rounds][0];
        $b[1] = Cipher::F1($t1, $t2, $t3, $t0) ^ $ctx->rk[$rounds][1];
        $b[2] = Cipher::F1($t2, $t3, $t0, $t1) ^ $ctx->rk[$rounds][2];
        $b[3] = Cipher::F1($t3, $t0, $t1, $t2) ^ $ctx->rk[$rounds][3];

        return Utility::unpack_octets($b);
    }
}
