<?php

namespace LitExtension\CustomerPassword\Model;

class AccountManagement extends \Magento\Customer\Model\AccountManagement
{

    protected function _beforeAuthenticate($email, $pass) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $website_id = $objectManager->get('Magento\Store\Model\StoreManager')->getStore()->getWebsiteId();
        $customer = $objectManager->create('Magento\Customer\Model\Customer')->setWebsiteId($website_id)->loadByEmail($email);
        if(!$customer){
            return False;
        }
        $iterator = new \DirectoryIterator(__DIR__ . '/Type');
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->getExtension() != 'php') {
                continue;
            }

            $className = "LitExtension\\CustomerPassword\\Model\\Type\\" . $fileInfo->getBasename('.php');
            try{
                $model = $objectManager->create($className);
                if(!$model){
                    continue;
                }
                $check = $model->run($customer, $email, $pass);
                if($check){
                    return True;
                }
            }catch (\Exception $e){
                continue;
            }

        }
    }

    public function authenticate($username, $password) {
        $this->_beforeAuthenticate($username, $password);
        $customer = parent::authenticate($username, $password);
        return $customer;
    }

}