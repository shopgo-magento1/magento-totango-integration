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
     * Totango service URL
     */
    const SERVICE_URL = 'https://sdr.totango.com/pixel.gif/';

    /**
     * General config paths
     */
    const XML_PATH_GENERAL_ENABLED      = 'totango/general/enabled';
    const XML_PATH_GENERAL_SERVICE_ID   = 'totango/general/service_id';
    const XML_PATH_GENERAL_ACCOUNT_ID   = 'totango/general/account_id';
    const XML_PATH_GENERAL_ACCOUNT_NAME = 'totango/general/account_name';
    const XML_PATH_GENERAL_USER_ID      = 'totango/general/user_id';

    /**
     * Trackers config path
     */
    const XML_PATH_TRACKERS = 'totango/trackers/';

    /**
     * Trackers advanced excluded admin users config path
     */
    const XML_PATH_TRACKERS_ADV_EXC_ADMIN = 'totango/trackers_advanced/excluded_admin_users';


    /**
     * Log file name
     *
     * @var string
     */
    protected $_logFile = 'totango.log';

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
        return Mage::getStoreConfig(self::XML_PATH_GENERAL_ENABLED);
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
                Mage::getStoreConfig(self::XML_PATH_TRACKERS . $tracker);

            if (!$result) {
                $this->log(array(
                    'message' => sprintf(
                        'Tracker "%s" is disabled',
                        $tracker
                    ),
                    'level' => 5
                ));
            }
        } else {
            $this->log(array(
                'message' => sprintf(
                    'Tracker "%s" is invalid',
                    $tracker
                ),
                'level' => 3
            ));
        }

        return $result;
    }

    /**
     * Get a list of of admin users to be excluded from tracking
     *
     * @return array
     */
    public function getExcludedAdminUsers()
    {
        $excludedAdminUsers =
            Mage::getStoreConfig(self::XML_PATH_TRACKERS_ADV_EXC_ADMIN);

        return array_map('trim', explode(',', $excludedAdminUsers));
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

        $this->log(sprintf('Track Totango %s event', $event));

        if (empty($data)) {
            $this->log(array(
                'message' => 'There is no tracking data to process',
                'level'   => 3
            ));

            return $result;
        }

        $params = array(
            'sdr_s' =>
                Mage::getStoreConfig(self::XML_PATH_GENERAL_SERVICE_ID),
            'sdr_o' =>
                Mage::getStoreConfig(self::XML_PATH_GENERAL_ACCOUNT_ID)
        );

        if (empty($params['sdr_s']) || empty($params['sdr_o'])) {
            $this->log(array(
                'message' => 'Insufficient tracking data',
                'level'   => 3
            ), '', '', true);

            return $result;
        }

        $accountName =
            Mage::getStoreConfig(self::XML_PATH_GENERAL_ACCOUNT_NAME);

        if ($accountName) {
            $params['sdr_odn'] = $accountName;
        }

        switch ($event) {
            case 'user-activity':
                if (isset($data['action']) && isset($data['module'])) {
                    $params['sdr_u'] =
                        Mage::getStoreConfig(self::XML_PATH_GENERAL_USER_ID);

                    $params['sdr_a'] = $data['action'];
                    $params['sdr_m'] = $data['module'];

                    $result = true;
                } else {
                    $this->log(array(
                        'message' => 'Insufficient tracking data',
                        'level'   => 3
                    ), '', '', true);
                }

                break;

            case 'account-attribute':
                if (is_array($data)) {
                    foreach ($data as $name => $value) {
                        $params["sdr_o.{$name}"] = $value;
                    }

                    $result = true;
                } else {
                    $this->log(array(
                        'message' => 'Insufficient tracking data',
                        'level'   => 3
                    ), '', '', true);
                }

                break;

            case 'user-attribute':
                if (is_array($data)) {
                    $params['sdr_u'] =
                        Mage::getStoreConfig(self::XML_PATH_GENERAL_USER_ID);

                    foreach ($data as $name => $value) {
                        $params["sdr_u.{$name}"] = $value;
                    }

                    $result = true;
                } else {
                    $this->log(array(
                        'message' => 'Insufficient tracking data',
                        'level'   => 3
                    ), '', '', true);
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

        $this->log(array(
            array('message' => 'Totango service call, start...'),
            array('message' => sprintf('Totango service URL: %s', $url)),
            array('message' => array('request_params' => $params))
        ));

        if (empty($params)) {
            $this->log(array(
                'message' => 'Could not send a request with empty parameters',
                'level'   => 3
            ));

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
                $this->log(
                    'Totango request was sent successfully ' .
                    '(A successful response does not mean that ' .
                    'it was received correctly by Totango. So, be careful)'
                );
            } else {
                $this->log(array(
                    'message' => 'Totango sent request response was unsuccessful',
                    'level'   => 3
                ));
            }
        } catch (Exception $e) {
            $this->log(array(
                'message' => sprintf(
                    '[Totango Request Send Exception]: %s',
                    $e->getMessage()
                ),
                'level' => 3
            ));

            $this->log($e, 'exception');
        }

        return $result;
    }
}
