<?php 
namespace xrow\Import;

use \eZ\Publish\API\Repository\Values\Content\Location;


class Process implements Importing {
    function __construct( Location $location, ContentType $type, Source $source ) {
        ;
    }
    function validate( ) {

    }
	function import( ) {
        $this->stopReplication();
        foreach( $this->source as $entry )
        {
        	$this->importEntry( $entry );
        }
        $this->startReplication();
	}
	private function importEntry( $entry ) {

	}
}