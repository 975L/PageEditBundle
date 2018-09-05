# UPGRADE

v1.x > v2.x
-----------
When upgrading from v1.x to v2.x you should(must) do the following if they apply to your case:

- The parameters entered in `config.yml` are not used anymore as they are managed by c975L/ConfigBundle, so you can delete them.
- As the parameters are not in `config.yml`, we can't access them via `$this[->container]->getParameter()`, so you have to replace `$this->getParameter('c975_l_page_edit.XXX')` by `$configService->getParameter('c975LPageEdit.XXX')`, where `$configService` is the injection of `c975L\ConfigBundle\Service\ConfigServiceInterface`.
- The `tinymceApiKey` is now managed by c975L/ConfigBundle, so you can delete if from `parameters.yml` and `parameters.yml.dist`, but before that, copy/paste it in the config.
- Before the first use of parameters, you **MUST** use the console command `php bin/console config:create-first-use c975l/pageedit-bundle c975LPageEdit` to create the config files with default data. **Using this command later will reset the config to default data**
- The urls used for Routes (create, delete, etc.) have been changed (pages -> pageedit) except for `pageedit_display` in order to allow any slug for pages. This has an impact only for bookmarked pages, not if you use the navigation provided by the bundle.