/**
 * Temă comună Chart.js — aliniată UI dark Volta (slate + accent discret).
 * Folosită când există window.Chart; nu depinde de Chart la încărcare.
 */
(function (global) {
  var C = {
    textPrimary: "rgb(248, 250, 252)",
    textSecondary: "rgb(203, 213, 225)",
    textMuted: "rgb(148, 163, 184)",
    grid: "rgba(148, 163, 184, 0.12)",
    gridAxis: "rgba(148, 163, 184, 0.2)",
    border: "rgb(51, 65, 85)",
    surface: "rgba(30, 41, 59, 0.96)",
    brand: "rgb(255, 238, 0)",
    font: "'Noto Sans', system-ui, -apple-system, sans-serif",
  };

  function isMobile() {
    return typeof global.innerWidth === "number" && global.innerWidth <= 768;
  }

  function ticks(isSmall, isLarge) {
    var m = isMobile();
    return {
      color: C.textSecondary,
      font: { family: C.font, size: m ? isSmall : isLarge, weight: "500" },
      padding: m ? 6 : 10,
    };
  }

  function gridLines(extra) {
    var g = {
      color: C.grid,
      drawBorder: false,
      lineWidth: 1,
      borderDash: [4, 4],
    };
    if (extra && typeof extra === "object") {
      for (var k in extra) {
        if (Object.prototype.hasOwnProperty.call(extra, k)) g[k] = extra[k];
      }
    }
    return g;
  }

  function tooltip() {
    return {
      enabled: true,
      backgroundColor: C.surface,
      titleColor: C.textPrimary,
      bodyColor: C.textSecondary,
      borderColor: C.border,
      borderWidth: 1,
      padding: 12,
      cornerRadius: 10,
      titleFont: { family: C.font, size: 13, weight: "600" },
      bodyFont: { family: C.font, size: 12, weight: "500" },
      displayColors: true,
      boxPadding: 6,
      caretSize: 6,
    };
  }

  function legendTop(isMobileView) {
    return {
      display: true,
      position: "top",
      align: "center",
      labels: {
        color: C.textSecondary,
        font: { family: C.font, size: isMobileView ? 11 : 13, weight: "500" },
        padding: isMobileView ? 10 : 14,
        usePointStyle: true,
        pointStyle: "circle",
        boxWidth: isMobileView ? 8 : 10,
        boxHeight: isMobileView ? 8 : 10,
      },
    };
  }

  /**
   * Opțiuni implicite pentru grafice cartesiene (bar / line / mixed).
   * @param {object} [overrides] — merge adânc doar la primul nivel în plugins/scales dacă e nevoie, altfel înlocuiește chei.
   */
  function cartesianDefaults(overrides) {
    var m = isMobile();
    var base = {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: "index", intersect: false },
      animation: {
        duration: m ? 500 : 900,
        easing: "easeOutQuart",
      },
      plugins: {
        legend: legendTop(m),
        tooltip: tooltip(),
      },
      scales: {
        x: {
          ticks: Object.assign(ticks(9, 12), {
            maxRotation: m ? 45 : 0,
            minRotation: m ? 45 : 0,
          }),
          grid: gridLines(),
          border: { display: false },
        },
        y: {
          beginAtZero: true,
          ticks: ticks(10, 12),
          grid: gridLines(),
          border: { display: false },
        },
      },
    };
    if (!overrides) return base;
    return deepMerge(base, overrides);
  }

  function deepMerge(a, b) {
    var out = {};
    var k;
    for (k in a) {
      if (Object.prototype.hasOwnProperty.call(a, k)) out[k] = a[k];
    }
    for (k in b) {
      if (!Object.prototype.hasOwnProperty.call(b, k)) continue;
      if (
        b[k] &&
        typeof b[k] === "object" &&
        !Array.isArray(b[k]) &&
        typeof a[k] === "object" &&
        a[k] &&
        !Array.isArray(a[k])
      ) {
        out[k] = deepMerge(a[k], b[k]);
      } else {
        out[k] = b[k];
      }
    }
    return out;
  }

  global.VoltaChartTheme = {
    colors: C,
    font: C.font,
    isMobile: isMobile,
    ticks: ticks,
    gridLines: gridLines,
    tooltip: tooltip,
    legendTop: legendTop,
    cartesianDefaults: cartesianDefaults,
  };
})(typeof window !== "undefined" ? window : this);
