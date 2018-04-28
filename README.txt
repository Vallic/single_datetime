CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

The Single Date Time Picker module supports date/time and date fields. You can
use it on Datetime and Datetime Range fields.

Features:

 * Support Datetime and Datetime Range field types
 * 12/24 hour format display
 * Option to choose granularity for minutes
 * Option to disable specific days global (etc. Saturday)
 * Option to disable specific dates, useful for holidays, working days, etc.

 * For a full description of the module visit:
   https://www.drupal.org/project/single_datetime

 * To submit bug reports and feature suggestions, or to track changes visit:
   https://www.drupal.org/project/issues/single_datetime


REQUIREMENTS
------------

This module requires the following outside of Drupal core:
 
 * Plugin Date and Time Picker library - https://github.com/xdan/datetimepicker


INSTALLATION
------------

 * Install the Single Date Time Picker module as you would normally install a
   contributed Drupal module. Visit https://www.drupal.org/node/1897420 for
   further information.
 * Install external library from xdan trough composer or manual. Library
   should be available at yourdrupalsite.com/libraries/datetimepicker

    1. Manual
       Download https://github.com/xdan/datetimepicker/archive/2.5.20.zip,
       and extract inside drupal root folder: libraries/datetimepicker

    2. Composer using drupal-libraries-installer

       In your Drupal project add drupal-libraries-folder
       `composer require balbuf/drupal-libraries-installer`

       Libraries need to be defined as example below
       `"extra": {
           "drupal-libraries": {
               "datetimepicker":
               "https://github.com/xdan/datetimepicker/archive/2.5.20.zip"
           },
        }`

       Ensure composer packages of type drupal-library are
       configured to install to the appropriate path.

       Read more at https://github.com/balbuf/drupal-libraries-installer



CONFIGURATION
-------------

    1. Navigate to Administration > Extend and enable the module.
    2. Navigate to Administration > Structure > Content types > [Content type 
       with date field] > Manage form display.
    3. Select the date field to edit and choose "Single Date Time Picker" from
       Widget drop down.
    4. Edit the Field Plugin Setting by selecting the gear icon.
    5. Select the hours format: 12 hours or 24 hours.
    6. Select granularity for minutes in calendar: 5, 10, 15, 30, or 60 minutes.
    7. Select days which are to be disabled in calendar.
    8. To disable specific dates from calendar enter days in following format
       d.m.Y etc. 31.12.2018. Each date in new line. This is used for specific
       dates.
    9. Update and Save.


MAINTAINERS
-----------

 * Valentino Međimorec (valic) - https://www.drupal.org/u/valic

Supporting organization:

 * Vallic - https://www.drupal.org/vallic
