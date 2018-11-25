<?php

namespace Drupal\single_datetime\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;

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
class SingleDateTimeRangeWidget extends SingleDateTimeBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Wrap all of the select elements with a fieldset.
    $element['#theme_wrappers'][] = 'fieldset';

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
      '#title' => $element['#required'] ? $this->t('End date') : $this->t('End date (optional)'),
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

    // Merge defaults with field settings.
    $element_defaults = [
      '#date_date_format' => $date_format,
      '#date_date_element' => $date_type,
      '#date_date_callbacks' => [],
      '#date_time_format' => $time_format,
      '#date_time_element' => $time_type,
      '#date_time_callbacks' => [],
    ];

    // Build elements array with all data.
    $element['value'] = array_merge($element['value'], $element_defaults, $this->getCommonElementSettings());
    $element['end_value'] = array_merge($element['end_value'], $element_defaults, $this->getCommonElementSettings());

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
