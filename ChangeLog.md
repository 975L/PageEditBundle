# ChangeLog

## v6.4

- Removed use of`c975L/ServicesBundle` and replaced by `c975L/SiteBundle` (09/03/2025)
- Removed use of`c975L/IncludeLibraryBundle` (09/03/2025)

## v6.3

- Added ->setMaxAge(3600) to controllers (15/09/2024)

## v6.2

- Suppressed spaceless filter as it's deprecated (12/09/2024)

## v6.1.2

- Changed DependencyInjection Extension (10/09/2024)

## v6.1.1

- Updated Command file (31/03/2024)

## v6.1

- Removed use of static variables (26/03/2024)
- Removed support for SF 3 structure (26/03/2024)

## v6.0.5

- Correction for sitemap to remove the "/pages/home" part (25/03/2024)

## v6.0.4

- Cosmetic changes (22/01/2024)

## v6.0.3

- Corrected UtilsController file (17/01/2024)

## v6.0.2

- Corrected UtilsController file (17/01/2024)

## v6.0.1

- Added previous removed files... (17/01/2024)

## v6.0

- Changed to new recomended bundle SF 7 structure (16/01/2024)

Upgrading from v5.x? **Check UPGRADE.md**

## v5.0

- Changed to AbstractBundle (04/12/2023)
- Changed routes to attribute (04/12/2023)

Upgrading from v4.x? **Check UPGRADE.md**

## v4.1.2

- re-added FQCN (30/11/2023)

## v4.1.1

- Commented use of KNP SnappyBundle, to be replaced later (30/11/2023)

## v4.1

- Suppressed use of KNP SnappyBundle (30/11/2023)

## v4.0.2

- Added TreeBuilder return type (29/05/2023)

## v4.0.1

- Added missing return type (06/04/2023)

## v4.0

- Changed compatibility to PHP 8 (25/07/2022)

Upgrading from v3.x? **Check UPGRADE.md**

## v3.0.4

- Suppressed use of container (24/07/2022)

## v3.0.3

- Added return type for Voter (24/07/2022)

## v3.0.2

- Changed composer versions constraints (24/07/2022)

## v3.0.1

- Corrected Command return for SF 4 (14/10/2021)

## v3.0

- Changed `localizeddate` to `format_datetime` (11/10/2021)

Upgrading from v2.x? **Check UPGRADE.md**

## v2.6.3

- Added return for console Command (08/10/2021)

## v2.6.2

- Corrected `kernel.project_dir` calls (20/09/2021)

## v2.6.1

- Replaced `kernel.root_dir` by `kernel.project_dir` (03/09/2021)

## v2.6

- Changed `Symfony\Component\Translation\TranslatorInterface` to `Symfony\Contracts\Translation\TranslatorInterface` (03/09/2021)

## v2.5

- Removed versions constraints in composer (03/09/2021)

## v2.4.1

- Cosmetic changes due to Codacy review (04/03/2020)

## v2.4

- Removed use of symplify/easy-coding-standard as abandonned (19/02/2020)

## v2.3.1

- Removed composer.lock from Git (19/02/2020)

## v2.3

- Made use of apply spaceless (05/08/2019)

## v2.2.2.1

- Added alignment value for toolbar (05/08/2019)

## v2.2.2

- Corrected path for creation of pdf (15/07/2019)
- Made use of KnpPaginatorBundle v4 (15/07/2019)

## v2.2.1

- Removed use of ContainerInterface as not used anymore (08/07/2019)

## v2.2

- Removed Route `remove_trailing_slash` as it may lead to multiple redirections (04/07/2019)

## v2.1.5

- Added new naming for TinyMce (29/06/2019)

## v2.1.4.1

- Changed Github's author reference url (08/04/2019)

## v2.1.4

- Made use of Twig namespace (07/03/2019)

## v2.1.3

- Removed deprecations for @Method (13/02/2019)
- Implemented AstractController instead of Controller (13/02/2019)
- Modified Dependencyinjection rootNode to be not empty (13/02/2019)

## v2.1.2

- Corrected folder for sitemap for Symfony 4 (31/01/2019)

## v2.1.1

- Updated composer.json (15/01/2019)

## v2.1

- Corrected compatibility with Symfony 4 (13/01/2019)
- Made use of `templates` naming in README.md instead of `Resources/views` (13/01/2019)

## v2.0.4

- Modified required versions in `composer.json` (25/12/2018)

