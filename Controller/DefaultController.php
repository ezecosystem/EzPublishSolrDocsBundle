<?php

namespace xrow\EzPublishSolrDocsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use Symfony\Component\HttpFoundation\Response;
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
            $query = new Query();
            $query->query = new Query\Criterion\MatchAll();
            #$query->facetBuilders = new Facet\ContentTypeFacet(array("entries" => 10, "name" => "is_solrdoc_b"));
            #$query->query = new Query\Criterion\FullText( $searchtext );
            $query->facetBuilders = array( new Query\FacetBuilder\ContentTypeFacetBuilder(),
                    new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_rubriken____k")),
                    new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_schlagwoerter____k")),
            );
            #var_dump(new Query\FacetBuilder\ContentTypeFacetBuilder());
            $query->limit = 100;
            $result = $searchHandler->findContent( $query );
            #var_dump($result);
            #die("sldkjf");
            $request = $this->container->get('request');
            $response = new Response();
        }
        else
        {
            $query = new Query();
            #$query->query = new Query\Criterion\MatchAll();
            #$query->facetBuilders = new Facet\ContentTypeFacet(array("entries" => 10, "name" => "is_solrdoc_b"));
            $query->query = new Query\Criterion\FullText( $searchtext );
            $query->facetBuilders = array( new Query\FacetBuilder\ContentTypeFacetBuilder(),
                    new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_rubriken____k", "name" => "Testy") ),
                    new Query\FacetBuilder\FieldFacetBuilder(array("fieldPaths" => "attr_schlagwoerter____k"))
            );
            #var_dump(new Query\FacetBuilder\ContentTypeFacetBuilder());
            $query->limit = 100;
            $result = $searchHandler->findContent( $query );
            #var_dump($result);
            #die("sldkjf");
            $request = $this->container->get('request');
            $response = new Response();
        }
        
        
    
        return $this->render("xrowEzPublishSolrDocsBundle:Default:solrdocuniversalsearch.html.twig", array(
                'pagelayout' => "eZDemoBundle::pagelayout.html.twig",
                'route' => $request->get('_route'),
                'context' => $searchtext,
                'result' => $result,
                'current_locale' => "ger-DE"
        ), $response);
    }
}