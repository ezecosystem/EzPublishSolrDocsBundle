<?php

namespace xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Query\FacetBuilder;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

/**
 * Building a content type facet.
 *
 * If provided the search service returns a ContentTypeFacet
 *
 * @package eZ\Publish\API\Repository\Values\Content\Query
 */
class PrefixFacetBuilder extends FacetBuilder
{
    /**
     * The field paths starts with a field identifier and a sub path (for complex types)
     *
     * @var string[]
     */
    public $fieldPaths;
    
    
    /**
     * The field paths starts with a field identifier and a sub path (for complex types)
     *
     * @var string[]
     */
    public $searchpart;
    
    
}
