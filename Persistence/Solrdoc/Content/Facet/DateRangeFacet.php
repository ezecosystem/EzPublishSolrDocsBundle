<?php

namespace xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Facet;

use eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * This class holds counts of content with content type
 *
 */
class DateRangeFacet extends Facet
{
    /**
     * An array with contentTypeIdentifier as key and count of matching content objects as value
     *
     * @var array
     */
    public $entries;
    
    /**
     * The field paths starts with a field identifier and a sub path (for complex types)
     *
     * @var string[]
     */
    public $searchpart;
}
