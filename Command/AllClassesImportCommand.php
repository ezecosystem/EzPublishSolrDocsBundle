<?php
namespace xrow\EzPublishSolrDocsBundle\Command;
 
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
 
class AllClassesImportCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    /**
     * Configures the command
     */
protected function configure()
{
    $this->setName( 'xrow:solrdocs:allclassesimport' );
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
    $repository = $this->getContainer()->get( 'ezpublish.solrapi.repository' );
    $contentService = $repository->getContentService();
    $locationService = $repository->getLocationService();
    $contentTypeService = $repository->getContentTypeService();
    $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );
    $parentLocationId = 2;
    // instantiate a location create struct from the parent location
    $locationCreateStruct = $locationService->newLocationCreateStruct( $parentLocationId );
    
    
    # Creating s_artikel
    $output->writeln( "Creating a new Article and do commit." );
    $contentTypeIdentifier = "s_artikel";
    $contentType = $contentTypeService->loadContentTypeByIdentifier( $contentTypeIdentifier );
    $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'ger-DE' );

        $titel = "Circus Halligalli " . rand(2, 9876987);
        $veroeffentlichungsdatum = 1409131877;
        $vorspann = "<p>Es war einmal in einem tiefen dunklen Wald..</p>";
        $haupttext = "<p>Hier lebte die Bärenfamilie..</p>";
        $schlagwoerter = array("Bär", "Wald");
        $rubriken = array( "News", "Winter");
        $url = "http://www.haz.de/Nachrichten/Lokales/Hannover";
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
        $output->writeln( "Artikel erzeugt." );
        
        # Creating s_artikel
        $output->writeln( "Creating a new Foto and do commit." );
        $contentTypeIdentifier = "s_foto";
        $contentType = $contentTypeService->loadContentTypeByIdentifier( $contentTypeIdentifier );
        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'ger-DE' );
        
        $data=array();
        $data["veroeffentlichungsdatum"] = 1409141879;
        $data["titel"] = "Ein neues Foto Nr." . rand(2, 9876987);
        $data["schlagwoerter"] = array("Fotolia", "Wald");
        $data["rubriken"] = array( "Fotostrecke", "Sommer");
        $data["url"] = "http://www.xrow.de/Keyword";
        $contentCreateStruct->setField( 'veroeffentlichungsdatum', $data["veroeffentlichungsdatum"] );
        $contentCreateStruct->setField( 'titel', $data["titel"] );
        $contentCreateStruct->setField( 'schlagwoerter', $data["schlagwoerter"] );
        $contentCreateStruct->setField( 'rubriken', $data["rubriken"] );
        $contentCreateStruct->setField( 'url', $data["url"] );
        $draft = $contentService->createContent( $contentCreateStruct, array( $locationCreateStruct ) );
        $update = $client->createUpdate();
        $update->addCommit();
        $result = $client->update($update);
        $output->writeln( "Foto erzeugt erzeugt." );

        # Creating s_artikel
        $output->writeln( "Creating a new Veranstaltung and do commit." );
        $contentTypeIdentifier = "s_veranstaltung";
        $contentType = $contentTypeService->loadContentTypeByIdentifier( $contentTypeIdentifier );
        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'ger-DE' );
        
        $data=array();
        $data["veranstaltungsbeginn"] = 1409141879;
        $data["veranstaltungsende"] = 1409241879;
        $data["titel"] = "Eine neue Veranstaltung Nr." . rand(2, 9876987);
        $data["teaser"] = "Veranstaltung im neuen Rathaus";
        $data["haupttext"] = "<p>Im neuen Rathaus findet am 3.10.2014 eine ganz tolle Veranstaltung statt. Seien Sie dabei.</p>";
        $data["homepage_veranstaltung"] = "http://www.hannover.de";
        $data["homepage_staette"] = "http://www.neuesrathaus-hannover.de";
        $data["staette"] = array("Neues Rathaus");
        $data["stadt"] = "Hannover";
        $data["geodaten"] = array( "longitude" => 42.117629, "latitude" => -70.956766, "address" => "Abington, MA" );
        $data["rubriken"] = array( "Veranstaltung", "Herbst");
        $data["url"] = "http://www.veranstaltung.de";
        $data["bild_url"] = "http://upload.wikimedia.org/wikipedia/commons/c/ce/Neues_Rathaus_Hannover_001.JPG";
        
       
        $contentCreateStruct->setField( 'veranstaltungsbeginn', $data["veranstaltungsbeginn"] );
        $contentCreateStruct->setField( 'veranstaltungsende', $data["veranstaltungsende"] );
        $contentCreateStruct->setField( 'titel', $data["titel"] );
        $contentCreateStruct->setField( 'teaser', $data["teaser"] );
        $contentCreateStruct->setField( 'haupttext', $data["haupttext"] );
        $contentCreateStruct->setField( 'homepage_veranstaltung', $data["homepage_veranstaltung"] );
        $contentCreateStruct->setField( 'homepage_staette', $data["homepage_staette"] );
        $contentCreateStruct->setField( 'staette', $data["staette"] );
        $contentCreateStruct->setField( 'stadt', $data["stadt"] );
        $contentCreateStruct->setField( 'geodaten', $data["geodaten"] );
        $contentCreateStruct->setField( 'rubriken', $data["rubriken"] );
        $contentCreateStruct->setField( 'url', $data["url"] );
        $contentCreateStruct->setField( 'bild_url', $data["bild_url"] );
        
        $draft = $contentService->createContent( $contentCreateStruct, array( $locationCreateStruct ) );
        $update = $client->createUpdate();
        $update->addCommit();
        $result = $client->update($update);
        $output->writeln( "Veranstaltung erzeugt erzeugt." );






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