<?php
/**
 * @project: CustomerPassword
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

namespace LitExtension\CustomerPassword\Model\Type;

class Loaded extends \LitExtension\CustomerPassword\Model\Type {
    
    public function validatePassword($customer, $username, $password, $encrypted) {
        if (strstr($encrypted, '::')) {  // sha256 hash
            $stack = explode('::', $encrypted);
            if (sizeof($stack) === 2) {
                return ( hash('sha256', $stack[1] . $password) == $stack[0] );
            }
        } else { // legacy md5 hash - will be removed in production release           
            $stack = explode(':', $encrypted);
            if (sizeof($stack) === 2) {
                return ( md5($stack[1] . $password) == $stack[0] );
            }
        }
    }
}