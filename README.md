EzPublishSolrDocsBundle
=======================

php ezpublish/console xrow:solrdocs:testimport


Odata Validator

http://services.odata.org/validation/

Test services

http://services.odata.org/V4/OData/OData.svc/

Odata Gui Tool

http://pragmatiqa.com/xodata/

Testing XMLs

```sh
php ezpublish/console xrow:odata:validate --source="http://services.odata.org/V4/OData/OData.svc/Products?\$top=20&\$format=atom"
```
or
```sh
php ezpublish/console xrow:odata:validate --source="vendor/xrow/ezpublish-solrdocs-bundle/Lib/c1test.xml"
```
or
```sh
php ezpublish/console xrow:odata:import --source="<linkToSource>" --class="odata4product" --offset=0 --limit=1000 --conc=<1-4 processes threaded> --location="<location slash seperated>" --clean="[no|location|class|all]"
```


Add routes to ezpublish/config/routes.yml:

```yaml
xrow_ez_publish_solr_docs_homepage:
    path:     /solrdoc/{name}
    defaults: { _controller: xrowEzPublishSolrDocsBundle:Default:index }

xrow_ez_publish_solr_docs_showdoc:
    path:     /solrdocview/{remoteid}
    defaults: { _controller: xrowEzPublishSolrDocsBundle:Default:solrDocView }
    
xrow_ez_publish_universalsearch:
    path:     /universalsearch
    defaults: { _controller: xrowEzPublishSolrDocsBundle:Default:solrDocUniversalSearch }
    
xrow_ez_publish_universalsearch_slash:
    path:     /universalsearch/
    defaults: { _controller: xrowEzPublishSolrDocsBundle:Default:solrDocUniversalSearch }
    
xrow_ez_publish_universalsearch_withsearchtext:
    path:     /universalsearch/{searchtext}
    defaults: { _controller: xrowEzPublishSolrDocsBundle:Default:solrDocUniversalSearch }
    
xrow_ez_publish_universalsearch_ajax:
    path:     /universalsearch_ajax
    defaults: { _controller: xrowEzPublishSolrDocsBundle:Default:solrDocUniversalSearchAjax }
xrow_ez_publish_universalsearch_ussuggest:
    path:     /autosuggest/{config}/{searchtext}
    defaults: { _controller: xrowEzPublishSolrDocsBundle:Default:solrDocUniversalSearchAutoSuggest }
```

Components of this Bundle

* SOLR Storage Handler
* Import API
* Standard Import Scripts for formats like OData 4.0

The Import Model

We decided that it is neccary to abstarct the Import in the most simple way.

```php
$source = new OData\Source( $url );
$import = new Import\Process( $location, $contentType, $source );
if( $import->validate() ){
    $import->import();
}
```

A new Source is defined to implement Iterator, Countable, Sourceable to get properly imported though the Importer.
