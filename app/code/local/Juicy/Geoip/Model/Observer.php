<?php

class Juicy_Geoip_Model_Observer
{
    public function controllerActionPredispatch($e)
    {
        if(Mage::helper("geoip")->isModuleEnabled() == 1 && !Mage::helper("geoip")->isPrivateIp()){
            if(Mage::helper("geoip")->isTestMode() == 1){
                Mage::getModel('core/session')->unsGeoipChecked();
            }
            $session = Mage::getModel('core/session')->getGeoipChecked();
            if(!isset($session) || $session == false){           
                
                $redirStore = Mage::getModel('geoip/geoip')->runGeoip();             
                if($redirStore){
                    $e->getControllerAction()->getResponse()->setRedirect($redirStore);
                }
                Mage::getSingleton("core/session")->setGeoipChecked(true);
            }
        }
    }
}