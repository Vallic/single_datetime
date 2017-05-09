<?php

namespace Drupal\single_datetime\Element;

use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormStateInterface;

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
    static::setAttributes($element, ['form-text']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function processSingleDateTime(&$element, FormStateInterface $form_state, &$complete_form) {
    // Push field type to JS for changing between date only and time fields.
    // Difference between date and date range fields.
    if (isset($element['#date_type'])) {
      $complete_form['#attached']['drupalSettings']['single_datetime'][$element['#id']] = json_encode($element['#date_type']);
    }

    else {
      // Combine date range formats.
      $range_date_type = $element['#date_date_element'] . $element['#date_time_element'];
      $complete_form['#attached']['drupalSettings']['single_datetime'][$element['#id']] = json_encode($range_date_type);
    }

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
