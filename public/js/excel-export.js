(function () {
  'use strict';

  function hasXlsx() {
    return typeof window.XLSX !== 'undefined';
  }

  function ensureXlsx() {
    if (!hasXlsx()) {
      throw new Error('Biblioteca XLSX nu este incarcata.');
    }
  }

  function safeFileName(value) {
    const base = String(value || 'export').trim();
    return (base || 'export').replace(/[\\/:*?"<>|]+/g, '-');
  }

  function nowStamp() {
    const d = new Date();
    const pad = function (n) { return String(n).padStart(2, '0'); };
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + '_' + pad(d.getHours()) + '-' + pad(d.getMinutes());
  }

  function sheetName(name) {
    return String(name || 'Date').slice(0, 31);
  }

  function downloadWorkbook(workbook, fileName) {
    ensureXlsx();
    window.XLSX.writeFile(workbook, safeFileName(fileName) + '.xlsx');
  }

  function createWorkbook() {
    ensureXlsx();
    return window.XLSX.utils.book_new();
  }

  function appendAoaSheet(workbook, aoa, name) {
    const ws = window.XLSX.utils.aoa_to_sheet(aoa);
    window.XLSX.utils.book_append_sheet(workbook, ws, sheetName(name));
  }

  function appendUniqueAoaSheet(workbook, aoa, desiredName, usedNames) {
    var base = sheetName(desiredName || 'Date');
    var name = base;
    var idx = 2;
    while (usedNames.has(name)) {
      var suffix = ' ' + idx;
      name = sheetName(base.slice(0, Math.max(1, 31 - suffix.length)) + suffix);
      idx += 1;
    }
    usedNames.add(name);
    appendAoaSheet(workbook, aoa, name);
  }

  function exportTable(table, options) {
    ensureXlsx();
    if (!table) {
      throw new Error('Tabelul nu a fost gasit.');
    }
    const wb = createWorkbook();
    const ws = window.XLSX.utils.table_to_sheet(table, { raw: true });
    window.XLSX.utils.book_append_sheet(wb, ws, sheetName((options && options.sheetName) || 'Tabel'));
    downloadWorkbook(wb, (options && options.fileName) || ('tabel_' + nowStamp()));
  }

  function exportRows(headers, rows, options) {
    ensureXlsx();
    const wb = createWorkbook();
    const aoa = [];
    if (Array.isArray(headers) && headers.length) {
      aoa.push(headers);
    }
    (rows || []).forEach(function (row) { aoa.push(row); });
    appendAoaSheet(wb, aoa, (options && options.sheetName) || 'Date');
    downloadWorkbook(wb, (options && options.fileName) || ('date_' + nowStamp()));
  }

  function exportChart(chart, options) {
    ensureXlsx();
    if (!chart || !chart.data) {
      throw new Error('Graficul nu este disponibil.');
    }

    const labels = chart.data.labels || [];
    const datasets = chart.data.datasets || [];
    const header = ['Eticheta'].concat(datasets.map(function (ds) { return ds.label || 'Serie'; }));
    const rows = labels.map(function (label, i) {
      const row = [label];
      datasets.forEach(function (ds) {
        const val = Array.isArray(ds.data) ? ds.data[i] : '';
        row.push(val == null ? '' : val);
      });
      return row;
    });

    exportRows(header, rows, {
      fileName: (options && options.fileName) || ('grafic_' + nowStamp()),
      sheetName: (options && options.sheetName) || 'Grafic'
    });
  }

  function exportCurrentPage(options) {
    ensureXlsx();
    var wb = createWorkbook();
    var usedNames = new Set();
    var found = 0;

    var charts = Array.from(document.querySelectorAll('canvas'));
    charts.forEach(function (canvas, index) {
      var chartInstance = null;
      if (window.Chart && typeof window.Chart.getChart === 'function') {
        chartInstance = window.Chart.getChart(canvas);
      }
      if (!chartInstance || !chartInstance.data) {
        return;
      }
      var labels = chartInstance.data.labels || [];
      var datasets = chartInstance.data.datasets || [];
      if (!datasets.length) return;
      var header = ['Eticheta'].concat(datasets.map(function (ds) { return ds.label || 'Serie'; }));
      var rows = labels.map(function (label, i) {
        var row = [label];
        datasets.forEach(function (ds) {
          var val = Array.isArray(ds.data) ? ds.data[i] : '';
          row.push(val == null ? '' : val);
        });
        return row;
      });
      appendUniqueAoaSheet(wb, [header].concat(rows), 'Grafic ' + (index + 1), usedNames);
      found += 1;
    });

    var tables = Array.from(document.querySelectorAll('table'));
    tables.forEach(function (table, index) {
      var rows = table.querySelectorAll('tr');
      if (!rows.length) return;
      var ws = window.XLSX.utils.table_to_sheet(table, { raw: true });
      var baseName = 'Tabel ' + (index + 1);
      var name = sheetName(baseName);
      var idx = 2;
      while (usedNames.has(name)) {
        var suffix = ' ' + idx;
        name = sheetName(baseName.slice(0, Math.max(1, 31 - suffix.length)) + suffix);
        idx += 1;
      }
      usedNames.add(name);
      window.XLSX.utils.book_append_sheet(wb, ws, name);
      found += 1;
    });

    if (!found) {
      throw new Error('Pagina nu conține date exportabile (tabele/grafice).');
    }

    var fileName = (options && options.fileName)
      || ('export_pagina_' + nowStamp());
    downloadWorkbook(wb, fileName);
  }

  window.VoltaExcelExport = {
    nowStamp: nowStamp,
    exportTable: exportTable,
    exportRows: exportRows,
    exportChart: exportChart,
    exportCurrentPage: exportCurrentPage,
  };
})();
