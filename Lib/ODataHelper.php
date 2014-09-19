<?php

namespace xrow\EzPublishSolrDocsBundle\Lib;
use DOMDocument;
use DOMXPath;

class ODataHelper
{
    public static function validateAgainstXSD($pathToInputxml, $pathToValidatesxd)
    {
        set_error_handler(
        create_function(
        '$severity, $message, $file, $line',
        'throw new ErrorException($message, $severity, $severity, $file, $line);'
                )
        );
        try {
            $doc = new DOMDocument();
            $doc->load($pathToInputxml);
            $is_valid_xml = $doc->schemaValidate($pathToValidatesxd);
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }
        
        restore_error_handler();
        return $is_valid_xml;
    }

}