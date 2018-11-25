<?php

namespace Drupal\single_datetime\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\datetime\Plugin\Field\FieldWidget\DateTimeWidgetBase;

/**
 * Base class for SingleDateTime widget types.
 */
abstract class SingleDateTimeBase extends DateTimeWidgetBase implements ContainerFactoryPluginInterface {

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
      $container->get('entity.manager')->getStorage('date_format')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'hour_format' => '24h',
      'allow_times' => '15',
      'allowed_hours' => '',
      'disable_days' => [],
      'exclude_date' => '',
      'inline' => FALSE,
      'mask' => FALSE,
      'datetimepicker_theme' => 'default',
      'min_date' => '',
      'max_date' => '',
      'year_start' => '',
      'year_end' => '',
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
      '#description' => $this->t('Use mask for input. Example __.__.____ '),
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
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = t('Hours Format: @hour_format', ['@hour_format' => $this->getSetting('hour_format')]);
    $summary[] = t('Minutes Granularity: @allow_times', ['@allow_times' => $this->getSetting('allow_times')]);
    $summary[] = t('Allowed hours: @allowed_hours', ['@allowed_hours' => !empty($this->getSetting('allowed_hours')) ? $this->getSetting('allowed_hours') : t('All hours are allowed')]);

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
    $min_date = $this->getSetting('min_date');
    $max_date = $this->getSetting('max_date');
    $year_start = $this->getSetting('year_start');
    $year_end = $this->getSetting('year_end');

    $summary[] = t('Disabled days: @disabled_days', ['@disabled_days' => !empty($disabled_days) ? $disabled_days : t('None')]);

    $summary[] = t('Disabled dates: @disabled_dates', ['@disabled_dates' => !empty($this->getSetting('exclude_date')) ? $this->getSetting('exclude_date') : t('None')]);

    $summary[] = t('Display inline widget: @render_widget', ['@render_widget' => !empty($this->getSetting('inline')) ? t('Yes') : t('No')]);

    $summary[] = t('Use mask: @mask', ['@mask' => !empty($this->getSetting('mask')) ? t('Yes') : t('No')]);

    $summary[] = t('Theme: @theme', ['@theme' => ucfirst($this->getSetting('datetimepicker_theme'))]);

    $summary[] = t('Minimum date/time: @min_date', ['@min_date' => !empty($min_date) ? $min_date : t('None')]);

    $summary[] = t('Maximum date/time: @max_date', ['@max_date' => !empty($max_date) ? $max_date : t('None')]);

    $summary[] = t('Start year: @year_start', ['@year_start' => !empty($year_start) ? $year_start : t('None')]);

    $summary[] = t('End year: @year_end', ['@year_end' => !empty($year_end) ? $year_end : t('None')]);

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

      if (!empty($item['value'])) {
        // Date value is now string not instance of DrupalDateTime (without T).
        // String needs to be converted to DrupalDateTime.
        $start_date = new DrupalDateTime($item['value']);
        switch ($this->getFieldSetting('datetime_type')) {
          // Dates only.
          case DateTimeItem::DATETIME_TYPE_DATE:
          case DateRangeItem::DATETIME_TYPE_DATE:
            // If this is a date-only field, set it to the default time so the
            // timezone conversion can be reversed.
            datetime_date_default_time($start_date);
            $format = DATETIME_DATE_STORAGE_FORMAT;
            break;

          // All day.
          case DateRangeItem::DATETIME_TYPE_ALLDAY:
            // All day fields start at midnight on the starting date, but are
            // stored like datetime fields, so we need to adjust the time.
            // This function is called twice, so to prevent a double conversion
            // we need to explicitly set the timezone.
            $start_date->setTimeZone(timezone_open(drupal_get_user_timezone()));
            $start_date->setTime(0, 0, 0);
            $format = DATETIME_DATETIME_STORAGE_FORMAT;
            break;

          // Date and time.
          default:
            $format = DATETIME_DATETIME_STORAGE_FORMAT;
            break;
        }
        // Adjust the date for storage.
        $start_date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
        $item['value'] = $start_date->format($format);
      }

      // This is case for daterange field.
      if (!empty($item['end_value'])) {

        // Convert string to DrupalDateTime.
        $end_date = new DrupalDateTime($item['end_value']);
        switch ($this->getFieldSetting('datetime_type')) {
          case DateRangeItem::DATETIME_TYPE_DATE:
            // If this is a date-only field, set it to the default time so the
            // timezone conversion can be reversed.
            datetime_date_default_time($end_date);
            $format = DATETIME_DATE_STORAGE_FORMAT;
            break;

          case DateRangeItem::DATETIME_TYPE_ALLDAY:
            // All day fields end at midnight on the end date, but are
            // stored like datetime fields, so we need to adjust the time.
            // This function is called twice, so to prevent a double conversion
            // we need to explicitly set the timezone.
            $end_date->setTimeZone(timezone_open(drupal_get_user_timezone()));
            $end_date->setTime(23, 59, 59);
            $format = DATETIME_DATETIME_STORAGE_FORMAT;
            break;

          default:
            $format = DATETIME_DATETIME_STORAGE_FORMAT;
            break;
        }
        // Adjust the date for storage.
        $end_date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
        $item['end_value'] = $end_date->format($format);
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
      datetime_date_default_time($date);
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
      '#allow_times' => $this->getSetting('allow_times'),
      '#allowed_hours' => $this->getSetting('allowed_hours'),
      '#disable_days' => $this->getSetting('disable_days'),
      '#exclude_date' => $this->getSetting('exclude_date'),
      '#inline' => $this->getSetting('inline'),
      '#mask' => $this->getSetting('mask'),
      '#datetimepicker_theme' => $this->getSetting('datetimepicker_theme'),
      '#min_date' => $this->getSetting('min_date'),
      '#max_date' => $this->getSetting('max_date'),
      '#year_start' => $this->getSetting('year_start'),
      '#year_end' => $this->getSetting('year_end'),
    ];
  }

}
