Configuration
=============

This bundle provides ability to configure search engine for your needs.

Parameters
----------

Basically if you have Elasticsearch server running the default settings should be sufficient - search engine will automatically
define client and index configuration and then create index. 

You can configure your Elasticsearch client though. It uses the following parameters from file `app/parameters.yml`:

Basic parameters:
* **search_engine_name** - engine name, must be "elastic_search" for Elasticsearch engine;
* **search_engine_host** - host name which Elasticsearch should be connected to, don't forget to specify https:// if you're going to use SSL;
* **search_engine_port** - port number which Elasticsearch should use for connection;

Auth parameters:
* **search_engine_username** - login for HTTP Auth authentication;
* **search_engine_password** - password for HTTP Auth authentication;

Index name: 
* **search_engine_index_name** - name of Elasticsearch index to store data in;

SSL Authentication:
* **search_engine_ssl_verification** - path to cacert.pem certificate which is used to verifiy node's certificate
* **search_engine_ssl_cert** - path to client's public certificate file
* **search_engine_ssl_cert_password** - password for certificate defined in **search_engine_ssl_cert** option, it is optional parameter
* **search_engine_ssl_key** - path to client's private key file
* **search_engine_ssl_key_password** - password for key defined in **search_engine_ssl_key** option, it is optional parameter

You will likely need Shield installed in Elasticsearch for Cluster SSL authentication to work.
[See here.](https://www.elastic.co/products/shield)

For general information on configuring SSL certificates, see [ElasticSearch documentation](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html)).

If you need more specific Elasticsearch configuration, see following chapters.

Client configuration
--------------------

To configure your Elasticsearch engine you should put the next configuration to the `app/config.yml` under the `oro_search` section:

```yml
oro_search:
    engine: "elastic_search"
```

In this case all required configuration will be taken from `app/parameters.yml`  (see [Parameters section](#parameters))

If you need a thin configuration you can put your setting directly to `app/config.yml`

```yml
oro_search:
    engine: "elastic_search"
    engine_parameters:
        client:
            hosts: ['192.168.10.5:9200', '192.168.15.7:9200']
            # other configuration options for which setters exist in ElasticSearch\ClientBuilder class
            # (e.g. retries option can be used as setRetries() method exists)
            retries: 1
```

Index configuration
-------------------

All configuration, which needs to create index for Elasticsearch contains in `search.yml` files and in main `config.yml`.
This configuration will be converted to Elasticsearch mappings format and will be available in the following format:

```yml
oro_search:
    engine_parameters:
        client:
            # ... client configuration
        index:
            index: <indexName>
            body:
                mappings:                               # mapping parameters
                    <entityTypeName-1>:                 # name of type
                        properties:
                            <entityField-1>:            # name of field
                                type:   string          # type of field
                            # ... list of entity fields
                            <entityField-N>:
                                type:   string
                    # ... list of types
                    <entityTypeName-N>:
                        properties:
                            <entityField-1>:
                                type:   string
```

More information about index configuration you can find in
[Elasticsearch documentation](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_index_management_operations.html).

Disabled environment checks
---------------------------

Bundle provides ability to disable some system level checks that happen during the application installation or index
creation. These checks are used to ensure that environment is proper configured and ensure that search index in fact
accessible. But in some cases these checks might be disabled to isolate all interaction with Elasticsearch at
`/<indexName>/*` URL.

**Important!** Disabling of these checks might lead to inconsistent or unpredictable behaviour of an application.
Use them at your own risk.

Here are options that allow to disable checks:

* **system_requirements_check** (default `true`) - system requirements check during the application installation and
usage; please, make sure that supported version of Elasticsearch is used and all required plugins are installed

* **index_status_check** (default `true`) - check index accessibility and readiness after creation; please, make sure
that Elasticsearch index will be ready to work right after creation

Here is example of configuration that disables both of these checks:

```yml
oro_search:
    engine_parameters:
        system_requirements_check: false
        index_status_check: false
```
