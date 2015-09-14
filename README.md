# Magento Totango Integration #

### About Repository ###

* A Magento extension that integrates Magento platform with Totango tracking service.
* v1.0.0

### How To Call Totango Service? ###

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

### Authors ###

* Ammar (<ammar@shopgo.me>)
* Emad (<emad@shopgo.me>)
* Ahmad (<ahmadalkaid@shopgo.me>)
* Aya (<aya@shopgo.me>)
* ShopGo (support@shopgo.me)

### License ###

* OSL-3.0
* AFL-3.0
