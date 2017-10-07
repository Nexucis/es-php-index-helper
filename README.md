# Elasticsearch Index Helper for php

1. [Overview](#overview) 
2. [Installation](#installation)
3. [Quickstart](#quickstart)
3. [Contributions](#contributions)
4. [License](#license)

## Overview

This project provide an index Helper in order to help you to manage your ES Indices with no downtime. This helper implement the philosophy described in the 
[official documentation](https://www.elastic.co/guide/en/elasticsearch/guide/master/index-aliases.html) which can be summarized in a few words : *use alias instead of index directly*

### Versioning
This project use the following version rules: 

```
X.Y.ZZ
```

where : 
* X is the major version of ElasticSearch support by this project
* Y is the major version of this Index Helper. Be careful with this rule, you can have some breaking changes between two **Y** number. 
* ZZ is the minor version of this Index Helper. It will be increased when there are some bug fixes.

## Installation

The recommended method to install this library is through [Composer](https://getcomposer.org/).

1. In your `composer.json` file put the following dependency : 

```json
{
  "require":{
    "nexucis/es-index-helper": "X.Y.*"
  }
}
```

where X.Y is the version which fit your need. 

2. After that you need to install this new dependency : 

```bash
    php composer.phar install
```

3. To initialize the Index Helper, you need first to instantiate the elasticsearch client : 

```php

    <?php

    use Elasticsearch\ClientBuilder;
    use Nexucis\Elasticsearch\Helper\Nodowntime\IndexHelper;

    require 'vendor/autoload.php';

    $client = ClientBuilder::create()->build();
    $helper = new IndexHelper();
    $helper->setClient($client);
```

To configure the elasticsearch client, you can read the [associated documentation](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html)

## Quickstart

*We encourage users to see the interface in order to have an idea of all available methods. The following description is not exhaustive, so you will miss some method if you don't cast a glance at the code*

### Index Operations

#### Create an index

It all begins with an index creation :

```php
    <?php
    $alias = "myIndex";
    $helper->createIndex($alias);
```

As you can see, we pass an alias name and not and index name through the helper. With the Helper, you will see everything through an alias and not the index directly. 

The method below will create an index with the name `myIndex` and with the suffix `_v1`, and put an alias with the name `myIndex`.

If you request ElasticSearch directly (with [Sense](https://www.elastic.co/guide/en/sense/current/index.html) for example), you could see something like this : 

```bash
GET _cat/aliases
# which can give the following result : 
alias   index      filter routing.index routing.search
myIndex myIndex_v1 -      -            -
```

### Mapping Operation
This helper prove his existence when you want to change your mapping dynamically and you still want a fully access to your data. 

So to make this things possibily we need :

1. to create a second index like `myIndex_v2`
2. to copy the settings from `myIndex_v1` to `myIndex_v2`
3. to put the new mapping in `myIndex_v2`
4. to copy all documents from `myIndex_v1` to `myIndex_v2`.
5. and finally, we need to remove the old index `myIndex_v1` and put the alias `myIndex` in the new one

It does a lot of steps to perform, and to check. That's why this Helper comes here with the following simplify method : 

```php
 <?php
 $alias = "myIndex";
 $mapping = array();
 $helper->updateMapping($alias, $mapping);
```

You just need to provide the alias name and the new mapping and that's it.

:warning: With an index with many documents, this kind of method can take a lot of time. That's why it should be better :
    * With ElasticSearch `2.4`, if the method call is performed in an asynchronous process.
    * With ElasticSearch `5` or greater, if you set the last parameter `$waitForCompletion`to false. It will give a taskID which can be used with the [_task api](https://www.elastic.co/guide/en/elasticsearch/reference/current/tasks.html)
    
### Settings Operation
The same logic described below can be apply to the settings operation. In this way, you will find the method `updateSettings` which do the same things than the method `updateMapping` if you switch *mapping* by *settings* and vice versa.

## Contributions
Any contribution or suggestion would be really appreciated. Feel free to use the Issue section or to send a pull request.

## License
[MIT](./LICENSE)