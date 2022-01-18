<?php
/**
 * @project: CustomerPassword
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

namespace LitExtension\CustomerPassword\Model\Type;

class Zencart extends \LitExtension\CustomerPassword\Model\Type {

    public function validatePassword($customer, $username, $password, $pw_hash){
        $hash = new zcPassword();
        $check = $hash->CheckPassword($password, $pw_hash);
        return $check;
    }

}

class zcPassword
{
    public function validatePassword($plain, $encrypted)
    {
        $type = $this->detectPasswordType($encrypted);
        if ($type != 'unknown') {
            $method = 'validatePassword' . ucfirst($type);
            return $this->{$method}($plain, $encrypted);
        }
        $result = $this->password_verify($plain, $encrypted);
        return $result;
    }

    /**
     * validate a legacy md5 type password
     *
     * @param string $plain
     * @param string $encrypted
     * @return boolean
     */
    public function validatePasswordOldMd5($plain, $encrypted)
    {
        if ($this->zen_not_null($plain) && $this->zen_not_null($encrypted)) {
            $stack = explode(':', $encrypted);
            if (sizeof($stack) != 2)
                return false;
            if (md5($stack [1] . $plain) == $stack [0]) {
                return true;
            }
        }
        return false;
    }
    /**
     * validate a SHA256 type password
     *
     * @param string $plain
     * @param string $encrypted
     * @return boolean
     */
    public function validatePasswordCompatSha256($plain, $encrypted)
    {
        if ($this->zen_not_null($plain) && $this->zen_not_null($encrypted)) {
            $stack = explode(':', $encrypted);
            if (sizeof($stack) != 2)
                return false;
            if (hash('sha256', $stack [1] . $plain) == $stack [0]) {
                return true;
            }
        }
        return false;
    }

    function zen_not_null($value) {
        if (is_array($value)) {
            if (sizeof($value) > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            if (($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)) {
                return true;
            } else {
                return false;
            }
        }
    }

    function detectPasswordType($encryptedPassword)
    {
        $type = 'unknown';
        $tmp = explode(':', $encryptedPassword);
        if (count($tmp) == 2) {
            if (strlen($tmp [1]) > 2) {
                $type = 'compatSha256';
            } elseif (strlen($tmp [1]) == 2) {
                $type = 'oldMd5';
            }
        }
        return $type;
    }

    function password_verify($password, $hash) {
        $ret = crypt($password, $hash);
        if (!is_string($ret) || strlen($ret) != strlen($hash) || strlen($ret) <= 13) {
            return false;
        }

        $status = 0;
        for ($i = 0; $i < strlen($ret); $i++) {
            $status |= (ord($ret[$i]) ^ ord($hash[$i]));
        }

        return $status === 0;
    }

    function CheckPassword($password, $store_hash){
        $check = $this->validatePassword($password, $store_hash);
        if($check){
            return $store_hash;
        }
        $salt = substr($store_hash, strrpos($store_hash,':')+1, 2);
        $zc_password = md5($salt . $password) . ':' . $salt;
        if($zc_password == $store_hash){
            return $store_hash;
        }
        return false;
    }
}
