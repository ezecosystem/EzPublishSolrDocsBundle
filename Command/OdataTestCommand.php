<?php
namespace xrow\EzPublishSolrDocsBundle\Command;
 
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use xrow\EzPublishSolrDocsBundle\Lib\ODataHelper;
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
    $this->setName( 'xrow:solrdocs:odatatest' );
    $this->setDefinition(
        array(
            new InputOption( 'source', null, InputOption::VALUE_REQUIRED , 'Source of local document or http URL' )
        )
    );
}

protected function execute( InputInterface $input, OutputInterface $output )
{
    $sourcefile = $input->getOption('source');
    $odatahelper = new ODataHelper();
    #$validatexsd="vendor/xrow/ezpublish-solrdocs-bundle/Lib/edm.xsd";
    $validatexsd="vendor/xrow/ezpublish-solrdocs-bundle/Lib/c1test.xsd";
    try
    {
        $output->writeln( "" );
        $output->writeln( "Going to validate XML against " . $validatexsd );
        $check = $odatahelper->validateAgainstXSD($sourcefile, $validatexsd);
    
        if( $check["status"] === true )
        {
            $output->writeln( "Document is valid against " . $validatexsd );
        }
        else
        {
            $output->writeln( "Document '" . $sourcefile . "' is NOT valid against " . $validatexsd );
            $output->writeln( "Error(s): " );
            foreach ($check["errors"] as $number => $error)
            {
                $output->writeln( "" );
                $output->writeln( "Fehler #". ($number + 1) . ":" );
                $output->writeln( "------------------------------------------------------" );
                $output->writeln( "Message: " . $error->message );
                $output->writeln( "File: " . $error->file);
                $output->writeln( "Level: " . $error->level);
                $output->writeln( "Zeile: " . $error->line);
                $output->writeln( "Zeichen: " . $error->column);
                $output->writeln( "Code: " . $error->code);
            }
        }
        $output->writeln( "" );
        $output->writeln( "" );
        $output->writeln( "Fertig." );
        $output->writeln( "" );
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