System requirements
===================

This bundle has some requirements that have to be satisfied for the usage.

### Elasticsearch

This bundle supports Elasticsearch engine versions 2.*.
Developer can manually specify minimum allowed version and upper bound version in the application configuration.

### Required plugins

* [Delete By Query](https://www.elastic.co/guide/en/elasticsearch/plugins/2.4/plugins-delete-by-query.html)

To provide a possibility to refresh types, OroElasticSearchBundle relies on the Delete By Query functionality, and
it is required to install a plugin to support it. Please, follow the 
[official documentation for installation](https://www.elastic.co/guide/en/elasticsearch/plugins/2.4/plugins-delete-by-query.html#_installation)
to do that.
