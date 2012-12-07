<?php

class Inchoo_TComPayWay_Block_Redirect_Form_Submit extends Mage_Core_Block_Template
{
    protected function _construct()
    {
    	parent::_construct();
    }

    protected function _toHtml()
    {
        $helper = Mage::helper('inchoo_tcompayway/redirect');

        $session = Mage::getSingleton('checkout/session');

        $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());

        $billingAddress = Mage::getModel('sales/order_address')
            ->load($order->getBillingAddressId());

        $shopID = $helper->getShopId();

        $shoppingCartID = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST']:'');
        $shoppingCartID = str_replace('.', '', $shoppingCartID);
        $shoppingCartID .= $order->getIncrementId();

        $totalAmount = $helper->formatPrice($order->getGrandTotal());
        $signature = $helper->encode($shopID, $shoppingCartID, $totalAmount);

        $form = new Varien_Data_Form();
        $formId = 'inchoo_tcompayway_redirect_form';

        $form->setAction($helper->getPostUrl())
             ->setId($formId)
             ->setName($formId)
             ->setMethod('POST')
             ->setUseContainer(true);

        /* Required parameters */
        $form->addField('ShopID', 'hidden', array('name'=>'ShopID', 'value'=>$shopID));
        $form->addField('ShoppingCartID', 'hidden', array('name'=>'ShoppingCartID', 'value'=>$shoppingCartID));
        $form->addField('TotalAmount', 'hidden', array('name'=>'TotalAmount', 'value'=>$totalAmount));
        $form->addField('Signature', 'hidden', array('name'=>'Signature', 'value'=>$signature));

        /* Optional parameters */
        $form->addField('ReturnUrl', 'hidden', array('name'=>'ReturnUrl', 'value'=>Mage::getUrl('inchoo_tcompayway/redirect/success')));
        $form->addField('CancelUrl', 'hidden', array('name'=>'CancelUrl', 'value'=>Mage::getUrl('inchoo_tcompayway/redirect/cancel')));
        $form->addField('Lang', 'hidden', array('name'=>'Lang', 'value'=>$helper->getShortLocaleCode()));
        $form->addField('Curr', 'hidden', array('name'=>'Curr', 'value'=>''));
        $form->addField('CustomerFirstname', 'hidden', array('name'=>'CustomerFirstname', 'value'=>$helper->replaceCroatianChars($billingAddress->getFirstname())));
        $form->addField('CustomerSurname', 'hidden', array('name'=>'CustomerSurname', 'value'=>$helper->replaceCroatianChars($billingAddress->getLastname())));
        $form->addField('CustomerAddress', 'hidden', array('name'=>'CustomerAddress', 'value'=>$helper->replaceCroatianChars(implode('\n', $billingAddress->getStreet()))));
        $form->addField('CustomerCity', 'hidden', array('name'=>'CustomerCity', 'value'=>$helper->replaceCroatianChars($billingAddress->getCity())));
        $form->addField('CustomerZIP', 'hidden', array('name'=>'CustomerZIP', 'value'=>$billingAddress->getPostcode()));
        $form->addField('CustomerCountry', 'hidden', array('name'=>'CustomerCountry', 'value'=>$billingAddress->getCountryId()));
        $form->addField('CustomerPhone', 'hidden', array('name'=>'CustomerPhone', 'value'=>$billingAddress->getTelephone()));
        $form->addField('CustomerEmail', 'hidden', array('name'=>'CustomerEmail', 'value'=>$billingAddress->getEmail()));
        $form->addField('PaymentType', 'hidden', array('name'=>'PaymentType', 'value'=>'manual'));
        $form->addField('Installments', 'hidden', array('name'=>'Installments', 'value'=>'N'));

        $idSuffix = Mage::helper('core')->uniqHash();

        $submitButton = new Varien_Data_Form_Element_Submit(array(
            'value'    => $helper->__('Click here if you are not redirected within 10 seconds...'),
        ));

        $id = "submit_to_tcompayway_button_{$idSuffix}";
        $submitButton->setId($id);
        $form->addElement($submitButton);

        $html = '<html><body>';
        $html .= $this->__('You will be redirected to the T-Com Payway website in a few seconds.');
        $html .= $form->toHtml();
        $html .= '<script type="text/javascript">document.getElementById("'.$formId.'").submit();</script>';
        $html .= '</body></html>';

        return $html;
    }
}
