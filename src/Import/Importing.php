<?php

namespace xrow\EzPublishSolrDocsBundle\src\Import;

use \eZ\Publish\API\Repository\Values\Content\Location;

interface Importing
{
    public function __construct( $location, $ContentType, ImportSource $source, $repository );
    public function import(  );
    public function validate( $linktoxml );
    public function mapClass( $entry, $contentCreateStruct);
    
}