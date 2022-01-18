<?php

namespace LitExtension\CustomerPassword\Model\Type;
class Drupalcommerce extends \LitExtension\CustomerPassword\Model\Type {
    public function validatePassword($customer, $username, $password, $pw_hash){
        if (substr($pw_hash, 0, 2) == 'U$') {
            $stored_hash = substr($pw_hash, 1);
            $password = md5($password);
        }
        else {
            $stored_hash = $pw_hash;
        }

        $type = substr($stored_hash, 0, 3);
        switch ($type) {
            case '$S$':
                // A normal Drupal 7 password using sha512.
                $hash = $this->_password_crypt('sha512', $password, $stored_hash);
                break;
            case '$H$':
                // phpBB3 uses "$H$" for the same thing as "$P$".
            case '$P$':
                // A phpass password generated using md5.  This is an
                // imported password or from an earlier Drupal version.
                $hash = $this->_password_crypt('md5', $password, $stored_hash);
                break;
            default:
                return FALSE;
        }
        return ($hash && $stored_hash == $hash);
    }

    protected function _password_crypt($algo, $password, $setting) {
        // Prevent DoS attacks by refusing to hash large passwords.
        if (strlen($password) > 512) {
            return FALSE;
        }
        // The first 12 characters of an existing hash are its setting string.
        $setting = substr($setting, 0, 12);

        if ($setting[0] != '$' || $setting[2] != '$') {
            return FALSE;
        }
        $count_log2 = $this->_password_get_count_log2($setting);
        // Hashes may be imported from elsewhere, so we allow != DRUPAL_HASH_COUNT
        if ($count_log2 < 7 || $count_log2 > 30) {
            return FALSE;
        }
        $salt = substr($setting, 4, 8);
        // Hashes must have an 8 character salt.
        if (strlen($salt) != 8) {
            return FALSE;
        }

        // Convert the base 2 logarithm into an integer.
        $count = 1 << $count_log2;

        // We rely on the hash() function being available in PHP 5.2+.
        $hash = hash($algo, $salt . $password, TRUE);
        do {
            $hash = hash($algo, $hash . $password, TRUE);
        } while (--$count);

        $len = strlen($hash);
        $output =  $setting . $this->_password_base64_encode($hash, $len);
        // _password_base64_encode() of a 16 byte MD5 will always be 22 characters.
        // _password_base64_encode() of a 64 byte sha512 will always be 86 characters.
        $expected = 12 + ceil((8 * $len) / 6);
        return (strlen($output) == $expected) ? substr($output, 0, 55) : FALSE;
    }

    protected function _password_get_count_log2($setting) {
        $itoa64 = $this->_password_itoa64();
        return strpos($itoa64, $setting[3]);
    }

    protected function _password_itoa64() {
        return './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    }

    protected function _password_base64_encode($input, $count) {
        $output = '';
        $i = 0;
        $itoa64 = $this->_password_itoa64();
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