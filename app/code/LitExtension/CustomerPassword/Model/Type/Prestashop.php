<?php
/**
 * @project: CustomerPassword
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

namespace LitExtension\CustomerPassword\Model\Type;

class Prestashop extends \LitExtension\CustomerPassword\Model\Type {

    public function validatePassword($customer, $username, $string, $encrypted) {
        $part = explode(":", $encrypted);
        $pass = $part[0];
        $cookie_key = isset($part[1]) ? $part[1] : '';
        if(password_verify($string, $pass)) {
            return true;
        }
        if (!$cookie_key) {
            return false;
        }
        if (md5($cookie_key . $string) == $pass) {
            return true;
        }
        return false;
    }
}