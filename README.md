PageEditBundle
==============
PageEditBundle does the following:

- Displays pages requested,
- Provides tools to edit content of pages, unless of doing it via a code editor,
- Integrates with your web design,
- Protects twig code from being formatted,
- Archives the files before replacing them in order to be able to retrieve old versions,
- Gives the possibility to create a `sitemap.xml̀` of managed files, setting their change frequency and priority
- Allows to store specific templates in a `protected` folder to display it but not being able to modify it

It is, of course, still possible to modify directly those files with an editor.

This Bundle relies on the use of [TinyMce](https://www.tinymce.com/), [jQuery](https://jquery.com/) and [Bootstrap](http://getbootstrap.com/).

[PageEdit Bundle dedicated web page](https://975l.com/en/pages/pageedit-bundle).

Bundle installation
===================

Step 1: Download the Bundle
---------------------------
Add the following to your `composer.json > require section`
```
"require": {
    "c975L/pageedit-bundle": "1.*"
},
```
Then open a command console, enter your project directory and update composer, by executing the following command, to download the latest stable version of this bundle:

```bash
$ composer update
```

This command requires you to have Composer installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

Step 2: Enable the Bundles
--------------------------
Then, enable the bundles by adding them to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
<?php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new c975L\PageEditBundle\c975LPageEditBundle(),
        ];
    }
}
```

Step 3: Configure the Bundles
-----------------------------
Setup your Tinymce API key, optional if you use the cloud version, in `parameters.yml`
```yml
    #(Optional) Your Tinymce Api key if you use the cloud version
    #tinymceApiKey: YOUR_API_KEY
```

And then in `parameters.yml.dist`
```yml
    #(Optional) Your Tinymce Api key if you use the cloud version
    #tinymceApiKey:     ~
```

Then, in the `app/config.yml` file of your project, define the following:

```yml
#https://github.com/KnpLabs/KnpPaginatorBundle
knp_paginator:
    default_options:
        page_name: p
        distinct: true
    template:
        pagination: 'KnpPaginatorBundle:Pagination:twitter_bootstrap_v3_pagination.html.twig'

c975_l_page_edit:
    #Path where the files will be stored. The full path ('app/resources/views/[folderPages]') has to be added to .gitignore if Git is used
    folderPages: 'pages'
    #User's role needed to enable access to the edition of page
    roleNeeded: 'ROLE_ADMIN'
    #Base url for sitemap creation without leading slash
    sitemapBaseUrl: 'http://example.com'
    #(Optional) Array of available languages of the website
    sitemapLanguages: ['en', 'fr', 'es']
    #(Optional) Your tinymce language if you use one, MUST BE placed in 'web/vendor/tinymce/[tinymceLanguage].js'
    tinymceLanguage: 'fr_FR' #default null
    #(Optional) Your signout Route if you want to allow sign out from PageEdit toolbar
    signoutRoute: 'name_of_your_signout_route' #default null
    #(Optional) Your main dashboard route if you want to allow it from PageEdit toolbar
    dashboardRoute: 'your_dashboard_route' #default null
```

**If you use Git for version control, you need to add the full path `app/Resources/views/[folderPages]` in the `.gitignore`, otherwise all the content will be altered by Git. You also need to add the path `/web/images/[folderPages]` as it will contain the uploaded pictures**

Step 4: Enable the Routes
-------------------------
Then, enable the routes by adding them to the `app/config/routing.yml` file of your project:

```yml
c975_l_page_edit:
    resource: "@c975LPageEditBundle/Controller/"
    type:     annotation
    #Multilingual website use: prefix: /{_locale}
    prefix:   /
```

Step 5: Link and initialization of TinyMce
------------------------------------------
It is strongly recommended to use the [Override Templates from Third-Party Bundles feature](http://symfony.com/doc/current/templating/overriding.html) to integrate fully with your site.

For this, simply, create the following structure `app/Resources/c975LPageEditBundle/views/` in your app and then duplicate the files `layout.html.twig`, `skeleton.html.twig` in it, to override the existing Bundle files, then apply your needed changes, such as language, etc.

In `layout.html.twig`, it will mainly consist to extend your layout and define specific variables, i.e. :
```twig
{% extends 'layout.html.twig' %}

