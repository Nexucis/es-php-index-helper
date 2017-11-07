# Elasticsearch Index Helper for php

[![CircleCI](https://circleci.com/gh/Nexucis/es-php-index-helper.svg?style=shield)](https://circleci.com/gh/Nexucis/es-php-index-helper) [![codecov](https://codecov.io/gh/Nexucis/es-php-index-helper/branch/master/graph/badge.svg)](https://codecov.io/gh/Nexucis/es-php-index-helper) [![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE)

[![Latest Stable Version](https://poser.pugx.org/nexucis/es-index-helper/v/stable)](https://packagist.org/packages/nexucis/es-index-helper) [![Latest Unstable Version](https://poser.pugx.org/nexucis/es-index-helper/v/unstable)](https://packagist.org/packages/nexucis/es-index-helper)

1. [Overview](#overview) 
2. [Installation](#installation)
3. [Quickstart](#quickstart)
3. [Contributions](#contributions)
4. [License](#license)

## Overview

This project provides an index Helper in order to help you to manage your ES Indices with no downtime. This helper implements the philosophy described in the 
[official documentation](https://www.elastic.co/guide/en/elasticsearch/guide/master/index-aliases.html) which can be summarized in a few words : *use alias instead of index directly*

### Versioning
This project uses the following version rules: 

```
X.Y.Z
```

Where : 
* X is the major version of ElasticSearch supported by this project
* Y is the major version of this helper. Be careful with this rule, you can have some breaking changes between two **Y** number. 
* Z is the minor version of this Index Helper. It will be increased when there are some bug fixes.

## Installation

The recommended method to install this library is through [Composer](https://getcomposer.org/).

1. In your `composer.json` file put the following dependency : 

```json
{
  "require": {
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

*We encourage users to take a look at [the interface](./src/Nexucis/Elasticsearch/Helper/Nodowntime/IndexHelperInterface.php) in order to have an idea of all available methods. The following description is not exhaustive, so you will miss some method if you don't cast a glance at the code*

### Index Operations

#### Create an index

It all begins with an index creation :

```php
<?php
$alias = "myindex";
$helper->createIndex($alias);
```

As you can see, we pass an alias name and not and index name through the helper. With the Helper, you will see everything through an alias and not the index directly. 

The method below will create an index with the name `myindex` and with the suffix `_v1`, and put an alias with the name `myindex`.

If you request ElasticSearch directly (with [Sense](https://www.elastic.co/guide/en/sense/current/index.html) for example), you could see something like this : 

```bash
GET _cat/aliases
# which can give the following result : 
alias   index      filter routing.index routing.search
myIndex myindex_v1 -      -            -
```

### Mapping Operation
This helper proves his existence when you want to change your mapping dynamically and you still want full access to your data. 

So to make this things possible we need to:

1. create a second index like `myindex_v2`
2. copy the settings from `myindex_v1` to `myindex_v2`
3. put the new mapping in `myindex_v2`
4. copy all documents from `myindex_v1` to `myIndex_v2`.
5. remove the old index `myindex_v1` and put the alias `myindex` in the new one

It takes a lot of steps and verifications to check the update is done successfully. That's why this Helper comes here with the following simplify method : 

```php
<?php
$alias = "myindex";
$mapping = [
    'my_type' => [
        'properties' => [
            'first_name' => [
                'type' => 'string',
                'analyzer' => 'standard'
            ],
            'age' => [
                'type' => 'integer'
                ]
            ]
    ]
];
$helper->updateMapping($alias, $mapping);
```

You just need to provide the alias name and the new mapping and that's it.

:warning: With an index with many documents, the update can take a lot of time. That's why it's better:

* With ElasticSearch `2.4`, to call this method in an asynchronous process..
* With ElasticSearch `5` or greater, to set the parameter `$waitForCompletion` to false. It will return taskID, which can then be used with the [_task api](https://www.elastic.co/guide/en/elasticsearch/reference/current/tasks.html)
    
### Settings Operation
Indices settings can be updated the same way as mapping using the `updateSettings` method:

```php
<?php
$alias = "myindex";
$settings =[ 
    'number_of_shards' => 1,
    'number_of_replicas' => 0,
    'analysis' => [ 
        'filter' => [
            'shingle' => [
                'type' => 'shingle'
            ]
        ],
        'char_filter' => [
            'pre_negs' => [
                'type' => 'pattern_replace',
                'pattern' => '(\\w+)\\s+((?i:never|no|nothing|nowhere|noone|none|not|havent|hasnt|hadnt|cant|couldnt|shouldnt|wont|wouldnt|dont|doesnt|didnt|isnt|arent|aint))\\b',
                'replacement' => '~$1 $2'
            ],
            'post_negs' => [
                'type' => 'pattern_replace',
                'pattern' => '\\b((?i:never|no|nothing|nowhere|noone|none|not|havent|hasnt|hadnt|cant|couldnt|shouldnt|wont|wouldnt|dont|doesnt|didnt|isnt|arent|aint))\\s+(\\w+)',
                'replacement' => '$1 ~$2'
            ]
        ],
        'analyzer' => [
            'reuters' => [
                'type' => 'custom',
                'tokenizer' => 'standard',
                'filter' => ['lowercase', 'stop', 'kstem']
            ]
        ]
    ]
];
$helper->updateSettings($alias, $settings);
```

## Contributions
Any contribution or suggestion would be really appreciated. Feel free to use the Issue section or to send a pull request.

### Development - Run unit test
If you want to launch the unit test, you need to have a local elasticsearch instance which must be accessible through the url http://localhost:9200. A simply way to launch it, is to start the [corresponding container](https://hub.docker.com/_/elasticsearch/) : 

```bash
docker run -d -p 9200:9200 -p 9300:9300 elasticsearch:2.4
```

Once ElasticSearch is up, you can run the following command :

```bash
./vendor/bin/phpunit
```

## License
[MIT](./LICENSE)
