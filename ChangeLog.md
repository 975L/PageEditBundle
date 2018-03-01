# ChangeLog

v1.26.2.1
---------
- Removed the "|raw" for `toolbar_button` call as safe html is now sent (01/03/2018)

v1.26.2
-------
- Corrected Tinymce call with apiKey (01/03/2018)

v1.26.1
-------
- Corrected call for bootstrap in `tinymceInit.html.twig` (28/02/2018)

v1.26
-----
- Added c957L/IncludeLibrary to include libraries in `layout.html.twig` (27/02/2018)
- Changed `tinymceInit.html.twig` for include of Tinymce via c975L/IncludeLibrary (27/02/2018)

v1.25
-----
- Added 'Command' part auto-wire to `services.yml` (20/02/2018)
- Added 'Controller' part auto-wire to `services.yml` (20/02/2018)
- Abandoned Glyphicon and replaced by fontawesome (22/02/2018)
- Corrected display button in toolbar (22/02/2018)

v1.24.1
-------
- Corrected Route `pageedit_display` due to renaming of `edit` to `modify` (19/02/2018)

v1.24
-----
- Put `New` button before `edit` one in toolbar (17/02/2018)
- Renamed templates for forms (17/02/2018)
- Added `cancel` action to toolbar and removed from bottom of forms (17/02/2018)
- Changed wording for submit button for forms (17/02/2018)
- Added PageEdit title in help page (18/02/2018)
- Corrected `help-es.html.twig` for delete a page (19/02/2018)
- Suppressed translations taken from `c975L\ToolbarBundle` (19/02/2018)
- Renamed Route `edit` to `modify` (19/02/2018)

v1.23
-----
- Moved link to display page under the name of the page and suppression of the link "display" in the dashboard page (05/02/2018)
- Added posibilities to sort pages under dashboard (05/02/2018)
- Added "No pages" information in dashboard (05/02/2018)

v1.22.2
-------
- Renamed Twig function (05/02/2018)
- Updated ToolbarBundle product -> dashboard (05/02/2018)

v1.22.1
-------
- Corrections in `README.md` (04/02/2018)

v1.22
-----
- Change about composer download in `README.md` (04/02/2018)
- Add support in `composer.json`+ use of ^ for versions request (04/02/2018)
- Replace toolbar by use of c975L/ToolbarBundle (04/02/2018)

v1.21
-----
- Added the possibility to list a folder and return an arrayr of file + title (24/01/2018)

v1.20
-----
- Changed default value for sitemap priority in skeleton (24/01/2018)
- Corrected upload picture for sub-pages (24/01/2018)
- Added dates foreach bullet in `ChangeLog.md` (24/01/2018)

v1.19
-----
- Add of possibility to duplicate a page (24/01/2018)

v1.18
-----
- Add of advice about changing semantic url (16/01/2018)
- Add of possibility to have hierarchical url, i.e. `domain.com/folder/sub-folder/file` (18/01/2018)
- Add of possibility to list and display archived, deleted and redirected files (18/01/2018)
- Suppress of "set redirection part" in deleteFile Service as it's not used (22/01/2018)

v1.17.1
-------
- Separation of information about parameters.yml in `README.md` (16/08/2017)
- Direct call of Tinymce API key instead of repeating it in `config.yml`
- Changes in `README.md` (16/08/2017)

v1.17
-----
- Add of missing replacement of \" (20/07/2017)
- Add of service getTitle() and remove of duplicates uses (10/08/2017)
- Add of service getPriority() (10/08/2017)
- Add of service getChangeFrequency() (10/08/2017)
- Add of translation, where relevant, of title using Twig calls to translator, such as `'title_to_be_translated'|trans({}, 'domain')` (10/08/2017)
- Change of translation for priority (10/08/2017)

v1.16
-----
- Add of escaping for " in titles (20/07/2017)
- Set functions in Controller as service (20/07/2017)

v1.15.2
-------
- Run PHP CS-Fixer (18/07/2017)
- Remove of .travis.yml as tests have to be defined before (18/07/2017)

v1.15.1
-------
- Add "Best practice" for tinymceApiKey (08/07/2017)

v1.15
-----
- Update README.md (06/07/2017)
- Move of translated help pages to sub-folder `langugages` (07/07/2017)
- Add of class responsive for tables in `tinymceInit.html.twig` (07/07/2017)
- Make `tinymceInit.html.twig` re-usable by setting config keys `tinymceApiKey` and `tinymceLanguage` (07/07/2017)
- Redirection to dashboard in case of delete a page, in place of redirecting to the deleted page (07/07/2017)
- Add of signout button on toolbar + config signoutRoute (07/07/2017)
- Add of main dashboard button on toolbar + config dashboardRoute (07/07/2017)
- Moves sitemap information below the main textarea (07/07/2017)

v1.14.2
-------
- Update README.md (04/07/2017)
- Move `title`value in Twig templates in place of Controller, more simple (04/07/2017)

v1.14.1
-------
- Forgotten use for FileSystem in SitemapCreateCommand (03/07/2017)

