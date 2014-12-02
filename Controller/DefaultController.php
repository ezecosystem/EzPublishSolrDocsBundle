<?php

namespace xrow\EzPublishSolrDocsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('xrowEzPublishSolrDocsBundle:Default:index.html.twig', array('name' => $name));
    }

    public function solrDocViewAction( $remoteid )
    {
        $repository = $this->container->get( 'ezpublish.solrapi.repository' );
        #$searchService = $repository->getSearchService();
        $persistenceHandler = $this->container->get( 'ezpublish.spi.persistence.legacy_solrdoc' );
        $searchHandler = $persistenceHandler->searchHandler();
        $text = $remoteid;
        $result = $searchHandler->findSingle(new Query\Criterion\RemoteId( $text ) );

        $request = $this->container->get('request');
        $response = new Response();
        
        return $this->render("xrowEzPublishSolrDocsBundle:Default:solrdocviewsartikel.html.twig", array(
                'pagelayout' => "eZDemoBundle::pagelayout.html.twig",
                'route' => $request->get('_route'),
                'context' => $remoteid,
                'result' => (array)$result,
                'current_locale' => "ger-DE"
        ), $response);
    }
    

    
    public function solrDocUniversalSearchAutoSuggestAction( $config=1, $searchtext = ""  )
    {
        $response = new JsonResponse();
        $repository = $this->container->get( 'ezpublish.solrapi.repository' );
        $persistenceHandler = $this->container->get( 'ezpublish.spi.persistence.legacy_solrdoc' );
        $searchHandler = $persistenceHandler->searchHandler();
        $allfacetquery = new Query();
        $allfacetquery->query = new Query\Criterion\MatchAll();
        $allfacetquery->facetBuilders = array(
                new \xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Query\FacetBuilder\PrefixFacetBuilder(array("searchpart" => $searchtext, "name" => "Autosuggest")) 
        );
        $allfacetquery->limit = 0;
        $allfacets = $searchHandler->findContent( $allfacetquery );
        $facets=$allfacets->facets;
        $response->setData(array('message' => $searchtext, 'config' => $config, 'list' => $facets[0]->entries));
        return $response;
    }
    
    public function solrDocUniversalSearchAction( $searchtext = "" )
    {

        $repository = $this->container->get( 'ezpublish.solrapi.repository' );
        #$searchService = $repository->getSearchService();
        $persistenceHandler = $this->container->get( 'ezpublish.spi.persistence.legacy_solrdoc' );
        $searchHandler = $persistenceHandler->searchHandler();
        $reqparas = $this->get('request')->request->all();
        if( array_key_exists("SearchText", $reqparas))
        {
            $searchtext = $reqparas["SearchText"];
        }
        
        if( $searchtext == "" )
        {
            $request = $this->container->get('request');
            
            $allfacetquery = new Query();
            $allfacetquery->query = new Query\Criterion\MatchAll();
            $allfacetquery->facetBuilders = array(
                new \xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Query\FacetBuilder\DateRangeFacetBuilder(array("fieldPaths" => "attr_veroeffentlichungsdatum_dt", "name" => "Erschienen")),
                new Query\FacetBuilder\ContentTypeFacetBuilder(array("name" => "Klassen")),
                new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_rubriken____k", "name" => "Kategorien")),
                new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_schlagwoerter____k", "name" => "Schlagwörter", "limit" => 5)),
            );
           
            $allfacetquery->limit = 0;
            $allfacets = $searchHandler->findContent( $allfacetquery );
            
            
            $query = new Query();
            if( $request->query->get('Page') !== null)
            {
                $query->offset = (int)$request->query->get('Page') * 10;
            }
            $query->query = new Query\Criterion\MatchAll();

            if( $request->query->get('sortorder') !== null)
            {
                if($request->query->get('sortorder') == "1"  )
                {
                    $query->sortClauses = array( new Query\SortClause\DatePublished(Query::SORT_ASC) );
                }
                else
                {
                    $query->sortClauses = array( new Query\SortClause\DatePublished(Query::SORT_DESC) );
                }
            }
            if( $request->query->get('class_filter') !== null)
            {
                $classfilter = $request->query->get('class_filter');
                if( is_array( $classfilter ) )
                {
                    $query->filter = new Query\Criterion\LogicalAnd( array(
                            new Query\Criterion\CustomField("meta_class_name_ms", Operator::EQ, "(" . implode(" or ", $classfilter) . ")")
                    )
                    );
                }
                else
                {
                    $query->filter = new Query\Criterion\LogicalAnd( array(
                            new Query\Criterion\CustomField("meta_class_name_ms", Operator::EQ, $classfilter)
                    )
                    );
                }
                
            }
            
            $query->limit = 10;

            $result = $searchHandler->findContent( $query );

            $response = new Response();
        }
        else
        {
            $request = $this->container->get('request');
            $allfacetquery = new Query();
            $allfacetquery->query = new Query\Criterion\FullText( $searchtext );
            $allfacetquery->facetBuilders = array(
            new \xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Query\FacetBuilder\DateRangeFacetBuilder(array("fieldPaths" => "attr_veroeffentlichungsdatum_dt", "name" => "Erschienen")),
            new Query\FacetBuilder\ContentTypeFacetBuilder(array("name" => "Klassen")),
            new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_rubriken____k", "name" => "Kategorien")),
            new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_schlagwoerter____k", "name" => "Schlagwörter")),
            );
            $allfacetquery->limit = 0;
            $allfacets = $searchHandler->findContent( $allfacetquery );
            
            $query = new Query();
            if( $request->query->get('Page') !== null)
            {
                $query->offset = (int)$request->query->get('Page') * 10;
            }
            $query->query = new Query\Criterion\FullText( $searchtext );

            if( $request->query->get('class_filter') !== null)
            {
                $classfilter = $request->query->get('class_filter');
                if( is_array( $classfilter ) )
                {
                    $query->filter = new Query\Criterion\LogicalAnd( array(
                            new Query\Criterion\CustomField("meta_class_name_ms", Operator::EQ, "(" . implode(" or ", $classfilter) . ")")
                    ) );
                }
                else
                {
                    $query->filter = new Query\Criterion\LogicalAnd( array(
                            new Query\Criterion\CustomField("meta_class_name_ms", Operator::EQ, $classfilter)
                    ) );
                }
            
            }

            $query->limit = 10;
            $result = $searchHandler->findContent( $query );

            $response = new Response();
        }
        
        
        
        return $this->render("xrowEzPublishSolrDocsBundle:Default:solrdocuniversalsearch.html.twig", array(
                'pagelayout' => "eZDemoBundle::pagelayout.html.twig",
                'route' => $request->get('_route'),
                'context' => $searchtext,
                'result' => $result,
                'allfacets' => $allfacets,
                'current_locale' => "ger-DE"
        ), $response);
    }
    
    public function solrDocUniversalSearch2Action( )
    {
        $repository = $this->container->get( 'ezpublish.solrapi.repository' );
        $request = $this->container->get('request');
        $reqparas = $request->request->all();
        $persistenceHandler = $this->container->get( 'ezpublish.spi.persistence.legacy_solrdoc' );
        $searchHandler = $persistenceHandler->searchHandler();
        $response = new Response();
        return $this->render("xrowEzPublishSolrDocsBundle:Default:solrdocuniversalsearch_ajax.html.twig", array(
                'pagelayout' => "eZDemoBundle::pagelayout.html.twig",
        'route' => $request->get('_route'),
        'reqparas' => $reqparas,
        'context' => "test",
        'current_locale' => "ger-DE"
        ), $response);
    }

