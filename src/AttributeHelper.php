<?php

namespace Drupal\single_datetime;

use Drupal\Component\Serialization\Json;

/**
 * Class AttributeHelper.
 *
 * @package Drupal\single_datetime
 */
class AttributeHelper {

  /**
   * Attributes for textfield (or any non single_datetime field types).
   *
   * @return array
   *   Return attributes.
   */
  public static function defaultWidget() {
    return [
      'data-hour-format' => 24,
      'data-first-day' => \Drupal::config('system.date')->get('first_day'),
      'data-allow-seconds' => FALSE,
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
   * Date only widget - for non single_datetime field types.
   *
   * @return array
   *   Return attributes.
   */
  public static function defaultDateOnlyWidget() {
    return self::defaultWidget() + ['data-single-date-time' => 'date'];
  }

  /**
   * List of all attributes - (for non single_datetime field types).
   *
   * @return array
   *   Return all attributes.
   */
  public static function allAtributes() {
    return [
      'data-hour-format' => 24,
      'data-first-day' => \Drupal::config('system.date')->get('first_day'),
      'data-disable-days' => [],
      'data-allow-seconds' => FALSE,
      'data-allow-times' => 60,
      'data-allowed-hours' => Json::encode(range(0, 23)),
      'data-inline' => '0',
      'data-mask' => FALSE,
      'data-datetimepicker-theme' => 'default',
      'data-single-date-time' => 'datetime',
      'data-exclude-date' => '',
      'data-start-date' => date('Y-m-d'),
      'data-min-date' => date('Y-m-d  H:i:s'),
      'data-max-date' => date('Y-m-d  H:i:s'),
      'data-year-start' => '1970',
      'data-year-end' => date('Y'),
    ];
  }

  /**
   * All attributes for single_datetime field type.
   *
   * @return array
   *   Return formatted array.
   */
  public static function allElementAttributes() {
    return [
      '#hour_format' => 24,
      '#first_day' => \Drupal::config('system.date')->get('first_day'),
      '#disable_days' => [],
      '#allow_seconds' => FALSE,
      '#allow_times' => 60,
      '#allowed_hours' => Json::encode(range(0, 23)),
      '#inline' => '0',
      '#mask' => FALSE,
      '#datetimepicker_theme' => 'default',
      '#single_date_time' => 'datetime',
      '#exclude_date' => '',
      '#start_date' => '',
      '#min_date' => '',
      '#max_date' => '',
      '#year_start' => '1970',
      '#year_end' => date('Y'),
    ];
  }

}
