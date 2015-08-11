<?php
/**
 * ShopGo
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Shopgo
 * @package     Shopgo_Totango
 * @copyright   Copyright (c) 2015 Shopgo. (http://www.shopgo.me)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Observer model
 *
 * @category    Shopgo
 * @package     Shopgo_Totango
 * @authors     Ammar <ammar@shopgo.me>
 *              Emad  <emad@shopgo.me>
 *              Ahmad <ahmadalkaid@shopgo.me>
 *              Aya   <aya@shopgo.me>
 */
class Shopgo_Totango_Model_Observer
{
    /**
     * Track completed and canceled orders
     *
     * @param Varien_Event_Observer $observer
     * @return null
     */
    public function trackOrderStatus(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('totango');

        $currentOrderState = $observer->getOrder()->getState();
        $orderState = array(
            'complete' => Mage_Sales_Model_Order::STATE_COMPLETE,
            'canceled' => Mage_Sales_Model_Order::STATE_CANCELED
        );

        switch (true) {
            case $helper->isTrackerEnabled('complete_orders')
                && $currentOrderState == $orderState['complete']:
                $completeOrders = Mage::getModel('sales/order')
                    ->getCollection()
                    ->addAttributeToFilter('status', array(
                        'eq' => $orderState['complete']
                    ))->getSize();

                $helper->track('account-attribute', array(
                    'CompleteOrders' => $completeOrders
                ));

                break;
            case $helper->isTrackerEnabled('canceled_orders')
                && $currentOrderState == $orderState['canceled']:
                $canceledOrders = Mage::getModel('sales/order')
                    ->getCollection()
                    ->addAttributeToFilter('status', array(
                        'eq' => $orderState['canceled']
                    ))->getSize();

                $helper->track('account-attribute', array(
                    'CanceledOrders' => $canceledOrders
                ));

                break;
        }
    }
}
