<?php

define("GEOIP_STANDARD", 0);

class Juicy_Geoip_Model_Geoip
{   
    
    public function runGeoip(){
        $countryCode = $this->_getCountryCode();
        $pairArr = $this->_getPairArray();
        foreach($pairArr as $searchArr){
            if(in_array($countryCode, $searchArr)){
                $this->_setCurrency($searchArr);
                //This method returns the redirect for store if it needs one  
                //@TODO Make this a bit nicer
                return $this->_setStore($searchArr);
            }
        }
    }
    protected function _setCurrency($searchArr)
    {
        if(Mage::helper('geoip')->canSwitch("currency")){
            Mage::app()->getStore()->setCurrentCurrencyCode(next($searchArr));
        }
    }
    protected function _setStore($searchArr)
    {
        if(Mage::helper('geoip')->canSwitch("store")){
            $storeName = Mage::app()->getStore($searchArr['store'])->getName();
            if ($storeName) {
                $store = Mage::getModel('core/store')->load($storeName, 'name');
                if ($store->getName() != Mage::app()->getStore()->getName()) {
                    //Needs to return store URL for observer to redirect using event
                    return $store->getCurrentUrl(false);
                }
            }
        }
    }
    protected function _getCountryCode()
    {
        return Mage::helper('geoip')->isTestMode() 
                                        ? Mage::helper('geoip')->testOverrideCountry()
                                        : $this->_getCountryCodeFromIp($this->_getIp());
    }
    protected function _getPairArray()
    {
        return unserialize(Mage::getStoreConfig('geoip/geoipset/ippair', Mage::app()->getStore()));
    }
    
    protected function _getIp(){
        //Using Mage HTTP helper because Varnish can confuse PHP method
        return Mage::helper('core/http')->getRemoteAddr();
    }
    protected function _getCountryCodeFromIp($ip){    
        //If its 0, that means we're using Apache else we're using a file
        if(Mage::helper('geoip')->getConfig('general/apache_or_file') == 0){
            return geoip_country_code_by_name($ip);            
        }else{
            /*
            try{   
                $fileName = Mage::helper('geoip')->getConfig('general/file_location');
             * 
             * 
                return $country;
            }catch(Exception $e){
                Mage::throwException($e->getMessage());
            }
            */
            return geoip_country_code_by_name($ip); 
        }        
    }
}

