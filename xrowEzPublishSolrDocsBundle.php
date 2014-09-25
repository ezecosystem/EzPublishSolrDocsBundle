<?php

namespace xrow\EzPublishSolrDocsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use xrow\EzPublishSolrDocsBundle\DependencyInjection\Compiler\AggregateCriterionVisitorPass;
use xrow\EzPublishSolrDocsBundle\DependencyInjection\Compiler\AggregateFacetBuilderVisitorPass;
use xrow\EzPublishSolrDocsBundle\DependencyInjection\Compiler\AggregateFieldValueMapperPass;
use xrow\EzPublishSolrDocsBundle\DependencyInjection\Compiler\AggregateSortClauseVisitorPass;
use xrow\EzPublishSolrDocsBundle\DependencyInjection\Compiler\FieldRegistryPass;
use xrow\EzPublishSolrDocsBundle\DependencyInjection\Compiler\SignalSlotPass;
use xrow\EzPublishSolrDocsBundle\Lib\Globals;

class xrowEzPublishSolrDocsBundle extends Bundle
{
    public function boot()
    {
        // Set some static globals
        Globals::setSolrServerConfig($this->container->getParameter('xrow_ez_publish_solr_docs.solrserverconfig'));
        Globals::setSolrClassesConfig($this->container->getParameter('xrow_ez_publish_solr_docs.solr_classes'));
        Globals::setSolrGlobalConfig($this->container->getParameter('xrow_ez_publish_solr_docs.solrglobalconfig'));
    }

    public function build( ContainerBuilder $container )
    {
        parent::build( $container );
        $container->addCompilerPass( new AggregateCriterionVisitorPass );
        $container->addCompilerPass( new AggregateFacetBuilderVisitorPass );
        $container->addCompilerPass( new AggregateFieldValueMapperPass );
        $container->addCompilerPass( new AggregateSortClauseVisitorPass );
        $container->addCompilerPass( new FieldRegistryPass );
        $container->addCompilerPass( new SignalSlotPass );
    }
    
}