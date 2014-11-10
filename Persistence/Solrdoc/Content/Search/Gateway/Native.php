<?php
/**
 * File containing the Content Search Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Search\Gateway;

use xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Search\Gateway;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Query;
use xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Search\FieldNameGenerator;
use xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Search\CriterionVisitor;
use xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Search\SortClauseVisitor;
use xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Search\FacetBuilderVisitor;
use xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Search\FieldValueMapper;
use RuntimeException;

/**
 * The Content Search Gateway provides the implementation for one database to
 * retrieve the desired content objects.
 */
class Native extends Gateway
{
    /**
     * HTTP client to communicate with Solr server
     *
     * @var HttpClient
     */
    protected $client;

    /**
     * Query visitor
     *
     * @var CriterionVisitor
     */
    protected $criterionVisitor;

    /**
     * Sort clause visitor
     *
     * @var SortClauseVisitor
     */
    protected $sortClauseVisitor;

    /**
     * Facet builder visitor
     *
     * @var FacetBuilderVisitor
     */
    protected $facetBuilderVisitor;

    /**
     * Field value mapper
     *
     * @var FieldValueMapper
     */
    protected $fieldValueMapper;

    /**
     * Content Handler
     *
     * @var ContentHandler
     */
    protected $contentHandler;

    /**
     * Field name generator
     *
     * @var FieldNameGenerator
     */
    protected $nameGenerator;

    /**
     * @var bool
     */
    protected $commit = true;

    /**
     * Construct from HTTP client
     *
     * @param HttpClient $client
     * @param CriterionVisitor $criterionVisitor
     * @param SortClauseVisitor $sortClauseVisitor
     * @param FacetBuilderVisitor $facetBuilderVisitor
     * @param FieldValueMapper $fieldValueMapper
     * @param ContentHandler $contentHandler
     *
     * @return void
     */
    public function __construct( HttpClient $client, CriterionVisitor $criterionVisitor, SortClauseVisitor $sortClauseVisitor, FacetBuilderVisitor $facetBuilderVisitor, FieldValueMapper $fieldValueMapper, ContentHandler $contentHandler, FieldNameGenerator $nameGenerator )
    {
        $this->client              = $client;
        $this->criterionVisitor    = $criterionVisitor;
        $this->sortClauseVisitor   = $sortClauseVisitor;
        $this->facetBuilderVisitor = $facetBuilderVisitor;
        $this->fieldValueMapper    = $fieldValueMapper;
        $this->contentHandler      = $contentHandler;
        $this->nameGenerator       = $nameGenerator;
    }

