<?php
/**
 * @project: CustomerPassword
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

namespace LitExtension\CustomerPassword\Model\Type;

class Interspire extends \LitExtension\CustomerPassword\Model\Type {

    public function validatePassword($customer, $username, $password, $pw_hash){
        $part = explode(":", $pw_hash);
        if(count($part) < 2 || !$part[0] || !$part[1]){
            return false;
        }
        $pass_hash = $part[0];
        $salt = $part[1];
        $generate_pass = md5($salt.sha1($salt.$password));
        $generate_pass = substr($generate_pass, 0, 50);
        if($generate_pass == $pass_hash){
            return true;
        }
        return false;
    }

}