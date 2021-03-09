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
