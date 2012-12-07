<?php

class Inchoo_TComPayWay_Block_Redirect_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
    	parent::_construct();
    }

    protected function _toHtml()
    {
        $helper = Mage::helper('inchoo_tcompayway/redirect');
        
        $info = $helper->getPaymentStepInformation();
        
        if (!empty($info)) {
            return $info;
        }
        
        return '';
    }
}
