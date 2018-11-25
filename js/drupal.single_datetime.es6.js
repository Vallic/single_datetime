/**
 * @file
 * Javascript to initialize singe date time library.
 */

(($, Drupal, drupalSettings) => {
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
    const times = [];

    // Build array.
    $hours.forEach(i => {
      // Formatting hours.
      if (i < 10) {
        i = `0${i}`;
      }

      // Default granularity is one hour.
      if ($type === 60) {
        times.push(`${i}:00`);
      }

      // Custom settings, per minutes.
      else {
        const measure = 60 / $type;
        for (let j = 0; j < measure; j++) {
          let minutes = $type * j;

          if (j === 0) {
            minutes = "00";
          } else if (minutes < 10) {
            minutes = `0${minutes}`;
          }
          times.push(`${i}:${minutes}`);
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
    attach(context) {
      // Setting the current language for the calendar.
      const lang = drupalSettings.path.currentLanguage;

      $(context)
        .find("input[data-single-date-time]")
        .once("datePicker")
        .each(() => {
          const input = $(this);

          // Get widget type.
          const widgetType = input.data("singleDateTime");

          // Get hour format - 12 or 24.
          const hourFormat = input.data("hourFormat");

          // Get first day in week from Drupal.
          const dayOfWeekStart = input.data("firstDay");

          // Default values (used for dates only).
          let format = "Y-m-d";
          let allowTimepicker = false;

          // Get disabled days.
          const disabledWeekDays = input.data("disableDays");

          // Get excluded dates.
          const disabledDates = input.data("excludeDate");

          // Get minimum date.
          const minDate = input.data("minDate");

          // Get maximum date.
          const maxDate = input.data("maxDate");

          // Get start year.
          const yearStart = input.data("yearStart");

          // Get end year.
          const yearEnd = input.data("yearEnd");

          // Set the hour format.
          const formatTime = hourFormat === "12h" ? "h:i A" : "H:i";

          const inline = input.data("inline");

          const mask = Boolean(input.data("mask"));

          const theme = input.data("datetimepickerTheme");

          // Default empty array. Only calculate later if field type
          // includes times.
          let allowTimes = [];

          // If is date & time field.
          if (widgetType === "datetime") {
            format = hourFormat === "12h" ? "Y-m-d h:i:s A" : "Y-m-d H:i:s";
            allowTimepicker = true;

            // Get minute granularity, and allowed hours.
            allowTimes = SingleDatetimeAllowTimes(
              input.data("allowedHours"),
              input.data("allowTimes")
            );
          }

          $(`#${input.attr("id")}`).datetimepicker({
            format,
            formatTime,
            lazyInit: true,
            timepicker: allowTimepicker,
            todayButton: true,
            dayOfWeekStart,
            allowTimes,
            disabledWeekDays,
            disabledDates,
            formatDate: "d.m.Y",
            inline,
            mask,
            minDate,
            maxDate,
            yearStart,
            yearEnd,
            theme
          });
          // Explicitly set locale. Does not work with passed variable
          // in setttings above.
          $.datetimepicker.setLocale(lang);
        });
    }
  };
})(jQuery, Drupal, drupalSettings);
