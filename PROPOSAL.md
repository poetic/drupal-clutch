#Clutch V2 Proposal

Update Clutch to use Page manager and Drupal Block:

## Page Manager
**Pros**

- Support Layout Plugin.
- Support Drupal Block System.
- Build Single Page.
- Support Context.
- Allow override Node Page.

**Cons**
- Does not work with Quickedit
- Does not support Metatag(this can be solve with hook_form_alter)

## Block
We can use either Custom Block or Block API.
- Custom Block functions the same as Content Type. Everything will be stored in database.
- Block API to build Block Plugin and have it work the same way as Ctools Custom Pane on Drupal 7. There will be no fields store in database.

## Workflow

### Current Workflow

- read Webflow zip file
- generate theme
- create component types/content types
- create default component contents
- create page and associates components
- handle render components using find and replace logic

### Proposed Workflow
	
- have a separate module to implement layout for Page Manager
- read Webflow zip file
- generate theme
- generate block types/content types using Yaml file
- generate block contents
- create page using page manager and associate blocks
- generate block template override to handle render

## Difference between 2 workflows

- Have separate modules to handle the creation and rendering. Therefore we can disable the Clutch CLI on production using [Environment](https://www.drupal.org/node/2552677) module.
- Use Yaml file to store the structure of the block type.
- Let theme handle how blocks suppose to be rendered.
- Reduce complexity and maintenance effort for Clutch.
- Stick with Drupal community and Drupal Core.
- Custom Block supports Revision so this would work with deploy.
- Page and Page Variant are config entities so this would be part of CMI.
