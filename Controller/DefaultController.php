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
    
    public function solrDocUniversalSearchAjaxAction(  )
    {
        $response = new JsonResponse();
        $response->setData(array('message' => 'hello'));

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
            #$query->facetBuilders = new Facet\ContentTypeFacet(array("entries" => 10, "name" => "is_solrdoc_b"));
            #$query->query = new Query\Criterion\FullText( $searchtext );
            #&facet.query=attr_erstzulassung_dt:[NOW-1DAY TO NOW]&facet.query=attr_erstzulassung_dt:[NOW-7DAY TO NOW-1DAY]&facet.query=attr_erstzulassung_dt:[NOW-31DAY TO NOW-7DAY]&facet.query=attr_erstzulassung_dt:[NOW-365DAY TO NOW-31DAY]
            $allfacetquery->facetBuilders = array( new Query\FacetBuilder\ContentTypeFacetBuilder(array("name" => "Klassen")),
            new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_kategorie_s", "name" => "Kategorien")),
            new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_schlagwoerter____k", "name" => "Schlagwörter")),
            );
            $allfacetquery->limit = 0;
            $allfacets = $searchHandler->findContent( $allfacetquery );
            
            
            $query = new Query();
            $query->query = new Query\Criterion\MatchAll();
            #$query->facetBuilders = new Facet\ContentTypeFacet(array("entries" => 10, "name" => "is_solrdoc_b"));
            #$query->query = new Query\Criterion\FullText( $searchtext );
            #&facet.query=attr_erstzulassung_dt:[NOW-1DAY TO NOW]&facet.query=attr_erstzulassung_dt:[NOW-7DAY TO NOW-1DAY]&facet.query=attr_erstzulassung_dt:[NOW-31DAY TO NOW-7DAY]&facet.query=attr_erstzulassung_dt:[NOW-365DAY TO NOW-31DAY]
            $query->facetBuilders = array( new Query\FacetBuilder\ContentTypeFacetBuilder(array("name" => "Klassen")),
                    new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_kategorie_s", "name" => "Kategorien")),
                    new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_schlagwoerter____k", "name" => "Schlagwörter")),

            );
            #var_dump(new Query\FacetBuilder\ContentTypeFacetBuilder());
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
                # new Query\Criterion\CustomField("meta_class_name_ms", Operator::EQ, "s_artikel");
                #fq=meta_class_name_ms%3A"s_artikel"
                
            }
            
            $query->limit = 10;
            #var_dump($query);
            $result = $searchHandler->findContent( $query );
            #var_dump($result);
            #die("sldkjf");
            $response = new Response();
        }
        else
        {
            $request = $this->container->get('request');
            $allfacetquery = new Query();
            $allfacetquery->query = new Query\Criterion\FullText( $searchtext );
            $allfacetquery->facetBuilders = array( new Query\FacetBuilder\ContentTypeFacetBuilder(array("name" => "Klassen")),
                    new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_rubriken____k", "name" => "Testy") ),
                    new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_schlagwoerter____k"))
            );
            $allfacetquery->limit = 0;
            $allfacets = $searchHandler->findContent( $allfacetquery );
            
            $query = new Query();
            #$query->query = new Query\Criterion\MatchAll();
            #$query->facetBuilders = new Facet\ContentTypeFacet(array("entries" => 10, "name" => "is_solrdoc_b"));
            $query->query = new Query\Criterion\FullText( $searchtext );
            $query->facetBuilders = array( new Query\FacetBuilder\ContentTypeFacetBuilder(),
                    new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_rubriken____k", "name" => "Testy") ),
                    new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_schlagwoerter____k"))
            );
            
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
                # new Query\Criterion\CustomField("meta_class_name_ms", Operator::EQ, "s_artikel");
                #fq=meta_class_name_ms%3A"s_artikel"
            
            }
            
            #var_dump(new Query\FacetBuilder\ContentTypeFacetBuilder());
            $query->limit = 10;
            $result = $searchHandler->findContent( $query );
            #var_dump($result);
            #die("sldkjf");
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
        $persistenceHandler = $this->container->get( 'ezpublish.spi.persistence.legacy_solrdoc' );
        $searchHandler = $persistenceHandler->searchHandler();
                $response = new Response();
        return $this->render("xrowEzPublishSolrDocsBundle:Default:solrdocuniversalsearch_ajax.html.twig", array(
                'pagelayout' => "eZDemoBundle::pagelayout.html.twig",
        'route' => $request->get('_route'),
        'context' => "test",
        'current_locale' => "ger-DE"
        ), $response);
    }
    
}