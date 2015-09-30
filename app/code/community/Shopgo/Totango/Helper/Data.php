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
 * Data helper
 *
 * @category    Shopgo
 * @package     Shopgo_Totango
 * @author      Ammar  <ammar@shopgo.me>
 *              Emad   <emad@shopgo.me>
 *              Ahmad  <ahmadalkaid@shopgo.me>
 *              Aya    <aya@shopgo.me>
 *              ShopGo <support@shopgo.me>
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
     * Trackers config active field
     */
    const XML_PATH_TRACKERS_ACTIVE = 'active';

    /**
     * Trackers config active field values
     */
    const TRACKERS_ACTIVE_NONE   = 0;
    const TRACKERS_ACTIVE_ALL    = 1;
    const TRACKERS_ACTIVE_CUSTOM = 2;

    /**
     * Trackers advanced excluded admin users config path
     */
    const XML_PATH_TRACKERS_ADV_EXC_ADMIN = 'totango/trackers_advanced/excluded_admin_users';

    /**
     * Module var directory
     */
    const VAR_DIR = 'shopgo/totango/';

    /**
     * Persist config files
     */
    const PERSIST_FILE       = 'persist.xml';
    const PERSIST_LOCAL_FILE = 'persist.local.xml';

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
        'complete_orders', 'canceled_orders',
        'product', 'category',
        'attribute', 'carrier',
        'payment', 'admin_login'
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

        switch ($trackersActive) {
            case self::TRACKERS_ACTIVE_NONE:
                $this->log(array(
                    'message' => 'All trackers are inactive/disabled',
                    'level'   => 5
                ));

                break;

            case self::TRACKERS_ACTIVE_ALL:
                $result = true;

                $this->log(array(
                    'message' => 'All trackers are active/enabled',
                    'level'   => 5
                ));

                break;

            default:
                $trackers = array_flip(self::getTrackers());

                if (isset($trackers[$tracker])) {
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
        }

        return $result;
    }

    /**
     * Get module var directory path
     *
     * @return string
     */
    public function getVarDir()
    {
        return Mage::getBaseDir('var')
            . DS . str_replace('/', DS, self::VAR_DIR);
    }

    /**
     * Customized get Mage store config
     *
     * @param string $path
     * @param mixed $store
     * @return mixed
     */
    public function getConfig($path, $store = null)
    {
        $config = $this->_getPersistConfig($path);

        if ($config === null || trim($config) == '') {
            $config = Mage::getStoreConfig($path, $store);
        }

        return $config;
    }

    /**
     * Get persist config value
     *
     * @param string $path
     * @return mixed
     */
    private function _getPersistConfig($path)
    {
        $config = null;
        $varDir = $this->getVarDir();

        $persistConfig = array(
            'base'  => $varDir . self::PERSIST_FILE,
            'local' => $varDir . self::PERSIST_LOCAL_FILE
        );

        try {
            $persistBase = $this->_loadXmlFile($persistConfig['base']);

            if ($persistBase->hasFile) {
                $persistStatus = $persistBase->getNode(
                    self::XML_PATH_PERSIST_CONFIG_MODE_STATUS
                );

                if (!$persistStatus) {
                    $this->log(array(
                        'message' => 'Persist config status path is invalid',
                        'level'   => 3
                    ));

                    return $config;
                }

                if ($persistStatus->asArray() == 1) {
                    $this->log(array(
                        'message' => 'Persist config mode is enabled ' .
                                     '(Magento system configuration value is ignored!)',
                        'level'   => 5
                    ));

                    $persistLocal = $this->_loadXmlFile($persistConfig['local']);

                    return $this->_getPersistConfigFromSource(
                        $path, $persistBase, $persistLocal
                    );
                } else {
                    $this->log(array(
                        'message' => 'Persist config mode is disabled',
                        'level'   => 5
                    ));
                }
            } else {
                $this->log(array(
                    'message' => 'Could not read persist config file',
                    'level'   => 5
                ));
            }
        } catch (Exception $e) {
            $this->log(array(
                'message' => sprintf(
                    '[Persist Config Mode Exception]: %s',
                    $e->getMessage()
                ),
                'level' => 3
            ));

            $this->log($e, 'exception');
        }

        return $config;
    }

    /**
     * Get persist config from base or local sources
     *
     * @param string $path
     * @param Varien_Simplexml_Config $persistBase
     * @param Varien_Simplexml_Config $persistLocal
     * @return mixed
     */
    private function _getPersistConfigFromSource($path, $persistBase, $persistLocal)
    {
        $config = null;

        switch ($persistLocal->hasFile) {
            case true:
                $config = $persistLocal->getNode($path);

                if (!empty($config)) {
                    $config = $config->asArray();

                    $this->log(array(
                        'message' => 'Persist config "local" is used',
                        'level'   => 5
                    ));

                    if (trim($config) != '') {
                        // Break only if a persist config "local"
                        // value is found.
                        break;
                    }
                } else {
                    $this->log(array(
                        'message' => sprintf(
                            'Persist config "local" path (%s) ' .
                            'is invalid or does not exist',
                            $path
                        ),
                        'level' => 5
                    ));
                }

            case false:
                // It looks a bit odd, but this is a necessary check
                if (!$persistLocal->hasFile) {
                    $this->log(array(
                        'message' => 'Could not read persist config local file',
                        'level'   => 5
                    ));
                }

                $config = $persistBase->getNode($path);

                if (!empty($config)) {
                    $config = $config->asArray();
                } else {
                    $this->log(array(
                        'message' => sprintf(
                            'Persist config path (%s) ' .
                            'is invalid or does not exist',
                            $path
                        ),
                        'level' => 5
                    ));
                }

                break;
        }

        return $config;
    }

    /**
     * Load XML file
     *
     * @param string $file
     * @return Varien_Simplexml_Config
     */
    private function _loadXmlFile($file)
    {
        $xmlConfig = new Varien_Simplexml_Config();
        $xmlConfig->hasFile = $xmlConfig->loadFile($file);

        return $xmlConfig;
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

        return trim($excludedAdminUsers) != ''
            ? array_map('trim', explode(',', $excludedAdminUsers))
            : array();
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
        $events  = implode(', ', array_keys($data));

        $this->log(sprintf(
            'Track Totango %s event(s)',
            $events
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

        try {
            $httpClient = new Varien_Http_Client();

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
