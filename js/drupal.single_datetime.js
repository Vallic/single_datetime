/**
 * @file
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  function SingleDatetimeAllowTimes($type) {
    var times = [], i, j;

    // Build array.
    for (i = 0; i < 24; i++) {

      // Formatting hours.
      if (i < 10) {
        i = '0' + i;
      }

      // Default granularity is one hour.
      if ($type === 60) {
        times.push(i + ':' + '00');
      }

      // Custom settings, per minutes.
      else {
        var measure = 60 / $type;
        for (j = 0; j < measure; j++) {
          var minutes = $type * j;
          times.push(i + ':' + (j === 0 ? '00' : (minutes < 10 ? '0' + minutes : minutes)));
        }
      }
    }

    return times;
  }

  Drupal.behaviors.single_datetime = {
    attach: function (context, settings) {

      // Setting the current language for the calendar.
      var language = drupalSettings.path.currentLanguage;

      $(context).find('input[data-single-date-time]').once('datePicker').each(function () {
        var input = $(this);

        // Get widget type.
        var widgetType = input.data('singleDateTime');

        // Get hour format - 12 or 24.
        var hourFormat = input.data('hourFormat');

        // Get first day in week from Drupal.
        var startDayWeek = input.data('firstDay');

        // Default values (used for dates only).
        var dateType = 'Y-m-d';
        var allowTimepicker = false;

        // Get minute granularity
        var allowedTimes = SingleDatetimeAllowTimes(input.data('allowTimes'));

        // Get disabled days.
        var disabledDays = input.data('disableDays');

        // Get excluded dates.
        var excludeDates = input.data('excludeDate');

        // Set the hour format.
        var hoursFormat = (hourFormat === '12h') ? 'h:i A' : 'H:i';

        // If is date & time field.
        if (widgetType === 'datetime') {
          dateType = (hourFormat === '12h') ? 'Y-m-d h:i:s A' : 'Y-m-d H:i:s';
          allowTimepicker = true;
        }

        $("#" + input.attr('id')).datetimepicker({
          lang: language,
          format: dateType,
          formatTime: hoursFormat,
          lazyInit: true,
          timepicker: allowTimepicker,
          todayButton: true,
          dayOfWeekStart: startDayWeek,
          allowTimes: allowedTimes,
          disabledWeekDays: disabledDays,
          disabledDates: excludeDates,
          formatDate: 'd.m.Y',
        });
      });
    },
  };

})(jQuery, Drupal, drupalSettings);
