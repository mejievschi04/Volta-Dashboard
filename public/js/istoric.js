async function loadIstoric(period = 'all') {
  const url = `../api/api-istoric.php?period=${period}`;
  console.log('Încărcare date pentru perioada:', period, 'URL:', url);

  try {
    const response = await fetch(url);
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    const data = await response.json();
    console.log('Date primite:', data.length, 'înregistrări');

    // Selectăm tabelul din HTML
    const tbody = document.querySelector("#istoricTable tbody");
    tbody.innerHTML = "";

    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="10" style="text-align: center; color: #FFEE00;">Nu există date pentru perioada selectată.</td></tr>`;
      return;
    }

    // Generăm rândurile tabelului
    data.forEach(row => {
      const tr = document.createElement("tr");

      tr.innerHTML = `
        <td>${row.luna}</td>
        <td>${row.progres_plan}%</td>
        <td>${row.plan_luna}</td>
        <td>${row.vanzari_luna}</td>
        <td>${row.diferenta_plan}</td>
        <td>${row.comenzi}</td>
        <td>${row.comenzi_zi}</td>
        <td>${row.sesiuni}</td>
        <td>${row.conversie}</td>
        <td class="${row.diff_class || ''}">${row.vanzari_vs_anterioara || ''}</td>
      `;

      tbody.appendChild(tr);
    });

  } catch (err) {
    console.error("Eroare la încărcarea datelor:", err);
    const tbody = document.querySelector("#istoricTable tbody");
    tbody.innerHTML = `<tr><td colspan="10" style="text-align: center; color: #EF4444;">Eroare la încărcarea datelor: ${err.message}</td></tr>`;
  }
}

// Funcție pentru inițializare
function initIstoric() {
  const periodSelect = document.getElementById('periodSelect');
  
  if (periodSelect) {
    console.log('Selector găsit, valoare inițială:', periodSelect.value);
    
    // Încărcăm datele cu perioada selectată inițial
    loadIstoric(periodSelect.value);
    
    // Adăugăm event listener pentru schimbarea perioadei
    periodSelect.addEventListener('change', function() {
      console.log('Perioadă schimbată la:', this.value);
      loadIstoric(this.value);
    });
    
    // Stilizare hover pentru selector
    periodSelect.addEventListener('mouseenter', function() {
      this.style.borderColor = 'rgba(255, 238, 0, 0.6)';
      this.style.boxShadow = '0 0 15px rgba(255, 238, 0, 0.3)';
    });
    
    periodSelect.addEventListener('mouseleave', function() {
      this.style.borderColor = 'rgba(255, 238, 0, 0.3)';
      this.style.boxShadow = 'none';
    });
    
    periodSelect.addEventListener('focus', function() {
      this.style.borderColor = '#FFEE00';
      this.style.boxShadow = '0 0 20px rgba(255, 238, 0, 0.5)';
    });
    
    periodSelect.addEventListener('blur', function() {
      this.style.borderColor = 'rgba(255, 238, 0, 0.3)';
      this.style.boxShadow = 'none';
    });
  } else {
    console.warn('Selectorul periodSelect nu a fost găsit!');
    // Dacă nu există selector, încărcăm toate datele
    loadIstoric('all');
  }
}

// Adăugăm event listener pentru selectorul de perioadă
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initIstoric);
} else {
  // DOM-ul este deja încărcat
  initIstoric();
}
