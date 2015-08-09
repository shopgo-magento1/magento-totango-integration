<?php
class Shopgo_Totango_Model_Observer
{
    public function PostOrderData(Varien_Event_Observer $observer)
    {
        $CompletedOrders = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('status', array(
            'eq' => Mage_Sales_Model_Order::STATE_COMPLETE
        ));
        
        $CanceledOrders = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('status', array(
            'eq' => Mage_Sales_Model_Order::STATE_CANCELED
        ));
        
        $CompletedOrdersSize = $CompletedOrders->getSize();
        
        $CanceledOrdersSize = $CanceledOrders->getSize();
        
        $order = $observer->getOrder();
        if ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE)
        {
            Mage::log('Completed Orders: ' . $CompletedOrdersSize);
            
            Mage::helper('totango')->track('account-attribute', array(
                'CompletedOrders' => $CompletedOrders
            ));
        }
        
        if ($order->getState() == Mage_Sales_Model_Order::STATE_CANCELED)
        {
            Mage::log('Canceled Orders: ' . $CanceledOrdersSize);
            
            Mage::helper('totango')->track('account-attribute', array(
                'CanceledOrders' => $CanceledOrders
            ));
        }
        
    }
}