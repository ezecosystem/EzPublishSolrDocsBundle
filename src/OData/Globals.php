<?php

namespace xrow\OData;
 
class Globals
{
    protected static $solrServerConfig;
    
    protected static $solrglobalconfig;
    
    protected static $solrClassesConfig;
    
    public static function setSolrServerConfig($solrconfig)
    {
        self::$solrServerConfig = $solrconfig;
    }
 
    public static function getSolrServerConfig()
    {
        return self::$solrServerConfig;
    }
    
    public static function setSolrGlobalConfig($globalconfig)
    {
        self::$solrglobalconfig = $globalconfig;
    }
    
    public static function getSolrGlobalConfig()
    {
        return self::$solrglobalconfig;
    }
    
    public static function setSolrClassesConfig($solrclassesconfig)
    {
        self::$solrClassesConfig = $solrclassesconfig;
    }
    
    public static function getSolrClassesConfig()
    {
        return self::$solrClassesConfig;
    }
}