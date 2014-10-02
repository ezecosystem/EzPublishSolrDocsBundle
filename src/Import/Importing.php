<?php

namespace xrow\Import;

interface Importing
{
    public function __construct( Location $location, Source $source );
    public function import( $entry );
    public function validate( $entry );
    
}