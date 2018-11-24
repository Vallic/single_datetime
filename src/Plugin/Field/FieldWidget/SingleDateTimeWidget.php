<?php

namespace Drupal\single_datetime\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
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
class SingleDateTimeWidget extends SingleDateTimeBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $date_type = $this->getFieldSetting('datetime_type');

    // Field type.
    $element['value'] = [
      '#title' => $element['#title'],
      '#type' => 'single_date_time',
      '#date_timezone' => drupal_get_user_timezone(),
      '#default_value' => NULL,
      '#date_type' =>  $date_type,
      '#required' => $element['#required'],
    ];

    // Identify the type of date and time elements to use.
    switch ($date_type) {
      case DateTimeItem::DATETIME_TYPE_DATE:
        // A date-only field should have no timezone conversion performed, so
        // use the same timezone as for storage.
        $element['value']['#date_timezone'] = DATETIME_STORAGE_TIMEZONE;

        // If field is date only, use default time format.
        $format = DATETIME_DATE_STORAGE_FORMAT;
        break;

      default:
        // Assign the time format, because time will be saved in 24hrs format
        // in database.
        $format = ($this->getSetting('hour_format') === '12h') ? 'Y-m-d h:i:s A' : 'Y-m-d H:i:s';
        break;
    }

    // Merge with elements settings.
    $element['value'] = array_merge($element['value'], $this->getCommonElementSettings());

    if ($items[$delta]->date) {
      $date = $items[$delta]->date;
      // Manual define form for input field.
      $element['value']['#default_value'] =  $this->formatDefaultValue($date, $element['value']['#date_timezone'], $format);
    }

    return $element;
  }

}
