<?php

class OnlineShop_Plugin extends Pimcore_API_Plugin_Abstract implements Pimcore_API_Plugin_Interface {

    public static $configFile = "/OnlineShop/config/plugin_config.xml";

    public static function getConfig($readonly = true) {
        if(!$readonly) {
            $config = new Zend_Config_Xml(PIMCORE_PLUGINS_PATH . OnlineShop_Plugin::$configFile,
                null,
                array('skipExtends'        => true,
                    'allowModifications' => true));
        } else {
            $config = new Zend_Config_Xml(PIMCORE_PLUGINS_PATH . OnlineShop_Plugin::$configFile);
        }
        return $config;
    }

    public static function setConfig($onlineshopConfigFile) {
        $config = self::getConfig(false);
        $config->onlineshop_config_file = $onlineshopConfigFile;

        // Write the config file
        $writer = new Zend_Config_Writer_Xml(array('config'   => $config,
            'filename' => PIMCORE_PLUGINS_PATH . OnlineShop_Plugin::$configFile));
        $writer->write();
    }



    /**
     *  install function
     * @return string $message statusmessage to display in frontend
     */
    public static function install() {
        //Cart
        Pimcore_API_Plugin_Abstract::getDb()->query(
            "CREATE TABLE `plugin_onlineshop_cart` (
              `id` int(20) NOT NULL AUTO_INCREMENT,
              `userid` int(20) NOT NULL,
              `name` varchar(250) COLLATE utf8_bin DEFAULT NULL,
              `creationDateTimestamp` int(10) NOT NULL,
              `modificationDateTimestamp` int(10) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;"
        );

        //CartCheckoutData
        Pimcore_API_Plugin_Abstract::getDb()->query(
            "CREATE TABLE `plugin_onlineshop_cartcheckoutdata` (
              `cartId` int(20) NOT NULL,
              `key` varchar(150) COLLATE utf8_bin NOT NULL,
              `data` longtext,
              PRIMARY KEY (`cartId`,`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;"
        );

        //CartItem
        Pimcore_API_Plugin_Abstract::getDb()->query(
            "CREATE TABLE `plugin_onlineshop_cartitem` (
              `productId` int(20) NOT NULL,
              `cartId` int(20) NOT NULL,
              `count` int(20) NOT NULL,
              `itemKey` varchar(100) COLLATE utf8_bin NOT NULL,
              `parentItemKey` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '0',
              `comment` LONGTEXT ASCII,
              `addedDateTimestamp` int(10) NOT NULL,
              PRIMARY KEY (`itemKey`,`cartId`,`parentItemKey`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;"
        );

        //OrderEvent
        Pimcore_API_Plugin_Abstract::getDb()->query("
            CREATE TABLE `plugin_customerdb_event_orderEvent` (
              `eventid` int(11) NOT NULL DEFAULT '0',
              `orderObject__id` int(11) DEFAULT NULL,
              `orderObject__type` enum('document','asset','object') DEFAULT NULL,
              PRIMARY KEY (`eventid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");



        // Add FieldCollections
        $sourceFiles = scandir(PIMCORE_PLUGINS_PATH . '/OnlineShop/install/fieldcollection_sources');
        foreach ($sourceFiles as $filename) {
            if (!is_dir($filename)) {

                preg_match('/_(.*)_/', $filename, $matches);
                $key = $matches[1];

                try {
                    $fieldCollection = Object_Fieldcollection_Definition::getByKey($key);
                } catch(Exception $e) {
                    $fieldCollection = new Object_Fieldcollection_Definition();
                    $fieldCollection->setKey($key);
                }

                $data = file_get_contents(PIMCORE_PLUGINS_PATH . '/OnlineShop/install/fieldcollection_sources/' . $filename);
                $success = Object_Class_Service::importFieldCollectionFromJson($fieldCollection, $data);
                if(!$success){
                    Logger::err("Could not import $key FieldCollection.");
                }
            }
        }

        // Add classes
        self::createClass("FilterDefinition", PIMCORE_PLUGINS_PATH . '/OnlineShop/install/class_source/class_FilterDefinition_export.json');
        self::createClass("OnlineShopOrderItem", PIMCORE_PLUGINS_PATH . '/OnlineShop/install/class_source/class_OnlineShopOrderItem_export.json');
        self::createClass("OnlineShopOrder", PIMCORE_PLUGINS_PATH . '/OnlineShop/install/class_source/class_OnlineShopOrder_export.json');
        self::createClass("OfferToolOfferItem", PIMCORE_PLUGINS_PATH . '/OnlineShop/install/class_source/class_OfferToolOfferItem_export.json');
        self::createClass("OfferToolOffer", PIMCORE_PLUGINS_PATH . '/OnlineShop/install/class_source/class_OfferToolOffer_export.json');
        self::createClass("OfferToolCustomProduct", PIMCORE_PLUGINS_PATH . '/OnlineShop/install/class_source/class_OfferToolCustomProduct_export.json');

        //copy config file
        if(!is_file(PIMCORE_WEBSITE_PATH . "/var/plugins/OnlineShopConfig.xml")) {
            copy(PIMCORE_PLUGINS_PATH . "/OnlineShop/config/OnlineShopConfig_sample.xml", PIMCORE_WEBSITE_PATH . "/var/plugins/OnlineShopConfig.xml");
        }
        self::setConfig("/website/var/plugins/OnlineShopConfig.xml");


        // execute installations from subsystems
        $reflection = new ReflectionClass( __CLASS__ );
        $methods = $reflection->getMethods( ReflectionMethod::IS_STATIC );
        foreach($methods as $method)
        {
            /* @var ReflectionMethod $method */
            if(preg_match('#^install[A-Z]#', $method->name))
            {
                $func = $method->name;
                $success = self::$func();
            }
        }


        // create status message
        if(self::isInstalled()){
            $statusMessage = "installed"; // $translate->_("plugin_objectassetfolderrelation_installed_successfully");
        } else {
            $statusMessage = "not installed"; // $translate->_("plugin_objectassetfolderrelation_could_not_install");
        }
        return $statusMessage;

    }

    private static function createClass($classname, $filepath) {
        $class = Object_Class::getByName($classname);
        if(!$class) {
            $class = new Object_Class();
            $class->setName($classname);
        }
        $json = file_get_contents($filepath);

        $success = Object_Class_Service::importClassDefinitionFromJson($class, $json);
        if(!$success){
            Logger::err("Could not import $classname Class.");
        }
    }

    /**
     *
     * @return boolean
     */
    public static function needsReloadAfterInstall() {
        return true;
    }

    /**
     *  indicates wether this plugins is currently installed
     * @return boolean
     */
    public static function isInstalled() {
        $result = null;
        try{
            $result = Pimcore_API_Plugin_Abstract::getDb()->describeTable("plugin_onlineshop_cartitem");
        } catch(Exception $e){}
        return !empty($result);
    }

    /**
     * uninstall function
     * @return string $messaget status message to display in frontend
     */
    public static function uninstall() {

        Pimcore_API_Plugin_Abstract::getDb()->query("DROP TABLE IF EXISTS `plugin_onlineshop_cart`");
        Pimcore_API_Plugin_Abstract::getDb()->query("DROP TABLE IF EXISTS `plugin_onlineshop_cartcheckoutdata`");
        Pimcore_API_Plugin_Abstract::getDb()->query("DROP TABLE IF EXISTS `plugin_onlineshop_cartitem`");
        Pimcore_API_Plugin_Abstract::getDb()->query("DROP TABLE IF EXISTS `plugin_customerdb_event_orderEvent`");


        // execute uninstallation from subsystems
        $reflection = new ReflectionClass( __CLASS__ );
        $methods = $reflection->getMethods( ReflectionMethod::IS_STATIC );
        foreach($methods as $method)
        {
            /* @var ReflectionMethod $method */
            if(preg_match('#^uninstall[A-Z]#', $method->name))
            {
                $func = $method->name;
                $success = self::$func();
            }
        }


        // create status message
        if(!self::isInstalled()){
            $statusMessage = "uninstalled successfully"; //  $translate->_("plugin_objectassetfolderrelation_uninstalled_successfully");
        } else {
            $statusMessage = "did not uninstall"; // $translate->_("plugin_objectassetfolderrelation_could_not_uninstall");
        }
        return $statusMessage;

    }


    /**
     * @return string $jsClassName
     */
    public static function getJsClassName() {
    }

    /**
     *
     * @param string $language
     * @return string path to the translation file relative to plugin direcory
     */
    public static function getTranslationFile($language) {
        if ($language == "de") {
            return "/OnlineShop/texts/de.csv";
        } else if ($language == "en") {
            return "/OnlineShop/texts/en.csv";
        } else {
            return null;
        }
    }

    /**
     * @param Object_Abstract $object
     * @return void
     */
    public function postAddObject(Object_Abstract $object) {
        if ($object instanceof OnlineShop_Framework_ProductInterfaces_IIndexable) {
            $indexService = OnlineShop_Framework_Factory::getInstance()->getIndexService();
            $indexService->updateIndex($object);
        }
     //   parent::postAddObject($object);
    }

    /**
     * @param Object_Abstract $object
     * @return void
     */
    public function postUpdateObject(Object_Abstract $object) {
        if ($object instanceof OnlineShop_Framework_ProductInterfaces_IIndexable) {
            $indexService = OnlineShop_Framework_Factory::getInstance()->getIndexService();
            $indexService->updateIndex($object);
        }
      //  parent::postUpdateObject($object);
    }

    public function preDeleteObject(Object_Abstract $object) {
        if ($object instanceof OnlineShop_Framework_ProductInterfaces_IIndexable) {
            $indexService = OnlineShop_Framework_Factory::getInstance()->getIndexService();
            $indexService->deleteFromIndex($object);
        }
      //  parent::preDeleteObject($object);
    }

    /**
     * @var Zend_Log
     */
    private static $sqlLogger = null;

    /**
     * @return Zend_Log
     */
    public static function getSQLLogger() {
        if(!self::$sqlLogger) {


            // check for big logfile, empty it if it's bigger than about 200M
            $logfilename = PIMCORE_WEBSITE_PATH . '/var/log/online-shop-sql.log';
            if (filesize($logfilename) > 200000000) {
                file_put_contents($logfilename, "");
            }

            $prioMapping = array(
                "debug" => Zend_Log::DEBUG,
                "info" => Zend_Log::INFO,
                "notice" => Zend_Log::NOTICE,
                "warning" => Zend_Log::WARN,
                "error" => Zend_Log::ERR,
                "critical" => Zend_Log::CRIT,
                "alert" => Zend_Log::ALERT,
                "emergency" => Zend_Log::EMERG
            );

            $prios = array();
            $conf = Pimcore_Config::getSystemConfig();
            if($conf->general->loglevel) {
                $prioConf = $conf->general->loglevel->toArray();
                if(is_array($prioConf)) {
                    foreach ($prioConf as $level => $state) {
                        if($state) {
                            $prios[$level] = $prioMapping[$level];
                        }
                    }
                }
            }

            $logger = new Zend_Log();
            $logger->addWriter(new Zend_Log_Writer_Stream($logfilename));

            foreach($prioMapping as $key => $mapping) {
                if(!array_key_exists($key, $prios)) {
                    $logger->addFilter(new Zend_Log_Filter_Priority($mapping, "!="));
                }
            }

            self::$sqlLogger = $logger;
        }
        return self::$sqlLogger;
    }


    /**
     * install pricing rule system
     *
     * @return bool
     */
    private static function installPricingRules()
    {
        // PricingRules
        Pimcore_API_Plugin_Abstract::getDb()->query("
            CREATE TABLE IF NOT EXISTS `plugin_onlineshop_pricing_rule` (
            `id` INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(50) NULL DEFAULT NULL,
            `label` TEXT NULL,
            `description` TEXT NULL,
            `behavior` ENUM('additiv','stopExecute') NULL DEFAULT NULL,
            `active` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
            `prio` TINYINT(3) UNSIGNED NOT NULL,
            `condition` TEXT NOT NULL COMMENT 'configuration der condition',
            `actions` TEXT NOT NULL COMMENT 'configuration der action',
            PRIMARY KEY (`id`),
            UNIQUE INDEX `name` (`name`),
            INDEX `active` (`active`)
        )
        ENGINE=InnoDB
        AUTO_INCREMENT=0;
        ");

        // create permission key
        $key = 'plugin_onlineshop_pricing_rules';
        $permission = new User_Permission_Definition();
        $permission->setKey( $key );

        $res = new User_Permission_Definition_Resource();
        $res->configure( Pimcore_Resource::get() );
        $res->setModel( $permission );
        $res->save();

        return true;
    }


    /**
     * remove pricing rule system
     *
     * @return bool
     */
    private static function uninstallPricingRules()
    {
        // remove tables
        Pimcore_API_Plugin_Abstract::getDb()->query("DROP TABLE IF EXISTS `plugin_onlineshop_pricing_rule`");

        // remove permissions
        $key = 'plugin_onlineshop_pricing_rules';
        $db = Pimcore_Resource::get();
        $db->delete('users_permission_definitions', '`key` = ' . $db->quote($key) );

        return true;
    }



    public function maintenance() {
        $checkoutManager = OnlineShop_Framework_Factory::getInstance()->getCheckoutManager(new OnlineShop_Framework_Impl_Cart());
        $checkoutManager->cleanUpPendingOrders();
    }
}
