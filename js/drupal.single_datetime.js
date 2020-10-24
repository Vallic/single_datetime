/**
 * @file
 * Javascript to initialize singe date time library.
 */

(function ($, Drupal, drupalSettings) {
  /**
   * Function to return list of hours / minutes formatted as array.
   *
   * @param {Array} $hours
   *   List for allowed hours as array.
   * @param {string} $type
   *   Granularity for allowed intervals in minutes.
   *
   * @return {Array}
   *   Return minutes formatted in array by allowed intervals.
   */
  function SingleDatetimeAllowTimes($hours, $type) {
    var times = [];

    // Build array.
    $hours.forEach(function (i) {
      // Formatting hours.
      if (i < 10) {
        i = "0" + i;
      }

      // Default granularity is one hour.
      if ($type === 60) {
        times.push(i + ":00");
      }

      // Custom settings, per minutes.
      else {
          var measure = 60 / $type;
          for (var j = 0; j < measure; j++) {
            var minutes = $type * j;

            if (j === 0) {
              minutes = "00";
            } else if (minutes < 10) {
              minutes = "0" + minutes;
            }
            times.push(i + ":" + minutes);
          }
        }
    });

    return times;
  }

  /**
   * Attaches the single_datetime behavior.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.single_datetime = {
    attach: function attach(context) {

      // Setting the current language for the calendar.
      var lang = drupalSettings.path.currentLanguage;

      $(context).find("input[data-single-date-time]").once("datePicker").each(function () {
        var input = $(this);

        // Get widget type.
        var widgetType = input.data("singleDateTime");

        // Get hour format - 12 or 24.
        var hourFormat = input.data("hourFormat");

        // Get first day in week from Drupal.
        var dayOfWeekStart = input.data("firstDay");

        // Default values (used for dates only).
        var format = "Y-m-d";
        var allowTimepicker = false;

        // Get disabled days.
        var disabledWeekDays = input.data("disableDays");

        // Get excluded dates.
        var disabledDates = input.data("excludeDate");

        // Get start date.
        var startDate = input.data("startDate");

        // Get minimum date.
        var minDate = input.data("minDate");

        // Get maximum date.
        var maxDate = input.data("maxDate");

        // Get start year.
        var yearStart = input.data("yearStart");

        // Get end year.
        var yearEnd = input.data("yearEnd");

        // Set the hour format.
        var formatTime = hourFormat === "12h" ? "h:i A" : "H:i";

        var customFormat = input.data('customFormat');

        var inline = input.data("inline");

        var allowSeconds = Boolean(input.data("allowSeconds"));

        var mask = Boolean(input.data("mask"));

        var theme = input.data("datetimepickerTheme");

        var allowBlank = Boolean(input.data("allowBlank"));

        // Default empty array. Only calculate later if field type
        // includes times.
        var allowTimes = [];

        // If is date & time field.
        if (widgetType === "datetime") {
          var hourFormatDefault =  allowSeconds === true ? "Y-m-d H:i:00" : "Y-m-d H:i:s";
          var hourFormatA = allowSeconds === true ? "Y-m-d h:i:00 A" : "Y-m-d h:i:s A";
          format = hourFormat === "12h" ? hourFormatA : hourFormatDefault;
          allowTimepicker = true;

          // Get minute granularity, and allowed hours.
          allowTimes = SingleDatetimeAllowTimes(input.data("allowedHours"), input.data("allowTimes"));
        }

        if (typeof customFormat !== 'undefined') {
          format = customFormat;
        }

        $("#" + input.attr("id")).datetimepicker({
          format: format,
          formatTime: formatTime,
          lazyInit: true,
          timepicker: allowTimepicker,
          todayButton: true,
          dayOfWeekStart: dayOfWeekStart,
          allowTimes: allowTimes,
          disabledWeekDays: disabledWeekDays,
          disabledDates: disabledDates,
          formatDate: "d.m.Y",
          inline: inline,
          mask: mask,
          startDate: startDate,
          minDate: minDate,
          maxDate: maxDate,
          yearStart: yearStart,
          yearEnd: yearEnd,
          theme: theme,
          allowBlank: allowBlank
        });

        if (lang === 'pt-br') {
          lang = 'pt-BR';
        }

        if (lang === 'zh-hans') {
          lang = 'zh';
        }

        if (lang === 'zh-hant') {
          lang = 'zh-TW';
        }

        // Explicitly set locale. Does not work with passed variable
        // in settings above.
        $.datetimepicker.setLocale(lang);
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
