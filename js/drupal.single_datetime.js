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
      var lang = drupalSettings.path.currentLanguage;

      $(context).find('input[data-single-date-time]').once('datePicker').each(function () {
        var input = $(this);

        // Get widget type.
        var widgetType = input.data('singleDateTime');

        // Get hour format - 12 or 24.
        var hourFormat = input.data('hourFormat');

        // Get first day in week from Drupal.
        var dayOfWeekStart = input.data('firstDay');

        // Default values (used for dates only).
        var format = 'Y-m-d';
        var allowTimepicker = false;

        // Get minute granularity
        var allowTimes = SingleDatetimeAllowTimes(input.data('allowTimes'));

        // Get disabled days.
        var disabledWeekDays = input.data('disableDays');

        // Get excluded dates.
        var disabledDates = input.data('excludeDate');

        // Get minimum date.
        var minDate = input.data('minDate');

        // Get maximum date.
        var maxDate = input.data('maxDate');

        // Get start year.
        var yearStart = input.data('yearStart');

        // Get end year.
        var yearEnd = input.data('yearEnd');

        // Set the hour format.
        var formatTime = (hourFormat === '12h') ? 'h:i A' : 'H:i';

        var inline = input.data('inline');

        var theme = input.data('datetimepickerTheme');

        // If is date & time field.
        if (widgetType === 'datetime') {
          format = (hourFormat === '12h') ? 'Y-m-d h:i:s A' : 'Y-m-d H:i:s';
          allowTimepicker = true;
        }

        $('#' + input.attr('id')).datetimepicker({
          lang,
          format,
          formatTime,
          lazyInit: true,
          timepicker: allowTimepicker,
          todayButton: true,
          dayOfWeekStart,
          allowTimes,
          disabledWeekDays,
          disabledDates,
          formatDate: 'd.m.Y',
          inline,
          minDate,
          maxDate,
          yearStart,
          yearEnd,
          theme,
        });
      });
    },
  };

})(jQuery, Drupal, drupalSettings);
