<?php

namespace Drupal\single_datetime\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\datetime\Plugin\Field\FieldWidget\DateTimeWidgetBase;

/**
 * Base class for SingleDateTime widget types.
 */
abstract class SingleDateTimeBase extends DateTimeWidgetBase {

  /**
   * The date format storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dateStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityStorageInterface $date_storage) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->dateStorage = $date_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')->getStorage('date_format')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'hour_format' => '24h',
      'allow_seconds' => FALSE,
      'allow_times' => '15',
      'allowed_hours' => '',
      'disable_days' => [],
      'exclude_date' => '',
      'inline' => FALSE,
      'mask' => FALSE,
      'datetimepicker_theme' => 'default',
      'start_date' => '',
      'min_date' => '',
      'max_date' => '',
      'year_start' => '',
      'year_end' => '',
      'allow_blank' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $elements = [];
    $elements['hour_format'] = [
      '#type' => 'select',
      '#title' => $this->t('Hours Format'),
      '#description' => $this->t('Select the hours format'),
      '#options' => [
        '12h' => $this->t('12 Hours'),
        '24h' => $this->t('24 Hours'),
      ],
      '#default_value' => $this->getSetting('hour_format'),
      '#required' => TRUE,
    ];
    $elements['allow_seconds'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Seconds default 00'),
      '#description' => $this->t('Select if you want to set the seconds default 00.'),
      '#default_value' => $this->getSetting('allow_seconds'),
      '#required' => FALSE,
    ];
    $elements['allow_times'] = [
      '#type' => 'select',
      '#title' => $this->t('Minutes granularity'),
      '#description' => $this->t('Select granularity for minutes in calendar'),
      '#options' => [
        '5' => $this->t('5 minutes'),
        '10' => $this->t('10 minutes'),
        '15' => $this->t('15 minutes'),
        '30' => $this->t('30 minutes'),
        '60' => $this->t('60 minutes'),
      ],
      '#default_value' => $this->getSetting('allow_times'),
      '#required' => TRUE,
    ];
    $elements['allowed_hours'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed hours'),
      '#description' => $this->t('Specify allowed hours on time picker. Leave empty for no restrictions. Enter hours in following format of number, etc: 8,9,10,11,12,13,14,15,16,17. Separate by comma. This is used in combination with minutes granularity.'),
      '#default_value' => $this->getSetting('allowed_hours'),
      '#required' => FALSE,
    ];
    $elements['disable_days'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Disable specific days in week'),
      '#description' => $this->t('Select days which are disabled in calendar, etc. weekends or just Friday'),
      '#options' => [
        '1' => $this->t('Monday'),
        '2' => $this->t('Tuesday'),
        '3' => $this->t('Wednesday'),
        '4' => $this->t('Thursday'),
        '5' => $this->t('Friday'),
        '6' => $this->t('Saturday'),
        '7' => $this->t('Sunday'),
      ],
      '#default_value' => $this->getSetting('disable_days'),
      '#required' => FALSE,
    ];
    $elements['exclude_date'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Disable specific dates from calendar'),
      '#description' => $this->t('Enter days in following format d.m.Y etc. 31.12.2018. Each date in new line. This is used for specific dates, if you want to disable all weekends use settings above, not this field.'),
      '#default_value' => $this->getSetting('exclude_date'),
      '#required' => FALSE,
    ];
    $elements['inline'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Render inline'),
      '#description' => $this->t('Select if you want to render the widget inline.'),
      '#default_value' => $this->getSetting('inline'),
      '#required' => FALSE,
    ];
    $elements['mask'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use mask'),
      '#description' => $this->t('Use mask for input. Example __.__.____'),
      '#default_value' => $this->getSetting('mask'),
      '#required' => FALSE,
    ];
    $elements['datetimepicker_theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Theme'),
      '#description' => $this->t('Setting a color scheme. Now only supported default and dark theme'),
      '#options' => [
        'default' => $this->t('Default'),
        'dark' => $this->t('Dark'),
      ],
      '#default_value' => $this->getSetting('datetimepicker_theme'),
      '#required' => FALSE,
    ];
    $elements['start_date'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Start date'),
      '#description' => $this->t('Initial date to display when the input has no value and the picker is opened',
        [':external' => 'https://xdsoft.net/jqplugins/datetimepicker/']),
      '#default_value' => $this->getSetting('start_date'),
      '#required' => FALSE,
    ];
    $elements['min_date'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Set a limit to the minimum date/time allowed to pick.'),
      '#description' => $this->t('Examples: \'0\' for now, \'+1970/01/02\' for tomorrow, \'12:00\' for time, \'13:45:34\',formatTime:\'H:i:s\'. <a href=":external">More info</a>',
        [':external' => 'https://xdsoft.net/jqplugins/datetimepicker/']),
      '#default_value' => $this->getSetting('min_date'),
      '#required' => FALSE,
    ];
    $elements['max_date'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Set a limit to the maximum date/time allowed to pick.'),
      '#description' => $this->t('Examples: \'0\' for now, \'+1970/01/02\' for tomorrow, \'12:00\' for time, \'13:45:34\',formatTime:\'H:i:s\'. <a href=":external">More info</a>.',
        [':external' => 'https://xdsoft.net/jqplugins/datetimepicker/']),
      '#default_value' => $this->getSetting('max_date'),
      '#required' => FALSE,
    ];
    $elements['year_start'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Start year'),
      '#description' => $this->t('Start value for fast Year selector - used only for selector'),
      '#default_value' => $this->getSetting('year_start'),
      '#required' => FALSE,
    ];
    $elements['year_end'] = [
      '#type' => 'textfield',
      '#title' => $this->t('End year'),
      '#description' => $this->t('End value for fast Year selector - used only for selector'),
      '#default_value' => $this->getSetting('year_end'),
      '#required' => FALSE,
    ];
    $elements['allow_blank'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow blank'),
      '#description' => $this->t('Allow deleting the value to unset a date.'),
      '#default_value' => $this->getSetting('allow_blank'),
      '#required' => FALSE,
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('Hours Format: @hour_format', ['@hour_format' => $this->getSetting('hour_format')]);
    $summary[] = $this->t('Set Seconds default 00: @allow_seconds', ['@allow_seconds' => !empty($this->getSetting('allow_seconds')) ? $this->t('Yes') : $this->t('No')]);
    $summary[] = $this->t('Minutes Granularity: @allow_times', ['@allow_times' => $this->getSetting('allow_times')]);
    $summary[] = $this->t('Allowed hours: @allowed_hours', ['@allowed_hours' => !empty($this->getSetting('allowed_hours')) ? $this->getSetting('allowed_hours') : $this->t('All hours are allowed')]);

    $options = [
      '1' => $this->t('Monday'),
      '2' => $this->t('Tuesday'),
      '3' => $this->t('Wednesday'),
      '4' => $this->t('Thursday'),
      '5' => $this->t('Friday'),
      '6' => $this->t('Saturday'),
      '7' => $this->t('Sunday'),
    ];

    $disabled_days = [];
    foreach ($this->getSetting('disable_days') as $value) {
      if (!empty($value)) {
        $disabled_days[] = $options[$value];
      }
    }

    $disabled_days = implode(',', $disabled_days);
    $start_date = $this->getSetting('start_date');
    $min_date = $this->getSetting('min_date');
    $max_date = $this->getSetting('max_date');
    $year_start = $this->getSetting('year_start');
    $year_end = $this->getSetting('year_end');

    $summary[] = $this->t('Disabled days: @disabled_days', ['@disabled_days' => !empty($disabled_days) ? $disabled_days : $this->t('None')]);
    $summary[] = $this->t('Disabled dates: @disabled_dates', ['@disabled_dates' => !empty($this->getSetting('exclude_date')) ? $this->getSetting('exclude_date') : $this->t('None')]);
    $summary[] = $this->t('Display inline widget: @render_widget', ['@render_widget' => !empty($this->getSetting('inline')) ? $this->t('Yes') : $this->t('No')]);
    $summary[] = $this->t('Use mask: @mask', ['@mask' => !empty($this->getSetting('mask')) ? $this->t('Yes') : $this->t('No')]);
    $summary[] = $this->t('Theme: @theme', ['@theme' => ucfirst($this->getSetting('datetimepicker_theme'))]);
    $summary[] = $this->t('Start date: @start_date', ['@start_date' => !empty($start_date) ? $start_date : $this->t('Today')]);
    $summary[] = $this->t('Minimum date/time: @min_date', ['@min_date' => !empty($min_date) ? $min_date : $this->t('None')]);
    $summary[] = $this->t('Maximum date/time: @max_date', ['@max_date' => !empty($max_date) ? $max_date : $this->t('None')]);
    $summary[] = $this->t('Start year: @year_start', ['@year_start' => !empty($year_start) ? $year_start : $this->t('None')]);
    $summary[] = $this->t('End year: @year_end', ['@year_end' => !empty($year_end) ? $year_end : $this->t('None')]);
    $summary[] = $this->t('Allow blank: @allow_blank', ['@allow_blank' => !empty($this->getSetting('allow_blank')) ? $this->t('Yes') : $this->t('No')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // The widget form element type has transformed the value to a
    // DrupalDateTime object at this point. We need to convert it back to the
    // storage timezone and format.
    foreach ($values as &$item) {

      if (!empty($item['value']) && !preg_match('/____|__-__/m', $item['value'])) {

        // Date value is now string not instance of DrupalDateTime (without T).
        // String needs to be converted to DrupalDateTime.
        $start_date = new DrupalDateTime($item['value']);
        $datetime_type = $this->getFieldSetting('datetime_type');

        if ($datetime_type === DateTimeItem::DATETIME_TYPE_DATE) {
          // If this is a date-only field, set it to the default time so the
          // timezone conversion can be reversed.
          $start_date->setDefaultDateTime();
          $format = DateTimeItemInterface::DATE_STORAGE_FORMAT;
        } else {
          $format = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;
        }

        // Adjust the date for storage.
        $start_date->setTimezone(new \DateTimezone(DateTimeItemInterface::STORAGE_TIMEZONE));
        $item['value'] = $start_date->format($format);
      }
    }

    return $values;
  }

  /**
   * Creates a date string for use as a default value.
   *
   * This will take a default value, apply the proper timezone for display in
   * a widget, and set the default time for date-only fields.
   *
   * @param object $date
   *   The UTC default date.
   * @param string $timezone
   *   The timezone to apply.
   * @param string $format
   *   Date format to apply.
   *
   * @return string
   *   String for use as a default value in a field widget.
   */
  public function formatDefaultValue($date, $timezone, $format) {
    // The date was created and verified during field_load(), so it is safe to
    // use without further inspection.
    if ($this->getFieldSetting('datetime_type') === DateTimeItem::DATETIME_TYPE_DATE) {
      // A date without time will pick up the current time, use the default
      // time.
      $date->setDefaultDateTime();
    }
    $date->setTimezone(new \DateTimeZone($timezone));

    // Format date.
    return $date->format($format);
  }

  /**
   * Return array of field settings.
   *
   * @return array
   *   Formatted array of all available settings.
   */
  public function getCommonElementSettings() {
    return [
      '#hour_format' => $this->getSetting('hour_format'),
      '#allow_seconds' => $this->getSetting('allow_seconds'),
      '#allow_times' => $this->getSetting('allow_times'),
      '#allowed_hours' => $this->getSetting('allowed_hours'),
      '#disable_days' => $this->getSetting('disable_days'),
      '#exclude_date' => $this->getSetting('exclude_date'),
      '#inline' => $this->getSetting('inline'),
      '#mask' => $this->getSetting('mask'),
      '#datetimepicker_theme' => $this->getSetting('datetimepicker_theme'),
      '#start_date' => $this->getSetting('start_date'),
      '#min_date' => $this->getSetting('min_date'),
      '#max_date' => $this->getSetting('max_date'),
      '#year_start' => $this->getSetting('year_start'),
      '#year_end' => $this->getSetting('year_end'),
      '#allow_blank' => $this->getSetting('allow_blank'),
    ];
  }

}
