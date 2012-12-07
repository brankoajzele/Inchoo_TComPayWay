<?php

class Inchoo_TComPayWay_Model_System_Config_Source_Payment_Type
{
    const TYPE_MANUAL = 'manual';
    const TYPE_AUTO = 'auto';

    public function toOptionArray()
    {
        $options = array(
            array('value' => self::TYPE_MANUAL, 'label' => Mage::helper('inchoo_tcompayway/redirect')->__('Manual')),
            array('value' => self::TYPE_AUTO, 'label' => Mage::helper('inchoo_tcompayway/redirect')->__('Auto')),
        );
        
        return $options;
    }
}
