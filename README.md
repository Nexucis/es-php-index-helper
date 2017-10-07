# Elasticsearch Index Helper for php

1. [Overview](#overview) 
2. [Guideline on how to use it](guideline-on-how-to-use-it)
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

## Guideline on how to use it

### Installation

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

## Contributions
Any contribution or suggestion would be really appreciated. Feel free to use the Issue section or to send a pull request.

## License
[MIT](./LICENSE)