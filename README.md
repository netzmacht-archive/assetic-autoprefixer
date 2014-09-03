[Autoprefixer filter](https://github.com/ai/autoprefixer)
=========================================================

This is a filter implementation to use the [Autoprefixer filter](https://github.com/ai/autoprefixer)
within the [PHP assetic framework](https://github.com/kriswallsmith/assetic).

Requirements
------------

ai/autoprefixer is required to be installed.

### Install autoprefixer globally

```bash
sudo npm install -g autoprefixer
```

### Install autoprefixer locally

```bash
npm install autoprefixer
```

Usage in PHP
------------

```php
use Bit3\Assetic\Filter\Autoprefixer\AutoprefixerFilter;

// if you have installed autoprefixer globally
$autoprefixerBinary = '/usr/bin/autoprefixer';

// if you have installed autoprefixer locally
$autoprefixerBinary = '/../node_modules/.bin/autoprefixer';

$autoprefixerFilter = new AutoprefixerFilter($autoprefixerBinary);

// if node.js binary is not installed as /usr/bin/node
// (e.g. on debian/ubuntu the binary is named /usr/bin/nodejs)
$autoprefixerFilter->setNodeBin('/usr/bin/nodejs');
```

Usage in Symfony2
-----------------

This project comes with a assetic filter configuration file, located in the `config` directory.

Define the autoprefixer binary path in the `parameters.yml`:

```yaml
parameters:
  # if you have installed autoprefixer globally
  assetic.autoprefixer.bin: /usr/bin/autoprefixer

  # if you have installed autoprefixer locally
  assetic.autoprefixer.bin: %kernel.root_dir%/../node_modules/.bin/autoprefixer

  # if node.js binary is not installed as /usr/bin/node
  # (e.g. on debian/ubuntu the binary is named /usr/bin/nodejs)
  assetic.node.bin: /usr/bin/nodejs
```

Then enable the filter in the `assetic` configuration chapter:

```yaml
# Assetic Configuration
assetic:
    filters:
        autoprefixer:
          resource: "%kernel.root_dir%/../vendor/bit3/assetic-autoprefixer/config/autoprefixer.xml"
          # if you like, you can use apply_to here :-)
          # otherwise you use the filter in your template with filter="autoprefixer"
``