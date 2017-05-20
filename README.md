PageEditBundle
==============

PageEditBundle is a simple tool to edit content of twig pages unless of doing so via a code editor.


BundleInstallation
==================

Step 1: Download the Bundle
---------------------------
Add the following to your `composer.json > require section`
```
"require": {
    ...
    "c975L/pageedit-bundle": "1.*"
},
```
Then open a command console, enter your project directory and update composer,
by executing the following command, to download the latest stable version of this bundle:

```bash
$ composer update
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new c975L\PageEditBundle\c975LPageEditBundle();
        ];

        // ...
    }

    // ...
}
```