public function solrDocUniversalSearchAjaxAction( )
    {
        $jsonConfig=$this->container->getParameter('universalsearchtabconfig');
        $reqparas = $this->get('request')->request->all();
        $request_set=array();
        $request_set["tab"]="Alle";
        if( array_key_exists("config", $reqparas) )
        {
            $request_set["config"] = $reqparas["config"];
        }
        if( array_key_exists("searchtext", $reqparas) )
        {
            $request_set["searchtext"] = $reqparas["searchtext"];
        }
        if( array_key_exists("tabview", $reqparas) )
        {
            $request_set["tabview"] = $reqparas["tabview"];
            $request_set["tab"]=str_replace('tabView_', '', $request_set["tabview"]);
        }
        if( array_key_exists("offset", $reqparas) )
        {
            $request_set["offset"] = $reqparas["offset"];
        }
        if( array_key_exists("limit", $reqparas) )
        {
            $request_set["limit"] = $reqparas["limit"];
        }
        if( array_key_exists("queryfilter", $reqparas) )
        {
            $request_set["queryfilter"] = $reqparas["queryfilter"];
        }
        if( array_key_exists("page", $reqparas) )
        {
            $request_set["page"] = $reqparas["page"];
        }
        else
        {
            $request_set["page"] = 0;
        }
        
        $persistenceHandler = $this->container->get( 'ezpublish.spi.persistence.legacy_solrdoc' );
        $searchHandler = $persistenceHandler->searchHandler();
        $searchfor="*:*";
        if( $request_set["searchtext"] != ""){
            $searchfor = $request_set["searchtext"];
        }
        
        $allfacetquery = new Query();
        $allfacetquery->filter = new Query\Criterion\LogicalAnd( array(
                            new Query\Criterion\CustomField("meta_url_alias_ms", Operator::EQ, "1/root/haz.de/"),
                            new Query\Criterion\CustomField("meta_language_code_ms", Operator::EQ, "ger-DE"),
                            new Query\Criterion\FullText( $searchfor )
        ) );
        $allfacetquery->facetBuilders = array(
                new \xrow\EzPublishSolrDocsBundle\Persistence\Solrdoc\Content\Query\FacetBuilder\DateRangeFacetBuilder(array("fieldPaths" => "attr_veroeffentlichungsdatum_dt", "name" => "Erschienen")),
                new Query\FacetBuilder\ContentTypeFacetBuilder(array("name" => "Klassen", "minCount" => 0)),
                new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_rubriken____k", "name" => "Kategorien")),
                new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_schlagwoerter____k", "name" => "Schlagwörter")),
        );
        $allfacetquery->limit = 0;
        $allfacets = $searchHandler->findContent( $allfacetquery );
        $jsonFacets=array();
        $activefilters=array();
        $tabViewcount=0;
        foreach( $allfacets->facets as $facet )
        {
            if( $facet->name == "Klassen")
            {
                $jsonFacets["main"]["class"]=$facet->entries;
            }
            elseif( $facet->name == "DateRange")
            {
                foreach($facet->entries as $key => $entry)
                {
                    $jsonFacets["main"]["Erschienen"][$key]=$entry;
                }
                
            }
            else
            {
                $jsonFacets["main"][$facet->name]=$facet->entries;
            }
            
        }
        $jsonFacets["main"]["totalCount"]=$allfacets->totalCount;
        $jsonFacets["query"]=array();
        
        
        
        
        if( !array_key_exists("tabview", $request_set) || $request_set["tabview"] == "tabView_Alle" )
        {
            $results=array();
            $class_array=$jsonConfig[$request_set["config"]]["tabsClassesAll"];
            $tabViewcount=0;
            foreach( $class_array as $classID )
            {
                $query = new Query();
                $offset=(int)0;
                $limit=(int)3;
                $query->limit = $limit;
                if( $request_set["page"] > 0)
                {
                    $offset = $request_set["page"] * $limit;
                }
                $query->offset = $offset;
                $query->filter = new Query\Criterion\LogicalAnd( array(
                        new Query\Criterion\CustomField("meta_url_alias_ms", Operator::EQ, "1/root/haz.de/"),
                        new Query\Criterion\CustomField("meta_language_code_ms", Operator::EQ, "ger-DE"),
                        new Query\Criterion\FullText( $searchfor ),
                        new Query\Criterion\CustomField("meta_class_name_ms", Operator::EQ, $classID)
                ) );
                $query->sortClauses = array( new Query\SortClause\DatePublished(Query::SORT_DESC) );
                $result = $searchHandler->findContent( $query );
                $results[$classID]["elements"]=$result->searchHits;
                $results[$classID]["count"]=count($result->searchHits);
                $results[$classID]["totalcount"]=$result->totalCount;
                if( $results[$classID]["totalcount"] > $tabViewcount )
                {
                    $tabViewcount=$results[$classID]["totalcount"];
                }
            }
            
        }
        else
        {

            $results=array();
            foreach( $jsonConfig[$request_set["config"]]["tabConfig"][$request_set["tab"]]["classes"] as $classID )
            {
                $query = new Query();
    
                $offset=(int)$jsonConfig[$request_set["config"]]["tabConfig"][$request_set["tab"]]["offset"];
                $limit=(int)$jsonConfig[$request_set["config"]]["tabConfig"][$request_set["tab"]]["limit"];
                $query->limit = $limit;
                if( $request_set["page"] > 0)
                {
                    $offset = $request_set["page"] * $limit;
                }
                $query->offset = $offset;
                
                $filterqueries=array();
                $filterqueries[]=new Query\Criterion\FullText( $searchfor );
                foreach( $jsonConfig[$request_set["config"]]["tabConfig"][$request_set["tab"]]["filter"] as $fqs )
                {
                    $filterqueries[]=new Query\Criterion\CustomField($fqs["fieldPaths"], Operator::EQ, $fqs["value"]);
                }
                
                if( array_key_exists("queryfilter", $request_set ) && is_array( $request_set["queryfilter"] ) && count( $request_set["queryfilter"] ) > 0 )
                {
                    foreach ( $request_set["queryfilter"] as $queryfilter )
                    {
                        $queryparts=explode(";", $queryfilter);
                        $filterqueries[]=new Query\Criterion\CustomField($queryparts[0], Operator::EQ, '"' . $queryparts[1] . '"');
                        $activefilters[]=array('field' => $queryparts[0], 'value' => $queryparts[1]);
                    }
                }
                $query->filter = new Query\Criterion\LogicalAnd( $filterqueries );
                $facetbuilder_array=array();
                foreach( $jsonConfig[$request_set["config"]]["tabConfig"][$request_set["tab"]]["facets"] as $fbs )
                {
                    if( $fbs["type"] == "field")
                    {
                        $facetbuilder_array[]=new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => $fbs["fieldPaths"], "name" => $fbs["name"], "limit" => $fbs["limit"] ));
                    }
                }
                if( count($facetbuilder_array) > 0 )
                {
                    $query->facetBuilders = $facetbuilder_array;
                }
                
                $query->sortClauses = array( new Query\SortClause\DatePublished(Query::SORT_DESC) );
                
                $result = $searchHandler->findContent( $query );
                $results[$classID]["elements"]=$result->searchHits;
                $results[$classID]["count"]=count($result->searchHits);
                $results[$classID]["totalcount"]=$result->totalCount;
            
                foreach( $result->facets as $facet )
                {
                    if( $facet->name == "Klassen")
                    {
                        $jsonFacets["query"]["class"]=$facet->entries;
                    }
                    elseif( $facet->name == "DateRange")
                    {
                        foreach($facet->entries as $key => $entry)
                        {
                            $jsonFacets["query"][$facet->entries][$key]=$entry;
                        }
                
                    }
                    else
                    {
                        $jsonFacets["query"][$facet->name]=$facet->entries;
                    }
                
                }
                $jsonFacets["query"]["totalCount"]=$allfacets->totalCount;
                $tabViewcount=$results[$classID]["totalcount"];
            }
        }
        
        $response = new JsonResponse();
        $response->setData(array('config' => $request_set, 'facetten' => $jsonFacets, 'results' => $results, 'activefilters' => $activefilters, 'actTabViewcount' => $tabViewcount));
        return $response;
    }
    
}