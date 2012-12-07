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
class Inchoo_TComPayWay_Model_Redirect extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'inchoo_tcompayway_redirect';

    protected $_isGateway                   = false; /* Seems like its used for capturing of funds, which we cannot do with redirect gateway. */
    protected $_canAuthorize                = true; /* Required to be true or else we wont be able to go trough checkout. */
    protected $_canUseInternal              = false; /* This method cannot be used from Magento admin area. */
    protected $_isInitializeNeeded          = true; /* If set to true, it calls initialize() instead of authorize() or capture(). */

    protected $_formBlockType = 'inchoo_tcompayway/redirect_form';
    protected $_infoBlockType = 'inchoo_tcompayway/redirect_info';

    /**
     * This function works with Mage_Checkout_Model_Type_Onepage -> saveOrder().
     *
     * Check the line that says:
     * $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
     *
     * Before the redirect to 3rd party payment system, order info is set into session:
     * $this->_checkoutSession->setLastOrderId($order->getId())
     *      ->setRedirectUrl($redirectUrl)
     *      ->setLastRealOrderId($order->getIncrementId());
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('inchoo_tcompayway/redirect/redirect', array('_secure' => true));
    }
    
    /**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return Mage_Payment_Model_Abstract
     */    
    public function initialize($paymentAction, $stateObject)
    {
        /**
         * This is just so that we do not get default 
         * "Customer Notification Not Applicable " 
         * message under order comments history.
         */
        $stateObject->setState(Mage_Sales_Model_Order::STATE_NEW);
        $stateObject->setStatus('pending');
        $stateObject->setIsNotified(false);
        
        return $this;
    }     
}