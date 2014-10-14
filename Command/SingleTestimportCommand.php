<?php
namespace xrow\EzPublishSolrDocsBundle\Command;
 
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
 
class SingleTestimportCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    /**
     * Configures the command
     */
protected function configure()
{
    $this->setName( 'xrow:solrdocs:singletestimport' );
    $this->setDefinition(
        array(
            new InputArgument( 'name', InputArgument::OPTIONAL, 'An argument' )
        )
    );
}

protected function execute( InputInterface $input, OutputInterface $output )
{
    $client = new \Solarium\Client($this->getContainer()->getParameter('xrow_ez_publish_solr_docs.solrserverconfig'));
    
    try
    {
    $output->writeln( "Creating a new Article and do commit." );
    $repository = $this->getContainer()->get( 'ezpublish.solrapi.repository' );
    $contentService = $repository->getContentService();
    $locationService = $repository->getLocationService();
    $contentTypeService = $repository->getContentTypeService();
    $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );
    $parentLocationId = 2;
    // instantiate a location create struct from the parent location
    $locationCreateStruct = $locationService->newLocationCreateStruct( $parentLocationId );
    $contentTypeIdentifier = "s_artikel";
    $contentType = $contentTypeService->loadContentTypeByIdentifier( $contentTypeIdentifier );
    $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'ger-DE' );

        $titel = "Ein neuer Artikel " . rand(2, 9876987);
        $veroeffentlichungsdatum = 1409131877;
        $vorspann = "<p>Dieser Artikel hat einen relativ langen Vorspann. Wie eigentlich auch immer.</p>";
        $haupttext = "<p>Dieser Artikel hat einen relativ <b>kurzen</b> Haupttext. Dieser Artikel hat einen relativ <b>kurzen</b> Haupttext.
Dieser Artikel hat einen relativ <b>kurzen</b> Haupttext. Dieser Artikel hat einen relativ <b>kurzen</b> Haupttext. Dieser Artikel hat einen relativ <b>kurzen</b> Haupttext.
Dieser Artikel hat einen relativ <b>kurzen</b> Haupttext. Dieser Artikel hat einen relativ <b>kurzen</b> Haupttext. Dieser Artikel hat einen relativ <b>kurzen</b> Haupttext. Dieser Artikel hat einen relativ <b>kurzen</b> Haupttext. 
Dieser Artikel hat einen relativ <b>kurzen</b> Haupttext. 
</p>";
        $schlagwoerter = array("Solr", "neuer Artikel", "Gewitter");
        $rubriken = array( "Nachrichten", "Meinung");
        $url = "http://www.test.de/Nachrichten/Lokales/Hannover";
        $contentCreateStruct->setField( 'titel', $titel );
        $contentCreateStruct->setField( 'veroeffentlichungsdatum', $veroeffentlichungsdatum );
        $contentCreateStruct->setField( 'vorspann', $vorspann );
        $contentCreateStruct->setField( 'haupttext', $haupttext );
        $contentCreateStruct->setField( 'schlagwoerter', $schlagwoerter );
        $contentCreateStruct->setField( 'rubriken', $rubriken );
        $contentCreateStruct->setField( 'url', $url );
        $draft = $contentService->createContent( $contentCreateStruct, array( $locationCreateStruct ) );

        $update = $client->createUpdate();
        $update->addCommit();
        $result = $client->update($update);
        #$content = $contentService->publishVersion( $draft->versionInfo );
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