v1.14
-----
- Remove of "<div class="container">" in templates as it extends `layout.html.twig` and this kind of data has to be set site by site (03/07/2017)
- Add of class "img-responsive" for images and remove of dimensions (03/07/2017)
- Add of a "protected" folder to store the templates for which the content MUST NOT be modified via Tinymce, but still displayed with PageEdit (03/07/2017)
- Move the call of `tinymce.js` to `tinymceInit.html.twig` instead of `layout.html.twig` to avoid calling it for pages that don't need it. (03/07/2017)
- Group toolbars in one file (03/07/2017)
- Add of semantic url value in dashboard (03/07/2017)
- Add of link to dashboard on PageEdit label in toolbar and remove of dashboard button (03/07/2017)
- Update return location for uploaded images to absolute url + set absolute in tinymceInit (03/07/2017)
- Group in one function the creation of folders needed by the Bundle (03/07/2017)
- Remove of "required" on textarea.tinymce as it won't submit for a new page, a refresh has to be done - https://github.com/tinymce/tinymce/issues/2584 (03/07/2017)

v1.13.1
-------
- Modification of SitemapCreateCommand to allow multilingual (01/07/2017)

v1.13
-----
- Add a Console Command to create sitemap of managed files (01/07/2017)

v1.12
-----
- Change wording for validate button (30/06/2017)
- Move `tinymceInit.html.twig` to `views` folder in order to simplify it's overridding as Tinymce can be initialized only once (30/06/2017)
- Add options to Tinymce init (30/06/2017)
- Add list of pages available for easy linking from Link tool (30/06/2017)
- Add information on help pages (30/06/2017)
- Add link to dedicated web page (30/06/2017)

v1.11.2
-------
- Add informations about registering third party bundles in README.md (29/06/2017)

v1.11.1
-------
- Add of requirement of "cocur/slugify" in composer.json forgotten (29/06/2017)

v1.11
-----
- Correction of 'images_upload_url' twice in layout.html.twig (29/06/2017)
- Move in a separate file of the initialization of Tinymce (29/06/2017)
- Add of pagination via KnpPaginator (dashboard) (29/06/2017)
- Remove of slugify function and replace by cocur/slugify (29/06/2017)

v.1.10
------
- Correction for "Title not found" translation in dashboard (20/06/2017)
- Correction "text.special_formatting" to include {# #} (20/06/2017)
- Add in `README.md` of possibilty to use `prefix: /{_locale}` in `routing.yml` for multilingual website (21/06/2017)
- Correction for title in edit mode when title is Twig code (21/06/2017)
- Add of upload picture feature (21/06/2017)

v1.9
----
- Esthetic changes in layout.html.twig (18/06/2017)
- Correction when pages are not including skeleton yet to display on dashboard to facilitate migration (19/06/2017)
- Remove of readonly on semantic url, to give possibility to change title without changing url and file name, and allow only one name in cas of multi languages (19/06/2017)
- Add of possibility to set the title as translation of term using Twig code `{{ 'text.cookies_policy'|trans({}, 'commons') }}` (19/06/2017)

v1.8
----
- Make readonly of semantic_url field and replacment by automatic slugging of title (18/06/2017)
- Set up a redirection (302) if title (and then slug and name of file) has changed, to the new file (18/06/2017)
- Add of references to jQuery and Bootstrap in README.md (18/06/2017)
- Add of button in toolbars (edit/Delete) to display the page (18/06/2017)
- Add of translation domain to avoid other texts coming from other bundle with same keyword (18/06/2017)
- Add of id of user who mades the change of the page in the filename of archived file (18/06/2017)

v1.7.1
------
- Add of PageEdit toolbars for New/edit/Delete page (17/06/2017)

v1.7
----
- Update of README.md (13/06/2017)
- Corrections in translations (13/06/2017)
- Suppression of "if" test for actual route in layout/html.twig (15/06/2017)
- Add of dashboard link in toolbar (17/06/2017)
- Add of help link in toolbar (17/06/2017)
- Add of pageedit_title in skeleton.html.twig (17/06/2017)
- Add of remove of all signs not to be used in semantic url (17/06/2017)

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
- Renaming of layout.html.twig (05/06/2017)

v1.4
----
- Move toolbar above content instead of below (04/06/2017)
- Add of a message to prevent deletion of Twig calls between {} (04/06/2017)
- Add 'action' to PageEdit object to differentiate call of form (04/06/2017)
- Add of possibility to delete page (04/06/2017)

v1.3.1
------
- Corrections in translations files (04/06/2017)

v1.3
----
- Add of a link 'Cancel' on the Edit page (04/06/2017)
- Group in one file for Controllers (04/06/2017)
- Separation of data from pageEdit.html.twig to layout.html.twig to allow other forms (04/06/2017)
- Move to sub-folder `forms`of template `pageEdit.html.twig` (04/06/2017)
- Add of possibility to create a page (04/06/2017)
- Add of title and slug data for page (04/06/2017)
- Separation in functions in Controller for re-usable parts (04/06/2017)

v1.2
----
- Group in one file and rename of the template used as skeleton (04/06/2017)
- Changes in README.md (04/06/2017)

v1.1.2 & v1.1.3
---------------
- Suppression of extra comma in composer.json (03/06/2017)

v1.1.1
------
- Changes in README.md (03/06/2017)

v1.1
----
- Add of code files (31/05/2017)
- Add of explanations in README.md (31/05/2017)

v1.0
----
- Creation of bundle (20/05/2017)