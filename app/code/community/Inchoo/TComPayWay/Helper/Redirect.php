<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Inchoo
 * @package     Inchoo_TComPayWay
 * @copyright   Copyright (c) Branko Ajzele
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
class Inchoo_TComPayWay_Helper_Redirect extends Mage_Payment_Helper_Data
{
    const XML_PATH_SHOP_ID = 'payment/inchoo_tcompayway_redirect/shop_id';
    const XML_PATH_SECRET_KEY = 'payment/inchoo_tcompayway_redirect/secret_key';
    const XML_PATH_DEBUG_MODE = 'payment/inchoo_tcompayway_redirect/debug_mode';
    const XML_PATH_POST_URL = 'payment/inchoo_tcompayway_redirect/post_url';
    const XML_PATH_ORDER_STATUS = 'payment/inchoo_tcompayway_redirect/order_status';
    const XML_PATH_PAYED_ORDER_STATUS = 'payment/inchoo_tcompayway_redirect/payed_order_status';
    const XML_PATH_CANCELED_ORDER_STATUS = 'payment/inchoo_tcompayway_redirect/canceled_order_status';
    const XML_PATH_PAYMENT_TYPE = 'payment/inchoo_tcompayway_redirect/payment_type';
    const XML_PATH_AUTO_INVOICE_PAYED_ORDER = 'payment/inchoo_tcompayway_redirect/auto_invoice_payed_order';
    const XML_PATH_AUTO_SHIP_INVOICED_ORDER = 'payment/inchoo_tcompayway_redirect/auto_ship_invoiced_order';
    const XML_PATH_PAYMENT_STEP_INFORMATION = 'payment/inchoo_tcompayway_redirect/payment_step_information';
    const XML_PATH_PAYMENT_PROGRESS_INFORMATION = 'payment/inchoo_tcompayway_redirect/payment_progress_information';
    
    public function getShortLocaleCode()
    {
        $loc = substr(Mage::app()->getLocale()->getLocaleCode(), 3, 2);

        if($loc != 'HR') {
            $loc = '';
        }

        return $loc;
    }

    public function getShopId()
    {
        return Mage::getStoreConfig(self::XML_PATH_SHOP_ID);
    }

    public function getSecretKey()
    {
        return Mage::getStoreConfig(self::XML_PATH_SECRET_KEY);
    }

    public function encode($shopId, $shoppingCartId, $totalAmount)
    {
        $sk = $this->getSecretKey();
        $sig = $shopId . $sk . $shoppingCartId . $sk . $totalAmount . $sk;

        $sigEncrypted = md5($sig);

        return $sigEncrypted;
    }

    public function encodeResponse($shopId, $shoppingCartId, $totalAmount, $tid)
    {
        $sk = $this->getSecretKey();
        $sig = $shopId . $sk . $shoppingCartId . $sk . $totalAmount . $sk . $tid . $sk;
        $sigEncrypted = md5($sig);
        return $sigEncrypted;
    }

    public function replaceCroatianChars($text)
    {
        $hr_chars = array('č', 'ć', 'đ', 'š', 'ž', 'Č', 'Ć', 'Đ', 'Š', 'Ž');
        $ascii_chars = array('c', 'c', 'dj', 's', 'z', 'C', 'C', 'Dj', 'S', 'Z');
        $result = str_replace($hr_chars, $ascii_chars, $text);

        return $result;
    }

    public function init()
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
        $this->order = $order;

        return $order;
    }

    public function formatPrice($price)
    {
        $pricef = floatval($price);
        $result = number_format ($pricef, 2 , ',' , '');
        return $result;
    }

    public function getDebugMode()
    {
        return (bool)((int)Mage::getStoreConfig(self::XML_PATH_DEBUG_MODE));
    }

    public function getPostUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_POST_URL);
    }

    public function getOrderStatusConfig()
    {
        return Mage::getStoreConfig(self::XML_PATH_ORDER_STATUS);
    }
    
    public function getPaymentStepInformation()
    {
        return Mage::getStoreConfig(self::XML_PATH_PAYMENT_STEP_INFORMATION);
    }
    
    public function getPaymentProgressInformation()
    {
        return Mage::getStoreConfig(self::XML_PATH_PAYMENT_PROGRESS_INFORMATION);
    }    
    
    public function getPayedOrderStatus() 
    {
            return Mage::getStoreConfig(self::XML_PATH_PAYED_ORDER_STATUS);
    }
    
    public function getCanceledOrderStatus() 
    {
            return Mage::getStoreConfig(self::XML_PATH_CANCELED_ORDER_STATUS);
    }
    
    public function getAutomaticallyInvoicePayedOrder() 
    {
        if ($this->getPaymentType() === Inchoo_TComPayWay_Model_System_Config_Source_Payment_Type::TYPE_MANUAL) {
            return false;
        }
        
        return (bool)((int)Mage::getStoreConfig(self::XML_PATH_AUTO_INVOICE_PAYED_ORDER));
    }
    
    public function getAutomaticallyShipInvoicedOrder() 
    {
        if (!$this->getAutomaticallyInvoicePayedOrder()) {
            return false;
        }
        
        return (bool)((int)Mage::getStoreConfig(self::XML_PATH_AUTO_SHIP_INVOICED_ORDER));
    }   
    
    public function getPaymentType() 
    {
            return Mage::getStoreConfig(self::XML_PATH_PAYMENT_TYPE);
    }
}
