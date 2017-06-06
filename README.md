PageEditBundle
==============

PageEditBundle does the following:

- Display pages requested,
- Provides tool to edit content of pages, unless of doing it via a code editor,
- Integrates with your wed design,
- Protects twig code from being formatted,
- Archives the files before replacing them in order to be able to retrieve old versions.

It is, of course, still possible to modify directly those files with an editor.

This Bundle relies on the use of [TinyMce](https://www.tinymce.com/).

Bundle installation
===================

Step 1: Download the Bundle
---------------------------
Add the following to your `composer.json > require section`
```
"require": {
    ...
    "c975L/pageedit-bundle": "1.*"
},
```
Then open a command console, enter your project directory and update composer, by executing the following command, to download the latest stable version of this bundle:

```bash
$ composer update
```

This command requires you to have Composer installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

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
            new c975L\PageEditBundle\c975LPageEditBundle(),
        ];

        // ...
    }

    // ...
}
```

Step 3: Configure the Bundle
----------------------------

Then, in the `app/config.yml` file of your project, define `folderPages` (path where the files will be stored) and `roleNeeded` (The role needed to enable access to the edition of page).

```yml
#app/config/config.yml

c975_l_page_edit:
    folderPages: 'pages' #The full path to this folder has to be added to .gitignore if Git is used
    roleNeeded: 'ROLE_ADMIN'
```

**As the files are edited online, if you use Git for version control, you need to add the full path to them (defined in `folderPages`) in the `.gitignore` file to ignore them, otherwise all the content will be altered by Git.**

Step 4: Enable the Routes
-------------------------

Then, enable the routes by adding them to the `app/config/routing.yml` file of your project:

```yml
#app/config/routing.yml

...
c975_l_page_edit:
    resource: "@c975LPageEditBundle/Controller/"
    type:     annotation
    prefix:   /
```

Step 5: Link and initialization of TinyMce
------------------------------------------

It is strongly recommend to use the [Override Templates from Third-Party Bundles feature](http://symfony.com/doc/current/templating/overriding.html) to integrate fully with your site.

For this, simply, create the following structure `app/Resources/c975LPageEditBundle/views/` in your app and then duplicate the file `layout.html.twig` in it, to override the existing Bundle file.

In the overridding file, you must add a link to the cloud version (recommended) `https://cloud.tinymce.com/stable/tinymce.min.js` of TinyMce. You will need a free API key (available from the download link) **OR** download and link to your project [https://www.tinymce.com/download/](https://www.tinymce.com/download/).

You also need to initialize TinyMce ([language pack](https://www.tinymce.com/download/language-packages/) via `language_url`, css used by site via `content_css`, tools, etc.).

Information about options is available at [https://www.tinymce.com/docs/get-started-cloud/editor-and-features/](https://www.tinymce.com/docs/get-started-cloud/editor-and-features/).

Example of initialization (see `layout.html.twig` file).

```javascript
    tinymce.init({
        selector: 'textarea.tinymce',
        statusbar: true,
        menubar: false,
        browser_spellcheck: true,
        contextmenu: false,
        schema: 'html5 strict',
        image_advtab: true,
        content_css : [
            'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',
        ],
        //language_url : 'http://example.com/js/tinymce/fr_FR.js',
        plugins: [
            'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            'searchreplace wordcount visualblocks visualchars code fullscreen',
            'insertdatetime media nonbreaking save table contextmenu directionality',
            'emoticons template paste textcolor colorpicker textpattern imagetools codesample toc help',
        ],
        toolbar: [
            'styleselect | removeformat bold italic strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
            'undo redo | cut copy paste | insert link image imagetools emoticons table | print preview code | fullscreen help',
        ],
    });
```

Step 6: Definitions of start and end of template for file saving
----------------------------------------------------------------

When the Twig file is saved, it is concatenated with the content of `Resources/views/skeleton.html.twig` to obtain the full file.

This file must extends your layout in order to display correctly, so you need to override it as explained above for `layout.html.twig`. So, duplicate the file `skeleton.html.twig` in `app/Resources/c975LPageEditBundle/views/` and set your data in it.

**Take care to keep `{% block pageEdit %}` and `{% endblock %}` as they are the entry and exit points to defines content.**

**Also, keep `{% block toolbar %}` to keep toolbar, `{% set pageedit_title="%title%" %}` and `{% set pageedit_slug="%slug%" %}` used for metadata.**

Step 7: How to use
------------------

The Route to display a page is `http://example.com/pages/{page}`, the one to edit is `http://example.com/pages/edit/{page}`.

Toolbar to display modification link is displayed below the page, if allowed by access rule.

Link to a page, in Twig, can be done by `<a href="{{ path('pageedit_display_page', { 'page': 'slug' }) }}">Title of the page</a>`.

Step 8 - Migrating existing files to PageEdit
---------------------------------------------

To migrate existing files, you need to create new pages and then copy/paste all the content.

Depending on the number of pages, size and formatting of content, etc. you may do it via the TinyMce editor **OR** directly in the generated files under `app/Resources/views/[folderPages]` (`folderPages`has been defined in Step 3 above).
