<?php
namespace xrow\EzPublishSolrDocsBundle\Command;
 
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use xrow\EzPublishSolrDocsBundle\Lib\ODataHelper;
use DOMDocument;
use DOMXPath;
 
class OdataTestCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    /**
     * Configures the command
     */
protected function configure()
{
    $this->setName( 'xrow:solrdocs:odatatest' );
    $this->setDefinition(
        array(
            new InputArgument( 'name', InputArgument::OPTIONAL, 'An argument' )
        )
    );
}

protected function execute( InputInterface $input, OutputInterface $output )
{
    $odatahelper = new ODataHelper();
    #$client = new \Solarium\Client($this->getContainer()->getParameter('xrow_ez_publish_solr_docs.solrserverconfig'));
    #$repository = $this->getContainer()->get( 'ezpublish.solrapi.repository' );
    // Checking
    
    #http://docs.oasis-open.org/odata/odata/v4.0/os/schemas/edm.xsd
    
    #WORKING
    #$inputxml="vendor/xrow/ezpublish-solrdocs-bundle/Lib/testxml.xml";
    #$validatexsd="vendor/xrow/ezpublish-solrdocs-bundle/Lib/testxsd.xsd";
    
    #Trying
    $inputxml="vendor/xrow/ezpublish-solrdocs-bundle/Lib/c1test.xml";
    $validatexsd="vendor/xrow/ezpublish-solrdocs-bundle/Lib/c1test.xsd";
    
    #Ressources
    #$validatexsd="vendor/xrow/ezpublish-solrdocs-bundle/Lib/edmx.xsd";
    #$validatexsd="vendor/xrow/ezpublish-solrdocs-bundle/Lib/edm.xsd";
    #$validatexsd="vendor/xrow/ezpublish-solrdocs-bundle/Lib/odataatom.xsd";
    
    try
    {
    $output->writeln( "Going to validate XML against " . $validatexsd );
    
    if( $odatahelper->validateAgainstXSD($inputxml, $validatexsd) )
    {
        $output->writeln( "Document is valid against " . $validatexsd );
    }
    else
    {
        $output->writeln( "Document is NOT valid against " . $validatexsd );
    }
    $output->writeln( "Done." );
    }
    // Content type or location not found
    catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
    {
    $output->writeln( $e->getMessage() );
    }
    // Invalid field value
    catch ( \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException $e )
    {
    $output->writeln( $e->getMessage() );
    }
    // Required field missing or empty
    catch ( \eZ\Publish\API\Repository\Exceptions\ContentValidationException $e )
    {
    $output->writeln( $e->getMessage() );
    }
    
    
}
    /**
     * Executes the command
     * @param InputInterface $input
     * @param OutputInterface $output
     */
}