## v2.0.3

- Added missing use (25/12/2018)

## v2.0.2

- Corrected `UPGRADE.md` for `php bin/console config:create` (03/12/2018)
- Added rector to composer dev part (23/12/2018)
- Modified required versions in composer (23/12/2018)

## v2.0.1

- Fixed Twig extension `PageEditFolderContent` (06/09/2018)

## v2.0

- Updated composer.json (01/09/2018)
- Created branch 1.x (02/09/2018)
- Made use of c975L/ConfigBundle (03/09/2018)
- Made use of c975L/ServicesBundle (03/09/2018)
- Added `bundle.yaml` (03/09/2018)
- Updated `PageEditVoter` (03/09/2018)
- Updated `README.md` (03/09/2018)
- Added `UPGRADE.md` (03/09/2018)
- Added `PageEditFormFactory` + Interface (03/09/2018)
- Removed SubmitButton from `PageEditType` (03/09/2018)
- Updated `PageEdit` Entity (03/09/2018)
- Removed FQCN (03/09/2018)
- Removed declaration of parameters in Configuration class as they are end-user parameters and defined in c975L/ConfigBundle (05/09/2018)
- Split `PageService` in multiple files + Interface (05/09/2018)
- Updated `SitemapCreateCommand` (05/09/2018)
- Added link to BuyMeCoffee (05/09/2018)
- Added link to apidoc (05/09/2018)
- Made Controllers skinny (05/09/2018)
- Made use of text buttons in the dashboard (05/09/2018)
- Added php documentation (05/09/2018)
- Added `pageedit_config` Route (05/09/2018)

Upgrading from v1.x? **Check UPGRADE.md**

## v1.x

## v1.30.1

- Fixed Voter constants (31/08/2018)

## v1.30

