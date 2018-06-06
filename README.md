[![Build Status](https://travis-ci.org/solt9029/PHPUtility.svg?branch=master)](https://travis-ci.org/solt9029/PHPUtility)

# 開発環境

- php: 7.1.9

# 概要

- 便利な関数の寄せ集め

# 使い方

- install

```
composer require solt9029/php-utility:dev-master
```

- coding

```php
require_once('vendor/autoload.php');
use Solt9029\Utility;
Utility::dist([[0, 0], [10, 10]]);
```