{# Defines specific variables #}
{% set title = 'PageEdit (' ~ title ~ ')' %}

{% block content %}
    <div class="container">
        {% block pageedit_content %}
        {% endblock %}
    </div>
{% endblock %}
```

It is recommended to use [Tinymce Cloud version](https://go.tinymce.com/cloud/). You will need a [free API key](https://store.ephox.com/my-account/api-key-manager/).
**OR** you can download and link to your project [https://www.tinymce.com/download/](https://www.tinymce.com/download/).

If you want to keep all the available tools and make no change to Tinymce as it is, you don't need to overwrite `tinymceInit.html.twig`. You just need to provide, in `config.yml` your `tinymceApiKey`, if you use the cloud version and the `tinymceLanguage` (+ upload the corresponding file on your server under `web/vendor/tinymce/[tinymceLanguage].js`). Or you can overwrite `tinymceInit.html.twig`.

Step 6: Definitions of start and end of template for file saving
----------------------------------------------------------------
When the Twig file is saved, it is concatenated with the content of `Resources/views/skeleton.html.twig` to obtain the full file.

This file must extends your layout in order to display correctly, so you need to override it as explained above for `layout.html.twig`. So, duplicate the file `skeleton.html.twig` in `app/Resources/c975LPageEditBundle/views/` and set your data in it.

**Take care to keep `{% block pageedit_content %}` and `{% endblock %}` as they are the entry and exit points to defines content.**

**Also, keep `{% block toolbar %}` to keep toolbar and `{% set pageedit_title="%title%" %}` used for metadata.**


How to use
----------
The Route to display a page is `http://example.com/pages/{page}`, the one to edit is `http://example.com/pages/edit/{page}`.

A toolbar is displayed below the title if user is identified and has the acess rights.

Link to a page, in Twig, can be done by `<a href="{{ path('pageedit_display', { 'page': 'slug' }) }}">Title of the page</a>`.

The different Routes (naming self-explanatory) available are:
- pageedit_display
- pageedit_new
- pageedit_edit
- pageedit_delete
- pageedit_dashboard
- pageedit_upload
- pageedit_slug
- pageedit_links
- pageedit_help

Integrate sub-pages
-------------------
To add sub-pages in sub-folders, simply use a "/" as separator in the Url semantic field.

Protect specific templates
--------------------------
If you need to protect specific templates (containing lot of Twig tag, Twig variable setting, etc. or if you don't want your final user to be able to modify them, to not break the website), simply put those templates in `app/Resources/views/[folderPages]/protected`, they will be displayed as other, and included in the sitemap, but not available for modifications.
**You just need to encapsulate the content of the template within the `skeleton.html.twig`.**

Migrating existing files to PageEdit
------------------------------------
To migrate existing files, simply move your existing template in the folder defined in `app/Resources/views/[folderPages]` (`folderPages` has been defined in Step 3 above), access to PageEdit dashboard and do the modifications. The skeleton will be added to new files and old ones will be archived.

You can use the command `git rm -r --cached app/Resources/views/[folderPages]` to remove it from Git if the folder was previously indexed.

**Don't forget to make a copy of it if you use Git as versionning system and if you have added this folder in the `.gitignore`, otherwise your files will be deleted at next commit !**

If files have been deleted, simply use the code below:

```
git log #Gives you latest commit
git checkout <id_commit> #Indicate here the id of the commit obtained above
#Access to your files, copy/paste them somewhere else
git checkout HEAD #Get back to latest version
```

Create Sitemap
--------------
In a console use `php bin/console pageedit:createSitemap` to create a `sitemap-[folderPages].xml` in the `web` folder of your project. You can use a crontab to generate it every day.
You can add this file in a `sitemap-index.xml`that groups all your sitemaps or directly use it if you have only one.