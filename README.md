Magento Totango Integration
===========================

A Magento extension that integrates Magento platform with Totango tracking service.

[![Build Status](https://travis-ci.org/shopgo-magento1/magento-totango-integration.svg?branch=master)](https://travis-ci.org/shopgo-me/magento-totango-integration)
[![Dependency Status](https://www.versioneye.com/user/projects/55f9839b3ed894001e0007ab/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55f9839b3ed894001e0007ab)
[![GitHub version](https://badge.fury.io/gh/shopgo-magento1%2Fmagento-totango-integration.svg)](http://badge.fury.io/gh/shopgo-magento1%2Fmagento-totango-integration)

What Is Totango?
----------------

Totango is the leading Customer Engagement platform that helps SaaS companies create an active and engaged user base.

Totango is built on the idea that customer success is all about understanding, measuring, and delivering business value to customers. To do this, Totango’s platform is focused on identifying and monitoring “leading indicators” that help SaaS companies predict at-risk customers and proactively drive positive customer experiences.

Totango pulls in data from multiple systems to assemble a rich profile of every customer, including a predictive health rating and an “engagement score,” which is computed based on data science and machine learning.

How To Call Totango Service?
----------------------------

* Track User-Activity Event:
```
Mage::helper('totango')->track(array(
    'user-activity' => array(
        'action' => 'MY_ACTION',
        'module' => 'MY_MODULE'
    )
));
```
* Track Account-Attribute Event:
```
Mage::helper('totango')->track(array(
    'account-attribute' => array(
        'MY_ATTRIBUTE1_NAME' => 'MY_ATTRIBUTE1_VALUE',
        'MY_ATTRIBUTE2_NAME' => 'MY_ATTRIBUTE2_VALUE',
        'MY_ATTRIBUTE3_NAME' => 'MY_ATTRIBUTE3_VALUE',
        ...
    )
));
```
* Track User-Attribute Event:
```
Mage::helper('totango')->track(array(
    'user-attribute' => array(
        'MY_ATTRIBUTE1_NAME' => 'MY_ATTRIBUTE1_VALUE',
        'MY_ATTRIBUTE2_NAME' => 'MY_ATTRIBUTE2_VALUE',
        'MY_ATTRIBUTE3_NAME' => 'MY_ATTRIBUTE3_VALUE',
        ...
    )
));
```

* Track Multiple Events:
```
Mage::helper('totango')->track(array(
    'user-activity' => array(
        'action' => 'MY_ACTION',
        'module' => 'MY_MODULE'
    ),
    'account-attribute' => array(
        'MY_ATTRIBUTE1_NAME' => 'MY_ATTRIBUTE1_VALUE',
        'MY_ATTRIBUTE2_NAME' => 'MY_ATTRIBUTE2_VALUE',
        'MY_ATTRIBUTE3_NAME' => 'MY_ATTRIBUTE3_VALUE',
        ...
    ),
    'user-attribute' => array(
        'MY_ATTRIBUTE1_NAME' => 'MY_ATTRIBUTE1_VALUE',
        'MY_ATTRIBUTE2_NAME' => 'MY_ATTRIBUTE2_VALUE',
        'MY_ATTRIBUTE3_NAME' => 'MY_ATTRIBUTE3_VALUE',
        ...
    )
));
```

Authors
-------

* Ammar (<ammar@shopgo.me>)
* Emad (<emad@shopgo.me>)
* Ahmad (<ahmadalkaid@shopgo.me>)
* Aya (<aya@shopgo.me>)
* ShopGo (<support@shopgo.me>)

License
-------

* OSL-3.0
* AFL-3.0
