<?php

class Shopgo_Totango_Helper_Data extends Shopgo_Core_Helper_Abstract
{
    const SERVICE_URL = 'https://sdr.totango.com/pixel.gif/';

    const XML_PATH_TOTANGO_GENERAL_ENABLED = 'shopgo_totango/general/enabled';

    const XML_PATH_TOTANGO_GENERAL_SERVICE_ID   = 'shopgo_totango/general/service_id';
    const XML_PATH_TOTANGO_GENERAL_ACCOUNT_ID   = 'shopgo_totango/general/account_id';
    const XML_PATH_TOTANGO_GENERAL_ACCOUNT_NAME = 'shopgo_totango/general/account_name';
    const XML_PATH_TOTANGO_GENERAL_USER_ID      = 'shopgo_totango/general/user_id';

    const XML_PATH_TOTANGO_TRACKERS = 'shopgo_totango/trackers/';


    public function isEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_TOTANGO_GENERAL_ENABLED);
    }

    public function isTrackerEnabled($tracker)
    {
        return Mage::getStoreConfig(self::XML_PATH_TOTANGO_TRACKERS . $tracker);
    }

    public function track($event, $data)
    {
        if (empty($data)) {
            return false;
        }

        $params = array(
            'sdr_s' => Mage::getStoreConfig(self::XML_PATH_TOTANGO_GENERAL_SERVICE_ID),
            'sdr_o' => Mage::getStoreConfig(self::XML_PATH_TOTANGO_GENERAL_ACCOUNT_ID)
        );

        $accountName = Mage::getStoreConfig(self::XML_PATH_TOTANGO_GENERAL_ACCOUNT_NAME);

        if ($accountName) {
            $params['sdr_odn'] = $accountName;
        }

        switch ($event) {
            case 'user-activity':
                $params['sdr_u'] = Mage::getStoreConfig(self::XML_PATH_TOTANGO_GENERAL_USER_ID);
                $params['sdr_a'] = $data['action'];
                $params['sdr_m'] = $data['module'];
                break;
            case 'account-attribute':
                $params['sdr_o.' . $data['attribute']['name']] = $data['attribute']['value'];
                break;
            case 'user-attribute':
                $params['sdr_u'] = Mage::getStoreConfig(self::XML_PATH_TOTANGO_GENERAL_USER_ID);
                $params['sdr_u.' . $data['attribute']['name']] = $data['attribute']['value'];
                break;
            default:
                return false;
        }

        return $this->sendRequest($params);
    }

    public function sendRequest($params)
    {
        if (empty($params)) {
            return false;
        }

        $result     = '';
        $url        = self::SERVICE_URL;
        $httpClient = new Varien_Http_Client();

        $result = $httpClient
            ->setUri($url)
            ->setHeaders('Content-Type: image/gif')
            ->setParameterPost($params)
            ->request(Varien_Http_Client::POST)
            ->getBody();

        return $result;
    }
}
