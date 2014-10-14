<?php
namespace xrow\EzPublishSolrDocsBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use xrow\OData;
use xrow\EzPublishSolrDocsBundle\src\Import\Core_Thread;
use DOMDocument;
use DOMXPath;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\PhpProcess;

class ProcessTestCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{

    /**
     * Configures the command
     */
    protected function configure()
    {
        $this->setName('xrow:odata:ptest');
    }

    /**
     * Executes the command
     * 
     * @param InputInterface $input            
     * @param OutputInterface $output            
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Start");
        
        // test to see if threading is available
        if( !Core_Thread::available() ) {
            die( 'Threads not supported' );
        }
        #var_dump(self::paralel(10, "t1"));
        // create 2 thread objects
        $t1 = new Core_Thread( array('xrow\EzPublishSolrDocsBundle\Command\ProcessTestCommand', 'paralel'));
        $t2 = new Core_Thread( array('xrow\EzPublishSolrDocsBundle\Command\ProcessTestCommand', 'paralel'));
        
        // start them
        $t1->start( 10, 't1' );
        $t2->start( 10, 't2' );
        
        // keep the program running until the threads finish
        while( $t1->isAlive() && $t2->isAlive() ) {
             
        }
        #- See more at: http://blog.motane.lu/2009/01/02/multithreading-in-php/#sthash.nBeHweEm.dpuf

        
        $output->writeln("Ende");
    }
    // function to be ran on separate threads
    public static function paralel( $_limit, $_name ) {
        for ( $index = 0; $index < $_limit; $index++ ) {
            echo 'Now running thread ' . $_name . PHP_EOL;
            sleep( 1 );
        }
    }
}