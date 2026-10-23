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
    border: "rgba(71, 85, 105, 0.7)",
    surface: "rgba(30, 41, 59, 0.96)",
    /** Accent principal Volta (galben curat) */
    brand: "rgb(255, 238, 0)",
    brandSoft: "rgba(255, 238, 0, 0.16)",
    font: "'Noto Sans', system-ui, -apple-system, sans-serif",
    series: {
      amber: { line: "rgb(255, 238, 0)", area: "rgba(255, 238, 0, 0.14)" },
      cyan: { line: "rgb(180, 188, 204)", area: "rgba(180, 188, 204, 0.16)" },
      violet: { line: "rgb(167, 139, 250)", area: "rgba(167, 139, 250, 0.16)" },
      emerald: { line: "rgb(52, 211, 153)", area: "rgba(52, 211, 153, 0.16)" },
      rose: { line: "rgb(251, 113, 133)", area: "rgba(251, 113, 133, 0.16)" },
      slate: { line: "rgb(148, 163, 184)", area: "rgba(148, 163, 184, 0.14)" },
    },
  };

  var docEl = global.document && global.document.documentElement;
  if (docEl && docEl.getAttribute("data-theme") === "neon-glass") {
    C.grid = "rgba(45, 212, 232, 0.09)";
    C.gridAxis = "rgba(45, 212, 232, 0.18)";
    C.surface = "rgba(10, 12, 32, 0.94)";
    C.brand = "rgb(45, 212, 232)";
    C.brandSoft = "rgba(45, 212, 232, 0.16)";
    C.series.amber = { line: "rgb(45, 212, 232)", area: "rgba(45, 212, 232, 0.18)" };
    C.series.cyan = { line: "rgb(214, 92, 196)", area: "rgba(214, 92, 196, 0.16)" };
    C.series.violet = { line: "rgb(146, 128, 236)", area: "rgba(146, 128, 236, 0.18)" };
    C.series.emerald = { line: "rgb(52, 211, 153)", area: "rgba(52, 211, 153, 0.16)" };
    C.series.rose = { line: "rgb(236, 112, 150)", area: "rgba(236, 112, 150, 0.18)" };
  }
  var neon = !!(docEl && docEl.getAttribute("data-theme") === "neon-glass");

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
      titleColor: C.brand,
      bodyColor: C.textPrimary,
      borderColor: neon ? "rgba(45, 212, 232, 0.35)" : "rgba(255, 238, 0, 0.25)",
      borderWidth: 1,
      padding: 12,
      cornerRadius: 10,
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
          grid: gridLines({ lineWidth: 1, borderDash: [] }),
          border: { display: false },
        },
        y: {
          beginAtZero: true,
          ticks: ticks(10, 12),
          grid: gridLines({ lineWidth: 1, borderDash: [] }),
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
    /** Culori solide (fără gradient) — compatibile cu clone JSON în modal */
    barSolid: {
      brand: neon ? "rgb(45, 212, 232)" : "rgb(255, 238, 0)",
      brandHover: neon ? "rgb(140, 232, 244)" : "rgb(255, 247, 120)",
      coral: neon ? "rgb(214, 92, 196)" : "rgb(251, 113, 133)",
      coralHover: neon ? "rgb(228, 132, 214)" : "rgb(253, 164, 175)",
      sky: neon ? "rgb(146, 128, 236)" : "rgb(180, 188, 204)",
      skyHover: neon ? "rgb(196, 176, 255)" : "rgb(212, 218, 228)",
    },
  };
})(typeof window !== "undefined" ? window : this);
