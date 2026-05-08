(function () {
  'use strict';

  function hasExcelJS() {
    return typeof window.ExcelJS !== 'undefined';
  }

  function ensureExcelJS() {
    if (!hasExcelJS()) {
      throw new Error('Biblioteca ExcelJS nu este incarcata.');
    }
  }

  function nowStamp() {
    const d = new Date();
    const pad = function (n) { return String(n).padStart(2, '0'); };
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + '_' + pad(d.getHours()) + '-' + pad(d.getMinutes());
  }

  function safeFileName(value) {
    const base = String(value || 'export').trim();
    return (base || 'export').replace(/[\\/:*?"<>|]+/g, '-');
  }

  function sheetName(name) {
    return String(name || 'Date').slice(0, 31);
  }

  function argb(hex) {
    const v = String(hex || '').replace('#', '').toUpperCase();
    if (v.length === 8) return v;
    if (v.length === 6) return 'FF' + v;
    return 'FF' + v.padStart(6, '0').slice(0, 6);
  }

  function createWorkbook() {
    ensureExcelJS();
    const wb = new window.ExcelJS.Workbook();
    wb.creator = 'VOLTA';
    wb.lastModifiedBy = 'VOLTA';
    wb.created = new Date();
    wb.modified = new Date();
    return wb;
  }

  function textValue(value) {
    if (value === null || value === undefined) return '';
    return String(value).trim();
  }

  function isNumericValue(value) {
    if (typeof value === 'number') return true;
    if (typeof value !== 'string') return false;
    const t = value.replace(/\s+/g, '').replace(',', '.');
    return t !== '' && !Number.isNaN(Number(t));
  }

  function coerceValue(value) {
    if (typeof value === 'number' || value === null || value === undefined) return value;
    if (typeof value !== 'string') return value;
    const t = value.trim();
    if (t === '') return '';
    if (/^[+-]?\d+([.,]\d+)?$/.test(t)) {
      const n = Number(t.replace(',', '.'));
      if (!Number.isNaN(n)) return n;
    }
    return value;
  }

  function maxCols(aoa) {
    return (aoa || []).reduce(function (max, row) {
      return Math.max(max, Array.isArray(row) ? row.length : 0);
    }, 0);
  }

  function normalizeAoa(aoa, coerceNumbers) {
    return (aoa || []).map(function (row) {
      return (Array.isArray(row) ? row : []).map(function (value) {
        return coerceNumbers ? coerceValue(value) : value;
      });
    });
  }

  function buildTableAoa(table) {
    const rows = [];
    if (!table) return rows;
    const trs = table.querySelectorAll('tr');
    trs.forEach(function (tr) {
      const row = [];
      tr.querySelectorAll('th,td').forEach(function (cell) {
        row.push(textValue(cell.innerText || cell.textContent));
      });
      rows.push(row);
    });
    return rows;
  }

  function addRows(ws, aoa) {
    (aoa || []).forEach(function (row) {
      ws.addRow(row);
    });
  }

  function rowValues(ws, rowNumber) {
    const row = ws.getRow(rowNumber);
    const values = [];
    for (let c = 1; c <= row.cellCount; c += 1) {
      const v = row.getCell(c).value;
      values.push(v);
    }
    return values;
  }

  function nonEmptyValues(values) {
    return (values || []).filter(function (v) {
      return textValue(v) !== '';
    });
  }

  function hasOnlyOneTextValue(values) {
    const filled = nonEmptyValues(values);
    return filled.length === 1 && typeof filled[0] === 'string';
  }

  function isHeaderLike(values) {
    const filled = nonEmptyValues(values);
    if (filled.length < 2) return false;
    return filled.every(function (v) {
      return typeof v === 'string' && !isNumericValue(v);
    });
  }

  function isTotalRow(values) {
    const first = textValue(values[0]).toUpperCase();
    return first === 'TOTAL' || first.indexOf('TOTAL') === 0;
  }

  function autoFitColumns(ws, startRow) {
    const widths = [];
    const start = startRow || 1;
    for (let c = 1; c <= ws.columnCount; c += 1) {
      let max = 10;
      for (let r = start; r <= ws.rowCount; r += 1) {
        const cell = ws.getRow(r).getCell(c);
        const v = cell.value;
        if (v === null || v === undefined || v === '') continue;
        const len = textValue(v).length;
        if (len > max) max = len;
      }
      widths[c - 1] = { width: Math.min(Math.max(max + 2, 10), 36) };
    }
    ws.columns = widths;
  }

  function setCellStyle(cell, style) {
    if (!cell) return;
    if (style.font) cell.font = style.font;
    if (style.fill) cell.fill = style.fill;
    if (style.border) cell.border = style.border;
    if (style.alignment) cell.alignment = style.alignment;
    if (style.numFmt) cell.numFmt = style.numFmt;
  }

  function defaultBorder(color) {
    const c = argb(color);
    return {
      top: { style: 'thin', color: { argb: c } },
      bottom: { style: 'thin', color: { argb: c } },
      left: { style: 'thin', color: { argb: c } },
      right: { style: 'thin', color: { argb: c } },
    };
  }

  function mergeIfSparse(ws, rowNumber, lastCol) {
    const values = rowValues(ws, rowNumber);
    if (!hasOnlyOneTextValue(values)) return;
    if (lastCol <= 1) return;
    ws.mergeCells(rowNumber, 1, rowNumber, lastCol);
  }

  function applyBranding(ws, title, lastCol) {
    const border = defaultBorder('334155');
    const darkFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: argb('0F172A') } };
    const whiteFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: argb('FFFFFF') } };
    const softFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: argb('FFF9D6') } };

    ws.getRow(1).height = 28;
    ws.getRow(2).height = 10;
    ws.getRow(3).height = 8;

    ws.getCell('A1').value = '';
    ws.getCell('A2').value = '';
    ws.getCell('A3').value = '';
    ws.getCell('B1').value = title || 'Raport exportat';
    ws.getCell('B2').value = '';
    ws.getCell('B3').value = '';

    if (lastCol > 1) {
      ws.mergeCells(1, 2, 1, lastCol);
    }

    for (let c = 1; c <= Math.max(lastCol, 3); c += 1) {
      const cell1 = ws.getRow(1).getCell(c);
      const cell2 = ws.getRow(2).getCell(c);
      const cell3 = ws.getRow(3).getCell(c);
      setCellStyle(cell1, {
        font: { name: 'Aptos', bold: true, color: { argb: argb('FFFFFF') }, size: 18 },
        alignment: { vertical: 'middle', horizontal: 'center', wrapText: true },
        border: border,
        fill: darkFill,
      });
      setCellStyle(cell2, {
        border: border,
        fill: whiteFill,
      });
      setCellStyle(cell3, {
        border: border,
        fill: whiteFill,
      });
    }
  }

  function applyTheme(ws, options) {
    const startRow = 4;
    const border = defaultBorder('334155');
    const altFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: argb('F8FAFC') } };
    const whiteFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: argb('FFFFFF') } };
    const darkFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: argb('0F172A') } };
    const titleFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: argb('111827') } };
    const totalFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: argb('0F172A') } };
    const softFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: argb('FFF9D6') } };
    const yellowFont = { name: 'Aptos', bold: true, color: { argb: argb('FFEE00') } };

    ws.views = [{ state: 'frozen', ySplit: 4 }];

    for (let r = startRow; r <= ws.rowCount; r += 1) {
      const row = ws.getRow(r);
      const values = rowValues(ws, r);
      const filled = nonEmptyValues(values);
      const rowIsTotal = isTotalRow(values);
      const rowIsSingleTitle = hasOnlyOneTextValue(values);
      const rowIsHeader = isHeaderLike(values);

      if (rowIsSingleTitle) {
        mergeIfSparse(ws, r, ws.columnCount);
        row.height = 24;
        for (let c = 1; c <= ws.columnCount; c += 1) {
          const cell = row.getCell(c);
          setCellStyle(cell, {
            font: { name: 'Aptos', bold: true, size: 13, color: { argb: argb('111827') } },
            alignment: { vertical: 'middle', horizontal: 'center', wrapText: true },
            border: border,
            fill: softFill,
          });
        }
        continue;
      }

      if (rowIsHeader) {
        row.height = 22;
        for (let c = 1; c <= ws.columnCount; c += 1) {
          const cell = row.getCell(c);
          if (!cell.value && c > filled.length) continue;
          setCellStyle(cell, {
            font: yellowFont,
            alignment: { vertical: 'middle', horizontal: 'center', wrapText: true },
            border: border,
            fill: titleFill,
          });
        }
        continue;
      }

      if (rowIsTotal) {
        row.height = 24;
        for (let c = 1; c <= ws.columnCount; c += 1) {
          const cell = row.getCell(c);
          if (!cell.value && c > filled.length) continue;
          setCellStyle(cell, {
            font: { name: 'Aptos', bold: true, size: 12, color: { argb: argb('FFEE00') } },
            alignment: { vertical: 'middle', horizontal: 'center', wrapText: true },
            border: border,
            fill: totalFill,
          });
          if (isNumericValue(cell.value)) {
            cell.alignment = { vertical: 'middle', horizontal: 'right', wrapText: true };
            cell.numFmt = cell.numFmt || '#,##0.00';
          }
        }
        continue;
      }

      row.height = row.height || 20;
      const rowFill = ((r - startRow) % 2 === 0) ? whiteFill : altFill;
      for (let c = 1; c <= ws.columnCount; c += 1) {
        const cell = row.getCell(c);
        if (cell.value === null || cell.value === undefined || cell.value === '') continue;
        setCellStyle(cell, {
          font: { name: 'Aptos', size: 11, color: { argb: argb('111827') } },
          alignment: {
            vertical: 'middle',
            horizontal: c === 1 ? 'left' : (isNumericValue(cell.value) ? 'right' : 'center'),
            wrapText: true,
            indent: c === 1 ? 1 : 0,
          },
          border: border,
          fill: rowFill,
        });
        if (isNumericValue(cell.value)) {
          cell.numFmt = cell.numFmt || '#,##0.00';
        }
      }
    }

    autoFitColumns(ws, 1);
    ws.getColumn(1).width = Math.max(ws.getColumn(1).width || 0, 16);
  }

  async function buildStyledWorksheet(workbook, name, aoa, options) {
    const normalized = normalizeAoa(aoa, !!(options && options.coerceNumbers));
    const lastCol = Math.max(3, maxCols(normalized));
    const ws = workbook.addWorksheet(sheetName(name));
    ws.properties.defaultRowHeight = 20;
    applyBranding(ws, (options && options.title) || sheetName(name), lastCol);
    addRows(ws, normalized);
    applyTheme(ws, options || {});
    return ws;
  }

  async function downloadWorkbook(workbook, fileName) {
    ensureExcelJS();
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = safeFileName(fileName) + '.xlsx';
    document.body.appendChild(a);
    a.click();
    a.remove();
    setTimeout(function () {
      URL.revokeObjectURL(url);
    }, 1000);
  }

  async function exportTable(table, options) {
    ensureExcelJS();
    if (!table) {
      throw new Error('Tabelul nu a fost gasit.');
    }
    const wb = createWorkbook();
    const aoa = buildTableAoa(table);
    await buildStyledWorksheet(wb, (options && options.sheetName) || 'Tabel', aoa, {
      title: (options && options.sheetName) || 'Tabel',
      subtitle: 'Exportat la ' + new Date().toLocaleString('ro-RO'),
      coerceNumbers: false
    });
    await downloadWorkbook(wb, (options && options.fileName) || ('tabel_' + nowStamp()));
  }

  async function exportRows(headers, rows, options) {
    ensureExcelJS();
    const wb = createWorkbook();
    const aoa = [];
    if (Array.isArray(headers) && headers.length) {
      aoa.push(headers);
    }
    (rows || []).forEach(function (row) {
      aoa.push(Array.isArray(row) ? row : [row]);
    });
    await buildStyledWorksheet(wb, (options && options.sheetName) || 'Date', aoa, {
      title: (options && options.sheetName) || 'Date',
      subtitle: 'Exportat la ' + new Date().toLocaleString('ro-RO'),
      coerceNumbers: !(options && options.coerceNumbers === false)
    });
    await downloadWorkbook(wb, (options && options.fileName) || ('date_' + nowStamp()));
  }

  async function exportSheets(sheets, fileName) {
    ensureExcelJS();
    const wb = createWorkbook();
    const used = new Set();
    for (const s of (sheets || [])) {
      const rawName = (s && s.name) || 'Foaie';
      const aoa = s && s.aoa ? s.aoa : [];
      const base = sheetName(rawName);
      let name = base;
      let idx = 2;
      while (used.has(name)) {
        const suffix = ' ' + idx;
        name = sheetName(base.slice(0, Math.max(1, 31 - suffix.length)) + suffix);
        idx += 1;
      }
      used.add(name);
      await buildStyledWorksheet(wb, name, aoa, {
        title: rawName,
        subtitle: 'Exportat la ' + new Date().toLocaleString('ro-RO'),
        coerceNumbers: !(s && s.coerceNumbers === false)
      });
    }
    await downloadWorkbook(wb, fileName || ('export_' + nowStamp()));
  }

  async function exportChart(chart, options) {
    ensureExcelJS();
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
    await exportRows(header, rows, {
      fileName: (options && options.fileName) || ('grafic_' + nowStamp()),
      sheetName: (options && options.sheetName) || 'Grafic'
    });
  }

  async function exportCurrentPage(options) {
    ensureExcelJS();
    const wb = createWorkbook();
    const used = new Set();
    let found = 0;

    const charts = Array.from(document.querySelectorAll('canvas'));
    for (let i = 0; i < charts.length; i += 1) {
      const canvas = charts[i];
      const chartInstance = window.Chart && typeof window.Chart.getChart === 'function' ? window.Chart.getChart(canvas) : null;
      if (!chartInstance || !chartInstance.data) continue;
      const labels = chartInstance.data.labels || [];
      const datasets = chartInstance.data.datasets || [];
      if (!datasets.length) continue;
      const header = ['Eticheta'].concat(datasets.map(function (ds) { return ds.label || 'Serie'; }));
      const rows = labels.map(function (label, idx) {
        const row = [label];
        datasets.forEach(function (ds) {
          const val = Array.isArray(ds.data) ? ds.data[idx] : '';
          row.push(val == null ? '' : val);
        });
        return row;
      });
      const sheetTitle = 'Grafic ' + (i + 1);
      const base = sheetName(sheetTitle);
      let name = base;
      let idx = 2;
      while (used.has(name)) {
        const suffix = ' ' + idx;
        name = sheetName(base.slice(0, Math.max(1, 31 - suffix.length)) + suffix);
        idx += 1;
      }
      used.add(name);
      await buildStyledWorksheet(wb, name, [header].concat(rows), {
        title: sheetTitle,
        subtitle: 'Exportat la ' + new Date().toLocaleString('ro-RO'),
        coerceNumbers: true
      });
      found += 1;
    }

    const tables = Array.from(document.querySelectorAll('table'));
    for (let j = 0; j < tables.length; j += 1) {
      const table = tables[j];
      const rows = table.querySelectorAll('tr');
      if (!rows.length) continue;
      const aoa = buildTableAoa(table);
      const sheetTitle = 'Tabel ' + (j + 1);
      const base = sheetName(sheetTitle);
      let name = base;
      let idx = 2;
      while (used.has(name)) {
        const suffix = ' ' + idx;
        name = sheetName(base.slice(0, Math.max(1, 31 - suffix.length)) + suffix);
        idx += 1;
      }
      used.add(name);
      await buildStyledWorksheet(wb, name, aoa, {
        title: sheetTitle,
        subtitle: 'Exportat la ' + new Date().toLocaleString('ro-RO'),
        coerceNumbers: false
      });
      found += 1;
    }

    if (!found) {
      throw new Error('Pagina nu contine date exportabile (tabele/grafice).');
    }

    await downloadWorkbook(wb, (options && options.fileName) || ('export_pagina_' + nowStamp()));
  }

  window.VoltaExcelExport = {
    nowStamp: nowStamp,
    exportTable: exportTable,
    exportRows: exportRows,
    exportSheets: exportSheets,
    exportChart: exportChart,
    exportCurrentPage: exportCurrentPage,
  };
})();
