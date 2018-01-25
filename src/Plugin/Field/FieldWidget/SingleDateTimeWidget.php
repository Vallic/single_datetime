<?php

namespace Drupal\single_datetime\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\datetime\Plugin\Field\FieldWidget\DateTimeWidgetBase;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;

/**
 * Plugin implementation of the SingleDateTimeWidget widget.
 *
 * @FieldWidget(
 *   id = "single_date_time_widget",
 *   label = @Translation("Single Date Time Picker"),
 *   field_types = {
 *     "datetime"
 *   }
 * )
 */
class SingleDateTimeWidget extends DateTimeWidgetBase implements ContainerFactoryPluginInterface {

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

    foreach ($values as &$item) {
      if (!empty($item['value'])) {

        // Date value is now string not instance of DrupalDateTime (without T).
        $date = new DrupalDateTime($item['value']);
        switch ($this->getFieldSetting('datetime_type')) {
          case DateTimeItem::DATETIME_TYPE_DATE:
            // If this is a date-only field, set it to the default time so the
            // timezone conversion can be reversed.
            datetime_date_default_time($date);
            $format = DATETIME_DATE_STORAGE_FORMAT;
            break;

          default:
            $format = DATETIME_DATETIME_STORAGE_FORMAT;
            break;
        }

        // Adjust the date for storage.
        $date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
        $item['value'] = $date->format($format);
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
      $container->get('entity.manager')->getStorage('date_format')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Field type.
    $element['value'] = [
      '#title' => $element['#title'],
      '#type' => 'single_date_time',
      '#date_timezone' => drupal_get_user_timezone(),
      '#default_value' => NULL,
      '#date_type' => NULL,
      '#required' => $element['#required'],
    ];

    // Identify the type of date and time elements to use.
    switch ($this->getFieldSetting('datetime_type')) {
      case DateTimeItem::DATETIME_TYPE_DATE:
        // A date-only field should have no timezone conversion performed, so
        // use the same timezone as for storage.
        $element['value']['#date_timezone'] = DATETIME_STORAGE_TIMEZONE;

        // If field is date only, use default time format.
        $format = DATETIME_DATE_STORAGE_FORMAT;

        // Type of the field.
        $element['value']['#date_type'] = $this->getFieldSetting('datetime_type');
        break;

      default:
        // Type of the field.
        $element['value']['#date_type'] = $this->getFieldSetting('datetime_type');

        // Assign the time format, because time will be saved in 24hrs format
        // in database.
        $format = ($this->getSetting('hour_format') == '12h') ? 'Y-m-d h:i:s A' : 'Y-m-d H:i:s';
        break;
    }

    if ($items[$delta]->date) {
      $date = $items[$delta]->date;

      // The date was created and verified during field_load(), so it is safe to
      // use without further inspection.
      if ($this->getFieldSetting('datetime_type') == DateTimeItem::DATETIME_TYPE_DATE) {
        // A date without time will pick up the current time, use the default
        // time.
        datetime_date_default_time($date);
      }

      $date->setTimezone(new \DateTimeZone($element['value']['#date_timezone']));

      // Manual define form for input field.
      $element['value']['#default_value'] = $date->format($format);
    }

    $element['value']['#hour_format'] = $this->getSetting('hour_format');
    $element['value']['#allow_times'] = $this->getSetting('allow_times');
    $element['value']['#disable_days'] = $this->getSetting('disable_days');
    $element['value']['#exclude_date'] = $this->getSetting('exclude_date');
    return $element;
  }

}
