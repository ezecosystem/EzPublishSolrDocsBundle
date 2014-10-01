<?php
namespace xrow\OData;

use DOMDocument;
use DOMXPath;

class Helper
{
    const SCHEMA = "vendor/xrow/ezpublish-solrdocs-bundle/Resources/schema/atom-oasis.xsd";
    public static function validate($pathToInputxml, &$errors)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->load($pathToInputxml);
        #$dom->xinclude();
        $errors = array();
        if (! $dom->schemaValidate(self::SCHEMA)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            return false;
        } else {
            return true;
        }
    }

    public static function LibXMLErrorToString($error)
    {
        $return = "";
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "Warning $error->code: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "Error $error->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "Fatal Error $error->code: ";
                break;
        }
        $return .= trim($error->message);
        if ($error->file) {
            $return .= " in $error->file";
        }
        if ($error->line) {
            $return .= " on line $error->line";
        }
        if ($error->column) {
            $return .= " on column $error->column";
        }
        return $return;
    }
}