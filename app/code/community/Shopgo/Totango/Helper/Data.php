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
     * Module name
     */
    const MODULE_NAME = 'Shopgo_Totango';

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
     * Trackers config active field
     */
    const XML_PATH_TRACKERS_ACTIVE = 'active';

    /**
     * Trackers advanced excluded admin users config path
     */
    const XML_PATH_TRACKERS_ADV_EXC_ADMIN = 'totango/trackers_advanced/excluded_admin_users';

    /**
     * Persist config file path inside var directory
     */
    const PERSIST_CONFIG_FILE_VAR_PATH = 'shopgo/totango/persist.xml';

    /**
     * Persist config mode status path
     */
    const XML_PATH_PERSIST_CONFIG_MODE_STATUS = 'persist';


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
        $result = $this->getConfig(self::XML_PATH_GENERAL_ENABLED);

        if (!$result) {
            $this->log(array(
                'message' => 'The module is disabled',
                'level'   => 5
            ));
        }

        return $result;
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

        $trackersActive = $this->getConfig(
            self::XML_PATH_TRACKERS . self::XML_PATH_TRACKERS_ACTIVE
        );

        if ($trackersActive === 1) {
            $result = true;

            $this->log(array(
                'message' => 'All trackers are active/enabled',
                'level'   => 5
            ));
        } elseif (in_array($tracker, self::getTrackers())) {
            $result = $this->getConfig(
                self::XML_PATH_TRACKERS . $tracker
            );

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
     * Customized get mage store config
     *
     * @param string $path
     * @param mixed $store
     * @return mixed
     */
    public function getConfig($path, $store = null)
    {
        $persistConfigPath = Mage::getBaseDir('var')
                           . DS . self::PERSIST_CONFIG_FILE_VAR_PATH;

        try {
            $xmlConfig = new Varien_Simplexml_Config();

            if ($xmlConfig->loadFile($persistConfigPath)) {
                $persistMode = $xmlConfig->getNode(
                    self::XML_PATH_PERSIST_CONFIG_MODE_STATUS
                )->asArray();

                if ($persistMode === 1) {
                    $this->log(array(
                        'message' => 'Persist config mode is enabled ' .
                                     '(Magento system configuration value is ignored!)',
                        'level'   => 5
                    ));

                    return $persistConfig->getNode($path)->asArray();
                } else {
                    $this->log(array(
                        'message' => 'Persist config mode is disabled',
                        'level'   => 5
                    ));
                }
            } else {
                $this->log(array(
                    'message' => 'Could not read Persist config file does not exist',
                    'level'   => 5
                ));
            }
        } catch (Exception $e) {
            $this->log(array(
                'message' => sprintf(
                    '[Get Config Persist Mode Exception]: %s',
                    $e->getMessage()
                ),
                'level' => 3
            ));

            $this->log($e, 'exception');
        }

        return Mage::getStoreConfig($path, $store);
    }

    /**
     * Get a list of of admin users to be excluded from tracking
     *
     * @return array
     */
    public function getExcludedAdminUsers()
    {
        $excludedAdminUsers = $this->getConfig(
            self::XML_PATH_TRACKERS_ADV_EXC_ADMIN
        );

        return array_map('trim', explode(',', $excludedAdminUsers));
    }

    /**
     * Log data user-activity event or attribute-updates
     *
     * @param array $data
     * @return bool
     */
    public function track($data)
    {
        $canSend = false;

        $events      = array_keys($data);
        $multiEvents = count($events) > 1 ? 's' : '';
        $events      = implode(', ', $events);

        $this->log(sprintf(
            'Track Totango %s event%s',
            $events, $multiEvents
        ));

        if (empty($data)) {
            $this->log(array(
                'message' => 'There is no tracking data to process',
                'level'   => 3
            ));

            return $canSend;
        }

        $params = array(
            'sdr_s' => $this->getConfig(
                self::XML_PATH_GENERAL_SERVICE_ID
            ),
            'sdr_o' => $this->getConfig(
                self::XML_PATH_GENERAL_ACCOUNT_ID
            )
        );

        if (empty($params['sdr_s']) || empty($params['sdr_o'])) {
            $this->log(array(
                'message' => 'Insufficient tracking data ' .
                             '(Service ID and Account ID are mandatory)',
                'level'   => 3
            ));

            return $canSend;
        }

        $accountName = $this->getConfig(
            self::XML_PATH_GENERAL_ACCOUNT_NAME
        );

        if ($accountName) {
            $params['sdr_odn'] = $accountName;
        }

        $canSend = true;

        foreach ($data as $event => $_data) {
            $eventData = $this->_getEventData($event, $_data);
            $canSend   = $canSend && $eventData['can_send'];
            $params    = array_merge($params, $eventData['params']);
        }

        if ($canSend) {
            return $this->_sendRequest($params);
        } else {
            $this->log(array(
                'message' => 'Could not send a request to Totango',
                'level'   => 3
            ));
        }
    }

    /**
     * Get Totango event data
     *
     * @param string $event
     * @param array $data
     * @return array
     */
    private function _getEventData($event, $data)
    {
        $canSend = false;
        $params  = array();

        switch ($event) {
            case 'user-activity':
                if (isset($data['action']) && isset($data['module'])) {
                    $params['sdr_u'] = $this->getConfig(
                        self::XML_PATH_GENERAL_USER_ID
                    );

                    $params['sdr_a'] = $data['action'];
                    $params['sdr_m'] = $data['module'];

                    $canSend = true;
                } else {
                    $this->log(array(
                        'message' => sprintf(
                            'Insufficient tracking data for %s event',
                            $event
                        ),
                        'level' => 3
                    ));
                }

                break;

            case 'account-attribute':
                if (is_array($data) && !empty($data)) {
                    foreach ($data as $name => $value) {
                        $params["sdr_o.{$name}"] = $value;
                    }

                    $canSend = true;
                } else {
                    $this->log(array(
                        'message' => sprintf(
                            'Insufficient tracking data for %s event',
                            $event
                        ),
                        'level' => 3
                    ));
                }

                break;

            case 'user-attribute':
                if (is_array($data) && !empty($data)) {
                    $params['sdr_u'] = $this->getConfig(
                        self::XML_PATH_GENERAL_USER_ID
                    );

                    foreach ($data as $name => $value) {
                        $params["sdr_u.{$name}"] = $value;
                    }

                    $canSend = true;
                } else {
                    $this->log(array(
                        'message' => sprintf(
                            'Insufficient tracking data for %s event',
                            $event
                        ),
                        'level' => 3
                    ));
                }

                break;

            default:
                $this->log(array(
                    'message' => sprintf(
                        'The requested event %s is invalid',
                        $event
                    ),
                    'level' => 3
                ));
        }

        return array(
            'can_send' => $canSend,
            'params'   => $params
        );
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
        $url    = self::SERVICE_URL;

        $this->log(array(
            array('message' => 'Totango service call, Start...'),
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
                    'it was received correctly by Totango. So, be careful!)'
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
