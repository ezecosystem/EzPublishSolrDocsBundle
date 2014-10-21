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
    protected $_feed;
    protected $_feedurl;
    protected $title;
    protected $id;
    protected $_offset;
    protected $_limit;
    
    public function __construct($entries)
    {
        $this->_entries = $entries;
    }
    
    public function current()
    {
        return $this->_entries->item($this->_iterations);
    }
    public function key ()
    {
        return $this->_iterations;
    }
    public function toKey ( $_iteration )
    {
        return $this->_iterations = $_iteration;
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
        return $this->_entries->length;
    }
    
    public function validateImport( )
    {
        return true;
    }
    
    public function setOffset ( $offset )
    {
        return $this->_offset = $offset;
    }
    
    public function setLimit ( $limit )
    {
        return $this->_limit = $limit;
    }
    
    public function offset ( )
    {
        return $this->_offset;
    }
    
    public function limit ( )
    {
        return $this->_limit;
    }
}