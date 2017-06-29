# Changelog

v1.11.2
-------
- Add informations about registering third party bundles in README.md (29/06/2017)

v1.11.1
-------
- Add of requirement of "cocur/slugify" in composer.json forgotten (29/06/2017)

v1.11
-----
- Correction of 'images_upload_url' twice in layout.html.twig (29/06/2017)
- Move in a separate file of the initialization of Tinymce
- Add of pagination via KnpPaginator (dashboard)
- Remove of slugify function and replace by cocur/slugify

v.1.10
------
- Correction for "Title not found" translation in dashboard (20/06/2017)
- Correction "text.special_formatting" to include {# #}
- Add in `README.md` of possibilty to use `prefix: /{_locale}` in `routing.yml` for multilingual website (21/06/2017)
- Correction for title in edit mode when title is Twig code
- Add of upload picture feature

v1.9
----
- Esthetic changes in layout.html.twig (18/06/2017)
- Correction when pages are not including skeleton yet to display on dashboard to facilitate migration (19/06/2017)
- Remove of readonly on semantic url, to give possibility to change title without changing url and file name, and allow only one name in cas of multi languages
- Add of possibility to set the title as translation of term using Twig code `{{ 'text.cookies_policy'|trans({}, 'commons') }}`

v1.8
----
- Make readonly of semantic_url field and replacment by automatic slugging of title (18/06/2017)
- Set up a redirection (302) if title (and then slug and name of file) has changed, to the new file
- Add of references to jQuery and Bootstrap in README.md
- Add of button in toolbars (edit/Delete) to display the page
- Add of translation domain to avoid other texts coming from other bundle with same keyword
- Add of id of user who mades the change of the page in the filename of archived file

v1.7.1
------
- Add of PageEdit toolbars for New/edit/Delete page (17/06/2017)

v1.7
----
- Update of README.md (13/06/2017)
- Corrections in translations
- Suppression of "if" test for actual route in layout/html.twig (15/06/2017)
- Add of dashboard link in toolbar (17/06/2017)
- Add of help link in toolbar
- Add of pageedit_title in skeleton.html.twig
- Add of remove of all signs not to be used in semantic url

v1.6.3
------
- Correction (2) of routes in toolbar (08/06/2017)

v1.6.2
------
- Correction of routes in toolbar (08/06/2017)

v1.6.1
------
- Suppresion of " in Routes (08/06/2017)

v1.6
----
- Renaming of routes `975l_XXX` and `pageedit_XXX` (more accurate) [BC-BREAK] (08/06/2017)

v1.5
----
- Renaming of route `975l_display_page` by `pageedit_display_page` (more accurate) [BC-BREAK] (06/06/2017)

v1.4.1.2
--------
- Add mention lo License in php files (05/06/2017)

v1.4.1.1
--------
- Suppression of button for download zip as function is not available yet (05/06/2017)

v1.4.1
------
- Arrangments of getters and setters (05/06/2017)
- Renaming of layout.html.twig

v1.4
----
- Move toolbar above content instead of below (04/06/2017)
- Add of a message to prevent deletion of Twig calls between {}
- Add 'action' to PageEdit object to differentiate call of form
- Add of possibility to delete page

v1.3.1
------
- Corrections in translations files (04/06/2017)

v1.3
----
- Add of a link 'Cancel' on the Edit page (04/06/2017)
- Group in one file for Controllers
- Separation of data from pageEdit.html.twig to layout.html.twig to allow other forms
- Move to sub-folder `forms`of template `pageEdit.html.twig`
- Add of possibility to create a page
- Add of title and slug data for page
- Separation in functions in Controller for re-usable parts

v1.2
----
- Group in one file and rename of the template used as skeleton (04/06/2017)
- Changes in README.md

v1.1.2 & v1.1.3
---------------
- Suppression of extra comma in composer.json (03/06/2017)

v1.1.1
------
- Changes in README.md (03/06/2017)

v1.1
----
- Add of code files (31/05/2017)
- Add of explanations in README.md

v1.0
----
- Creation of bundle (20/05/2017)