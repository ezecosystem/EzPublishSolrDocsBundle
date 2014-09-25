<?php

namespace xrow\EzPublishSolrDocsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class xrowEzPublishSolrDocsExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('legacy_solr_override.yml');
        $loader->load('legacy_solrdoc.yml');
        $loader->load('solrdocsconfig.yml');
        $solrserverconfig = $container->getParameter('xrow_ez_publish_solr_docs.solrserver');
        $solrclasses = $container->getParameter('xrow_ez_publish_solr_docs.solr_classes');
        $solrClassesConfig = $container->getParameter('xrow_ez_publish_solr_docs.solrglobalconfig');
        $container->setParameter('xrow_ez_publish_solr_docs.solrserverconfig', $solrserverconfig);
        $container->setParameter('xrow_ez_publish_solr_docs.solr_classes', $solrclasses);
        $container->setParameter('xrow_ez_publish_solr_docs.solrglobalconfig', $solrClassesConfig);
        
    }
    /*
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
	*/
}
