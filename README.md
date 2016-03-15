# Houston

## Usage
To build the distribution atop the latest Drupal core, run:

    drush make https://raw.githubusercontent.com/poetic/houston/master/makefiles/stubs/build.make.yml?token=AFnkxe_tP5BbmbD0QCBR5a7n2sVCKwj_ks5WzIpCwA%3D%3D [platform_name]

To build for development (includes working copy of the profile):

    drush make https://raw.githubusercontent.com/poetic/houston/master/makefiles/stubs/dev.make.yml?token=AFnkxWa3CTHIvg9aII8j62KYJjxpQkIIks5WzIonwA%3D%3D [platform_name]

To re-build an existing platform installed using one of the above commands, run this from your platform root:

    drush make profiles/houston/makefiles/stubs/local-dev.make.yml

This will rebuild Drupal core and contrib in an existing code-base without
touching the houston profile itself. This is useful when working on houston.

To make sure you can use Composer Manager to download third party libraries, run this from your platform root:

    php profiles/houston/modules/contrib/composer_manager/scripts/init.php

then run this to install all libraries:

    composer drupal-update

If you have trouble with houston/vendor/jcalderonzumba/gastonjs, remove gastonjs then re-run `composer drupal-update`

## Maintenance
The makefiles that define the various requirements for this installation
profile are structured for easy readability and maintenance.

For dev builds, Drush Make will prefer the latest versions of contrib modules,
except for those specifying pinned versions.

For production builds, we use a makefile with all versions locked down. To
update this makefile, run the following command from the profile root:

    drush make --no-build --no-core makefiles/profile.make.yml --lock=houston.make


