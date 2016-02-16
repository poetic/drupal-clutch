Clutch
========

A super module that will help speeding up website conversion from Webflow design. Clutch allows you to create pages and attach multiple components(bands) on pages.

## Component
Component is an entity with bundles support. Use this module to create singleton entity for each bundle type.

Need to have:
  - Token support

Go here to view:
  - Component Type List: `admin/config/search/path/patterns/custom_page`
  - Component List `admin/structure/component`

## Custom Page (Page_)
Custom Page allows you to create pages as a replacement for Page Manger. Even though pathauto pattern is already implemented, you need to resave custom page pattern at `/admin/config/search/path/patterns/custom_page` to activate it.
Will research on how to automate this.

Already had:
  - Pathauto support
  - Metatag support
  - Token support

Need to have:
  - Custom Entity Referenced Field that allows to reference both components and views.

Go here to view:
  - Page Settings: `admin/structure/custom_page/settings`
  - Page List: `admin/structure/custom_page`

## Clutch API Form
A configuration form where you can create/update/delete components faster `admin/config/clutch/clutch-api`.

## Usage
Clutch module should be enabled together with other contrib modules once you install the site. A front end theme needs to be enabled before using this module.
Your theme need to have a directory called components which contains sub directories named as component types. These sub directories should contain a twig template file coming from webflow(The generator should handle this).
Once all steps are done, you should see a list of bundles on the Clutch API form.
