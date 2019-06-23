Changelog
=========

## [v7.0.0](https://github.com/Nexucis/es-php-index-helper/tree/7.0.0)
* Upgrade elasticsearch-php dependency version from 6.0.X to 7.0.1
* Requirement of PHP 7.1 instead of 7.0 that is not supported since 1st Jan 2019
* **[Public]** add a new parameter `$includeTypeName` in the method `updateMappings`, following the [elasticsearch recommendation](https://www.elastic.co/blog/moving-from-types-to-typeless-apis-in-elasticsearch-7-0)

## [v6.1.1](https://github.com/Nexucis/es-php-index-helper/tree/6.1.1)

### Feature
* **[Dev]**: Support elastic lib with version between 6.0.1 and 7.0.0

## [v6.1.0](https://github.com/Nexucis/es-php-index-helper/tree/6.1.0)

### Feature
* **[Public]** : add a new method `advancedSearchDocument` that takes an object `SearchParameter` that allows the possibility to customize the searching in an advanced mode
* **[Public]** : Update elasticsearch-php dependency version from 6.0.0 to 6.0.1
* **[Dev]**: composer wraps the way to start the test and the checktyle of the code

## [v6.0.0](https://github.com/Nexucis/es-php-index-helper/tree/6.0.0)

### Feature
* Upgrade elasticsearch-php dependency version from 5.3.1 to 6.0.0
* **[Public]** Add new method `deleteIndexByAlias` which cannot be taken an index as parameter

### Breaking Changes

* **[Public]** Make `deleteIndex `protected
* **[Public]** Rename `createIndex `by `createIndexByAlias`

## [v5.1.0](https://github.com/Nexucis/es-php-index-helper/tree/5.1.0)
*All modifications below are exactly the same that in the version 2.1.0*

* **[Public]** Add public method : **getDocument**
* **[Public]** Add **refresh** parameter to the following methods : 
     * addDocument
     * updateDocument
     * updateDocument
* **[Internal]** Add some verification in protected method **copySettings**
* **[Test]** Improve test on method : **addDocument**

## [v5.0.0](https://github.com/Nexucis/es-php-index-helper/tree/5.0.0)

* Upgrade elasticsearch-php dependencie version from 2.3.1 to 5.3.1
* **[Circleci]** Improve `wait_es_up.sh` script` which didn't work anymore with the es version 5.5.2
* **[Test]** Update test with new mapping syntax
* **[Documentation]** Add supported version section in README

## [v2.1.0](https://github.com/Nexucis/es-php-index-helper/tree/2.1.0)

* **[Public]** Add public method : **getDocument**
* **[Public]** Add **refresh** parameter to the following methods : 
     * addDocument
     * updateDocument
     * updateDocument
* **[Internal]** Add some verification in protected method **copySettings**
* **[Test]** Improve test on method : **addDocument**

## [v2.0.0](https://github.com/Nexucis/es-php-index-helper/tree/2.0.0)

This is the first release. I hope you'll enjoy it !