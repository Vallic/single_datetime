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
      '#type' => 'single_date_time',
      '#date_timezone' => drupal_get_user_timezone(),
      '#default_value' => NULL,
      '#date_type' => NULL,
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

        // Manually define format for input field (avoid T).
        $format = 'Y-m-d H:i:s';
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

    return $element;
  }

}
