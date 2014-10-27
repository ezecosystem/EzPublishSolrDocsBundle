<?php
namespace xrow\OData;

use DOMDocument;
use DOMXPath;

class Helper
{
    const SCHEMA_ODATA4_test = "vendor/xrow/ezpublish-solrdocs-bundle/Resources/schema/odata4_classes/odata4_content_solrtestdoc.xsd";
    
    public static function validate($pathToInputxml, &$errors)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->load($pathToInputxml);
        if (! $dom->schemaValidate(self::SCHEMA_ODATA4_test)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            return false;
        }
        else
        {
            return true;
            
        }
    }
    
    public static function validateDom( $dom, &$errors, $contenttypeidentifier)
    {
        $schemaplace=self::CreateODataSchemas($contenttypeidentifier);
        libxml_use_internal_errors(true);
        $errors = array();
        if (! $dom->schemaValidate($schemaplace)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            return false;
        }
        else
        {
            return true;
        }
    }

    public static function CreateODataSchemas($contenttypeidentifier)
    {
        
        $class=$contenttypeidentifier;
        
                $basepath="vendor/xrow/ezpublish-solrdocs-bundle/Resources/schema/odata4_classes/";
                $basexsd=$basepath."odata4_content.xsd.replaceme";
                $basedataxsd=$basepath."odata4_content_data.xsd.replaceme";
                $basemetadataxsd=$basepath."odata4_content_metadata.xsd.replaceme";
        
                $tmpxsd=$basepath."odata4_content_" . $class["identifier"] . ".xsd";
                $tmpdataxsd=$basepath."odata4_content_data_" . $class["identifier"] . ".xsd";
                $tmpmetadataxsd=$basepath."odata4_content_metadata_" . $class["identifier"] . ".xsd";
        
                $xsdcontent=file_get_contents($basexsd);
                $xsdcontent=str_replace("{odata4_content_data}", "odata4_content_data_" . $class["identifier"].".xsd", $xsdcontent);
                $xsdcontent=str_replace("{odata4_content_metadata}", "odata4_content_metadata_" . $class["identifier"].".xsd", $xsdcontent);
        
                $tempxsd_new=fopen($tmpxsd, "w") or die("Unable to open file!");
                fwrite($tempxsd_new, $xsdcontent);
                fclose($tempxsd_new);
        
        
                $classdefmetadata="";
                $classdefdata="";
                foreach( $class["fields"] as $field )
                {
                    $classdefmetadata.="<xsd:element name=\"" . $field["identifier"] . "\" />\n";
                    if( $field["isRequired"] )
                    {
                        $classdefdata.="<xsd:element ref=\"d:" . $field["identifier"] . "\" minOccurs=\"1\"/>\n";
                    }
                    else
                    {
                        $classdefdata.="<xsd:element ref=\"d:" . $field["identifier"] . "\" minOccurs=\"0\"/>\n";
                    }
                }
        
                $xsdcontent=file_get_contents($basedataxsd);
                $xsdcontent=str_replace("{odata4classdescription}", $classdefmetadata, $xsdcontent);
                $tempxsd_new=fopen($tmpdataxsd, "w") or die("Unable to open file!");
                fwrite($tempxsd_new, $xsdcontent);
                fclose($tempxsd_new);
        
                $xsdcontent=file_get_contents($basemetadataxsd);
                $xsdcontent=str_replace("{odata4classdescriptionmetadata}", $classdefdata, $xsdcontent);
                $xsdcontent=str_replace("{odata4_content_data}", "odata4_content_data_" . $class["identifier"].".xsd", $xsdcontent);
                $tempxsd_new=fopen($tmpmetadataxsd, "w") or die("Unable to open file!");
                fwrite($tempxsd_new, $xsdcontent);
                fclose($tempxsd_new);

        return $tmpxsd;
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