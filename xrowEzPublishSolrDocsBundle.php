<?php

namespace xrow\EzPublishSolrDocsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
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
}
