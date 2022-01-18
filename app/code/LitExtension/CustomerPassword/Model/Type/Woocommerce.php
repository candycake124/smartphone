<?php
/**
 * @project: CustomerPassword
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

namespace LitExtension\CustomerPassword\Model\Type;

class Woocommerce extends \LitExtension\CustomerPassword\Model\Type {

    public function validatePassword($customer, $username, $password, $pw_hash){
        $hash = new WooPasswordHash(10, true);
        $check = $hash->CheckPassword($password, $pw_hash);
        return $check;
    }
}

class WooPasswordHash {

    var $_itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    var $_iteration_count_log2;
    var $_portable_hashes;
    var $_random_state;

    function PasswordHash($iteration_count_log2, $portable_hashes) {
        $this->_itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31)
            $iteration_count_log2 = 8;
        $this->_iteration_count_log2 = $iteration_count_log2;

        $this->_portable_hashes = $portable_hashes;

        $this->_random_state = microtime();
        if (function_exists('getmypid'))
            $this->_random_state .= getmypid();
    }

    function get_random_bytes($count) {
        $output = '';
        if (@is_readable('/dev/urandom') &&
            ($fh = @fopen('/dev/urandom', 'rb'))) {
            if (function_exists('stream_set_read_buffer')) {
                stream_set_read_buffer($fh, 0);
            }
            $output = fread($fh, $count);
            fclose($fh);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $output = openssl_random_pseudo_bytes($count, $orpb_secure);

            if ($orpb_secure != true) {
                $output = '';
            }
        } elseif (defined('MCRYPT_DEV_URANDOM')) {
            $output = mcrypt_create_iv($count, MCRYPT_DEV_URANDOM);
        }

        if (strlen($output) < $count) {
            $output = '';
            for ($i = 0; $i < $count; $i += 16) {
                $this->_random_state = md5(microtime() . $this->_random_state);
                $output .=
                    pack('H*', md5($this->_random_state));
            }
            $output = substr($output, 0, $count);
        }

        return $output;
    }

    function encode64($input, $count) {
        $output = '';
        $i = 0;
        do {
            $value = ord($input[$i++]);
            $output .= $this->_itoa64[$value & 0x3f];
            if ($i < $count)
                $value |= ord($input[$i]) << 8;
            $output .= $this->_itoa64[($value >> 6) & 0x3f];
            if ($i++ >= $count)
                break;
            if ($i < $count)
                $value |= ord($input[$i]) << 16;
            $output .= $this->_itoa64[($value >> 12) & 0x3f];
            if ($i++ >= $count)
                break;
            $output .= $this->_itoa64[($value >> 18) & 0x3f];
        } while ($i < $count);

        return $output;
    }

    function gensalt_private($input) {
        $output = chr(36).chr(80).chr(36);
        $output .= $this->_itoa64[min($this->_iteration_count_log2 +
            ((PHP_VERSION >= '5') ? 5 : 3), 30)];
        $output .= $this->encode64($input, 6);

        return $output;
    }

    function crypt_private($password, $setting) {
        $output = '*0';
        if (substr($setting, 0, 2) == $output)
            $output = '*1';

        $id = substr($setting, 0, 3);
        # We use "$P$", phpBB3 uses "$H$" for the same thing
        if ($id != chr(36).chr(80).chr(36) && $id != chr(36).chr(72).chr(36))
            return $output;

        $count_log2 = strpos($this->_itoa64, $setting[3]);

        if ($count_log2 < 7 || $count_log2 > 30)
            return $output;
        $count = 1 << $count_log2;

        $salt = substr($setting, 4, 8);
        if (strlen($salt) != 8)
            return $output;

        # We're kind of forced to use MD5 here since it's the only
        # cryptographic primitive available in all versions of PHP
        # currently in use.  To implement our own low-level crypto
        # in PHP would result in much worse performance and
        # consequently in lower iteration counts and hashes that are
        # quicker to crack (by non-PHP code).
        if (PHP_VERSION >= '5') {
            $hash = md5($salt . $password, TRUE);
            do {
                $hash = md5($hash . $password, TRUE);
            } while (--$count);
        } else {
            $hash = pack('H*', md5($salt . $password));
            do {
                $hash = pack('H*', md5($hash . $password));
            } while (--$count);
        }

        $output = substr($setting, 0, 12);
        $output .= $this->encode64($hash, 16);

        return $output;
    }

    function gensalt_extended($input) {
        $count_log2 = min($this->_iteration_count_log2 + 8, 24);
        # This should be odd to not reveal weak DES keys, and the
        # maximum valid value is (2**24 - 1) which is odd anyway.
        $count = (1 << $count_log2) - 1;

        $output = '_';
        $output .= $this->_itoa64[$count & 0x3f];
        $output .= $this->_itoa64[($count >> 6) & 0x3f];
        $output .= $this->_itoa64[($count >> 12) & 0x3f];
        $output .= $this->_itoa64[($count >> 18) & 0x3f];

        $output .= $this->encode64($input, 3);

        return $output;
    }

    function gensalt_blowfish($input) {
        # This one needs to use a different order of characters and a
        # different encoding scheme from the one in encode64() above.
        # We care because the last character in our encoded string will
        # only represent 2 bits.  While two known implementations of
        # bcrypt will happily accept and correct a salt string which
        # has the 4 unused bits set to non-zero, we do not want to take
        # chances and we also do not want to waste an additional byte
        # of entropy.
        $itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        $output = chr(36).chr(50).chr(97).chr(36);
        $output .= chr(ord('0') + $this->_iteration_count_log2 / 10);
        $output .= chr(ord('0') + $this->_iteration_count_log2 % 10);
        $output .= chr(36);

        $i = 0;
        do {
            $c1 = ord($input[$i++]);
            $output .= $itoa64[$c1 >> 2];
            $c1 = ($c1 & 0x03) << 4;
            if ($i >= 16) {
                $output .= $itoa64[$c1];
                break;
            }

            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 4;
            $output .= $itoa64[$c1];
            $c1 = ($c2 & 0x0f) << 2;

            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 6;
            $output .= $itoa64[$c1];
            $output .= $itoa64[$c2 & 0x3f];
        } while (1);

        return $output;
    }

    function HashPassword($password) {
        $random = '';

        if (CRYPT_BLOWFISH == 1 && !$this->_portable_hashes) {
            $random = $this->get_random_bytes(16);
            $hash = crypt($password, $this->gensalt_blowfish($random));
            if (strlen($hash) == 60)
                return $hash;
        }

        if (CRYPT_EXT_DES == 1 && !$this->_portable_hashes) {
            if (strlen($random) < 3)
                $random = $this->get_random_bytes(3);
            $hash = crypt($password, $this->gensalt_extended($random));
            if (strlen($hash) == 20)
                return $hash;
        }

        if (strlen($random) < 6)
            $random = $this->get_random_bytes(6);
        $hash = $this->crypt_private($password, $this->gensalt_private($random));
        if (strlen($hash) == 34)
            return $hash;

        # Returning '*' on error is safe here, but would _not_ be safe
        # in a crypt(3)-like function used _both_ for generating new
        # hashes and for validating passwords against existing hashes.
        return '*';
    }

    function CheckPassword($password, $stored_hash) {
        $hash = $this->crypt_private($password, $stored_hash);
        if ($hash[0] == '*')
            $hash = crypt($password, $stored_hash);

        return $hash == $stored_hash;
    }

}