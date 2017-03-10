# AmqpOroMessageQueue Bundle

The bundle register AmqpTransportFactory.

## AMQP (RabbitMQ) Transport

RabbitMQ provides better and faster messages delivery versus DBAL. You should prefer to use it if possible.

### Options

The config settings for the [default RabbitMQ Access Control settings](https://www.rabbitmq.com/access-control.html) (a user named guest with a default password of guest, granted full access to the / virtual host) are:

```yaml
# app/config/config.yml

oro_message_queue:
  transport:
    default: 'amqp'
    amqp:
        host: 'localhost' 
        port: '5672' 
        user: 'guest' 
        password: 'guest' 
        vhost: '/' 
```

We can also move the specified options to the `parameters.yml`:

```yaml
# app/config/config.yml

oro_message_queue:
    transport:
        default: '%message_queue_transport%'
        '%message_queue_transport%': '%message_queue_transport_config%'
    client: ~
```


```yaml
# app/config/parameters.yml

    message_queue_transport: 'amqp'
    message_queue_transport_config: { host: 'localhost', port: '5672', user: 'guest', password: 'guest', vhost: '/' }
```

## RabbitMQ installation

You need to have RabbitMQ **version 3.6.*** installed to use the AMQP transport. To install the RabbitMQ you should follow the [download and installation manual](https://www.rabbitmq.com/download.html). 

After the installation please check you have all the required plugins installed and enabled. 

## RabbitMQ plugins

### Required plugins 
 
Plugin name | Version  | Appointment
------------|----------|------------
rabbitmq_delayed_message_exchange|0.0.1|A plugin that adds delayed-messaging (or scheduled-messaging) to RabbitMQ. [See also](https://github.com/rabbitmq/rabbitmq-delayed-message-exchange)

The plugin `rabbitmq_delayed_message_exchange` is necessarily needed for the proper work but it is not installed by default so you need to download, install and enable it. 
  
To download it use a command like

```
curl http://www.rabbitmq.com/community-plugins/v3.6.x/rabbitmq_delayed_message_exchange-0.0.1.ez > {RABBITMQ_HOME}/plugins/rabbitmq_delayed_message_exchange-0.0.1.ez
```
  
To enable it use the command
  
```
rabbitmq-plugins enable --offline rabbitmq_delayed_message_exchange
```

### Recommended plugins

Plugin name | Version  | Appointment
------------|----------|------------
rabbitmq_management|3.6.*|Provides an HTTP-based API for management and monitoring of your RabbitMQ server. [See also](https://www.rabbitmq.com/management.html)


### Plugins management

To enable plugins, use the `rabbitmq-plugins` tool:
 ```
rabbitmq-plugins enable plugin-name
 ```
 
And to disable plugins again, use:
 ```
rabbitmq-plugins disable plugin-name
 ```
 
You can see a list of which plugins are enabled with:
 ```
 rabbitmq-plugins list  -e 
 ```

You will see something like:

```
[e*] amqp_client                       3.6.5 
[e*] mochiweb                          2.13.1 
[E*] rabbitmq_delayed_message_exchange 0.0.1
[E*] rabbitmq_management               3.6.5
[e*] rabbitmq_management_agent         3.6.5
[e*] rabbitmq_web_dispatch             3.6.5
[e*] webmachine                        1.10.3
```

The sign `[E*]` means that the plugin was explicitly enabled i.e. somebody enabled manually. The sign `[e*]` means the plugin was  implicitly enabled i.e. enabled automatically as it was required for some other enabled plugin.

[More about RabbitMQ plugins](https://www.rabbitmq.com/community-plugins.html)

[More about RabbitMQ plugins management](https://www.rabbitmq.com/plugins.html)

## Troubleshooting

The exception

```
  [PhpAmqpLib\Exception\AMQPRuntimeException]  
  Broken pipe or closed connection   
```
  
might be caused by one of the following reasons:

* The plugin `rabbitmq_delayed_message_exchange` is missing
* The RabbitMQ version is too old (older than 3.5.8)
  
## RabbitMQ Useful Hints

* You can see the RabbitMQ default web interface here if the `rabbitmq_management` plugin is enabled: `http://localhost:15672/`. [See more details here](https://www.rabbitmq.com/management.html).
* You can temporary stop RabbitMQ by running the command `rabbitmqctl stop_app`. The command will stop the RabbitMQ application, leaving the Erlang node running. You can resume it with the command `rabbitmqctl start_app`. [See more details here](https://www.rabbitmq.com/man/rabbitmqctl.1.man.html).
