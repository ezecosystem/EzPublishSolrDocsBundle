<?php
namespace xrow\EzPublishSolrDocsBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use xrow\OData;
use DOMDocument;
use DOMXPath;
use Symfony\Component\Console\Input\InputOption;

class OdataTestCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{

    /**
     * Configures the command
     */
    protected function configure()
    {
        $this->setName('xrow:odata:validate');
        $this->setDefinition(array(
            new InputOption('source', null, InputOption::VALUE_REQUIRED, 'Path of document or HTTP URL')
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
        
        try {
            $output->writeln("Validating: $sourcefile");
            
            $check = OData\Helper::validate($sourcefile, $errors);
            
            if ($check) {
                $output->writeln("Document is a valid ODATA source. ");
            } else {
                $output->writeln("Document '" . $sourcefile . "' isn`t valid ODATA source ");
                foreach ($errors as $error) {
                    $output->writeln(OData\Helper::LibXMLErrorToString($error));
                }
                throw new \Exception( "Source is not valid." );
            }
            $output->writeln("Done.");
        }        // Content type or location not found
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