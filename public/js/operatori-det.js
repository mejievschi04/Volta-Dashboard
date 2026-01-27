async function loadOperatorDetail(operator) {
  const tbody = document.querySelector("#detailTable tbody");
  tbody.innerHTML = "";

  try {
    const res = await fetch(`../api/operatori-det-data.php?operator=${encodeURIComponent(operator)}`);
    if (!res.ok) throw new Error("Eroare la încărcarea datelor operatorului");
    const data = await res.json();

    const months = [];
    const realizatValues = [];

    data.forEach(row => {
      months.push(row.Luna);
      realizatValues.push(Number(row.Realizat) || 0);

      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${row.Luna}</td>
        <td>${Number(row.Plan).toLocaleString("ro-RO")}</td>
        <td>${Number(row.Realizat).toLocaleString("ro-RO")}</td>
        <td>${Number(row["%"]).toLocaleString("ro-RO")}%</td>
        <td style="color:${row.Diff.startsWith("+") ? "green" : "red"}">${row.Diff}</td>
      `;
      tbody.appendChild(tr);
    });

    const ctx = document.getElementById("trendChart").getContext("2d");
    new Chart(ctx, {
      type: "line",
      data: {
        labels: months,
        datasets: [{
          label: "Realizat",
          data: realizatValues,
          borderColor: "black",
          backgroundColor: "#ffee00",
          fill: true,
          tension: 0.3
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: v => v.toLocaleString("ro-RO")
            }
          }
        }
      }
    });

  } catch (err) {
    console.error("Eroare încărcare date operator:", err);
  }
}

// Se apelează cu numele operatorului
const urlParams = new URLSearchParams(window.location.search);
const operator = urlParams.get("file");
if (operator) loadOperatorDetail(operator);
