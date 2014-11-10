<?php 

namespace xrow\OData;

use xrow\EzPublishSolrDocsBundle\src\Import\ImportSource;
use xrow\OData;
use DOMDocument;
use XMLReader;
use DateTime;

class Source extends ImportSource
{
    public function current( $optionalRow=null )
    {
        
        if ( !$this->_entries )
        {
            $this->_entries = $this->feed->getElementsByTagName('feed')->item(0)->getElementsByTagName('entry');
        }
        $attrib=array();
        if( $optionalRow == null )
        {
            $elementsRow = (int)$this->_iterations;
        }
        else 
        {
            $elementsRow=(int)$optionalRow;
        }
        #$startzeit=microtime(true);
        #echo " " . $elementsRow . " ";
        #foreach($this->_entries->item($elementsRow)->getElementsByTagName('properties') as $elements)
        foreach($this->_entries[$elementsRow]->getElementsByTagName('properties') as $elements)
        {
            foreach( $elements->childNodes as $entry)
            {
                if($entry->nodeName != "#text")
                {
                    if( $entry->getAttribute('metadata:type') == "String" )
                    {
                        if($entry->textContent == "")
                            $attribs[$entry->localName] = "";
                        else
                            $attribs[$entry->localName] = (string)$entry->textContent;
                    }
                    elseif( $entry->getAttribute('metadata:type') == "Decimal" )
                    {
                        if($entry->textContent == "")
                            $attribs[$entry->localName] = (float)0;
                        else
                            $attribs[$entry->localName] = (float)$entry->textContent;
                    }
                    elseif( $entry->getAttribute('metadata:type') == "Double" )
                    {
                        if($entry->textContent == "")
                            $attribs[$entry->localName] = (float)0;
                        else
                            $attribs[$entry->localName] = (float)$entry->textContent;
                    }
                    elseif( $entry->getAttribute('metadata:type') == "Int16" || $entry->getAttribute('m:type') == "Int32" || $entry->getAttribute('m:type') == "Int64" )
                    {
                        if($entry->textContent == "")
                            $attribs[$entry->localName] = (int)0;
                        else
                            $attribs[$entry->localName] = (int)$entry->textContent;
                    }
                    elseif( $entry->getAttribute('metadata:type') == "Boolean" )
                    {
                        if($entry->textContent == "")
                            $attribs[$entry->localName] = (boolean) false;
                        else
                            $attribs[$entry->localName] = (boolean)$entry->textContent;
                    }
                    elseif( $entry->getAttribute('metadata:type') == "DateTimeOffset" )
                    {
                        if($entry->textContent == "")
                            $attribs[$entry->localName] = "";
                        else
                        {
                            #$attribs[$entry->localName] = strtotime($entry->textContent);
                            $attribs[$entry->localName] = (int)strtotime($entry->textContent);
                        }
                    }
                    elseif( $entry->getAttribute('metadata:type') == "DateTime" )
                    {
                        if($entry->textContent == "")
                            $attribs[$entry->localName] = "";
                        else
                            $attribs[$entry->localName] = strtotime($entry->textContent);
                    }
                    elseif( $entry->getAttribute('metadata:type') == "Collection" )
                    {
                        $collection=array();
                        foreach( $entry->childNodes as $child)
                        {
                            if($child->localName == "element")
                            {
                                $collection[] = trim((string)$child->textContent);
                            }
                        }
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
        /*
        $durationInMilliseconds = (microtime(true) - $startzeit) * 1000;
        $timing = number_format($durationInMilliseconds, 3, '.', '') . "ms";
        if($durationInMilliseconds > 1000)
        {
            $timing = number_format($durationInMilliseconds / 1000, 1, '.', '') . "sec";
        }
        echo "\nP:" . $timing;
        */
        return $attribs;
    }
    public function __construct ($xml, $offset, $limit, $contenttypeidentifier)
    {
        /*
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
        */
        
        $this->feed = new DOMDocument();
        if (!@$this->feed->load($xml))
        {
            throw new \Exception( "The source is not readable." );
        }
        $reader = new XMLReader();
        
        $reader->open($xml);
        
        $my_docs=array();
        while ($reader->read())
        {
            switch ($reader->nodeType)
            {
                case (XMLReader::ELEMENT):
                    if ($reader->localName == "entry")
                    {
                        $node = $reader->expand();
                        $dom = new DomDocument();
                        $n = $dom->importNode($node,true);
                        $dom->appendChild($n);
                        $my_docs[] = $dom;
                    }
            }
        }
        $this->toKey(0);
        $this->setOffset($offset);
        $this->setLimit($limit);
        $this->setContentTypeIdentifier($contenttypeidentifier);
        $this->title = $this->feed->getElementsByTagName('feed')->item(0)->getElementsByTagName('title')->item(0)->nodeValue;
        $this->id = $this->feed->getElementsByTagName('feed')->item(0)->getElementsByTagName('id')->item(0)->nodeValue;
        #$this->_entries = $this->feed->getElementsByTagName('feed')->item(0)->getElementsByTagName('entry');
        $this->_entries = $my_docs;
        
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