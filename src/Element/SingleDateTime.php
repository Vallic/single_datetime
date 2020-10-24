<?php

namespace Drupal\single_datetime\Element;

use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Json;

/**
 * Provides a SingleDateTime form element.
 *
 * @FormElement("single_date_time")
 */
class SingleDateTime extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {

    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#multiple' => FALSE,
      '#maxlength' => 512,
      '#process' => [[$class, 'processSingleDateTime']],
      '#pre_render' => [[$class, 'preRenderSingleDateTime']],
      '#size' => 25,
      '#theme_wrappers' => ['form_element'],
      '#theme' => 'input__textfield',
    ];

  }

  /**
   * Render element for input.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRenderSingleDateTime(array $element) {
    $element['#attributes']['type'] = 'text';
    Element::setAttributes($element, ['id', 'name', 'value', 'size']);
    static::setAttributes($element, ['form-date']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function processSingleDateTime(&$element, FormStateInterface $form_state, &$complete_form) {
    // Get system regional settings.
    $first_day = \Drupal::config('system.date')->get('first_day');

    // Get disabled days.
    $disabled_days = [];

    // Get active days.
    foreach ($element['#disable_days'] as $value) {
      if (!empty($value)) {
        // Exception for Sunday - should be 0 (on widget options need to be 7).
        $disabled_days[] = (int) $value < 7 ? (int) $value : 0;
      }
    }

    // Get excluded dates.
    $exclude_date = [];

    if (!empty($element['#exclude_date'])) {
      $exclude_date = explode("\n", $element['#exclude_date']);
    }

    // Default, build array for all hours.
    $allowed_hours = range(0, 23);

    // If we have specifics, use that list.
    if (!empty($element['#allowed_hours'])) {
      $allowed_hours = explode(',', $element['#allowed_hours']);
    }

    // Default settings.
    $settings = [
      'data-hour-format' => $element['#hour_format'],
      'data-allow-seconds' => !empty($element['#allow_seconds']) ? 1 : 0,
      'data-allow-times' => (int) $element['#allow_times'],
      'data-allowed-hours' => Json::encode($allowed_hours),
      'data-first-day' => $first_day,
      'data-disable-days' => Json::encode($disabled_days),
      'data-exclude-date' => $exclude_date,
      'data-inline' => !empty($element['#inline']) ? 1 : 0,
      'data-mask' => !empty($element['#mask']) ? 1 : 0,
      'data-datetimepicker-theme' => $element['#datetimepicker_theme'],
      'data-custom-format' => $element['#custom_format'] ?? NULL,
    ];

    // Year start.
    if (!empty($element['#year_start'])) {
      $settings['data-year-start'] = $element['#year_start'];
    }

    // Year end.
    if (!empty($element['#year_end'])) {
      $settings['data-year-end'] = $element['#year_end'];
    }

    // Start date.
    if (strlen($element['#start_date'])) {
      $settings['data-start-date'] = $element['#start_date'];
    }

    // Min/Max date settings.
    if (strlen($element['#min_date'])) {
      $settings['data-min-date'] = $element['#min_date'];
    }

    if (strlen($element['#max_date'])) {
      $settings['data-max-date'] = $element['#max_date'];
    }

    // Allow blank.
    if (!empty($element['#allow_blank'])) {
      $settings['data-allow-blank'] = $element['#allow_blank'];
    }

    // Push field type to JS for changing between date only and time fields.
    // Difference between date and date range fields.
    if (isset($element['#date_type'])) {
      $settings['data-single-date-time'] = $element['#date_type'];
    }

    else {
      // Combine date range formats.
      $range_date_type = $element['#date_date_element'] . $element['#date_time_element'];
      $settings['data-single-date-time'] = $range_date_type;
    }

    // Append our attributes to element.
    $element['#attributes'] += $settings;

    // Disable Chrome autofill on widget.
    $element['#attributes']['autocomplete'] = 'off';

    // Prevent keyboard on mobile devices, but only if allowBlank is false
    // otherwise a user won't be able to delete a date.
    if (!$element['#allow_blank']) {
      $element['#attributes']['onfocus'] = 'blur();';
    }

    // Attach library.
    $element['#attached']['library'][] = 'single_datetime/datetimepicker';

    return $element;
  }

  /**
   * Return default settings. Pass in values to override defaults.
   *
   * @param array $values
   *   Some Desc.
   *
   * @return array
   *   Some Desc.
   */
  public static function settings(array $values = []) {
    $settings = [
      'lang' => 'en',
    ];

    return array_merge($settings, $values);
  }

}
