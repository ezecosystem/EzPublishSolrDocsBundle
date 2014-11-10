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
            new InputOption('limit', null, InputOption::VALUE_OPTIONAL, 'Limit of importing entries, default 5000', 5000),
            new InputOption('offset', null, InputOption::VALUE_OPTIONAL, 'Offset of importing entries, default 0', 0),
            new InputOption('conc', null, InputOption::VALUE_OPTIONAL, 'Concurring processes, default 1', 1),
            new InputOption('location', null, InputOption::VALUE_OPTIONAL, 'Virtual location e.g. Solrdoc/Test/anywhere', '/Solrdocs'),
            new InputOption('clean', null, InputOption::VALUE_OPTIONAL, 'Clean docs before import, default no values: [no|location|class|all]', 'no')
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
        $processesopt = $input->getOption('conc');

        
        // Check Cleaning before import
    switch ($input->getOption('clean'))
    {
        case "no":
            $output->writeln("Not cleaning");
            break;
        case "all":
            $output->writeln("CLEANING ALL!");
            $client = new \Solarium\Client($this->getContainer()->getParameter('xrow_ez_publish_solr_docs.solrserverconfig'));
            $update = $client->createUpdate();
            #$update->addDeleteQuery('meta_class_name_ms:"Video"');
            $update->addDeleteQuery('*:*');
            $update->addCommit();
            $result = $client->update($update);
            break;
        case "class":
            $output->writeln("CLEANING CLASS: " . $contentTypeIdentifier);
            $client = new \Solarium\Client($this->getContainer()->getParameter('xrow_ez_publish_solr_docs.solrserverconfig'));
            $update = $client->createUpdate();
            $update->addDeleteQuery('meta_class_identifier_ms:"' . trim($contentTypeIdentifier) .'"');
            $update->addCommit();
            $result = $client->update($update);
            break;
        case "location":
            $output->writeln("CLEANING Location: " . $input->getOption('location'));
            $client = new \Solarium\Client($this->getContainer()->getParameter('xrow_ez_publish_solr_docs.solrserverconfig'));
            $url = trim($input->getOption('location'), '/');
            $url_array=explode("/", $url);
            $url_alias_cats = array();
            foreach( $url_array as $depth => $part )
            {
                $fullpart = "";
                for ($i = 0; $i <= $depth; $i++)
                {
                $fullpart.= $url_array[$i] . "/";
                }
                $url_alias_cats[] = $depth . "/" . $fullpart;
            }
            $update = $client->createUpdate();
            $update->addDeleteQuery('meta_parent_url_alias_ms:"' . array_pop($url_alias_cats) .'"');
            $update->addCommit();
            $result = $client->update($update);
            break;
        default:
            $output->writeln("CLEANING anything since no vaild option.");
        }
        
        
        try {
            $startzeit=microtime(true);
            $output->writeln("Starting Import...");
            $output->writeln("------------------");
            $output->writeln("");
            
            // PREPARING
            $repository = $this->getContainer()->get( 'ezpublish.solrapi.repository' );
            $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );
            $contentTypeService = $repository->getContentTypeService();
            $locationService = $repository->getLocationService();
            $parentLocation = $input->getOption('location');
            #$parentLocationId = 2;
            $location = $locationService->newLocationCreateStruct( $parentLocation );
            
            $ContentType = $contentTypeService->loadContentTypeByIdentifier( $contentTypeIdentifier );
            $classes=$this->getContainer()->getParameter('xrow_ez_publish_solr_docs.solr_classes');
            $contentTypeIdentifierarray = $classes[$contentTypeIdentifier];
            // IMPORTING NOW
            
            try
            {
                $source = new OData\Source( $sourcefile, $offsetopt, $limitopt, $contentTypeIdentifierarray);
            }
            catch (\Exception $e)
            {
                $output->writeln("Error: " . $e->getMessage());
                $output->writeln("Import stops here.");
                exit();
            }
            
            $output->writeln("Sourced: " . $sourcefile);
            #$output->writeln("---------------");
            $output->writeln("");
            
            $import = new Import\Process( $location, $ContentType, $source, $repository, $processesopt );
            
            
            $output->writeln("Validating...");
            #$output->writeln("---------------");
            $output->writeln("");
            
            if($import->validate( $sourcefile ))
            {
                $output->writeln("");
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