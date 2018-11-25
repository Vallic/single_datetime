<?php

namespace Drupal\single_datetime;

use Drupal\Component\Serialization\Json;

/**
 * Class AttributeHelper
 *
 * @package Drupal\single_datetime
 */
class AttributeHelper {

  /**
   * Attributes for creating default widget.
   *
   * @return array
   *   Return attributes.
   */
  public static function defaultWidget() {
    return [
      'data-hour-format' => 24,
      'data-first-day' => \Drupal::config('system.date')->get('first_day'),
      'data-disable-days' => [],
      'data-allow-times' => 60,
      'data-allowed-hours' => Json::encode(range(0, 23)),
      'data-inline' => '0',
      'data-mask' => FALSE,
      'data-datetimepicker-theme' => 'default',
      'data-single-date-time' => 'datetime',
    ];
  }

  /**
   * Date only widget.
   *
   * @return array
   *   Return attributes.
   */
  public static function defaultDateOnlyWidget() {
    return self::defaultWidget() + ['data-single-date-time' => 'date'];
  }

  /**
   * List of all attributes.
   *
   * @return array
   *   Return all attributes.
   */
  public static function allAtributes() {
    return [
      'data-hour-format' => 24,
      'data-first-day' => \Drupal::config('system.date')->get('first_day'),
      'data-disable-days' => [],
      'data-allow-times' => 60,
      'data-allowed-hours' => Json::encode(range(0, 23)),
      'data-inline' => '0',
      'data-mask' => FALSE,
      'data-datetimepicker-theme' => 'default',
      'data-single-date-time' => 'datetime',
      'data-exclude-date' => '',
      'data-min-date' => date('Y-m-d  H:i:s'),
      'data-max-date' => date('Y-m-d  H:i:s'),
      'data-year-start' => '1970',
      'data-year-end' => date('Y'),
    ];
  }

}