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

class OdataCreateSchemaCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{

    /**
     * Configures the command
     */
    protected function configure()
    {
        $this->setName('xrow:odata:createschema');
        $this->setDefinition(array(
            new InputOption('class', null, InputOption::VALUE_REQUIRED, 'Class of creation')
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
        $contentTypeIdentifier = $input->getOption('class');
     
            $repository = $this->getContainer()->get( 'ezpublish.solrapi.repository' );
            $contentTypeService = $repository->getContentTypeService();
            $ContentType = $contentTypeService->loadContentTypeByIdentifier( $contentTypeIdentifier );
            $classes=$this->getContainer()->getParameter('xrow_ez_publish_solr_docs.solr_classes');
            $routePath="vendor/xrow/ezpublish-solrdocs-bundle/Resources/schema/odata4_classes/";
            
            foreach($classes as $class )
            {
                if( $class["identifier"] == "solrtestdoc" )
                {
                    $basepath="vendor/xrow/ezpublish-solrdocs-bundle/Resources/schema/odata4_classes/";
                    $basexsd=$basepath."odata4_content.xsd.replaceme";
                    $basedataxsd=$basepath."odata4_content_data.xsd.replaceme";
                    $basemetadataxsd=$basepath."odata4_content_metadata.xsd.replaceme";
                    
                    $tmpxsd=$basepath."odata4_content_" . $class["identifier"] . ".xsd";
                    $tmpdataxsd=$basepath."odata4_content_data_" . $class["identifier"] . ".xsd";
                    $tmpmetadataxsd=$basepath."odata4_content_metadata_" . $class["identifier"] . ".xsd";
                    
                    $xsdcontent=file_get_contents($basexsd);
                    $xsdcontent=str_replace("{odata4_content_data}", "odata4_content_data_" . $class["identifier"].".xsd", $xsdcontent);
                    $xsdcontent=str_replace("{odata4_content_metadata}", "odata4_content_metadata_" . $class["identifier"].".xsd", $xsdcontent);
                    
                    $tempxsd_new=fopen($tmpxsd, "w") or die("Unable to open file!");
                    fwrite($tempxsd_new, $xsdcontent);
                    fclose($tempxsd_new);
                    
                    
                    $classdefmetadata="";
                    $classdefdata="";
                    foreach( $class["fields"] as $field )
                    {
                        $classdefmetadata.="<xsd:element name=\"" . $field["identifier"] . "\" />\n";
                        if( $field["isRequired"] )
                        {
                            $classdefdata.="<xsd:element ref=\"d:" . $field["identifier"] . "\" minOccurs=\"1\"/>\n";
                        }
                        else
                        {
                            $classdefdata.="<xsd:element ref=\"d:" . $field["identifier"] . "\" minOccurs=\"0\"/>\n";
                        }
                    }

                    $xsdcontent=file_get_contents($basedataxsd);
                    $xsdcontent=str_replace("{odata4classdescription}", $classdefmetadata, $xsdcontent);
                    $tempxsd_new=fopen($tmpdataxsd, "w") or die("Unable to open file!");
                    fwrite($tempxsd_new, $xsdcontent);
                    fclose($tempxsd_new);
                    
                    $xsdcontent=file_get_contents($basemetadataxsd);
                    $xsdcontent=str_replace("{odata4classdescriptionmetadata}", $classdefdata, $xsdcontent);
                    $xsdcontent=str_replace("{odata4_content_data}", "odata4_content_data_" . $class["identifier"].".xsd", $xsdcontent);
                    $tempxsd_new=fopen($tmpmetadataxsd, "w") or die("Unable to open file!");
                    fwrite($tempxsd_new, $xsdcontent);
                    fclose($tempxsd_new);
                    
                }
            }
            
            
            $output->writeln( "" );
            $output->writeln( "CREATE done" );
        
        
    }
}