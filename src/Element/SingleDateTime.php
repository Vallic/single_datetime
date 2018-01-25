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
    foreach ($element['#disable_days'] as $key => $value) {
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

    // Default settings.
    $settings = [
      'data-hour-format' => $element['#hour_format'],
      'data-allow-times' => intval($element['#allow_times']),
      'data-first-day' => $first_day,
      'data-disable-days' => Json::encode($disabled_days),
      'data-exclude-date' => $exclude_date,
    ];

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

    // Attach library.
    $complete_form['#attached']['library'][] = 'single_datetime/datetimepicker';

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
