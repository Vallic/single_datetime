CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Views exposed filters
 * Maintainers


INTRODUCTION
------------

The Single Date Time Picker module supports date/time and date fields. You can
use it on Datetime and Datetime Range fields.

Features:

 * Support Datetime and Datetime Range field types
 * Support for Views exposed filters
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

    2. Composer using zodiacmedia/drupal-libraries-installer.
       This PHP packages should be automatically installed with this module.

       Libraries need to be defined as example below
       `"extra": {
           "drupal-libraries": {
               "datetimepicker":
               "https://github.com/xdan/datetimepicker/archive/2.5.20.zip"
           },
        }`

       Ensure composer packages of type drupal-library are
       configured to install to the appropriate path.

       Read more at zodiacmedia/drupal-libraries-installer

    3. Using packagist for JS libraries
       https://asset-packagist.org/

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


VIEWS EXPOSED FILTERS
-------------

This module contains submodule `single_datetime_exposed`.
If you enable it, all exposed filters of types `date` and `search_api_date` will
automatically use date time picker on exposed filters.

It works with all operators, and none configuration is needed.
If you need customized configuration, suggesting that you create your
implementation based on this submodule.

FORM API USAGE
-------------
You can use SingleDateTime in other parts of the Drupal FORM API with minor
adjustments. If you are using non single_datetime elements, as textfield field
type, you need to attach proper attributes on the field, and attach JS library
(see examples below).

Second choice is that you can create custom field of field type
single_datetime directly.

Also exist helper `\Drupal\single_datetime\AttributeHelper` with some default
attributes options and examples. See examples below.



**Custom single_datetime field**
Using AttributeHelper you can create default widget:

```
$form['purchase_date'] = [
 '#title' => 'Purchase date',
 '#type' => 'single_date_time',
 '#date_timezone' => date_default_timezone_get(),
 '#default_value' => NULL,
 '#date_type' =>  'datetime',
 '#required' => TRUE,
] + \Drupal\single_datetime\AttributeHelper::allElementAttributes();
 ```


Directly using your attributes.
```
 $form['purchase_date'] = [
    '#title' => 'Purchase date',
    '#type' => 'single_datetime',
    '#time' => FALSE,
    '#required' => TRUE,
    '#hour_format' => 24,
    '#first_day' => \Drupal::config('system.date')->get('first_day'),
    '#disable_days' => [],
    '#allow_times' => 60,
    '#allowed_hours' => Json::encode(range(0, 23)),
    '#inline' => '0',
    '#mask' => FALSE,
    '#datetimepicker_theme' => 'default',
    '#single_date_time' => 'datetime',
    '#exclude_date' => '',
    '#min_date' => '',
    '#max_date' => '',
    '#year_start' => '1970',
    '#year_end' => date('Y'),
 ];
 ```

**Textfield or any other field type**

Using AttributeHelper you can create default widget:
```// Using datetimepicker module.
 $form['purchase_date'] = [
   '#title' => 'Purchase date',
   '#type' => 'textfield',
   '#time' => FALSE,
   '#required' => TRUE,
   '#attributes' => \Drupal\single_datetime\AttributeHelper::defaultWidget(),
 ];
 $form['#attached']['library'][] = 'single_datetime/datetimepicker';
 $form['#attributes']['autocomplete'] = 'off';
 ```


Directly using your attributes.
```// Using datetimepicker module.
 $form['purchase_date'] = [
   '#title' => 'Purchase date',
   '#type' => 'textfield',
   '#time' => FALSE,
   '#required' => TRUE,
   '#attributes' => [
     'data-first-day' => '0',
     'data-disable-days' => [],
     'data-inline' => '0',
     'data-datetimepicker-theme' => 'default',
     'data-single-date-time' => 'date',
     'data-max-date' => date('Y-m-d'),
     'data-year-start' => '2005',
     'data-year-end' => date('Y'),
   ],
 ];
 $form['#attached']['library'][] = 'single_datetime/datetimepicker';
 $form['#attributes']['autocomplete'] = 'off';
 ```



MAINTAINERS
-----------

 * Valentino Međimorec (valic) - https://www.drupal.org/u/valic

Supporting organization:

 * Vallic - https://www.drupal.org/vallic
