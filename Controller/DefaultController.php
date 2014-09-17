<?php

namespace xrow\EzPublishSolrDocsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('xrowEzPublishSolrDocsBundle:Default:index.html.twig', array('name' => $name));
    }
	
	    public function solrDocViewAction( $remoteid )
    {
        $repository = $this->container->get( 'ezpublish.solrapi.repository' );
        $searchService = $repository->getSearchService();
        $text = $remoteid;
        $query = new \eZ\Publish\API\Repository\Values\Content\Query();
        $query->criterion = new \eZ\Publish\API\Repository\Values\Content\Query\Criterion\FullText( $text );
        $result = $searchService->findContent( $query, array(), false );
        
        $request = $this->container->get('request');
        $response = new Response();
        
        
        return $this->render("xrowEzPublishSolrDocsBundle:Default:solrdocviewsartikel.html.twig", array(
                'pagelayout' => "eZDemoBundle::pagelayout.html.twig",
                'route' => $request->get('_route'),
                'context' => $remoteid,
                'result' => $result,
                'current_locale' => "ger-DE"
        ), $response);
    }
}