<?php
namespace xrow\EzPublishSolrDocsBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use xrow\OData;
use xrow\EzPublishSolrDocsBundle\src\Import;
use DOMDocument;
use DOMXPath;
use Symfony\Component\Console\Input\InputOption;
use xrow\EzPublishSolrDocsBundle\src\Import\Core_Thread;

class ThreadedOdataImportCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{

    /**
     * Configures the command
     */
    protected function configure()
    {
        $this->setName('xrow:odata:timport');
        $this->setDefinition(array(
            new InputOption('source', null, InputOption::VALUE_REQUIRED, 'Path of document or HTTP URL'),
            new InputOption('class', null, InputOption::VALUE_REQUIRED, 'Class of creation'),
            new InputOption('limit', null, InputOption::VALUE_OPTIONAL, 'Limit of importing entries'),
            new InputOption('offset', null, InputOption::VALUE_OPTIONAL, 'Offset of importing entries')
        ));
    }

    /**
     * Executes the command
     * 
     * @param InputInterface $input            
     * @param OutputInterface $output            
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourcefile = $input->getOption('source');
        $contentTypeIdentifier = $input->getOption('class');
        $limitopt = $input->getOption('limit');
        $offsetopt = $input->getOption('offset');

        try {
            $startzeit=microtime(true);
            $output->writeln("Starting Import");
            $output->writeln("---------------");
            $output->writeln("");
            
            // PREPARING
            $repository = $this->getContainer()->get( 'ezpublish.solrapi.repository' );
            $repository1 = $this->getContainer()->get( 'ezpublish.solrapi.repository' );
            $repository2 = $this->getContainer()->get( 'ezpublish.solrapi.repository' );
            $contentTypeService = $repository->getContentTypeService();
            $locationService = $repository->getLocationService();
            $parentLocationId = 2;
            $location = $locationService->newLocationCreateStruct( $parentLocationId );
            $location1 = $locationService->newLocationCreateStruct( $parentLocationId );
            $location2 = $locationService->newLocationCreateStruct( $parentLocationId );
            $ContentType = $contentTypeService->loadContentTypeByIdentifier( $contentTypeIdentifier );
            $ContentType1 = $contentTypeService->loadContentTypeByIdentifier( $contentTypeIdentifier );
            $ContentType2 = $contentTypeService->loadContentTypeByIdentifier( $contentTypeIdentifier );
            
            // IMPORTING NOW
            $offset = 0;
            $limit = 2000;
            if( $offsetopt !== null )
            {
                $offset=$offsetopt;
            }
            if( $limitopt !== null )
            {
                $limit=$limitopt;
            }
            
            
            
            
            
            
            
            
            
            
            
            
            
            // test to see if threading is available
            if( !Core_Thread::available() ) {
                die( 'Threads not supported' );
            }
            #var_dump(self::paralel(10, "t1"));
            // create 2 thread objects
            $t1 = new Core_Thread( array('xrow\EzPublishSolrDocsBundle\Command\OdataImportCommand', '_doImport'));
            $t2 = new Core_Thread( array('xrow\EzPublishSolrDocsBundle\Command\OdataImportCommand', '_doImport'));
            
            // start them
                            $t1->start( $sourcefile, 0, 10, $location1, $ContentType1, $repository1, $output, $startzeit );
                            $t2->start( $sourcefile, 11, 10, $location2, $ContentType2, $repository2, $output, $startzeit );
            
                            // keep the program running until the threads finish
                            while( $t1->isAlive() && $t2->isAlive() ) {
                             
            }
            
            
            
            
            
            #self::_doImport($sourcefile, $offset, $limit, $location, $ContentType, $repository, $output, $startzeit);
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            /*
            $source = new OData\Source( $sourcefile, $offset, $limit);
            
            $output->writeln("Sourced " . $sourcefile);
            $output->writeln("---------------");
            $output->writeln("");
            
            $import = new Import\Process( $location, $ContentType, $source, $repository );
            
            
            $output->writeln("Validate and go");
            $output->writeln("---------------");
            $output->writeln("");
            
            if($import->validate( $source ))
            {
                $output->writeln("Import is valid.");
                $output->writeln("Rows: " . $source->count());
                $import->import($source);
            }
            else
            {
                $output->writeln("Import is NOT valid.");
            }
            $output->writeln("");
            $output->writeln("---------------");
            $output->writeln("Finished Import.");
            
            // ECHO TIMING
            $durationInMilliseconds = (microtime(true) - $startzeit) * 1000;
            $timing = number_format($durationInMilliseconds, 3, '.', '') . "ms";
            if($durationInMilliseconds > 1000)
            {
                $timing = number_format($durationInMilliseconds / 1000, 1, '.', '') . "sec";
            }
            $output->writeln("Dauer: " . $timing);
            
            
            */
            
            // DOING COMMIT
            $client = new \Solarium\Client($this->getContainer()->getParameter('xrow_ez_publish_solr_docs.solrserverconfig'));
            $update = $client->createUpdate();
            $update->addCommit();
            $result = $client->update($update);
            
            $output->writeln( "" );
            $output->writeln( "COMMIT done" );
            }
        catch (\eZ\Publish\API\Repository\Exceptions\NotFoundException $e) {
            $output->writeln($e->getMessage());
        }        // Invalid field value
        catch (\eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException $e) {
            $output->writeln($e->getMessage());
        }        // Required field missing or empty
        catch (\eZ\Publish\API\Repository\Exceptions\ContentValidationException $e) {
            $output->writeln($e->getMessage());
        }
    }
    public static function _doImport($sourcefile, $offset, $limit, $location, $ContentType, $repository, $output, $startzeit)
    {

        $source = new OData\Source( $sourcefile, $offset, $limit);
        
        $output->writeln("Sourced " . $sourcefile);
        $output->writeln("---------------");
        $output->writeln("");
        
        $import = new Import\Process( $location, $ContentType, $source, $repository );
        
        
        $output->writeln("Validate and go");
        $output->writeln("---------------");
        $output->writeln("");
        
        if($import->validate( $source ))
        {
            $output->writeln("Import is valid.");
            $output->writeln("Rows: " . $source->count());
            $import->import($source);
        }
        else
        {
            $output->writeln("Import is NOT valid.");
        }
        $output->writeln("");
        $output->writeln("---------------");
        $output->writeln("Finished Import.");
        
        // ECHO TIMING
        $durationInMilliseconds = (microtime(true) - $startzeit) * 1000;
        $timing = number_format($durationInMilliseconds, 3, '.', '') . "ms";
        if($durationInMilliseconds > 1000)
        {
            $timing = number_format($durationInMilliseconds / 1000, 1, '.', '') . "sec";
        }
        $output->writeln("Dauer: " . $timing);
    }
}