<?php
namespace xrow\EzPublishSolrDocsBundle\Command;
 
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use xrow\EzPublishSolrDocsBundle\Lib\ODataHelper;
use DOMDocument;
use DOMXPath;
 
class SearchTestCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    /**
     * Configures the command
     */
protected function configure()
{
    $this->setName( 'xrow:solrdocs:searchtest' );
    $this->setDefinition(
        array(
            new InputArgument( 'searchquery', InputArgument::OPTIONAL, 'A text to search for' )
        )
    );
}

protected function execute( InputInterface $input, OutputInterface $output )
{
    /** @var \eZ\Publish\SPI\Persistence\Handler $persistenceHandler */
    #$persistenceHandler = $this->getContainer()->get( 'ezpublish.solrapi.persistence_handler' );
    #$persistenceHandler = $this->getContainer()->get( 'ezpublish.spi.persistence.legacy_solr' );
    
    $persistenceHandler = $this->getContainer()->get( 'ezpublish.spi.persistence.legacy_solrdoc' );
    $searchHandler = $persistenceHandler->searchHandler();
    
    #$repository = $this->getContainer()->get( 'ezpublish.solrapi.repository' );
    #$searchService = $repository->getSearchService();
    
    #$persistenceHandler = $this->getContainer()->get( 'ezpublish.spi.persistence.legacy_solr' );
    /** @var \eZ\Publish\Core\Persistence\Solr\Content\Search\Handler $searchHandler */
    #$searchHandler = $persistenceHandler->searchHandler();
    
    
    #$client = new \Solarium\Client($this->getContainer()->getParameter('xrow_ez_publish_solr_docs.solrserverconfig'));
    #$repository = $this->getContainer()->get( 'ezpublish.solrapi.repository' );
    // Searching
    
    #$locationService = $repository->getLocationService();
    $query = new Query();
    $query->query = new Query\Criterion\FullText( "solr" );
    #$query->facetBuilders = new Facet\ContentTypeFacet(array("entries" => 10, "name" => "is_solrdoc_b"));
    $query->facetBuilders = array( new Query\FacetBuilder\ContentTypeFacetBuilder() );
    var_dump(new Query\FacetBuilder\ContentTypeFacetBuilder());
    $query->limit = 20;
    #$query->filter = new Query\Criterion\MatchAll("*" );
    #$query->filter =new Query\Criterion\ContentTypeIdentifier( 'folder' );
    #var_dump($query);
    $result = $searchHandler->findContent( $query );
    #$result = $searchService->findContent( $query );
    foreach($result->searchHits as $hit)
    {
        var_dump($hit->valueObject->meta_name_t);
    }
    
    $output->writeln( "Treffer:" );
    var_dump($result->totalCount);
    
    
    #$query = new Query();
    #$criterion1 = new Criterion\Subtree( $locationService->loadLocation( 2 )->pathString );
    #$criterion2 = new Criterion\ContentTypeIdentifier( 'article' );
    #$query->criterion = new Criterion\LogicalAnd(
    #        array( $criterion1, $criterion2 )
    #);
    #die("stop");
    
    $output->writeln( "Done." );

}
    /**
     * Executes the command
     * @param InputInterface $input
     * @param OutputInterface $output
     */
}