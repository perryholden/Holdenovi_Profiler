# Holdenovi_Profiler

## Description

A Magento 2 profiler based on AOE_Profiler for Magento 1

## Installation via GitHub

```bash
git clone -b master git@github.com:perryholden/Holdenovi_Profiler.git app/code/Holdenovi/Profiler
bin/magento module:enable Holdenovi_Profiler
bin/magento setup:upgrade
```

### Installation via Composer

```bash
composer config repositories.holdenovi_profiler vcs https://github.com/perryholden/Holdenovi_Profiler.git
composer require holdenovi/module-profiler
bin/magento module:enable Holdenovi_Profiler
bin/magento setup:upgrade
```

## Configuration

To enable the profiler:

1. Copy the `holdenovi_profiler.xml.sample` file to `var/holdenovi_profiler.xml` and configure based on XML file comments.
2. Disable the full-page and block caches: `bin/magento cache:disable full_page block_html`
3. Run the following command: `bin/magento dev:profiler:enable '{"drivers":[{"type":"Holdenovi\\Profiler\\Driver\\Hierarchy"}]}'`

## Enable database profiling

Add this to your `env.php` under `db` → `connection` → `default`:

```php
'profiler' => [
    'class' => '\\Magento\\Framework\\DB\\Profiler',
    'enabled' => true
],
```

## Notes

Depending on the complexity of your site a lot of data ends up being collected during a profile run. The first problem with this is that the database table
holding this information might be growing. Please double check the cron settings of the cleanup task.

By default MySQL comes with max_allowed_packet set to 1 MB. One profile run could exceed 1 MB. Please check `var/log/system.log` for error messages and increase this setting in your MySQL server configuration. (Also see: https://dev.mysql.com/doc/refman/8.0/en/packet-too-large.html)

```
[mysqld]
max_allowed_packet=16M
```

## TODO:

* Add back in `captureModelInfo` and `captureBacktraces` features.
* Implement cron cleanup of old run records in the database.
* Implement logging of invalid nesting.