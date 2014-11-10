<?php
namespace xrow\EzPublishSolrDocsBundle\Command;
 
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use XMLReader;
use DOMDocument;
 
class TestimportCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    /**
     * Configures the command
     */
protected function configure()
{
    $this->setName( 'xrow:solrdocs:testimport' );
    $this->setDefinition(
        array(
            new InputArgument( 'name', InputArgument::OPTIONAL, 'An argument' )
        )
    );
}

public function getTimingToNow( $id = "0" )
{
    $ident = "PerformanceExtensionMicrotime" . $id;
    if (!isset($GLOBALS[$ident])) {
        return 0;
    }
    
    $durationInMilliseconds = (microtime(true) - $GLOBALS[$ident]) * 1000;
    $timing = number_format($durationInMilliseconds, 3, '.', '') . "ms";
    if($durationInMilliseconds > 1000)
    {
        $timing = number_format($durationInMilliseconds / 1000, 1, '.', '') . "sec";
    }
    return $timing;
}

public function startTiming( $id = "0" )
{
    $ident = "PerformanceExtensionMicrotime" . $id;
    $GLOBALS[$ident] = microtime(true);
}

protected function execute( InputInterface $input, OutputInterface $output )
{
    $client = new \Solarium\Client($this->getContainer()->getParameter('xrow_ez_publish_solr_docs.solrserverconfig'));
    
    try
    {
    $output->writeln( "Start..." );
    $this->startTiming("start");
    
    #$update = $client->createUpdate();
    #$update->addCommit();
    #$result = $client->update($update);
    #die("Alles committed");
    #$update = $client->createUpdate();
    #$update->addDeleteQuery('meta_class_name_ms:"Video"');
    #$update->addDeleteQuery('*:*');
    #$update->addCommit();
    #$result = $client->update($update);
    #die("alles is weck");
    #$output->writeln( "Time until solr cleaned:" . $this->getTimingToNow( "start" ) );

    /** @var $repository \eZ\Publish\API\Repository\Repository */
    #$repository = $this->getContainer()->get( 'ezpublish.api.repository' );
    $repository = $this->getContainer()->get( 'ezpublish.solrapi.repository' );
    
    $output->writeln( "Time until repo loaded:" . $this->getTimingToNow( "start" ) );
    
    $contentService = $repository->getContentService();
    $locationService = $repository->getLocationService();
    $contentTypeService = $repository->getContentTypeService();

    $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );
    
    $parentLocationId = 2;
    
    // instantiate a location create struct from the parent location
    $locationCreateStruct = $locationService->newLocationCreateStruct( $parentLocationId );
    
    $this->startTiming("contentcreation_all");
    $onlycreatemilliseconds = 0;
    
    for ($i = 1; $i <= 10; $i++)
    {
         
        $this->startTiming("contentcreation_item");

        $contentTypeIdentifier = "s_artikel";
        $contentType = $contentTypeService->loadContentTypeByIdentifier( $contentTypeIdentifier );
        
        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'ger-DE' );
        
        $titel = "Titel neuer Solr Artikel Nr. $i " . rand(2, 9876987);
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
        
        #$contentCreateStruct->setField( 'my_integer', rand(2, 9876987) );
        #$contentCreateStruct->setField( 'my_xml', $meinXML );
        #$contentCreateStruct->setField( 'my_text', $body );
        #$contentCreateStruct->setField( 'my_int', 12 );
        #$contentCreateStruct->setField( 'my_keyword', array("Nachrichten","Gewitter" ));
        #$contentCreateStruct->setField( 'my_boolean', true );
        #$contentCreateStruct->setField( 'my_time', 1245253245 );
        #$contentCreateStruct->setField( 'my_datetime', 326325600 );
        #$contentCreateStruct->setField( 'my_float', 5.2214 );
        #$contentCreateStruct->setField( 'my_geo', array( "longitude" => 42.117629, "latitude" => -70.956766, "address" => "Abington, MA" ));
        // create a draft using the content and location create struct and publish it
        $startmillimeasure=microtime(true);
        $draft = $contentService->createContent( $contentCreateStruct, array( $locationCreateStruct ) );
        $endmillimeasure=microtime(true);
        $onlycreatemilliseconds=$onlycreatemilliseconds + ( ($endmillimeasure - $startmillimeasure ) * 1000 ); 
        $output->writeln( "Time for item " . $i . " :"  . $this->getTimingToNow( "contentcreation_item" ) );
    
    }
    
    
    
    
    $output->writeln( "Time for contentcreation:" . $this->getTimingToNow( "contentcreation_all" ) );
    
    
    
    $this->startTiming("addcommitattheend");
    
    
    $update = $client->createUpdate();
    $update->addCommit();
    $result = $client->update($update);
    $output->writeln( "Time for COMMIT:" . $this->getTimingToNow( "addcommitattheend" ) );
    
    $output->writeln( "Time until end:" . $this->getTimingToNow( "start" ) );
    $output->writeln( "Time only for create:" . number_format($onlycreatemilliseconds / 1000, 1, '.', '') . " sec" );
    
    # We do not publish, creation is done while creating draft since NO versioning
    #$content = $contentService->publishVersion( $draft->versionInfo );
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