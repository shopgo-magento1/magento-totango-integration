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

        switch (true) {
            case $helper->isTrackerEnabled('completed_orders'):
                $stateComplete = Mage_Sales_Model_Order::STATE_COMPLETE;
                $orderState    = $observer->getOrder()->getState();

                if ($orderState == $stateComplete) {
                    $completedOrders = Mage::getModel('sales/order')
                        ->getCollection()
                        ->addAttributeToFilter('status', array(
                            'eq' => $stateComplete
                        ))->getSize();

                    $helper->track('account-attribute', array(
                        'CompletedOrders' => $completedOrders
                    ));
                }

                break;
            case $helper->isTrackerEnabled('canceled_orders'):
                $stateCanceled = Mage_Sales_Model_Order::STATE_CANCELED;
                $orderState    = $observer->getOrder()->getState();

                if ($orderState == $stateCanceled) {
                    $canceledOrders = Mage::getModel('sales/order')
                        ->getCollection()
                        ->addAttributeToFilter('status', array(
                            'eq' => $stateCanceled
                        ))->getSize();

                    $helper->track('account-attribute', array(
                        'CanceledOrders' => $canceledOrders
                    ));
                }

                break;
        }
    }
}
