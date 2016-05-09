#Clutch

![clutch_logo](https://github.com/poetic/clutch/blob/features/refactor-find-and-replace/assets/clutch.png)

A super module that will help speeding up website conversion from Webflow design. This repo contains Houston, Clutch, Component, Custom Page.

## Houston
An opinionated install profile focused on developer happiness.

## Component
Custom entity with bundles support. Use this module to create singleton entity for each bundle type.

## Custom Page (Page)
Custom Page allows you to create pages as a replacement for Page Manger.
	
	- Pathauto Support
	- Token Support
	- Metatag Support

## Clutch
This module will handle the creation of the Drupal site based on the Webflow design.

## Data Attribute Instruction
This workflow rely heavily on data attributes from templates. Developers should help Designers adding these attributes directly into Webflow.

### How to add data attribute

For example, this template is coming from Webflow:

	<div class="w-section sectionmission">
    <div class="w-container">
      <div class="missiondiv">
        <h3 class="h3 white">OUR MISSION</h3>
        <h2 class="h2 white italics">To provide emergency shelter, counseling and life changing services to at risk and homeless youth.</h2>
      </div>
    </div>
	</div>

After adding data attributes, it should become:

	<div class="w-section sectionmission" data-component="section_mission">
    <div class="w-container">
      <div class="missiondiv">
        <h3 class="h3 white" data-field="title" data-type="string" data-form-type="string_textfield" data-format-type="string">OUR&nbsp;MISSION</h3>
        <h2 class="h2 white italics"  data-field="description" data-type="string" data-form-type="string_textfield" data-format-type="string">To provide emergency shelter, counseling and life changing services to at risk and homeless youth.</h2>
      </div>
    </div>
	</div>

#### data-component
**_data-component_** defines component type. Clutch CLI will read html files from Webflow zip and break into small components inside of theme.

#### data-field
**_data-field_** defines field name. Each field name will be prefixed *data-component* to prevent conflict. *Make sure the field name does not exceed 32 characters(included data-component)*

#### data-type
**_data-type_** defines field type

#### data-form-type
**_data-form-type_** defines the edit format of the field.

#### data-format-type
**_data-form-type_** defines the render format of the field.

Besides these main data-attributes, we also have:
 - **_data-node_** to define content type.
 - **_data-paragraph_** to define paragraph type.
 - **_data-paragraph-field_** to define paragraph field(prevent conflict with *data-field*).
 - **_data-form_** to define form type.
 - **_data-menu_** to define menu.

These are the most common field Types/Form Display/Render Display. You can add more if need.

| Field Type                                          | Form Display                | Display                      |
| ----------------------------------------------------|-----------------------------|------------------------------|
|	text_with_summary (Text - formatted, long, with summary)	|  text_textarea_with_summary |  text_default/text_trimmed          |
|	boolean	                                            |  boolean_checkbox					  |  boolean                     |
|	datetime (Date) 	                                  |  datetime_default					  |  datetime_default            |
|	decimal (Number - decimal)						              |  number										  |  number_decimal	             |
|	email																	              |  email_default							|  basic_string		             |
|	integer (Number - integer)						              |  number										  |  number_integer	             |
|	link																                |  link_default							  |  link                        |
|	list_integer (List - integer)					              |  options_select						  |  list_default                 |
|	list_string (List - text)							              |  options_select						  |  list_default                 |
|	string_long (Text - plain, long)			              |  string_textarea					  |  basic_string                |
|	string (Text - plain)									              |  string_textfield					  |  string                      |
|	image     														              |  image_image					      |  image/responsive_image/background_image      |
|	entity_reference     														    |  entity_reference_autocomplete					      |  entity_reference_entity_id      |
|	entity_reference_revisions     														    |  entity_reference_paragraphs					      |  entity_reference_revisions_entity_view      |


## Usage
Recommend to use with [Nebula](https://github.com/poetic/nebula) and [Composer template for Drupal Projects](https://github.com/poetic/drupal-project) to install Drupal 8 site.

Assume you already installed Nebula and had a site called drupal.local

Download zip file from Webflow and put it inside drupal/web. At this point, zip file should contain html files with [data attributes](#data-attribute-instruction).

Go to nebula root level and follow these steps

- run `vagrant ssh`
- run `cd /var/www/drupal/web`
- run `drupal list` to see all the available commands. If this is your first time using drupal console, you need to run `drupal init`. `clutch:create` and `clutch:sync` should be available.
- run `drupal clutch:create` to start conversion process. This command will ask you the zip file name and the theme you want to give for your new site. Again, Clutch will unzip the file, create a new theme with all possible components from those data attributes.
- run `drupal clutch:sync` to start syncing process. This command will enable the theme, go through components folder to create component types and content types(if provided). It also create component contents using the default value from the template. Once this is finished. It will create pages using `components.yml` and associate components with pages.