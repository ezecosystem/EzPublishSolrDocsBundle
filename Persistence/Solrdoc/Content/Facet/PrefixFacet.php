<?php

namespace xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Facet;

use eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * This class holds counts of content with content type
 *
 */
class PrefixFacet extends Facet
{
    /**
     * An array with contentTypeIdentifier as key and count of matching content objects as value
     *
     * @var array
     */
    public $entries;
    
    public $searchpart;
}