    /**
     * Finds content objects for the given query.
     *
     * @todo define structs for the field filters
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findContent( Query $query, array $fieldFilters = array() )
    {
        $parameters = array(
            "q" => $this->criterionVisitor->visit( $query->query ),
            "fq" => $this->criterionVisitor->visit( $query->filter ),
            "sort" => implode(
                ", ",
                array_map(
                    array( $this->sortClauseVisitor, "visit" ),
                    $query->sortClauses
                )
            ),
            "fl" => "*,score",
            "wt" => "json",
        );

        if ( $query->offset !== null )
        {
            $parameters["start"] = $query->offset;
        }

        if ( $query->limit !== null )
        {
            $parameters["rows"] = $query->limit;
        }


        
        // @todo: Extract method
        $response = $this->client->request(
            'GET',
            '/solr/haz.de/select?' .
            http_build_query( $parameters ) .
            ( count( $query->facetBuilders ) ? '&facet=true&facet.sort=count&' : '' ) .
            implode(
                '&',
                array_map(
                    array( $this->facetBuilderVisitor, 'visit' ),
                    $query->facetBuilders
                )
            )."&hl=true&hl.fl=ezf_df_text&hl.simple.pre=<b>&hl.simple.post=<%2Fb>"
        );
       
        /*
        var_dump('/solr/haz.de/select?' .
            http_build_query( $parameters ) .
            ( count( $query->facetBuilders ) ? '&facet=true&facet.sort=count&' : '' ) .
            implode(
                '&',
                array_map(
                    array( $this->facetBuilderVisitor, 'visit' ),
                    $query->facetBuilders
                )
            )."&hl=true&hl.fl=ezf_df_text&hl.simple.pre=<b>&hl.simple.post=<%2Fb>");
            
        die("ende");
        */
        
        
        // @todo: Error handling?
        if( $response->headers["status"] == 200 )
        {
            $data = json_decode( $response->body );
            // @todo: Extract method
            
            $result = new SearchResult(
                    array(
                            'time'       => $data->responseHeader->QTime / 1000,
                            'maxScore'   => $data->response->maxScore,
                            'totalCount' => $data->response->numFound,
                    )
            );
            $highlight_array = array();
            if ( isset( $data->highlighting ) )
            {
                foreach ( $data->highlighting as $h_remoteid => $highlight )
                {
                    foreach( $highlight as $highlight_item )
                    {
                        $highlight_array[$h_remoteid] = $highlight_item;
                    }
                }
            }
            
            foreach ( $data->response->docs as $doc )
            {
                /*
                 $searchHit = new SearchHit(
                         array(
                                 'score'       => $doc->score,
                                 'valueObject' => $this->contentHandler->load( $doc->id, $doc->version_id )
                         )
                 );
                */
                $highlight_toID = "";
                if( array_key_exists($doc->meta_guid_ms, $highlight_array) )
                {
                    if( count($highlight_array[$doc->meta_guid_ms]) > 0  )
                    {
                        $highlight_toID = $highlight_array[$doc->meta_guid_ms][0];
                    }
                }
                if($doc->meta_id_si > 0 )
                {
                    /*
                    $searchHit = new SearchHit(
                            array(
                                    'score'       => $doc->score,
                                    'valueObject' => $this->contentHandler->load( $doc->meta_id_si, $doc->meta_current_version_si )
                            )
                    );
                    */
                    $searchHit = new SearchHit(
                            array(
                                    'score'       => $doc->score,
                                    'valueObject' => $doc,
                                    'highlight' => $highlight_toID
                            )
                    );
                }
                else
                {
                    $searchHit = new SearchHit(
                            array(
                                    'score'       => $doc->score,
                                    'valueObject' => $doc,
                                    'highlight' => $highlight_toID
                            )
                    );
                }
            
                $result->searchHits[] = $searchHit;
            }
            
            
            if ( isset( $data->facet_counts ) )
            {
                foreach ( $data->facet_counts->facet_fields as $field => $facet )
                {
                    $result->facets[] = $this->facetBuilderVisitor->map( $field, $facet );
                }
                foreach ( $data->facet_counts->facet_queries as $field => $facet )
                {
                    $result->facets[] = $this->facetBuilderVisitor->map( $field, array($facet) );
                }
            }
            

            return $result;
        }
        else
        {
            $data = json_decode( $response->body );
            
            $result = new SearchResult(
                    array(
                            'time'       => 0,
                            'maxScore'   => 0,
                            'totalCount' => 0,
                    )
            );
            return $result;
        }
    }

    /**
     * Indexes a content object
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Search\Field[][] $documents
     * @todo $documents should be generated more on demand then this and sent to Solr in chunks before final commit
     *
     * @return void
     */
    public function bulkIndexContent( array $documents )
    {
        $updates   = $this->createUpdates( $documents );
        $result   = $this->client->request(
            'POST',
            '/solr/update?' . ( $this->commit ? "softCommit=true&" : "" ) . 'wt=json',
            new Message(
                array(
                    'Content-Type' => 'text/xml',
                ),
                $updates
            )
        );

        if ( $result->headers["status"] !== 200 )
        {
            throw new RuntimeException( "Wrong HTTP status received from Solr: " . $result->headers["status"] );
        }
    }

    /**
     * Deletes a content object from the index
     *
     * @param int content id
     * @param int|null version id
     *
     * @return void
     */
    public function deleteContent( $contentId, $versionId = null )
    {
        $this->client->request(
            'POST',
            '/solr/update?' . ( $this->commit ? "softCommit=true&" : "" ) . 'wt=json',
            new Message(
                array(
                    'Content-Type' => 'text/xml',
                ),
                "<delete><query>id:" . (int)$contentId . ( $versionId !== null ? " AND version_id:" . (int)$versionId : "" ) . "</query></delete>"
            )
        );
    }

    /**
     * Deletes a location from the index
     *
     * @param mixed $locationId
     */
    public function deleteLocation( $locationId )
    {
        $response = $this->client->request(
            'GET',
            '/solr/select?' .
            http_build_query(
                array(
                    "q" => "path_mid:*/$locationId/*",
                    "fl" => "*",
                    "wt" => "json",
                )
            )
        );
        // @todo: Error handling?
        $data = json_decode( $response->body );

        $locationParent = array( $locationId );
        $contentToDelete = $contentToUpdate = array();
        foreach ( $data->response->docs as $doc )
        {
            // Check that this document only had one location in which case it can be removed.
            // @todo When orphaned objects will be possible, we will have to update those doc instead of removing.
            if ( $doc->location_parent_mid == $locationParent || $doc->location_mid == $locationParent )
            {
                $contentToDelete[] = $doc->id;
            }
            else
            {
                $contentToUpdate[] = $doc;
            }
        }

        if ( !empty( $contentToDelete ) )
        {
            $this->client->request(
                "POST",
                "/solr/update?" . ( $this->commit ? "softCommit=true&" : "" ) . "wt=json",
                new Message(
                    array(
                        "Content-Type" => "text/xml",
                    ),
                    "<delete><query>id:(" . implode( " ", $contentToDelete ) . ")</query></delete>"
                )
            );
        }

        if ( !empty( $contentToUpdate ) )
        {
            $jsonString = "";
            foreach ( $contentToUpdate as $doc )
            {
                // Removing location references in location_parent_mid, location_mid and path_mid
                // main_* fields are not modified since removing main node is not permitted.
                foreach ( $doc->location_parent_mid as $key => $value )
                {
                    if ( $value == $locationId )
                    {
                        unset( $doc->location_parent_mid[$key] );
                    }
                }
                foreach ( $doc->location_mid as $key => $value )
                {
                    if ( $value == $locationId )
                    {
                        unset( $doc->location_mid[$key] );
                    }
                }
                foreach ( $doc->path_mid as $key => $value )
                {
                    if ( strpos( $value, "/$locationId/" ) )
                    {
                        unset( $doc->path_mid[$key] );
                    }
                }

                // Reindex arrays
                $doc->location_parent_mid = array_values( $doc->location_parent_mid );
                $doc->location_mid = array_values( $doc->location_mid );
                $doc->path_mid = array_values( $doc->path_mid );

                if ( !empty( $jsonString ) )
                    $jsonString .= ",";

                $jsonString .= '"add": { "doc": ' . json_encode( $doc ) . "}";
            }

            $this->client->request(
                "POST",
                "/solr/update/json?" . ( $this->commit ? "softCommit=true&" : "" ) . "wt=json",
                new Message(
                    array(
                        "Content-Type: application/json",
                    ),
                    "{ $jsonString }"
                )
            );
        }
    }

    /**
     * Purges all contents from the index
     *
     * @return void
     */
    public function purgeIndex()
    {
        $this->client->request(
            'POST',
            '/solr/update?' . ( $this->commit ? "softCommit=true&" : "" ) . 'wt=json',
            new Message(
                array(
                    'Content-Type' => 'text/xml',
                ),
                '<delete><query>*:*</query></delete>'
            )
        );
    }

    /**
     * @param bool $commit
     */
    public function setCommit( $commit )
    {
        $this->commit = !!$commit;
    }

    /**
     * Create document(s) update XML
     *
     * @param array $documents
     *
     * @return string
     */
    protected function createUpdates( array $documents )
    {
        $xml = new \XmlWriter();
        $xml->openMemory();
        $xml->startElement( 'add' );

        foreach ( $documents as $document )
        {
            $xml->startElement( 'doc' );
            foreach ( $document as $field )
            {
                foreach ( (array)$this->fieldValueMapper->map( $field ) as $value )
                {
                    $xml->startElement( 'field' );
                    $xml->writeAttribute(
                        'name',
                        $this->nameGenerator->getTypedName( $field->name, $field->type )
                    );
                    $xml->text( $value );
                    $xml->endElement();
                }
            }
            $xml->endElement();
        }
        $xml->endElement();
        return $xml->outputMemory( true );
    }
}
