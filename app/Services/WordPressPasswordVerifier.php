<?php

namespace App\Services;

class WordPressPasswordVerifier
{
    /**
     * Verify a password against a WordPress phpass / bcrypt hash.
     */
    public static function check(string $password, string $hash): bool
    {
        if ($hash === '') {
            return false;
        }

        if (str_starts_with($hash, '$wp$')) {
            return password_verify($password, substr($hash, 3));
        }

        if (str_starts_with($hash, '$P$') || str_starts_with($hash, '$H$')) {
            return self::checkPortableHash($password, $hash);
        }

        return false;
    }

    protected static function checkPortableHash(string $password, string $hash): bool
    {
        if (strlen($hash) !== 34) {
            return false;
        }

        $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $count_log2 = strpos($itoa64, $hash[3]);

        if ($count_log2 === false || $count_log2 < 7 || $count_log2 > 30) {
            return false;
        }

        $count = 1 << $count_log2;
        $salt = substr($hash, 4, 8);

        if (strlen($salt) !== 8) {
            return false;
        }

        $hashed = md5($salt.$password, true);

        for ($i = 0; $i < $count; $i++) {
            $hashed = md5($hashed.$password, true);
        }

        $output = substr($hash, 0, 12).self::encode64($hashed, 16);

        return hash_equals($hash, $output);
    }

    protected static function encode64(string $input, int $count): string
    {
        $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $output = '';
        $i = 0;

        do {
            $value = ord($input[$i++]);
            $output .= $itoa64[$value & 0x3f];

            if ($i < $count) {
                $value |= ord($input[$i]) << 8;
            }

            $output .= $itoa64[($value >> 6) & 0x3f];

            if ($i++ >= $count) {
                break;
            }

            if ($i < $count) {
                $value |= ord($input[$i]) << 16;
            }

            $output .= $itoa64[($value >> 12) & 0x3f];

            if ($i++ >= $count) {
                break;
            }

            $output .= $itoa64[($value >> 18) & 0x3f];
        } while ($i < $count);

        return $output;
    }
}
