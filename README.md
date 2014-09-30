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
php ezpublish/console xrow:odata:validate --source="http://services.odata.org/V4/OData/OData.svc/Products?$top=20&$format=atom"
```
or
```sh
php ezpublish/console xrow:odata:validate --source="vendor/xrow/ezpublish-solrdocs-bundle/Lib/c1test.xml"
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
```
