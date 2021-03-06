<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Search\FacetBuilderVisitor;

use xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Search\FacetBuilderVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * Visits the ContentType facet builder
 */
class ContentType extends FacetBuilderVisitor
{
    /**
     * CHeck if visitor is applicable to current facet result
     *
     * @param string $field
     *
     * @return boolean
     */
    public function canMap( $field )
    {
        return $field === 'meta_class_name_ms';
    }

    /**
     * Map Solr facet result back to facet objects
     *
     * @param string $field
     * @param array $data
     *
     * @return Facet
     */
    public function map( $field, array $data )
    {
        return new Facet\ContentTypeFacet(
            array(
                'name'    => 'Class',
                'entries' => $this->mapData( $data ),
            )
        );
    }

    /**
     * Check if visitor is applicable to current facet builder
     *
     * @param FacetBuilder $facetBuilder
     *
     * @return boolean
     */
    public function canVisit( FacetBuilder $facetBuilder )
    {
        #var_dump("canvisit ContentType");
        #var_dump($facetBuilder instanceof \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\ContentTypeFacetBuilder);
        #var_dump($facetBuilder);
        return $facetBuilder instanceof \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\ContentTypeFacetBuilder;
        #return $facetBuilder instanceof FacetBuilder\ContentTypeFacetBuilder;
        #return $facetBuilder instanceof \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param FacetBuilder $facetBuilder;
     *
     * @return string
     */
    public function visit( FacetBuilder $facetBuilder )
    {
        $fieldpath="meta_class_name_ms";
        if( $facetBuilder->name != "" )
        {
            $facetname="{!ex=dt key=" . $facetBuilder->name . "}" . $fieldpath;
        }
        else
        {
            $facetname=$fieldpath;
        }

        
        return http_build_query(
                    array(
                        'facet.field'             => $facetname,
                        'f.meta_class_name_ms.facet.limit'    => $facetBuilder->limit,
                        'f.meta_class_name_ms.facet.mincount' => $facetBuilder->minCount,
                    )
                );
    }
}

