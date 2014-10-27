<?php 

namespace xrow\OData;

use xrow\EzPublishSolrDocsBundle\src\Import\ImportSource;
use xrow\OData;
use DOMDocument;

class Source extends ImportSource
{
    public function current()
    {
        if ( !$this->_entries )
        {
            $this->_entries = $this->feed->getElementsByTagName('feed')->item(0)->getElementsByTagName('entry');
        }
        $attrib=array();
        foreach($this->_entries->item($this->_iterations)->getElementsByTagName('properties') as $elements)
        {
            foreach( $elements->childNodes as $entry)
            {
                if($entry->nodeName != "#text")
                {
                    if( $entry->getAttribute('m:type') == "Edm.String" )
                    {
                        if($entry->textContent == "")
                            $attribs[$entry->localName] = "";
                        else
                            $attribs[$entry->localName] = $entry->textContent;
                    }
                    elseif( $entry->getAttribute('m:type') == "Decimal" )
                    {
                        if($entry->textContent == "")
                            $attribs[$entry->localName] = (float)0;
                        else
                            $attribs[$entry->localName] = (float)$entry->textContent;
                    }
                    elseif( $entry->getAttribute('m:type') == "Int16" )
                    {
                        if($entry->textContent == "")
                            $attribs[$entry->localName] = (int)0;
                        else
                            $attribs[$entry->localName] = (int)$entry->textContent;
                    }
                    elseif( $entry->getAttribute('m:type') == "Int32" )
                    {
                        if($entry->textContent == "")
                            $attribs[$entry->localName] = (int)0;
                        else
                            $attribs[$entry->localName] = (int)$entry->textContent;
                    }
                    elseif( $entry->getAttribute('m:type') == "Boolean" )
                    {
                        if($entry->textContent == "")
                            $attribs[$entry->localName] = (boolean) false;
                        else
                            $attribs[$entry->localName] = (boolean)$entry->textContent;
                    }
                    elseif( $entry->getAttribute('metadata:type') == "Edm.DateTime" )
                    {
                        if($entry->textContent == "")
                            $attribs[$entry->localName] = "";
                        else
                            $attribs[$entry->localName] = strtotime($entry->textContent);
                    }
                    elseif( $entry->getAttribute('metadata:type') == "Collection(Edm.String)" )
                    {
                        preg_match_all('|<data\:element>(.*)</data\:element>|U', $entry->textContent, $arrXml);
                        $collection=$arrXml[1];
                        if( count($collection) > 0 )
                            $attribs[$entry->localName] = $collection;
                        else
                            $attribs[$entry->localName] = array("");
                    }
                    else
                    {
                        if($entry->textContent == "")
                            $attribs[$entry->localName] = "";
                        else
                            $attribs[$entry->localName] = $entry->textContent;
                    }
                }
            }
        }

        return $attribs;
    }
    public function __construct ($xml, $offset, $limit, $contenttypeidentifier)
    {
        $this->feed = new DOMDocument();
        if (!@$this->feed->load($xml))
        {
            throw new \Exception( "The source is not readable." );
        }
        $this->toKey(0);
        $this->setOffset($offset);
        $this->setLimit($limit);
        $this->setContentTypeIdentifier($contenttypeidentifier);
        $this->title = $this->feed->getElementsByTagName('feed')->item(0)->getElementsByTagName('title')->item(0)->nodeValue;
        $this->id = $this->feed->getElementsByTagName('feed')->item(0)->getElementsByTagName('id')->item(0)->nodeValue;
        $this->_entries = $this->feed->getElementsByTagName('feed')->item(0)->getElementsByTagName('entry');
    }
    
    public function validateImport( )
    {
        try {
            $check = OData\Helper::validateDom($this->feed, $errors, $this->_contenttypeidentifier );

            if ($check) {
                echo"Document is a valid ODATA source. \n";
                return true;
            } else {
                echo "Document isn`t valid ODATA source\n";
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