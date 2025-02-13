CONTENTS OF THIS FILE
---------------------

* Introduction
* Modules and solutions

INTRODUCTION
------------

This module provides solutions for using media library with [group](https://www.drupal.org/project/group) and [groupmedia](https://www.drupal.org/project/groupmedia) modules.


Modules AND SOLUTIONS
----------------------

* Group media library:
  - Provides a media library state for group (GroupMediaLibraryState). That is being used by the other submodules.
  - GroupMediaLibraryState contains multiple parameters. For full description of the available parameters, see the GroupMediaLibraryState parameter bag.
  - Provides GroupFinder for finding group when in context of media library opener.
  - Dependencies: Media Library, Group, and Group Finder modules.
* Group media library - Group media:
  - Add support for using group media library with groupmedia module.
  - The module solves this [issue](https://www.drupal.org/project/groupmedia/issues/3238469)
  - Dependencies: Group Media Library, and Group Media modules.
* Group media library - Media Tracker:
  - Adds new media created in media library to corresponding group instantly.
  - Use this module if you want to instantly attach the media created in media library to corresponding group that is being found by the group finder.
  - Dependencies: Group Media Library, and Group Media modules.
* Group media library - Widget:
  - Allows to use media library field widget in group context.
  - The module makes media library widget aware of the group from which it has been opened. This is done by adding the GroupMediaLibraryState to the core MediaLibraryState by altering the media library widget.
  - The module solves this [issue](https://www.drupal.org/project/group/issues/3071489)
  - Dependencies: Group Media Library module.
