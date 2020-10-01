<?php

namespace Drupal\single_datetime\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;

/**
 * Plugin implementation of the SingleDateTimeTimestampWidget widget.
 *
 * @FieldWidget(
 *   id = "single_date_time_timestamp_widget",
 *   label = @Translation("Single Date Time Picker (timestamp)"),
 *   field_types = {
 *     "created",
 *     "timestamp"
 *   }
 * )
 */
class SingleDateTimeTimestampWidget extends SingleDateTimeBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $date_type = DateTimeItem::DATETIME_TYPE_DATETIME;

    // Field type.
    $element['value'] = [
      '#title' => $element['#title'],
      '#type' => 'single_date_time',
      '#date_timezone' => date_default_timezone_get(),
      '#default_value' => NULL,
      '#date_type' => $date_type,
      '#required' => $element['#required'],
      '#description' => $element['#description'],
    ];

    // Assign the time format, because time will be saved in 24hrs format in
    // database.
    $format = ($this->getSetting('hour_format') === '12h') ? 'Y-m-d h:i:s A' : 'Y-m-d H:i:s';

    // Merge with elements settings.
    $element['value'] = array_merge($element['value'], $this->getCommonElementSettings());

    if ($items[$delta]->value) {
      $timestamp = $items[$delta]->value;
      // Manual define form for input field.
      $date = DrupalDateTime::createFromTimestamp($timestamp);
      $element['value']['#default_value'] = $date->format($format);
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$item) {
      if (empty($item['value'])) {
        $item['value'] = NULL;
        continue;
      }
      // @todo The structure is different whether access is denied or not, to
      //   be fixed in https://www.drupal.org/node/2326533.
      if (isset($item['value']) && $item['value'] instanceof DrupalDateTime) {
        $date = $item['value'];
      }
      elseif (isset($item['value']['object']) && $item['value']['object'] instanceof DrupalDateTime) {
        $date = $item['value']['object'];
      }
      elseif (isset($item['value']) && is_string($item['value'])) {
        $date = new DrupalDateTime($item['value']);
      }
      else {
        $date = new DrupalDateTime();
      }
      $item['value'] = $date->getTimestamp();
    }
    return $values;
  }

}
