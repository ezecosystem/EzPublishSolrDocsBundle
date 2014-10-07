<?php 

namespace xrow\EzPublishSolrDocsBundle\src\Import;
use xrow\EzPublishSolrDocsBundle\src\Import\Sourceable;
use Countable;
use Iterator;
use DOMDocument;

abstract class ImportSource implements Iterator, Countable, Sourceable
{
    protected $_iterations;
    protected $_entries;
    protected $title;
    protected $id;
    
    public function __construct($entries)
    {
        $this->_entries = $entries;
    }
    
    public function current()
    {
        return $this->_entries[$this->_iterations];
    }
    public function key ()
    {
        return $this->_iterations;
    }
    public function next ()
    {
        return $this->_iterations++;
    }
    public function rewind ()
    {
        return $this->_iterations = 0;
    }
    public function valid ()
    {
        return $this->_iterations < $this->count();
    }
    
    public function count()
    {
        return count( $this->_entries );
    }
}