<?php

class Inchoo_TComPayWay_Block_Redirect_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
    	parent::_construct();
    }

    protected function _toHtml()
    {
        $helper = Mage::helper('inchoo_tcompayway/redirect');
        
        $info = $helper->getPaymentProgressInformation();
        
        if (!empty($info)) {
            return $info;
        }
        
        return $this->htmlEscape($this->getMethod()->getTitle());
    }
}
