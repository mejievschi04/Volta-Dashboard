/*
 * Tema „Neon Glass”: înlocuiește galbenul rămas în atributele style="" și în culorile graficelor Chart.js.
 * Aceeași paletă ca scripts/build-neon-recolor.mjs (păstrează formatul identic, selectorii [style*=] depind de el).
 */
(function () {
  'use strict';

  if (document.documentElement.getAttribute('data-theme') !== 'neon-glass') return;

  var RGB = { main: [0, 229, 255], deep: [0, 200, 240], dark: [0, 184, 212], light: [125, 249, 255] };
  var HEX = {
    ffee00: 'main', fe0: 'main', fff000: 'main', ffe600: 'main',
    facc15: 'deep', eab308: 'deep', ffd700: 'deep',
    e6d600: 'dark', e6d800: 'dark', f5e100: 'dark', f7d900: 'dark', f5e000: 'dark',
    fde047: 'light', fff35c: 'light'
  };
  var RGB_IN = {
    '255,238,0': 'main', '250,204,21': 'deep', '234,179,8': 'deep', '255,215,0': 'deep',
    '230,216,0': 'dark', '253,224,71': 'light', '255,247,120': 'light'
  };
  var HAS_YELLOW = /#(ffee00|fe0|fff000|ffe600|facc15|eab308|ffd700|e6d600|e6d800|f5e100|f7d900|f5e000|fde047|fff35c)\b|rgba?\(\s*(255\s*,\s*238\s*,\s*0|250\s*,\s*204\s*,\s*21|234\s*,\s*179\s*,\s*8|255\s*,\s*215\s*,\s*0|230\s*,\s*216\s*,\s*0|253\s*,\s*224\s*,\s*71|255\s*,\s*247\s*,\s*120)/i;

  function hexOf(k) {
    return '#' + RGB[k].map(function (n) { return n.toString(16).padStart(2, '0'); }).join('');
  }

  function recolor(text) {
    if (typeof text !== 'string' || !HAS_YELLOW.test(text)) return text;
    return text
      .replace(/#([0-9a-f]{6}|[0-9a-f]{3})\b/gi, function (m, h) {
        var k = HEX[h.toLowerCase()];
        return k ? hexOf(k) : m;
      })
      .replace(/rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(,\s*[\d.]+\s*)?\)/gi, function (m, r, g, b, a) {
        var k = RGB_IN[r + ',' + g + ',' + b];
        if (!k) return m;
        var c = RGB[k];
        return a ? 'rgba(' + c[0] + ', ' + c[1] + ', ' + c[2] + a.replace(/\s+/g, ' ') + ')' : 'rgb(' + c[0] + ', ' + c[1] + ', ' + c[2] + ')';
      });
  }

  function fixElement(el) {
    if (!el || el.nodeType !== 1) return;
    var style = el.getAttribute('style');
    if (style && HAS_YELLOW.test(style)) el.setAttribute('style', recolor(style));
    ['fill', 'stroke', 'stop-color', 'color'].forEach(function (attr) {
      var v = el.getAttribute(attr);
      if (v && HAS_YELLOW.test(v)) el.setAttribute(attr, recolor(v));
    });
  }

  function fixTree(root) {
    if (!root || root.nodeType !== 1) return;
    fixElement(root);
    var nodes = root.querySelectorAll('[style], [fill], [stroke], [stop-color], font[color]');
    for (var i = 0; i < nodes.length; i++) fixElement(nodes[i]);
  }

  var COLOR_KEYS = ['backgroundColor', 'borderColor', 'pointBackgroundColor', 'pointBorderColor',
    'pointHoverBackgroundColor', 'pointHoverBorderColor', 'hoverBackgroundColor', 'hoverBorderColor', 'color'];

  function fixDataset(ds) {
    COLOR_KEYS.forEach(function (key) {
      var v = ds[key];
      if (typeof v === 'string') ds[key] = recolor(v);
      else if (Array.isArray(v)) ds[key] = v.map(recolor);
    });
  }

  /* Chart.js își memorează culorile la creare, deci configurația se recolorează înainte de constructor. */
  function wrapChart(Base) {
    if (typeof Base !== 'function' || Base.__voltaNeon) return Base;
    var NeonChart = function (item, config) {
      if (config && config.data && Array.isArray(config.data.datasets)) config.data.datasets.forEach(fixDataset);
      return Reflect.construct(Base, [item, config], new.target || NeonChart);
    };
    NeonChart.prototype = Base.prototype;
    Object.setPrototypeOf(NeonChart, Base);
    NeonChart.__voltaNeon = true;
    return NeonChart;
  }

  var currentChart = wrapChart(window.Chart);
  try {
    Object.defineProperty(window, 'Chart', {
      configurable: true,
      enumerable: true,
      get: function () { return currentChart; },
      set: function (value) { currentChart = wrapChart(value); }
    });
  } catch (e) {}

  function start() {
    fixTree(document.body);
    new MutationObserver(function (mutations) {
      for (var i = 0; i < mutations.length; i++) {
        var m = mutations[i];
        if (m.type === 'attributes') fixElement(m.target);
        else for (var j = 0; j < m.addedNodes.length; j++) fixTree(m.addedNodes[j]);
      }
    }).observe(document.body, { subtree: true, childList: true, attributes: true, attributeFilter: ['style', 'fill', 'stroke', 'stop-color'] });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
