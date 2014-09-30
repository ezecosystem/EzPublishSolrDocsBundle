<?php

namespace xrow\EzPublishSolrDocsBundle\Lib;
use DOMDocument;
use DOMXPath;

class ODataHelper
{
    public static function validateAgainstXSD($pathToInputxml, $pathToValidatesxd)
    {

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->load($pathToInputxml);
        if(!$dom->schemaValidate($pathToValidatesxd))
        {
            $errors = libxml_get_errors();
            return array("status" => false, "errors" => $errors);
        }
        else return array("status" => true, "errors" => array());
    }
}