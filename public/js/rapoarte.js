 document.addEventListener("DOMContentLoaded", () => {
  const savedMonthsSelect = document.getElementById("savedMonths");
  const tbody = document.getElementById("rapoarteTbody");

  // Map lună -> link CSV public
  const sheetLinks = {
  "Ianuarie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=1494409538&single=true&output=csv",
  "Februarie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=1306671217&single=true&output=csv",
  "Martie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=1516455377&single=true&output=csv",
  "Aprilie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=677593108&single=true&output=csv",
  "Mai": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=1492087469&single=true&output=csv",
  "Iunie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=1027778141&single=true&output=csv",
  "Iulie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=1759203601&single=true&output=csv",
  "August": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=0&single=true&output=csv",
  "Septembrie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=308022222&single=true&output=csv",
  "Octombrie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=1776646642&single=true&output=csv",
  "Noiembrie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=1348206179&single=true&output=csv",
 
  // adaugă restul lunilor aici
  };

  // Populează select-ul
  Object.keys(sheetLinks).forEach(luna => {
    const option = document.createElement("option");
    option.value = luna;
    option.textContent = luna;
    savedMonthsSelect.appendChild(option);
  });

  // Funcție pentru parse CSV cu ghilimele și virgule în interior
  function parseCSVRow(row) {
    return row.split(/,(?=(?:[^"]*"[^"]*")*[^"]*$)/).map(cell => {
      if (!cell) return "";
      cell = cell.trim();
      if (cell.startsWith('"') && cell.endsWith('"')) {
        cell = cell.slice(1, -1); // elimină ghilimelele
      }
      return cell;
    });
  }

  // La schimbarea lunii
  savedMonthsSelect.addEventListener("change", async () => {
    const luna = savedMonthsSelect.value;
    if (!luna) return;

    const url = sheetLinks[luna];

    try {
      const res = await fetch(url);
      const csvText = await res.text();
      const rows = csvText.split("\n").map(r => parseCSVRow(r));
      const headers = rows[0];

      const allData = rows.slice(1).map(row => {
        const obj = {};
        headers.forEach((h, i) => obj[h.trim()] = row[i] ? row[i].trim() : "");
        return obj;
      });

      tbody.innerHTML = "";

      if (allData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6">Nu există date pentru această lună</td></tr>`;
        return;
      }

      allData.forEach(r => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${r["Дата"]}</td>
          <td>${r["Количество заказов"]}</td>
          <td>${r["Сеансы"]}</td>
          <td>${r["Конверсия покупок"]}</td>
          <td>${r["Среднии чек"]}</td>
          <td>${r["Сумма дохода"]}</td>
        `;
        tbody.appendChild(tr);
      });

    } catch (err) {
      console.error("Eroare la încărcarea CSV:", err);
      tbody.innerHTML = `<tr><td colspan="6">Eroare la încărcarea datelor</td></tr>`;
    }
  });
});
