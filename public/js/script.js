// Doar interacțiuni UI minimale (fără business logic)
const menuBtn = document.getElementById('menuBtn');
const sidebar = document.getElementById('sidebar');
menuBtn?.addEventListener('click', () => sidebar.classList.toggle('open'));

// Navigare simplă (scroll către secțiuni)
document.querySelectorAll('.nav a').forEach(a => {
  a.addEventListener('click', () => {
    // evidențiere link activ
    document.querySelectorAll('.nav a').forEach(x => x.classList.remove('active'));
    a.classList.add('active');
  })
});

// Animații simple
const style = document.createElement('style');
style.textContent = `
  @keyframes slide { from { transform: translateX(-100%);} to { transform: translateX(100%);} }
  @keyframes pulse { 0%,100% { opacity:.5; } 50% { opacity:1; } }
`;
document.head.appendChild(style);

// ---------------- CSV PARSER CORECT ----------------
function parseCSV(str) {
  const rows = [];
  let cur = '', row = [], insideQuotes = false;

  for (let i = 0; i < str.length; i++) {
    const c = str[i];
    if (c === '"') {
      if (insideQuotes && str[i + 1] === '"') {
        cur += '"'; // ghilimele escapate
        i++;
      } else {
        insideQuotes = !insideQuotes;
      }
    } else if (c === ',' && !insideQuotes) {
      row.push(cur);
      cur = '';
    } else if ((c === '\n' || c === '\r') && !insideQuotes) {
      if (cur || row.length) {
        row.push(cur);
        rows.push(row);
      }
      cur = '';
      row = [];
      if (c === '\r' && str[i + 1] === '\n') i++; // skip CRLF
    } else {
      cur += c;
    }
  }
  if (cur || row.length) {
    row.push(cur);
    rows.push(row);
  }
  return rows;
}

// ---------------- UTILITARE ----------------
function clean(val) {
  return val ? val.replace(/^"+|"+$/g, '').replace(/\r/g, '').replace(/"/g, '').trim() : "";
}

function toNumber(val) {
  if (!val) return 0;
  val = val.replace(/\./g, '').replace(',', '.'); // 1.234,56 -> 1234.56
  const n = parseFloat(val);
  return isNaN(n) ? 0 : n;
}

function formatNumber(val) {
  return new Intl.NumberFormat('ro-RO').format(val);
}

// ---------------- RENDER TABEL ----------------
function renderSheetTable(data) {
  const thead = document.querySelector("#sheetTable thead");
  const tbody = document.querySelector("#sheetTable tbody");
  thead.innerHTML = "";
  tbody.innerHTML = "";

  if (data.length === 0) return;

  // Afiseaza datele in consola
  console.log("Date tabel:", data);

  // header
  const headRow = document.createElement("tr");
  data[0].forEach(cell => {
    const th = document.createElement("th");
    th.textContent = cell;
    headRow.appendChild(th);
  });
  thead.appendChild(headRow);

  // body
  data.slice(1).forEach(row => {
    const tr = document.createElement("tr");
    row.forEach(cell => {
      const td = document.createElement("td");
      td.textContent = cell;
      tr.appendChild(td);
    });
    tbody.appendChild(tr);
  });
}

