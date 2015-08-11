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
     * Track orders based on their statuses
     *
     * @param Varien_Event_Observer $observer
     * @return null
     */
    public function trackOrderStatus(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('totango');

        // Current order state
        $orderState  = $observer->getOrder()->getState();
        // List of order states and their data
        $orderStates = array(
            Mage_Sales_Model_Order::STATE_COMPLETE => array(
                'tracker-name'   => 'complete_orders',
                'attribute-name' => 'CompleteOrders'
            ),
            Mage_Sales_Model_Order::STATE_CANCELED => array(
                'tracker-name'   => 'canceled_orders',
                'attribute-name' => 'CanceledOrders'
            )
        );

        foreach ($orderStates as $state => $data) {
            if ($helper->isTrackerEnabled($data['tracker-name'])
                && $orderState == $state) {
                $orders = Mage::getModel('sales/order')
                    ->getCollection()
                    ->addAttributeToFilter('status', array(
                        'eq' => $state
                    ))->getSize();

                $helper->track('account-attribute', array(
                    $data['attribute-name'] => $orders
                ));
            }
        }
    }
}
