PageEditBundle
==============
PageEditBundle does the following:

- Displays pages requested,
- Provides tools to edit content of pages, unless of doing it via a code editor,
- Integrates with your web design,
- Protects twig code from being formatted,
- Archives the files before replacing them in order to be able to retrieve old versions,
- Gives the possibility to create a `sitemap.xml̀` of managed files, setting their change frequency and priority,
- Allows to store specific templates in a `protected` folder to display it but not being able to modify it,
- Allows to create a PDF version of pages,

It is, of course, still possible to modify directly those files with an editor.

This Bundle relies on the use of [TinyMce](https://www.tinymce.com/), [jQuery](https://jquery.com/) and [Bootstrap](http://getbootstrap.com/).

[PageEditBundle dedicated web page](https://975l.com/en/pages/pageedit-bundle).

[PageEditBundle API documentation](https://975l.com/apidoc/c975L/PageEditBundle.html).

Bundle installation
===================

Step 1: Download the Bundle
---------------------------
Use [Composer](https://getcomposer.org) to install the library
```bash
    composer require c975l/pageedit-bundle
```

Step 2: Enable the Bundle
-------------------------
Then, enable the bundle by adding them to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
<?php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new c975L\PageEditBundle\c975LPageEditBundle(),
        ];
    }
}
```

Step 3: Configure the Bundle
----------------------------
Check dependencies for their configuration:
- [KnpPaginatorBundle](https://github.com/KnpLabs/KnpPaginatorBundle)
- [KnpSnappyBundle](https://github.com/KnpLabs/KnpSnappyBundle)
- [wkhtmltopdf-amd64](https://github.com/h4cc/wkhtmltopdf-amd64)

For KnpSnappyBundle you can use this configuration if it suits to your needs.
```yml
knp_snappy:
    process_timeout: 20
    temporary_folder: "%kernel.cache_dir%/snappy"
    pdf:
        enabled: true
        binary: "%kernel.root_dir%/../vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64"
        options:
            print-media-type: true
            page-size: A4
            orientation: 'portrait'
            encoding : utf-8
            dpi: 300
            images: true
            image-quality: 80
            margin-left: 10mm
            margin-right: 10mm
            margin-top: 10mm
            margin-bottom: 10mm
    image:
        enabled: false
```

v2.0+ of c975LPageEditBundle uses [c975L/ConfigBundle](https://github.com/975L/ConfigBundle) to manage configuration parameters. Use the Route "/pages/config" with the proper user role to modify them.

**Upgrading from v1.x? Check [UPGRADE.md](UPGRADE.md).**

**If you use Git for version control, you need to add the full path `app/Resources/views/[folderPages]` and `/web/images/[folderPages]` in the `.gitignore`, otherwise all the content will be altered by Git.**

Step 4: Enable the Routes
-------------------------
Then, enable the routes by adding them to the `app/config/routing.yml` file of your project:

```yml
c975_l_page_edit:
    resource: "@c975LPageEditBundle/Controller/"
    type: annotation
    prefix: /
    #Multilingual website use the following
    #prefix: /{_locale}
    #defaults:   { _locale: '%locale%' }
    #requirements:
    #    _locale: en|fr|es
```

Step 5: Link and initialization of TinyMce
------------------------------------------
It is strongly recommended to use the [Override Templates from Third-Party Bundles feature](http://symfony.com/doc/current/templating/overriding.html) to integrate fully with your site.

For this, simply, create the following structure `app/Resources/c975LPageEditBundle/views/` in your app and then duplicate the file `layout.html.twig` in it, to override the existing Bundle files, then apply your needed changes, such as language, etc.

In `layout.html.twig`, it will mainly consist to extend your layout and define specific variables, i.e. :
```twig
{% extends 'layout.html.twig' %}

{# Defines specific variables #}
{% set title = 'PageEdit (' ~ title ~ ')' %}

{% block content %}
    {% block pageedit_content %}
    {% endblock %}
{% endblock %}
```

It is recommended to use [Tinymce Cloud version](https://go.tinymce.com/cloud/). You will need a [free API key](https://store.ephox.com/my-account/api-key-manager/).
**OR** you can download and link to your project [https://www.tinymce.com/download/](https://www.tinymce.com/download/).

If you want to keep all the available tools and make no change to Tinymce as it is, you don't need to overwrite `tinymceInit.html.twig`. You just need to provide, in `parameters.yml`, your `tinymceApiKey` (see above) if you use the cloud version and the `tinymceLanguage` (+ upload the corresponding file on your server under `web/vendor/tinymce/[tinymceLanguage].js`). Oherwise you need to override `tinymceInit.html.twig`.

Step 6: Definitions of start and end of template for file saving
----------------------------------------------------------------
When the Twig file is saved, it is concatenated with the content of `Resources/views/skeleton.html.twig` to obtain the full file.

This file must extends your layout in order to display correctly. It is recommended to **NOT** override this file, but if you do so, take care to keep `{% block pageedit_content %}` and `{% endblock %}` as they are the entry and exit points to defines content.

How to use
----------
The Url to display a page is `http://example.com/pages/{page}`, the one to edit is `http://example.com/pages/modify/{page}`, to display a PDF is `http://example.com/pages/pdf/{page}`.

A toolbar is displayed below the title if user is identified and has the acess rights.

Link to a page, in Twig, can be done by `<a href="{{ path('pageedit_display', { 'page': 'slug' }) }}">Title of the page</a>`.

The different Routes (naming self-explanatory) available are:
- pageedit_home
- pageedit_config
- pageedit_display
- pageedit_pdf
- pageedit_create
- pageedit_modify
- pageedit_duplicate
- pageedit_delete
- pageedit_dashboard
- pageedit_upload
- pageedit_slug
- pageedit_links
- pageedit_help

Creation of PDF
---------------
PageEditBundle uses `KnpSnappyBundle` to generates PDF, which itself uses `wkhtmltopdf`. `wkhtmltopdf` requires that included files, like stylesheets, are included with an absolute url. But, there is a known problem with SSL, see https://github.com/wkhtmltopdf/wkhtmltopdf/issues/3001, which force you to downgrade openssl, like in https://gist.github.com/kai101/99d57462f2459245d28b4f5ea51aa7d0.

You can avoid this problem by including the whole content of included files, which is what `wkhtmltopdf` does, in your html output. To integrate them easily, you can, as [c975L/SiteBundle](https://github.com/975L/SiteBundle) does, use [c975L/IncludeLibraryBundle](https://github.com/975L/IncludeLibraryBundle) with the following code:
```twig
{# in your layout.html.twig > head #}
    {% if display == 'pdf' %}
        {{ inc_content('bootstrap', 'css', '3.*') }}
        {{ inc_content(absolute_url(asset('css/styles.min.css')), 'local') }}
    {% else %}
        {{ inc_lib('bootstrap', 'css', '3.*') }}
        {{ inc_lib('cookieconsent', 'css', '3.*') }}
        {{ inc_lib('fontawesome', 'css', '5.*') }}
        {{ inc_lib(absolute_url(asset('css/styles.min.css')), 'local') }}
    {% endif %}
```

Integrate sub-pages
-------------------
To add sub-pages in sub-folders, simply use a "/" as separator in the Url semantic field.

Homepage specific
-----------------
The home page can be managed via PageEdit, but as it is called at the root of the website it has a specificity. it's name is "home" and cannot be changed.

Protect specific templates
--------------------------
If you need to protect specific templates (containing lot of Twig tag, Twig variable setting, etc. or if you don't want your final user to be able to modify them, to not break the website), simply put those templates in `app/Resources/views/[folderPages]/protected`, they will be displayed as other, and included in the sitemap, but not available for modifications.
**You just need to encapsulate the content of the template within the `skeleton.html.twig`.**

Use the Twig Extension to automate building menus
-------------------------------------------------
You can use the provided Twig Extension `folder_content()` to easily build menus based on the content of a specific folder, for this use the following code:
```html
{% set files = folder_content('specific_folder') %}
<ul class="nav navbar-nav">
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Title <span class="caret"></span></a>
        <ul class="dropdown-menu">
            {% for file, title in files %}
                <li><a href="{{ path('pageedit_display', { 'page': file }) }}">{{ title }}</a></li>
            {% endfor %}
        </ul>
    </li>
</ul>
```

Migrating existing files to PageEdit
------------------------------------
To migrate existing files, simply move your existing templates in the folder defined in `app/Resources/views/[folderPages]` (`folderPages` has been defined in Step 3 above), access to PageEdit dashboard and do the modifications. The skeleton will be added to new files and old ones will be archived.

You can use the command `git rm -r --cached app/Resources/views/[folderPages]` to remove it from Git, if the folder was previously indexed.

**Don't forget to make a copy of it, if you use Git as versionning system and if you have added this folder in the `.gitignore`, otherwise your files will be deleted at next commit !**

If files have been deleted by Git, simply use the code below:

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

**If this project help you to reduce time to develop, you can [buy me a coffee](https://www.buymeacoffee.com/LaurentMarquet) :)**