<?php
/**
 * @project: CustomerPassword
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

namespace LitExtension\CustomerPassword\Model\Type;

class Oxideshop extends \LitExtension\CustomerPassword\Model\Type {

    public function validatePassword($customerModel, $email, $string, $encrypted) {       		
		///
		$part = explode(":", $encrypted);
        if(!$part[1]){
            return false;
        }
        $password_hash = $part[0];
        $hash = $part[1];
        if($password_hash == hash('sha512', $string.$hash)){
            return true;
        }elseif($password_hash == md5($string . strval(@hex2bin($hash)))){
            return true;
        }
        return false;
    }

}
