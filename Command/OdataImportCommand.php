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

class OdataImportCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{

    /**
     * Configures the command
     */
    protected function configure()
    {
        $this->setName('xrow:odata:import');
        $this->setDefinition(array(
            new InputOption('source', null, InputOption::VALUE_REQUIRED, 'Path of document or HTTP URL'),
            new InputOption('class', null, InputOption::VALUE_REQUIRED, 'Class of creation'),
            new InputOption('limit', null, InputOption::VALUE_OPTIONAL, 'Limit of importing entries, default 2000'),
            new InputOption('offset', null, InputOption::VALUE_OPTIONAL, 'Offset of importing entries, default 0')
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
            $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );
            $contentTypeService = $repository->getContentTypeService();
            $locationService = $repository->getLocationService();
            $parentLocationId = 2;
            $location = $locationService->newLocationCreateStruct( $parentLocationId );
            $ContentType = $contentTypeService->loadContentTypeByIdentifier( $contentTypeIdentifier );
            
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
            
            try
            {
                $source = new OData\Source( $sourcefile, $offset, $limit);
            }
            catch (\Exception $e)
            {
                $output->writeln("Error: " . $e->getMessage());
                $output->writeln("Import stops here.");
                exit();
            }
            
            $output->writeln("Sourced " . $sourcefile);
            $output->writeln("---------------");
            $output->writeln("");
            
            $import = new Import\Process( $location, $ContentType, $source, $repository );
            
            
            $output->writeln("Validate and go");
            $output->writeln("---------------");
            $output->writeln("");
            
            if($import->validate( $sourcefile ))
            {
                $output->writeln("Rows in total: " . $source->count());
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
}