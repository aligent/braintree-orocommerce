Index agent and search engine
=============================

Index agent and search engine are two basic classes used to work with Elasticsearch index and perform fulltext search.


Index agent
-----------

**Class:** Oro\Bundle\ElasticSearchBundle\Engine\IndexAgent

Index agent is used by the search engine to get index name, initialize client and perform reindexing.
Agent receives DI configuration of the search engine, like access credentials and index name, and uses it to setup entity mapping. Afterwards it adds
additional settings to tokenize text fields and merge all generated data with external configuration.

Entity mapping is being built, based on search entity configuration defined in `search.yml` files, main configuration and
field type mappings. Field type mapping are injected through DI as a parameter.

_oro\_elasticsearch.field\_type\_mapping_:

```yml
text:
    type: string
    store: true
    index: not_analyzed
decimal:
    type: double
    store: true
integer:
    type: integer
    store: true
datetime:
    type: date
    store: true
    format: "yyyy-MM-dd HH:mm:ss||yyyy-MM-dd"
```

To make search faster a field, that contains all text information ("all_text") is created, converted to lowercase and
split into tokens using nGram tokenizer. This field has custom search and index analyzers attached, defined
in additional index settings.

This data is used to create and initialize client (instance of Elasticsearch\Client) and then return it to
search engine to perform fulltext search. The Index agent class uses the ClientFactory class to create an instance. You can use the factory to instantiate as many clients with various configurations, as you wish. 

The agent provides ability to recreate the whole index only. Partial mapping recreation is no longer possible.

Full recreation deletes existing index and creates new one with defined configuration.

Recreation is used by search engine to perform the reindex operation.


Search engine
-------------

**Class:** Oro\Bundle\ElasticSearchBundle\Engine\ElasticSearch

Search engine is the core of search - it implements AbstractEngine interface and used by SearchBundle as the main engine.
Search engine uses index agent as a proxy that works directly with search index f.e. to get index name or
recreate index.

To perform save and delete operations search engine uses [Elasticsearch bulk API](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-bulk.html).
Deletion performs as is, but save requires to delete existing entity first and only then save new entity - it used to
avoid storing of old values that are not overridden because of empty fields.

Reindex operations recreate whole search index and then triggers save operation for
all affected entities.

Search engine uses [request builders](./request_builders.md) to build Elasticsearch search request
based on source query. Each request builder in chain receives current request, modifies it and returns altered data.
New request builders can be added to engine through DI.
