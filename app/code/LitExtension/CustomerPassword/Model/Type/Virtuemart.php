<?php
/**
 * @project: CustomerPassword
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

namespace LitExtension\CustomerPassword\Model\Type;

class Virtuemart extends \LitExtension\CustomerPassword\Model\Type {

    public function validatePassword($customer, $username, $password, $pw_hash){
        $hash = new VmPasswordHash(10, true);
        $check = $hash->verifyPassword($password, $pw_hash);
        return $check;
    }
}

class VmPasswordHash
{
    var $_itoa64;
    var $_iteration_count_log2;
    var $_portable_hashes;
    var $_random_state;

    function VmPasswordHash($iteration_count_log2, $portable_hashes)
    {
        $this->_itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31)
            $iteration_count_log2 = 8;
        $this->_iteration_count_log2 = $iteration_count_log2;

        $this->_portable_hashes = $portable_hashes;

        $this->_random_state = microtime();
        if (function_exists('getmypid'))
            $this->_random_state .= getmypid();
    }

    function crypt_private($password, $setting)
    {
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

    function encode64($input, $count)
    {
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

    function CheckPassword($password, $stored_hash)
    {
        $hash = $this->crypt_private($password, $stored_hash);
        if ($hash[0] == '*')
            $hash = crypt($password, $stored_hash);

        return $hash == $stored_hash;
    }

    public function verifyPassword($password, $hash, $user_id = 0)
    {
        $rehash = false;
        $match = false;

        // If we are using phpass
        if (strpos($hash, chr(36).chr(80).chr(36)) === 0)
        {
            // Use PHPass's portable hashes with a cost of 10.
            $phpass = new VmPasswordHash(10, true);

            $match = $phpass->CheckPassword($password, $hash);

            $rehash = false;
        }
        else
        {
            // Check the password
            $parts = explode(':', $hash);
            $crypt = $parts[0];
            $salt  = @$parts[1];

            $rehash = true;

            $testcrypt = md5($password . $salt) . ($salt ? ':' . $salt : '');

            $match = $this->timingSafeCompare($hash, $testcrypt);
        }

        return $match;
    }

    public function timingSafeCompare($known, $unknown)
    {
        // Prevent issues if string length is 0
        $known .= chr(0);
        $unknown .= chr(0);

        $knownLength = strlen($known);
        $unknownLength = strlen($unknown);

        // Set the result to the difference between the lengths
        $result = $knownLength - $unknownLength;

        // Note that we ALWAYS iterate over the user-supplied length to prevent leaking length info.
        for ($i = 0; $i < $unknownLength; $i++)
        {
            // Using % here is a trick to prevent notices. It's safe, since if the lengths are different, $result is already non-0
            $result |= (ord($known[$i % $knownLength]) ^ ord($unknown[$i]));
        }

        // They are only identical strings if $result is exactly 0...
        return $result === 0;
    }
}

