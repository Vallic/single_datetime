<?php

namespace Drupal\single_datetime\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;
use Drupal\datetime_range\Plugin\Field\FieldWidget\DateRangeWidgetBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;

/**
 * Plugin implementation of the 'daterange_default' widget.
 *
 * @FieldWidget(
 *   id = "single_date_time_range_widget",
 *   label = @Translation("Single Date Time Picker"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class SingleDateTimeRangeWidget extends DateRangeWidgetBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'hour_format' => '24h',
      'allow_times' => '15',
      'disable_days' => [],
      'exclude_date' => '',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $elements = array();
    $elements['hour_format'] = array(
      '#type' => 'select',
      '#title' => $this->t('Hours Format'),
      '#description' => $this->t('Select the hours format'),
      '#options' => array(
        '12h' => $this->t('12 Hours'),
        '24h' => $this->t('24 Hours'),
      ),
      '#default_value' => $this->getSetting('hour_format'),
      '#required' => TRUE,
    );
    $elements['allow_times'] = array(
      '#type' => 'select',
      '#title' => $this->t('Minutes granularity'),
      '#description' => $this->t('Select granularity for minutes in calendar'),
      '#options' => array(
        '5' => $this->t('5 minutes'),
        '10' => $this->t('10 minutes'),
        '15' => $this->t('15 minutes'),
        '30' => $this->t('30 minutes'),
        '60' => $this->t('60 minutes'),
      ),
      '#default_value' => $this->getSetting('allow_times'),
      '#required' => TRUE,
    );
    $elements['disable_days'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Disable specific days in week'),
      '#description' => $this->t('Select days which are disabled in calendar, etc. weekends or just Friday'),
      '#options' => array(
        '1' => $this->t('Monday'),
        '2' => $this->t('Tuesday'),
        '3' => $this->t('Wednesday'),
        '4' => $this->t('Thursday'),
        '5' => $this->t('Friday'),
        '6' => $this->t('Saturday'),
        '7' => $this->t('Sunday'),
      ),
      '#default_value' => $this->getSetting('disable_days'),
      '#required' => FALSE,
    );
    $elements['exclude_date'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Disable specific dates from calendar'),
      '#description' => $this->t('Enter days in following format d.m.Y etc. 31.12.2018. Each date in new line. This is used for specific dates, if you want to disable all weekends use settings above, not this field.'),
      '#default_value' => $this->getSetting('exclude_date'),
      '#required' => FALSE,
    );
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = t('Hours Format: @hour_format', ['@hour_format' => $this->getSetting('hour_format')]);
    $summary[] = t('Minutes Granularity: @allow_times', ['@allow_times' => $this->getSetting('allow_times')]);

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
    foreach ($this->getSetting('disable_days') as $key => $value) {
      if (!empty($value)) {
        $disabled_days[] = $options[$value];
      }
    }

    $disabled_days = implode(',', $disabled_days);

    $summary[] = t('Disabled days: @disabled_days', ['@disabled_days' => !empty($disabled_days) ? $disabled_days : t('None')]);

    $summary[] = t('Disabled dates: @disabled_dates', ['@disabled_dates' => !empty($this->getSetting('exclude_date')) ? $this->getSetting('exclude_date') : t('None')]);

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

        // Convert string to DrupalDateTime.
        $start_date = new DrupalDateTime($item['value']);
        switch ($this->getFieldSetting('datetime_type')) {
          case DateRangeItem::DATETIME_TYPE_DATE:
            // If this is a date-only field, set it to the default time so the
            // timezone conversion can be reversed.
            datetime_date_default_time($start_date);
            $format = DATETIME_DATE_STORAGE_FORMAT;
            break;

          case DateRangeItem::DATETIME_TYPE_ALLDAY:
            // All day fields start at midnight on the starting date, but are
            // stored like datetime fields, so we need to adjust the time.
            // This function is called twice, so to prevent a double conversion
            // we need to explicitly set the timezone.
            $start_date->setTimeZone(timezone_open(drupal_get_user_timezone()));
            $start_date->setTime(0, 0, 0);
            $format = DATETIME_DATETIME_STORAGE_FORMAT;
            break;

          default:
            $format = DATETIME_DATETIME_STORAGE_FORMAT;
            break;
        }
        // Adjust the date for storage.
        $start_date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
        $item['value'] = $start_date->format($format);
      }

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
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Overwriting default validateStartEnd validation with our function.
    // validateStartEnd uses date object, we are submitting date as string.
    $element['#element_validate'][0] = [$this, 'validateSingleDateTime'];

    // Start value.
    $element['value'] = [
      '#title' => $this->t('Start date'),
      '#type' => 'single_date_time',
      '#date_timezone' => drupal_get_user_timezone(),
      '#default_value' => NULL,
      '#required' => $element['#required'],
    ];

    // End value.
    $element['end_value'] = [
      '#title' => $this->t('End date'),
      '#type' => 'single_date_time',
      '#date_timezone' => drupal_get_user_timezone(),
      '#default_value' => NULL,
      '#required' => $element['#required'],
    ];

    // Identify the type of date and time elements to use.
    switch ($this->getFieldSetting('datetime_type')) {
      case DateRangeItem::DATETIME_TYPE_DATE:
      case DateRangeItem::DATETIME_TYPE_ALLDAY:
        $date_type = 'date';
        $time_type = 'none';
        $date_format = $this->dateStorage->load('html_date')->getPattern();
        $time_format = '';
        break;

      default:
        $date_type = 'date';
        $time_type = 'time';
        $date_format = $this->dateStorage->load('html_date')->getPattern();
        $time_format = $this->dateStorage->load('html_time')->getPattern();

        if ($this->getSetting('hour_format') === '12h') {
          $time_format = 'h:i:s A';
        }

        break;
    }

    $element['value'] += [
      '#date_date_format' => $date_format,
      '#date_date_element' => $date_type,
      '#date_date_callbacks' => [],
      '#date_time_format' => $time_format,
      '#date_time_element' => $time_type,
      '#date_time_callbacks' => [],
      '#hour_format' => $this->getSetting('hour_format'),
      '#allow_times' => $this->getSetting('allow_times'),
      '#disable_days' => $this->getSetting('disable_days'),
      '#exclude_date' => $this->getSetting('exclude_date'),
    ];

    $element['end_value'] += [
      '#date_date_format' => $date_format,
      '#date_date_element' => $date_type,
      '#date_date_callbacks' => [],
      '#date_time_format' => $time_format,
      '#date_time_element' => $time_type,
      '#date_time_callbacks' => [],
      '#hour_format' => $this->getSetting('hour_format'),
      '#allow_times' => $this->getSetting('allow_times'),
      '#disable_days' => $this->getSetting('disable_days'),
      '#exclude_date' => $this->getSetting('exclude_date'),
    ];

    // Make single date format from date / time parts.
    // Trim spaces in case of date type only.
    $format = trim($date_format . ' ' . $time_format);

    if ($items[$delta]->start_date) {
      $start_date = $items[$delta]->start_date;
      $element['value']['#default_value'] = $this->formatDefaultValue($start_date, $element['value']['#date_timezone'], $format);
    }

    if ($items[$delta]->end_date) {
      $end_date = $items[$delta]->end_date;
      $element['end_value']['#default_value'] = $this->formatDefaultValue($end_date, $element['end_value']['#date_timezone'], $format);
    }

    return $element;
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
  protected function formatDefaultValue($date, $timezone, $format) {
    // The date was created and verified during field_load(), so it is safe to
    // use without further inspection.
    if ($this->getFieldSetting('datetime_type') == DateTimeItem::DATETIME_TYPE_DATE) {
      // A date without time will pick up the current time, use the default
      // time.
      datetime_date_default_time($date);
    }
    $date->setTimezone(new \DateTimeZone($timezone));

    // Format date.
    $formatted_date = $date->format($format);

    return $formatted_date;
  }

  /**
   * Callback #element_validate to ensure that the start date <= the end date.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   */
  public function validateSingleDateTime(array &$element, FormStateInterface $form_state, array &$complete_form) {

    // String to DrupalDateTime.
    $start_date = new DrupalDateTime($element['value']['#value']);
    $end_date = new DrupalDateTime($element['end_value']['#value']);

    if ($start_date instanceof DrupalDateTime && $end_date instanceof DrupalDateTime) {
      if ($start_date->getTimestamp() !== $end_date->getTimestamp()) {
        $interval = $start_date->diff($end_date);
        if ($interval->invert === 1) {
          $form_state->setError($element, $this->t('The @title end date cannot be before the start date', ['@title' => $element['#title']]));
        }
      }
    }
  }

}
