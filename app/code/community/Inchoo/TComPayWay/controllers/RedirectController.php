<?php
/**
 * @copyright   Copyright (c) Branko Ajzele
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
class Inchoo_TComPayWay_RedirectController extends Mage_Core_Controller_Front_Action
{
    /**
     * When a customer chooses TComPayWay Redirect on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('inchoo_tcompayway/redirect_form_submit')->toHtml());
    }

    /**
     * When a customer cancel payment from TComPayWay Redirect.
     */
    public function cancelAction()
    {
        $helper = Mage::helper('inchoo_tcompayway/redirect');

        $session = Mage::getSingleton('checkout/session');
        
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
                    $order->cancel()->save();
                
                    $comment = $helper->__('Order intentionally canceled by customer through PayWay system.');
                    
                    $historyItem = Mage::getResourceModel('sales/order_status_history_collection')
                                        ->setOrderFilter($order)
                                        ->setOrder('created_at', 'desc')
                                        ->addFieldToFilter('entity_name', Mage_Sales_Model_Order::HISTORY_ENTITY_NAME)
                                        ->addFieldToFilter('status', Mage_Sales_Model_Order::STATE_CANCELED)
                                        ->setPageSize(1)
                                        ->getFirstItem();
                    
                    if ($historyItem) {
                        $historyItem->setComment($comment);
                        $historyItem->setIsCustomerNotified(0);
                        $historyItem->save();
                    }              
                
                Mage::getSingleton('core/session')
                    ->addNotice($helper->__('Order %s has been successfully canceled!', $order->getIncrementId()));
            }
        }

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->_redirect('sales/order/history');
        } else {
            $this->_redirect('checkout/cart');
        }
    }

    public function  successAction()
    {
        $helper = Mage::helper('inchoo_tcompayway/redirect');
        
        $session = Mage::getSingleton('checkout/session');
        
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());

            if ($order && $order->getIncrementId() == $session->getLastRealOrderId()) {
                
                $allowedOrderStates = Mage::getModel('inchoo_tcompayway/system_config_source_order_status')->getAvailableStates();
                
                if (in_array($order->getState(), $allowedOrderStates)) {
                    $session->unsLastRealOrderId();
                    
                    $sig = $this->getRequest()->getParam('sig'); /* signature from payway */
                    $tid = $this->getRequest()->getParam('tid');
                    $card = $this->getRequest()->getParam('card');

                    if(empty($sig) || empty($tid) || empty($card)) {
                        Mage::getSingleton('core/session')->addNotice($helper->__('Missing required parameters from PayWay.'));
                        $this->_redirect('no-route');
                        return;
                    }
                    
                    $shopID = $helper->getShopId();

                    $shoppingCartID = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST']:'');
                    $shoppingCartID = str_replace('.', '', $shoppingCartID);
                    $shoppingCartID .= $order->getData('increment_id');
                    
                    $totalAmount = $helper->formatPrice($order->getData('grand_total'));
                    $signature = $helper->encodeResponse($shopID, $shoppingCartID, $totalAmount, $tid);

                    if(strcmp(strtoupper($sig), strtoupper($signature)) != 0) {
                        Mage::getSingleton('core/session')->addError($helper->__('PayWay transaction signature mismatch.'));
                        $this->_redirect('no-route');
                        return;
                    }

                    $comment = $helper->__('PayWay system successfully charged %s card, transaction signature id %s.', $card, $sig);
                    
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
                    $order->setStatus($helper->getOrderStatusConfig());
                    /* Mage_Sales_Model_Order -> addStatusToHistory($status, $comment = '', $isCustomerNotified = false) */
                    $order->addStatusToHistory($helper->getPayedOrderStatus(), $comment, true);
                    
                    $order->save();
                    
                    if ($order->getId()) {
                        $order->sendNewOrderEmail();
                    }
                    
                    
                    if($helper->getAutomaticallyInvoicePayedOrder()) {
                        try {
                            if ($order->canInvoice()) {
                                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                                $invoice->register();
                                $invoice->getOrder()->setCustomerNoteNotify(false);
                                $invoice->getOrder()->setIsInProcess(true);
                                $order->addStatusHistoryComment('Automatically invoiced as per config option setup.', false);

                                $transactionSave = Mage::getModel('core/resource_transaction')
                                        ->addObject($invoice)
                                        ->addObject($invoice->getOrder());

                                $transactionSave->save();

                                if ($helper->getAutomaticallyShipInvoicedOrder()) {
                                    $shipment = $order->prepareShipment();
                                    $shipment->register();
                                    $order->setIsInProcess(true);
                                    $order->addStatusHistoryComment('Automatically shipped as per config option setup.', false);

                                    $transactionSave = Mage::getModel('core/resource_transaction')
                                            ->addObject($shipment)
                                            ->addObject($shipment->getOrder())
                                            ->save();          
                                }
                            }
                        } catch (Exception $e) {
                            $order->addStatusHistoryComment('Inchoo_Invoicer: Exception occurred during automaticallyInvoiceShipCompleteOrder action. Exception message: ' . $e->getMessage(), false);
                            $order->save();
                        }
                    }                    
                    
                    $this->_redirect('checkout/onepage/success', array('_secure'=>true));
                    return;
                } else {
                    Mage::getSingleton('core/session')->addError($helper->__('Current order state does not allow this action.'));
                    $this->_redirect('no-route');
                    return;                    
                }
            }
        }
        
        Mage::getSingleton('core/session')->addError($helper->__('Order information could not be found. Either cookie/session was destroyed or you accessed this link directly.'));    
        $this->_redirect('no-route');
        return;
    }
}
