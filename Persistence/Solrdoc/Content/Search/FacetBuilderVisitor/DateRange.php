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
#use xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * Visits the Field facet builder
 */
class DateRange extends FacetBuilderVisitor
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
        $checkfield=explode("meta_modified_dt_", $field);
        if( count($checkfield) ==2 )
        {
            return true;
        }
        else
        {
            $checkfield2=explode("attr_veroeffentlichungsdatum_dt_", $field);
            if( count($checkfield2) ==2 )
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        #return $field === 'meta_modified_dt';
        #return true;
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

        $field_string = explode("meta_modified_dt_", $field);
        $field_string2 = explode("attr_veroeffentlichungsdatum_dt_", $field);
        if( count($field_string2) > 1 )
        {
            $field_string = $field_string2;
        }
        return new \xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Facet\DateRangeFacet(
                array(
                        'name'    => "DateRange",
                        'entries' => array($field_string[1] => $data[0]),
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
        #var_dump("canvisit DateRange");
        #var_dump($facetBuilder instanceof \xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Query\FacetBuilder\DateRangeFacetBuilder);
        #var_dump($facetBuilder);
        #return true;
        return $facetBuilder instanceof \xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Query\FacetBuilder\DateRangeFacetBuilder;
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
        
        $fieldpath="meta_modified_dt";
        if( $facetBuilder->fieldPaths != "" )
        {
            $fieldpath=$facetBuilder->fieldPaths;
        }
        if( $facetBuilder->name != "" )
        {
            $facetname="{!ex=dt key=" . $facetBuilder->name . "}" . $fieldpath;
        }
        
        return http_build_query(
                    array(
                        'facet.query' => '{!ex=dt key="' . $fieldpath . '_heute"}' . $fieldpath.':[NOW/DAY TO NOW/DAY+1DAY]')) .
                        "&" .
                        http_build_query(
                        array(
                        'facet.query' => '{!ex=dt key="' . $fieldpath . '_in den letzten 7 Tagen"}' . $fieldpath.':[NOW/DAY-7DAYS TO NOW/DAY]')) .
                        "&" .
                        http_build_query(
                        array(
                        'facet.query' => '{!ex=dt key="' . $fieldpath . '_in den letzten 30 Tagen"}'. $fieldpath.':[NOW/DAY-31DAYS TO NOW/DAY-7DAYS]')) .
                        "&" .
                        http_build_query(
                        array(
                        'facet.query' => '{!ex=dt key="' . $fieldpath . '_in den letzten 365 Tagen"}'.$fieldpath.':[NOW-31DAY TO NOW-7DAY]')
        );
    }
}

