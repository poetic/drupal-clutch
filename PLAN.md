- Profile need to have a base theme. Base theme has all logic to render value for twig variables. Base theme also contains default page.html.twig template with 3 regions: header, content and footer.
- Run Cluch CLI to generate a report. Check with designers to see if anything missing(pages, blocks, fields associate to block, blocks associate to pages, views, form ...). If need, work with designers to add data attributes to webflow. Redo this step until report is good to run through site.

#Implementation:

##Theme:
- Remove zip file, read webflow sitemap.xml instead. Use Finder(symfony) or curl to read all url from site map.
- generate subthem
- read and download all webflow css, js. read and download custom js. read and write custom css(in the head or footer section) into custom css file.

##Clutch module:
- This module will build Drupal structure(blocks, content types, images styles...) and default contents. 
- Disable this module on production.

##Layout:
- Pages:
	- Read each page and look for class/data attribute for page layout(content region only). Some examples are `one-column-page`, `two-column-page`.
	- Generate page layout using layout plugin(yml file). Apply this for pages(pages manager).
- Content Types:
	- Look for data-node attribute and data-view-mode to generate template for content type
	- Clutch will generate node--node-type--view-mode. Ex: node--article--full.html.twig and node--article--teaser.html.twig
- Views:
	- Look for data-view attribute.
	- Copy views-view.html.twig, views-view-unformatted.html.twig, views-view-grouping.html.twig(if need), views-exposed-form.html.twig(if need) into subtheme. It's developers job to go back and edit these templates to reflect the changes.
- Form:
	- Look for data-form attribute to generate contact-message template
- Blocks:
	- Read data-block attribute to generate block template. This functionality is implemented

**Notes:** When generate templates, clutch will also do a find and replace static value with `{{ twig variables }}`. Theme preprocess will handle the return value of these variables.

##Menu:
- Cluch will crawl the nav region in index.html to generate menu structure(yml file). Clutch will also generate template for menu base on the data attribute/class/id. Example: main--menu.html.twig, footer--menu.html.twig

##Views:
- Clutch will generate a list of views/views blocks based on the data-view.

##Breakpoints:
- Clutch will use default breakpoints from webflow to generate breakpoints.yml (https://www.drupal.org/documentation/modules/breakpoint)

##Views mode:
- Clutch will use data-view-mode to generate a view-mode.yml file. This data attribute will only appear in content types.
