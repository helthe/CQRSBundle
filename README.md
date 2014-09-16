# Helthe CQRS Bundle [![Build Status](https://travis-ci.org/helthe/CQRSBundle.png?branch=master)](https://travis-ci.org/helthe/CQRSBundle) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/helthe/CQRSBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/helthe/CQRSBundle/?branch=master)

Helthe CQRS Bundle integrates the [Helthe CQRS Component](https://github.com/helthe/CQRS)
with your Symfony2 application.

## Installation

### Step 1: Add package requirement in Composer

#### Manually

Add the following in your `composer.json`:

```json
{
    "require": {
        // ...
        "helthe/cqrs-bundle": "dev-master"
    }
}
```

#### Using the command line

```bash
$ composer require 'helthe/cqrs-bundle=dev-master'
```

### Step 2: Register the bundle in the kernel

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Helthe\Bundle\CQRSBundle\HeltheCQRSBundle(),
    );
}
```

## Usage

The bundle registers command handler services automatically if they are tagged with the `helthe_cqrs.command_handler` tag.
The tag requires a `command` attribute which represents the command name. The command name needs to be the full class name
of the command class the handler can execute.

```xml
<service id="acme_demo.command_handler.your_handler_name" class="Acme\DemoBundle\CommandHandler\AcmeCommandHandler">
    <tag name="helthe_cqrs.command_handler" command="Acme\DemoBundle\Command\AcmeCommand" />
</service>
```

## Bugs

For bugs or feature requests, please [create an issue](https://github.com/helthe/CQRSBundle/issues/new).