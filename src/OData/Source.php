<?php 

namespace xrow\OData;

use xrow\EzPublishSolrDocsBundle\src\Import\ImportSource;
use DOMDocument;

class Source extends ImportSource
{
    public function __construct ($xml)
    {
        $feed = new DOMDocument();
        $feed->load($xml);
        $this->title = $feed->getElementsByTagName('feed')->item(0)->getElementsByTagName('title')->item(0)->nodeValue;
        $this->id = $feed->getElementsByTagName('feed')->item(0)->getElementsByTagName('id')->item(0)->nodeValue;
        $items = $feed->getElementsByTagName('feed')->item(0)->getElementsByTagName('entry');
        $attribs = array();
        foreach($items as $key => $item)
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
                                $attribs[$key][$entry->localName] = "";
                            else
                                $attribs[$key][$entry->localName] = $entry->textContent;
                        }
                        if( $entry->getAttribute('metadata:type') == "Edm.Decimal" )
                        {
                            if($entry->textContent == "")
                                $attribs[$key][$entry->localName] = (float)0;
                            else
                                $attribs[$key][$entry->localName] = (float)$entry->textContent;
                        }
                        if( $entry->getAttribute('metadata:type') == "Edm.DateTime" )
                        {
                            if($entry->textContent == "")
                                $attribs[$key][$entry->localName] = "";
                            else
                                $attribs[$key][$entry->localName] = strtotime($entry->textContent);
                        }
                        if( $entry->getAttribute('metadata:type') == "Collection(Edm.String)" )
                        {
                            preg_match_all('|<data\:element>(.*)</data\:element>|U', $entry->textContent, $arrXml);
                            $collection=$arrXml[1];
                            if( count($collection) > 0 )
                                $attribs[$key][$entry->localName] = $collection;
                            else
                                $attribs[$key][$entry->localName] = array("");
                        }
                    }
                }
            }
        }
        $this->_entries = $attribs;
    }
}