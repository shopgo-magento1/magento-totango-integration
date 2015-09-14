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
 * @copyright   Copyright (c) 2015 ShopGo. (http://www.shopgo.me)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Source model
 *
 * @category    Shopgo
 * @package     Shopgo_Totango
 * @author      Ammar  <ammar@shopgo.me>
 *              Emad   <emad@shopgo.me>
 *              Ahmad  <ahmadalkaid@shopgo.me>
 *              Aya    <aya@shopgo.me>
 *              ShopGo <support@shopgo.me>
 */
class Shopgo_Totango_Model_System_Config_Source_Trackersactive
{
    /**
     * Get trackers active options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper  = Mage::helper('adminhtml');

        $options = array(
            array(
                'label' => $helper->__('None'),
                'value' => Shopgo_Totango_Helper_Data::TRACKERS_ACTIVE_NONE
            ),
            array(
                'label' => $helper->__('All'),
                'value' => Shopgo_Totango_Helper_Data::TRACKERS_ACTIVE_ALL
            ),
            array(
                'label' => $helper->__('Custom'),
                'value' => Shopgo_Totango_Helper_Data::TRACKERS_ACTIVE_CUSTOM
            )
        );

        return $options;
    }
}
