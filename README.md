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

**As the files are edited online, if you use Git for version control, you need to add the path to them in the `.gitignore` file (explained below) to ignore them.**

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

**If you use Git for version control system, you need to add the path to folder defined in `folderPages` (that contains the files) in the `.gitignore` file, otherwise all the content will be altered by Git.**

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

For this, simply, create the following file and structure `c975LPageEditBundle/views/pageEdit.html.twig` in your app to override the existing Bundle template file.

In the overridding file, you must add a link to the cloud version (recommended) `https://cloud.tinymce.com/stable/tinymce.min.js` of TinyMce. You will need a free API key (available from the download link) **OR** download and link to your project [https://www.tinymce.com/download/](https://www.tinymce.com/download/).

You may wish to add an "if" call (to avoid call requests when PageEdit is not used) to the cloud/downloaded version in your `layout.html.twig`:
```twig
{# 975L PageEdit #}
    {% if app.request.attributes.get('_route') == '975l_page_edit' %}
        <script src="https://cloud.tinymce.com/stable/tinymce.min.js?apiKey=YOUR_API_KEY"></script>
    {% endif %}
```

You also need to initialize TinyMce ([language pack](https://www.tinymce.com/download/language-packages/) via `language_url`, css used by site via `content_css`, tools, etc.).

Information about options is available at [https://www.tinymce.com/docs/get-started-cloud/editor-and-features/](https://www.tinymce.com/docs/get-started-cloud/editor-and-features/).

Example of initialization (see `PageEdit.html.twig` file).

```javascript
    tinymce.init({
        language_url : 'http://example.com/js/tinymce/fr_FR.js',
        content_css : [
            'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',
            'http://example.com/css/styles.css',
        ],
        selector: 'textarea.tinymce',
        statusbar: false,
        plugins: 'code',
        browser_spellcheck: true,
        contextmenu: false,
        schema: 'html5 strict',
        toolbar: 'formatselect | bold italic | alignleft aligncenter alignright alignjustify | cut copy paste | undo redo bullist numlist outdent indent code',
    });
```

Step 6: Definitions of start and end of template for file saving
----------------------------------------------------------------

When the Twig file is saved, it is concatenated with the content of `Resources/views/startTemplate.html.twig` and `Resources/views/endTemplate.html.twig` to obtain the full file.

These two files contains the data you need to have, to display correctly (i.e. extends of layout, etc.). To do so, proceed as above for `pageEdit.html.twig` by creating your own files from examples delivered in the Bundle. **Take care to keep `{% block pageEdit %}` in `startTemplate.html.twig` and `{% endblock %}` in `endTemplate.html.twig` as they are the entry and exit points to defines content. Also, keep `{% block toolbar %}` to keep toolbar. **

In the same way, you can also override the `toolbar.html.twig` located in the same place.

Step 7: How to use
------------------

The Route to display a page is `http://example.com/pages/{page}`, the one to edit is `http://example.com/pages/edit/{page}`.

Toolbar to access modification is displayed below the page if allowed by access rule.

Link to a page, in Twig, can be done by `<a href="{{ path('975l_display_page', { 'page': 'slug' }) }}">Title of the page</a>`.
