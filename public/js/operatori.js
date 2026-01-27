async function loadMainTable() {
  // Verifică dacă există tabelul vechi (pentru compatibilitate)
  const tbody = document.querySelector("#mainTable tbody");
  if (!tbody) {
    // Tabelul vechi nu există, probabil folosim noul sistem Laravel
    // Nu facem nimic, datele vin deja din controller
    return;
  }

  tbody.innerHTML = "";

  try {
    const res = await fetch("../api/operatori-data.php");
    if (!res.ok) throw new Error("Eroare la încărcarea datelor: " + res.status);
    
    const data = await res.json();
    console.log("Date încărcate:", data); // DEBUG

    data.forEach(row => {
      const tr = document.createElement("tr");
      
      const plan = Number(row.Plan) || 0;
      const realizat = Number(row.Realizat) || 0;
      const pct = Number(row["%"]) || 0;
      
      // Diff numeric
      let diffVal = row.Diff ? parseFloat(row.Diff.replace(/\./g, '').replace(',', '')) : 0;
      let diffText = row.Diff || "0";

      tr.innerHTML = `
        <td>${row.Operator || "-"}</td>
        <td>${row.Luna || "-"}</td>
        <td>${plan.toLocaleString("ro-RO")}</td>
        <td>${realizat.toLocaleString("ro-RO")}</td>
        <td>${pct.toLocaleString("ro-RO")}%</td>
        <td style="color:${diffVal >= 0 ? "green" : "red"}">${diffText}</td>
      `;

      tbody.appendChild(tr);
    });

  } catch (err) {
    console.error("Eroare încărcare date:", err);
  }
}

document.addEventListener("DOMContentLoaded", loadMainTable);
