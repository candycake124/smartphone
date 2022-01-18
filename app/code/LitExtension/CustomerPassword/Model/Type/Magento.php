<?php
/**
 * @project: CustomerPassword
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

namespace LitExtension\CustomerPassword\Model\Type;

class Magento extends \LitExtension\CustomerPassword\Model\Type {

    public function validatePassword($customerModel, $email, $string, $encrypted) {
        $hashArr = explode(':', $encrypted);
        switch (count($hashArr)) {
            case 1:
                return $this->hash($string) === $encrypted;
            case 2:
                return $this->hash($hashArr[1] . $string) === $hashArr[0] || $this->hash256($hashArr[1] . $string) === $hashArr[0] || $this->getArgonHash($string, $hashArr[1]) === $hashArr[0];
        }
        return false;
    }
    
    public function hash($data)
    {
        return md5($data);
    }
	
	public function hash256($data)
	{
        return hash('sha256', $data);
    }
    public function getArgonHash($data, $salt = ''): string
    {
        $salt = empty($salt) ?
            random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES) :
            substr($salt, 0, SODIUM_CRYPTO_PWHASH_SALTBYTES);

        if (strlen($salt) < SODIUM_CRYPTO_PWHASH_SALTBYTES) {
            $salt = str_pad($salt, SODIUM_CRYPTO_PWHASH_SALTBYTES, $salt);
        }

        return bin2hex(
            sodium_crypto_pwhash(
                SODIUM_CRYPTO_SIGN_SEEDBYTES,
                $data,
                $salt,
                SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
                SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE,
                2
            )
        );
    }
}
