<?php
/**
 * @project: CustomerPassword
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

namespace LitExtension\CustomerPassword\Model\Type;

class Cart3d extends \LitExtension\CustomerPassword\Model\Type {

    public function validatePassword($customer, $username, $password, $pw_hash){
        $data = array(
            'email' => $username,
            'password' => $password,
        );
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $configModel = $objectManager->create('Magento\Config\Model\Config');
        $cart_url = $configModel->getConfigDataValue('lecupd/general/url');
        $check = $this->_request($cart_url, $data);
        return $check;
    }

    protected function _checkHeader($header) {
        preg_match('/Location:(.+)/', $header, $match);
        if ($match) {
            if (!strpos($match[1], 'error')) {
                return true;
            }
        }
        return false;
    }

    protected function _request($url, $data = array()) {
        $options = http_build_query($data);
        $ch = curl_init($url . '/login.asp');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $options);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response && $this->_checkHeader($response)) {
            return true;
        }
        return false;
    }
}