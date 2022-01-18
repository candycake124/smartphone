<?php
/**
 * @project: CustomerPassword
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

namespace LitExtension\CustomerPassword\Model\Type;

class Opencart extends \LitExtension\CustomerPassword\Model\Type {

    public function validatePassword($customerModel, $email, $string, $encrypted) {
        $part = explode(":", $encrypted);
        $sha1 = $part[0];
        $salt = isset($part[1]) ? $part[1] : '';
        if (!empty($salt)) {
            if (sha1($salt . sha1($salt . sha1($string))) == $sha1) {
                return true;
            } elseif (md5($string) == $sha1) {
                return true;
            }
        } else {
            if (md5($string) == $sha1) {
                return true;
            }
        }
        return false;
    }

}
