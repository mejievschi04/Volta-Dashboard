/**
 * Temă comună Chart.js — aliniată UI dark Volta (slate + accent discret).
 * Folosită când există window.Chart; nu depinde de Chart la încărcare.
 */
(function (global) {
  var C = {
    textPrimary: "rgb(248, 250, 252)",
    textSecondary: "rgb(203, 213, 225)",
    textMuted: "rgb(148, 163, 184)",
    grid: "rgba(148, 163, 184, 0.10)",
    gridAxis: "rgba(148, 163, 184, 0.18)",
    border: "rgba(71, 85, 105, 0.7)",
    surface: "rgba(15, 23, 42, 0.96)",
    brand: "rgb(250, 204, 21)",
    brandSoft: "rgba(250, 204, 21, 0.2)",
    font: "'Noto Sans', system-ui, -apple-system, sans-serif",
    series: {
      amber: { line: "rgb(250, 204, 21)", area: "rgba(250, 204, 21, 0.2)" },
      cyan: { line: "rgb(6, 182, 212)", area: "rgba(6, 182, 212, 0.2)" },
      violet: { line: "rgb(167, 139, 250)", area: "rgba(167, 139, 250, 0.2)" },
      emerald: { line: "rgb(16, 185, 129)", area: "rgba(16, 185, 129, 0.2)" },
      rose: { line: "rgb(244, 63, 94)", area: "rgba(244, 63, 94, 0.2)" },
      slate: { line: "rgb(148, 163, 184)", area: "rgba(148, 163, 184, 0.2)" },
    },
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
      backgroundColor: "rgba(2, 6, 23, 0.92)",
      titleColor: C.brand,
      bodyColor: C.textPrimary,
      borderColor: "rgba(250, 204, 21, 0.28)",
      borderWidth: 1,
      padding: 12,
      cornerRadius: 14,
      titleFont: { family: C.font, size: 13, weight: "700" },
      bodyFont: { family: C.font, size: 12, weight: "500" },
      displayColors: true,
      boxPadding: 8,
      boxWidth: 10,
      boxHeight: 10,
      usePointStyle: true,
      caretSize: 7,
      caretPadding: 10,
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
  /**
   * Gradient vertical în zona graficului (pentru bare / umpleri).
   * @param {CanvasRenderingContext2D} ctx
   * @param {{top:number,bottom:number,left:number,right:number}|undefined} chartArea
   * @param {{offset:number,color:string}[]} stops
   */
  function verticalGradient(ctx, chartArea, stops) {
    if (!chartArea || !stops || !stops.length) return stops && stops[0] ? stops[0].color : C.brand;
    var g = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
    for (var i = 0; i < stops.length; i++) {
      g.addColorStop(stops[i].offset, stops[i].color);
    }
    return g;
  }

  function cartesianDefaults(overrides) {
    var m = isMobile();
    var base = {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: "index", intersect: false },
      animation: {
        duration: m ? 520 : 1100,
        easing: "easeOutCubic",
      },
      layout: {
        padding: { top: 10, right: 8, bottom: 6, left: 4 },
      },
      elements: {
        line: {
          borderJoinStyle: "round",
          borderCapStyle: "round",
          borderWidth: m ? 2.2 : 2.6,
        },
        bar: {
          borderRadius: m ? 8 : 11,
          borderSkipped: false,
        },
        point: {
          hoverBorderWidth: 2,
        },
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
          grid: gridLines({ lineWidth: 1, borderDash: [2, 5] }),
          border: { display: false },
        },
        y: {
          beginAtZero: true,
          ticks: ticks(10, 12),
          grid: gridLines({ lineWidth: 1, borderDash: [2, 5] }),
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
    verticalGradient: verticalGradient,
    getSeriesPalette: function () {
      return C.series;
    },
    /** Preseturi gradient pentru bare dashboard */
    barGradients: {
      brand: function (ctx, chartArea) {
        return verticalGradient(ctx, chartArea, [
          { offset: 0, color: "rgba(255, 238, 0, 0.12)" },
          { offset: 0.45, color: "rgba(255, 238, 0, 0.42)" },
          { offset: 1, color: "rgba(250, 204, 21, 0.92)" },
        ]);
      },
      coral: function (ctx, chartArea) {
        return verticalGradient(ctx, chartArea, [
          { offset: 0, color: "rgba(251, 113, 133, 0.12)" },
          { offset: 0.5, color: "rgba(251, 113, 133, 0.45)" },
          { offset: 1, color: "rgba(244, 63, 94, 0.88)" },
        ]);
      },
      sky: function (ctx, chartArea) {
        return verticalGradient(ctx, chartArea, [
          { offset: 0, color: "rgba(56, 189, 248, 0.1)" },
          { offset: 0.5, color: "rgba(96, 165, 250, 0.42)" },
          { offset: 1, color: "rgba(59, 130, 246, 0.88)" },
        ]);
      },
    },
  };
})(typeof window !== "undefined" ? window : this);
