/*
 * Câmpuri de dată în format european (zz.ll.aaaa) indiferent de limba browserului.
 * Valoarea trimisă la server rămâne Y-m-d (sau Y-m pentru lună).
 * Un câmp poate rămâne nativ cu atributul data-native-date.
 */
(function () {
  'use strict';

  function parseEuropean(str, format) {
    var s = String(str || '').trim();
    var m = s.match(/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{4})$/);
    if (m) return new Date(parseInt(m[3], 10), parseInt(m[2], 10) - 1, parseInt(m[1], 10));
    m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (m) return new Date(parseInt(m[1], 10), parseInt(m[2], 10) - 1, parseInt(m[3], 10));
    m = s.match(/^(\d{4})-(\d{2})$/);
    if (m) return new Date(parseInt(m[1], 10), parseInt(m[2], 10) - 1, 1);
    return window.flatpickr.parseDate(s, format);
  }

  function copyLook(input, instance) {
    var alt = instance.altInput;
    if (!alt) return;
    alt.className = ((input.className || '') + ' volta-date-display').trim();
    var style = input.getAttribute('style');
    if (style) alt.setAttribute('style', style);
    alt.required = input.required;
    alt.disabled = input.disabled;
    if (input.getAttribute('aria-label')) alt.setAttribute('aria-label', input.getAttribute('aria-label'));
    if (input.id && !alt.id) {
      alt.id = input.id + '__display';
      var labels = document.querySelectorAll('label[for="' + input.id + '"]');
      for (var i = 0; i < labels.length; i++) labels[i].setAttribute('for', alt.id);
    }
    if (instance.calendarContainer) instance.calendarContainer.classList.add('volta-flatpickr');
  }

  function baseOptions(input) {
    return {
      altInput: true,
      allowInput: true,
      disableMobile: true,
      locale: 'ro',
      monthSelectorType: 'static',
      parseDate: parseEuropean,
      minDate: input.getAttribute('min') || null,
      maxDate: input.getAttribute('max') || null,
      onReady: function (selectedDates, dateStr, instance) { copyLook(input, instance); }
    };
  }

  function initDate(input) {
    var opts = baseOptions(input);
    opts.dateFormat = 'Y-m-d';
    opts.altFormat = 'd.m.Y';
    window.flatpickr(input, opts);
  }

  function initMonth(input) {
    if (typeof window.monthSelectPlugin !== 'function') return;
    var opts = baseOptions(input);
    opts.dateFormat = 'Y-m';
    opts.altFormat = 'F Y';
    opts.plugins = [new window.monthSelectPlugin({ shorthand: false, dateFormat: 'Y-m', altFormat: 'F Y' })];
    window.flatpickr(input, opts);
  }

  function init(root) {
    if (typeof window.flatpickr !== 'function') return;
    var scope = root || document;
    var inputs = scope.querySelectorAll('input[type="date"], input[type="month"]');
    for (var i = 0; i < inputs.length; i++) {
      var input = inputs[i];
      if (input._flatpickr || input.hasAttribute('data-native-date')) continue;
      if (input.type === 'month') initMonth(input); else initDate(input);
    }
  }

  function set(input, value) {
    if (!input) return;
    if (input._flatpickr) {
      if (value) input._flatpickr.setDate(value, false);
      else input._flatpickr.clear(false);
    } else {
      input.value = value === 'today' ? todayIso() : (value || '');
    }
  }

  function todayIso() {
    var d = new Date();
    var mm = String(d.getMonth() + 1).padStart(2, '0');
    var dd = String(d.getDate()).padStart(2, '0');
    return d.getFullYear() + '-' + mm + '-' + dd;
  }

  window.VoltaDateInputs = { init: init, set: set };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { init(); });
  } else {
    init();
  }
})();
