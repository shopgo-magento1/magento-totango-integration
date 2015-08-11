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
 * Data helper
 *
 * @category    Shopgo
 * @package     Shopgo_Totango
 * @authors     Ammar <ammar@shopgo.me>
 *              Emad  <emad@shopgo.me>
 *              Ahmad <ahmadalkaid@shopgo.me>
 *              Aya   <aya@shopgo.me>
 */
class Shopgo_Totango_Helper_Data extends Shopgo_Core_Helper_Abstract
{
    /**
     * Totango Service URL
     */
    const SERVICE_URL = 'https://sdr.totango.com/pixel.gif/';

    /**
     * CONFIG path constant: ENABLED
     */
    const XML_PATH_TOTANGO_GENERAL_ENABLED = 'shopgo_totango/general/enabled';


    /**
     * CONFIG path constant: SERVICE_ID
     */
    const XML_PATH_TOTANGO_GENERAL_SERVICE_ID   = 'shopgo_totango/general/service_id';

    /**
     * CONFIG path constant: ACCOUNT_ID
     */
    const XML_PATH_TOTANGO_GENERAL_ACCOUNT_ID   = 'shopgo_totango/general/account_id';

    /**
     * CONFIG path constant: ACCOUNT_NAME
     */
    const XML_PATH_TOTANGO_GENERAL_ACCOUNT_NAME = 'shopgo_totango/general/account_name';

    /**
     * CONFIG path constant: USER_ID
     */
    const XML_PATH_TOTANGO_GENERAL_USER_ID      = 'shopgo_totango/general/user_id';


    /**
     * CONFIG path constant: TRACKERS
     */
    const XML_PATH_TOTANGO_TRACKERS = 'shopgo_totango/trackers/';


    /**
     * Log file name
     *
     * @var string
     */
    protected $_logFile = 'shopgo_totango.log';

    /**
     * Trackers names
     *
     * @var array
     */
    private static $_trackers = array(
        'product', 'category',
        'attribute', 'shipping_payment',
        'complete_orders', 'canceled_orders',
        'admin_login'
    );


    /**
     * Get a list of predefined trackers
     *
     * @return array
     */
    public static function getTrackers()
    {
        return self::$_trackers;
    }

    /**
     * Check whether the extension is enabled or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_TOTANGO_GENERAL_ENABLED);
    }

    /**
     * Check whether a specific tracker is enabled or not
     *
     * @param string $tracker
     * @return bool
     */
    public function isTrackerEnabled($tracker)
    {
        $result = false;

        if (in_array($tracker, self::getTrackers())) {
            $result =
                Mage::getStoreConfig(self::XML_PATH_TOTANGO_TRACKERS . $tracker);

            if (!$result) {
                $this->log(sprintf('Tracker "%s" is disabled', $tracker));
            }
        } else {
            $this->log(sprintf('Tracker "%s" is invalid', $tracker));
        }

        return $result;
    }

    /**
     * Log data user-activity event or attribute-updates
     *
     * @param string $event
     * @param array $data
     * @return bool
     */
    public function track($event, $data)
    {
        $result = false;

        if (empty($data)) {
            $this->log('There is no tracking data provided');
            return $result;
        }

        $params = array(
            'sdr_s' =>
                Mage::getStoreConfig(self::XML_PATH_TOTANGO_GENERAL_SERVICE_ID),
            'sdr_o' =>
                Mage::getStoreConfig(self::XML_PATH_TOTANGO_GENERAL_ACCOUNT_ID)
        );

        $accountName =
            Mage::getStoreConfig(self::XML_PATH_TOTANGO_GENERAL_ACCOUNT_NAME);

        if ($accountName) {
            $params['sdr_odn'] = $accountName;
        }

        switch ($event) {
            case 'user-activity':
                if (isset($data['action']) && isset($data['module'])) {
                    $params['sdr_u'] =
                        Mage::getStoreConfig(self::XML_PATH_TOTANGO_GENERAL_USER_ID);

                    $params['sdr_a'] = $data['action'];
                    $params['sdr_m'] = $data['module'];

                    $result = true;
                } else {
                    $this->log('Insufficient tracking data');
                }

                break;
            case 'account-attribute':
                if (is_array($data)) {
                    foreach ($data as $name => $value) {
                        $params["sdr_o.{$name}"] = $value;
                    }

                    $result = true;
                } else {
                    $this->log('Insufficient tracking data');
                }

                break;
            case 'user-attribute':
                if (is_array($data)) {
                    $params['sdr_u'] =
                        Mage::getStoreConfig(self::XML_PATH_TOTANGO_GENERAL_USER_ID);

                    foreach ($data as $name => $value) {
                        $params["sdr_u.{$name}"] = $value;
                    }

                    $result = true;
                } else {
                    $this->log('Insufficient tracking data');
                }

                break;
        }

        return $result ? $this->_sendRequest($params) : $result;
    }

    /**
     * Send request to Totango
     *
     * @param array $params
     * @return bool
     */
    private function _sendRequest($params)
    {
        $result = false;

        if (empty($params)) {
            $this->log('Could not send a request with empty parameters');
            return $result;
        }

        $url        = self::SERVICE_URL;
        $httpClient = new Varien_Http_Client();

        try {
            $response = $httpClient
                ->setUri($url)
                ->setHeaders('Content-Type: image/gif')
                ->setParameterPost($params)
                ->request(Varien_Http_Client::POST);

            if ($response->isSuccessful()) {
                $result = true;
            } else {
                $this->log('"Send Request" response was unsuccessful');
            }
        } catch (Exception $e) {
            $this->log(sprintf('[Send Request Exception]: %s', $e->getMessage()));
            $this->log($e, 'exception');
        }

        return $result;
    }
}