- Made use of Voters for access rights (02/08/2018)
- Made Controllers more SOLID compliant (02/08/2018)
- Removed `Action` in controller method name as not requested anymore (02/08/2018)
- Use of Yoda notation (02/08/2018)
- Split Controller files (02/08/2018)
- Corrected dashboard view (02/08/2018)
- Replaced Route `pageedit_new` with `pageedit_create` to assure consistency in naming as 'new' is a php reserved word (02/08/2018)
- Added check for existing semantic url (02/08/2018)
- Displayed html code for Archived and Deleted pages as rendering may give errors (Route doesn't exist, etc.) (02/08/2018)

## v1.29.2.2

- Changed version required for symfony/templating (26/06/2018)

## v1.29.2.1

- Added 'home' as non-expected value for Route `pageedit_display` (26/06/2018)

## v1.29.2

- Modified `getSkeleton()` to use real path in place of template locator (22/05/2018)
- Added path for SF4 templates (22/05/2018)
- Corrected toolbar display (22/05/2018)

## v1.29.1

- Removed required in composer.json (22/05/2018)

## v1.29

- Modified toolbars calls due to modification of c975LToolbarBundle (13/05/2018)
- Removed calls to FOSUser (13/05/2018)
- Added templates for forms due to new calls for toolbars (13/05/2018)
- Added button to get pdf version (13/05/2018)
- Removed datetime set for archived file, and replaced by filemtime of the file (14/05/2018)

## v1.28.8

- Added regex class `[A-Z]` in `PageEditController.php` (16/04/2018)
- Removed unused parenthesis (16/04/2018)
- Removed 'home' as exception for `page` value in Route `pageedit_display` (16/04/2018)
- Suppressed content display when deleting page as there is the toolbar option to view it (16/04/2018)
- Replaced submit button by `SubmitType` (16/04/2018)

## v1.28.7

- Added 'home' as exception for `page` value in Route `pageedit_display` (14/04/2018)

## v1.28.6

- Removed `action` property on Entity `Event` and passed data with array `pageEditConfig` to the form (19/03/2018)

## v1.28.5

- Changed display order in Controller (12/03/2018)
- Used "GoneHttpException" for deleted page (12/03/2018)

## v1.28.4

- Corrected `tinymceInit.html.twig > convert_urls` which where causing it to add the url to src and href when modifying (09/03/2018)

## v1.28.3

- Added pagination above list of pages in dashboard (08/03/2018)
- Added clean content when writing file for img src (08/03/2018)

## v1.28.2

- Corrected `REAMDE.md` (08/03/2018)
- Allowed pdf creation for templates located in `protected` folder (08/03/2018)
- Replaced calls `$this->get('templating')->exists()` by `is_file()` (08/03/2018)
- Added pdf re-generation after an amount of time (24H) (08/03/2018)
- Corrected sitemap creation default frequency and priority (08/03/2018)

## v1.28.1

- Added `h4cc/wkhtmltopdf-amd64` to `composer.json` (07/03/2018)

## v1.28

- Added Route to generate PDF from page (06/03/2018)
- Changed `skeleton.html.twig` to avoid having to override it (06/03/2018)
- Renamed block `pageEdit` to `pageedit_content` in `skeleton.html.twig` to be coherent with other c975L bundles (06/03/2018)
- Added method `PageEditService > getContent()` (06/03/2018)
- Added method `PageEditService > getData()` to simplify code (06/03/2018)
- Added method `PageEditService > getPagesFolder()` to simplify code (06/03/2018)
- Added method `PageEditService > getOriginalContent()` to replace multiples calls (06/03/2018)
- Added `createNotFoundException()` for some method in `PageEditController` (06/03/2018)

## v1.27.2

- Modified value for linksAction array (05/03/2018)
- Added cleaning for url added by Tinymce in `PageEditService > writeFile()` (05/03/2018)

## v1.27.1

- Added "_locale requirement" part for multilingual prefix in `routing.yml` in `README.md` (04/03/2018)
- Added Route `remove_trailing_slash` (04/03/2018)

## v1.27

- Added the possibility to edit root page via PageEdit (03/03/2018)
- Added Route `pageedit_home` (03/03/2018)
- Added Route to redirect when calling "pages" without specifying {page} (03/03/2018)
- Added removing of trailing slash when calling `pageedit_display` (03/03/2018)
- Added a / in the semantic url column in dashboard view (03/03/2018)
- Added possibility to provide a description for page to be displayed in `og:description` and `meta description` (03/03/2018)
- Re-ordered Entity getters and setters (03/03/2018)
- Added `sitemap-index.xml/twig` to be used by sites if needed (03/03/2018)
- Corrected title using Twig trans() to display (03/03/2018)
- Changed default priority and changeFrequency (03/03/2018)

## v1.26.2.1

- Removed the "|raw" for `toolbar_button` call as safe html is now sent (01/03/2018)

## v1.26.2

- Corrected Tinymce call with apiKey (01/03/2018)

## v1.26.1

- Corrected call for bootstrap in `tinymceInit.html.twig` (28/02/2018)

## v1.26

- Added c957L/IncludeLibrary to include libraries in `layout.html.twig` (27/02/2018)
- Changed `tinymceInit.html.twig` for include of Tinymce via c975L/IncludeLibrary (27/02/2018)

## v1.25

- Added 'Command' part auto-wire to `services.yml` (20/02/2018)
- Added 'Controller' part auto-wire to `services.yml` (20/02/2018)
- Abandoned Glyphicon and replaced by fontawesome (22/02/2018)
- Corrected display button in toolbar (22/02/2018)

## v1.24.1

- Corrected Route `pageedit_display` due to renaming of `edit` to `modify` (19/02/2018)

## v1.24

- Put `New` button before `edit` one in toolbar (17/02/2018)
- Renamed templates for forms (17/02/2018)
- Added `cancel` action to toolbar and removed from bottom of forms (17/02/2018)
- Changed wording for submit button for forms (17/02/2018)
- Added PageEdit title in help page (18/02/2018)
- Corrected `help-es.html.twig` for delete a page (19/02/2018)
- Suppressed translations taken from `c975L\ToolbarBundle` (19/02/2018)
- Renamed Route `edit` to `modify` (19/02/2018)

## v1.23

- Moved link to display page under the name of the page and suppression of the link "display" in the dashboard page (05/02/2018)
- Added posibilities to sort pages under dashboard (05/02/2018)
- Added "No pages" information in dashboard (05/02/2018)

## v1.22.2

- Renamed Twig function (05/02/2018)
- Updated ToolbarBundle product -> dashboard (05/02/2018)

## v1.22.1

- Corrections in `README.md` (04/02/2018)

## v1.22

- Change about composer download in `README.md` (04/02/2018)
- Add support in `composer.json`+ use of ^ for versions request (04/02/2018)
- Replace toolbar by use of c975L/ToolbarBundle (04/02/2018)

## v1.21

- Added the possibility to list a folder and return an arrayr of file + title (24/01/2018)

## v1.20

- Changed default value for sitemap priority in skeleton (24/01/2018)
- Corrected upload picture for sub-pages (24/01/2018)
- Added dates foreach bullet in `ChangeLog.md` (24/01/2018)

## v1.19

- Add of possibility to duplicate a page (24/01/2018)

## v1.18

- Add of advice about changing semantic url (16/01/2018)
- Add of possibility to have hierarchical url, i.e. `domain.com/folder/sub-folder/file` (18/01/2018)
- Add of possibility to list and display archived, deleted and redirected files (18/01/2018)
- Suppress of "set redirection part" in deleteFile Service as it's not used (22/01/2018)

## v1.17.1

- Separation of information about parameters.yml in `README.md` (16/08/2017)
- Direct call of Tinymce API key instead of repeating it in `config.yml`
- Changes in `README.md` (16/08/2017)

## v1.17

- Add of missing replacement of \" (20/07/2017)
- Add of service getTitle() and remove of duplicates uses (10/08/2017)
- Add of service getPriority() (10/08/2017)
- Add of service getChangeFrequency() (10/08/2017)
- Add of translation, where relevant, of title using Twig calls to translator, such as `'title_to_be_translated'|trans({}, 'domain')` (10/08/2017)
- Change of translation for priority (10/08/2017)

## v1.16

- Add of escaping for " in titles (20/07/2017)
- Set functions in Controller as service (20/07/2017)

## v1.15.2

- Run PHP CS-Fixer (18/07/2017)
- Remove of .travis.yml as tests have to be defined before (18/07/2017)

## v1.15.1

- Add "Best practice" for tinymceApiKey (08/07/2017)

## v1.15

- Update README.md (06/07/2017)
- Move of translated help pages to sub-folder `langugages` (07/07/2017)
- Add of class responsive for tables in `tinymceInit.html.twig` (07/07/2017)
- Make `tinymceInit.html.twig` re-usable by setting config keys `tinymceApiKey` and `tinymceLanguage` (07/07/2017)
- Redirection to dashboard in case of delete a page, in place of redirecting to the deleted page (07/07/2017)
- Add of signout button on toolbar + config signoutRoute (07/07/2017)
- Add of main dashboard button on toolbar + config dashboardRoute (07/07/2017)
- Moves sitemap information below the main textarea (07/07/2017)

## v1.14.2

- Update README.md (04/07/2017)
- Move `title`value in Twig templates in place of Controller, more simple (04/07/2017)

## v1.14.1

- Forgotten use for FileSystem in SitemapCreateCommand (03/07/2017)

## v1.14

- Remove of "<div class="container">" in templates as it extends `layout.html.twig` and this kind of data has to be set site by site (03/07/2017)
- Add of class "img-responsive" for images and remove of dimensions (03/07/2017)
- Add of a "protected" folder to store the templates for which the content MUST NOT be modified via Tinymce, but still displayed with PageEdit (03/07/2017)
- Move the call of `tinymce.js` to `tinymceInit.html.twig` instead of `layout.html.twig` to avoid calling it for pages that don't need it. (03/07/2017)
- Group toolbars in one file (03/07/2017)
- Add of semantic url value in dashboard (03/07/2017)
- Add of link to dashboard on PageEdit label in toolbar and remove of dashboard button (03/07/2017)
- Update return location for uploaded images to absolute url + set absolute in tinymceInit (03/07/2017)
- Group in one function the creation of folders needed by the Bundle (03/07/2017)
- Remove of "required" on textarea.tinymce as it won't submit for a new page, a refresh has to be done - [#2584](https://github.com/tinymce/tinymce/issues/2584) (03/07/2017)

## v1.13.1

- Modification of SitemapCreateCommand to allow multilingual (01/07/2017)

## v1.13

- Add a Console Command to create sitemap of managed files (01/07/2017)

## v1.12

- Change wording for validate button (30/06/2017)
- Move `tinymceInit.html.twig` to `views` folder in order to simplify it's overridding as Tinymce can be initialized only once (30/06/2017)
- Add options to Tinymce init (30/06/2017)
- Add list of pages available for easy linking from Link tool (30/06/2017)
- Add information on help pages (30/06/2017)
- Add link to dedicated web page (30/06/2017)

## v1.11.2

- Add informations about registering third party bundles in README.md (29/06/2017)

## v1.11.1

- Add of requirement of "cocur/slugify" in composer.json forgotten (29/06/2017)

## v1.11

- Correction of 'images_upload_url' twice in layout.html.twig (29/06/2017)
- Move in a separate file of the initialization of Tinymce (29/06/2017)
- Add of pagination via KnpPaginator (dashboard) (29/06/2017)
- Remove of slugify function and replace by cocur/slugify (29/06/2017)

## v.1.10

- Correction for "Title not found" translation in dashboard (20/06/2017)
- Correction "text.special_formatting" to include {# #} (20/06/2017)
- Add in `README.md` of possibilty to use `prefix: /{_locale}` in `routing.yml` for multilingual website (21/06/2017)
- Correction for title in edit mode when title is Twig code (21/06/2017)
- Add of upload picture feature (21/06/2017)

## v1.9

- Esthetic changes in layout.html.twig (18/06/2017)
- Correction when pages are not including skeleton yet to display on dashboard to facilitate migration (19/06/2017)
- Remove of readonly on semantic url, to give possibility to change title without changing url and file name, and allow only one name in cas of multi languages (19/06/2017)
- Add of possibility to set the title as translation of term using Twig code `{{ 'text.cookies_policy'|trans({}, 'commons') }}` (19/06/2017)

## v1.8

- Make readonly of semantic_url field and replacment by automatic slugging of title (18/06/2017)
- Set up a redirection (302) if title (and then slug and name of file) has changed, to the new file (18/06/2017)
- Add of references to jQuery and Bootstrap in README.md (18/06/2017)
- Add of button in toolbars (edit/Delete) to display the page (18/06/2017)
- Add of translation domain to avoid other texts coming from other bundle with same keyword (18/06/2017)
- Add of id of user who mades the change of the page in the filename of archived file (18/06/2017)

## v1.7.1

- Add of PageEdit toolbars for New/edit/Delete page (17/06/2017)

## v1.7

- Update of README.md (13/06/2017)
- Corrections in translations (13/06/2017)
- Suppression of "if" test for actual route in layout/html.twig (15/06/2017)
- Add of dashboard link in toolbar (17/06/2017)
- Add of help link in toolbar (17/06/2017)
- Add of pageedit_title in skeleton.html.twig (17/06/2017)
- Add of remove of all signs not to be used in semantic url (17/06/2017)

## v1.6.3

- Correction (2) of routes in toolbar (08/06/2017)

## v1.6.2

- Correction of routes in toolbar (08/06/2017)

## v1.6.1

- Suppresion of " in Routes (08/06/2017)

## v1.6

- Renaming of routes `975l_XXX` and `pageedit_XXX` (more accurate) [BC-BREAK] (08/06/2017)

## v1.5

- Renaming of route `975l_display_page` by `pageedit_display_page` (more accurate) [BC-BREAK] (06/06/2017)

## v1.4.1.2

- Add mention lo License in php files (05/06/2017)

## v1.4.1.1

- Suppression of button for download zip as function is not available yet (05/06/2017)

## v1.4.1

- Arrangments of getters and setters (05/06/2017)
- Renaming of layout.html.twig (05/06/2017)

## v1.4

- Move toolbar above content instead of below (04/06/2017)
- Add of a message to prevent deletion of Twig calls between {} (04/06/2017)
- Add 'action' to PageEdit object to differentiate call of form (04/06/2017)
- Add of possibility to delete page (04/06/2017)

## v1.3.1

- Corrections in translations files (04/06/2017)

## v1.3

- Add of a link 'Cancel' on the Edit page (04/06/2017)
- Group in one file for Controllers (04/06/2017)
- Separation of data from pageEdit.html.twig to layout.html.twig to allow other forms (04/06/2017)
- Move to sub-folder `forms`of template `pageEdit.html.twig` (04/06/2017)
- Add of possibility to create a page (04/06/2017)
- Add of title and slug data for page (04/06/2017)
- Separation in functions in Controller for re-usable parts (04/06/2017)

## v1.2

- Group in one file and rename of the template used as skeleton (04/06/2017)
- Changes in README.md (04/06/2017)

## v1.1.2 & v1.1.3

- Suppression of extra comma in composer.json (03/06/2017)

## v1.1.1

- Changes in README.md (03/06/2017)

## v1.1

- Add of code files (31/05/2017)
- Add of explanations in README.md (31/05/2017)

## v1.0

- Creation of bundle (20/05/2017)
