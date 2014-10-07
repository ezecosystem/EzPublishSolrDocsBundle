<?php 
namespace xrow\EzPublishSolrDocsBundle\src\Import;

use \eZ\Publish\API\Repository\Values\Content\Location;


class Process implements Importing
{
    protected $location;
    protected $ContentType;
    protected $source;
    protected $repository;
    
    function __construct( $location, $ContentType, ImportSource $source, $repository )
    {
        $this->source = $source;
        $this->location = $location;
        $this->ContentType = $ContentType;
        $this->i_repository = $repository;

    }
    function validate( )
    {
        return true;
    }
    
    function import( ) {
        #$this->stopReplication();
        foreach( $this->source as $entry )
        {
            $this->importEntry( $entry );
        }
        #$this->startReplication();
    }
    
    private function importEntry( $entry )
    {
        $repository = $this->i_repository;
        $contentService = $repository->getContentService();
        $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );
        $contentCreateStruct = $contentService->newContentCreateStruct( $this->ContentType, 'ger-DE' );
        $contentCreateStruct_mapped = self::mapClass($entry, $contentCreateStruct);
        $draft = $contentService->createContent( $contentCreateStruct_mapped, array( $this->location ) );
        echo ".";
    }
    
    function mapClass( $entry, $contentCreateStruct )
    {
        #$classDef = array();
        foreach( $this->ContentType->fieldDefinitions as $field )
        {
            if( array_key_exists($field->identifier, $entry) )
            {
                $contentCreateStruct->setField( $field->identifier, $entry[$field->identifier] );
            }
            #$classDef[] = array( "id" => $field->identifier, "required" => $field->isRequired, "ezident" => $field->fieldTypeIdentifier  );
        }
        return $contentCreateStruct;
    }
    
}