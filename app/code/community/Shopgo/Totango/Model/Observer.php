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

    /**
    * Track Catalog "Product" records
    *
    * @param Varien_Event_Observer $observer
    * @return null
    */
    public function trackNewProduct(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('totango');

        if ($helper->isTrackerEnabled("product")) {
            $product   = $observer->getProduct();
            $isProduct = Mage::getModel('catalog/product')
                        ->getCollection()
                        ->addFieldToFilter('entity_id', $product->getId())
                        ->getFirstItem();
            
            if (!$isProduct->getId()) {
                $helper->track('user-activity', array(
                    'action' => 'NewProduct',
                    'module' => "Catalog"
                ));
                $helper->track('account-attribute', array(
                    'Products' => 100
                ));
            }
        }
    }
 
    /**
    * Track Catalog "Category" records
    *
    * @return null
    */
    public function trackNewCategory(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('totango');

        if ($helper->isTrackerEnabled("category")) {
            $categoryId     = $observer->getEvent()->getCategory()->getId();
            $allCategoryIds = Mage::getModel('catalog/category')
                              ->getCollection()
                              ->getAllIds();
            
            if (!in_array($categoryId, $allCategoryIds)){
                $helper->track('user-activity', array(
                    'action' => 'NewCategory',
                    'module' => 'Catalog'
                ));
                $helper->track('account-attribute', array(
                    'Categories' => 100
                ));
            }
        }
    }

    /**
    * Track Catalog "Attribute" records
    *
    * @param Varien_Event_Observer $observer
    * @return null
    */
    public function trackNewAttribute(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('totango');

        if ($helper->isTrackerEnabled("attribute"))
        {
            $attributeId = $observer->getEvent()->getAttribute()->getId();
            $isAttribute = Mage::getModel('eav/entity_attribute')
                           ->load($attributeId)
                           ->getAttributeCode();

            if (!$isAttribute) {
                $helper->track('user-activity', array(
                    'action' => 'NewAttribute',
                    'module' => 'Catalog'
                ));
                $helper->track('account-attribute', array(
                    'Attributes' => 100
                ));
            }
        }
    }
}
