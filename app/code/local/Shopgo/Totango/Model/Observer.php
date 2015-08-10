<?php
class Shopgo_Totango_Model_Observer
{
    public function getConfigData($key)
    {
        return Mage::getStoreConfig('shopgo_totango/trackers/' . $key);
    }
    
    public function PostOrderData(Varien_Event_Observer $observer)
    {
        if ($this->getConfigData('complete_order')) {
            $CompletedOrders     = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('status', array(
                'eq' => Mage_Sales_Model_Order::STATE_COMPLETE
            ));
            $CompletedOrdersSize = $CompletedOrders->getSize();
            $order               = $observer->getOrder();
            if ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
                Mage::helper('totango')->track('account-attribute', array(
                    'CompletedOrders' => $CompletedOrders
                ));
            }
        }
        
        if ($this->getConfigData('canceled_order')) {
            $CanceledOrders     = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('status', array(
                'eq' => Mage_Sales_Model_Order::STATE_CANCELED
            ));
            $CanceledOrdersSize = $CanceledOrders->getSize();
            $order              = $observer->getOrder();
            if ($order->getState() == Mage_Sales_Model_Order::STATE_CANCELED) {
                Mage::helper('totango')->track('account-attribute', array(
                    'CanceledOrders' => $CanceledOrders
                ));
            }
        }
        
    }
}