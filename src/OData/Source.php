<?php 

namespace xrow\OData;

use xrow\EzPublishSolrDocsBundle\src\Import\ImportSource;
use xrow\OData;
use DOMDocument;

class Source extends ImportSource
{
    public function __construct ($xml, $offset, $limit)
    {
        $feed = new DOMDocument();
        $feed->load($xml);
        echo "\nloaded... \n";
        $this->title = $feed->getElementsByTagName('feed')->item(0)->getElementsByTagName('title')->item(0)->nodeValue;
        $this->id = $feed->getElementsByTagName('feed')->item(0)->getElementsByTagName('id')->item(0)->nodeValue;
        $items = $feed->getElementsByTagName('feed')->item(0)->getElementsByTagName('entry');
        $attribs = array();
        $rows =0;
        foreach($items as $key => $item)
        {
            echo "Rows loaded: " . $key . "\r";
            if( $rows >= $limit )
            {
                break;
            }
            if( $key >= $offset )
            {
                foreach($item->getElementsByTagName('properties') as $elements)
                {
                    foreach( $elements->childNodes as $entry)
                    {
                        if($entry->nodeName != "#text")
                        {
                            if( $entry->getAttribute('metadata:type') == "Edm.String" )
                            {
                                if($entry->textContent == "")
                                    $attribs[$rows][$entry->localName] = "";
                                else
                                    $attribs[$rows][$entry->localName] = $entry->textContent;
                            }
                            if( $entry->getAttribute('metadata:type') == "Edm.Decimal" )
                            {
                                if($entry->textContent == "")
                                    $attribs[$rows][$entry->localName] = (float)0;
                                else
                                    $attribs[$rows][$entry->localName] = (float)$entry->textContent;
                            }
                            if( $entry->getAttribute('metadata:type') == "Edm.DateTime" )
                            {
                                if($entry->textContent == "")
                                    $attribs[$rows][$entry->localName] = "";
                                else
                                    $attribs[$rows][$entry->localName] = strtotime($entry->textContent);
                            }
                            if( $entry->getAttribute('metadata:type') == "Collection(Edm.String)" )
                            {
                                preg_match_all('|<data\:element>(.*)</data\:element>|U', $entry->textContent, $arrXml);
                                $collection=$arrXml[1];
                                if( count($collection) > 0 )
                                    $attribs[$rows][$entry->localName] = $collection;
                                else
                                    $attribs[$rows][$entry->localName] = array("");
                            }
                        }
                    }
                }
                $rows++;
            }
        }
        echo "\n";
        $this->_entries = $attribs;
    }
    
    public function validateImport( $linktoxml )
    {
        try {
        
            $check = OData\Helper::validate($linktoxml, $errors);
        
            if ($check) {
                echo"Document is a valid ODATA source. \n";
                return true;
            } else {
                echo "Document '" . $sourcefile . "' isn`t valid ODATA source\n";
                foreach ($errors as $error) {
                    echo OData\Helper::LibXMLErrorToString($error) . "\n";
                }
                throw new \Exception( "Source is not valid." );
            }
        }        // Content type or location not found
        catch (\eZ\Publish\API\Repository\Exceptions\NotFoundException $e) {
            $output->writeln($e->getMessage());
        }        // Invalid field value
        catch (\eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException $e) {
            $output->writeln($e->getMessage());
        }        // Required field missing or empty
        catch (\eZ\Publish\API\Repository\Exceptions\ContentValidationException $e) {
            $output->writeln($e->getMessage());
        }
    }
}