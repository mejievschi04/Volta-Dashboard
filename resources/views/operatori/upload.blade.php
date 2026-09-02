@extends('layouts.app')

@section('title', 'Încarcă Vânzări – VOLTA')

@section('content')
<div style="padding: 20px; max-width: 800px; margin: 0 auto;">
  <div style="margin-bottom: 20px;">
    <a href="{{ route('operatori.show', $operator->id) }}" style="color: #fff; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: rgba(107, 114, 128, 0.15); border-radius: 8px; border: 1px solid rgba(107, 114, 128, 0.3); transition: all 0.2s;" onmouseover="this.style.background='rgba(107, 114, 128, 0.25)'" onmouseout="this.style.background='rgba(107, 114, 128, 0.15)'">
      <i class="fas fa-arrow-left"></i> Înapoi la {{ $operator->nume }}
    </a>
  </div>

  <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border-radius: 16px; padding: 32px; border: 1px solid rgba(255, 238, 0, 0.2); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);">
    <h1 style="color: #ffee00; margin: 0 0 12px 0; font-size: 28px; font-weight: 800; display: flex; align-items: center; gap: 12px;">
      <i class="fas fa-file-excel" style="font-size: 32px;"></i>
      Încarcă Vânzări din Excel
    </h1>
    <p style="color: #9ca3af; margin: 0 0 32px 0; font-size: 14px;">
      Operator: <strong style="color: #fff;">{{ $operator->nume }}</strong>
    </p>

    <!-- Instructions -->
    <div style="background: var(--brand-10); border: 1px solid var(--brand-30); border-radius: 12px; padding: 20px; margin-bottom: 24px;">
      <h3 style="color: var(--brand); margin: 0 0 12px 0; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-info-circle"></i>Instrucțiuni
      </h3>
      <ul style="color: #9CA3AF; margin: 0; padding-left: 20px; font-size: 13px;">
        <li style="margin-bottom: 8px;">Fișierul trebuie să fie în format <strong>Excel (.xlsx, .xls)</strong> sau <strong>CSV</strong></li>
        <li style="margin-bottom: 8px;">Dimensiune maximă: <strong>10 MB</strong></li>
        <li style="margin-bottom: 8px;">Rândurile trebuie să conțină următoarele coloane (în ordine):</li>
      </ul>
      <div style="background: rgba(255, 255, 255, 0.05); border-radius: 8px; padding: 12px; margin-top: 12px; font-family: monospace; font-size: 12px; color: #9CA3AF;">
        <div style="margin-bottom: 6px;"><span style="color: #10B981;">1. Data</span> (YYYY-MM-DD, YYYY-MM, sau "ianuarie 2025")</div>
        <div style="margin-bottom: 6px;"><span style="color: #10B981;">2. Suma Fără TVA</span> (numeric, ex: 1000.50)</div>
        <div style="margin-bottom: 6px;"><span style="color: #10B981;">3. Suma Cu TVA</span> (numeric)</div>
        <div style="margin-bottom: 6px;"><span style="color: #10B981;">4. Profit</span> (numeric)</div>
        <div style="margin-bottom: 6px;"><span style="color: #10B981;">5. Nr. Vânzări</span> (întreg, opțional)</div>
      </div>
      <p style="color: #9ca3af; margin: 12px 0 0 0; font-size: 12px;">
        <strong>Formate de dată acceptate:</strong>
      </p>
      <ul style="color: #9ca3af; margin: 6px 0 0 0; padding-left: 20px; font-size: 11px;">
        <li>2025-01-15 (YYYY-MM-DD)</li>
        <li>2025-01 (YYYY-MM)</li>
        <li>ianuarie 2025 (lună în limba română + an)</li>
        <li>ian 2025 (abreviere lună + an)</li>
      </ul>
      <p style="color: #9ca3af; margin: 12px 0 0 0; font-size: 12px;">
        <strong>Notă:</strong> Rândurile cu dată goală vor fi ignorate. Dacă înregistrarea există, va fi actualizată. Datele cu lună vor fi convertite automat la prima zi a lunii (ex: 2025-01-01).
      </p>
    </div>

    <!-- Upload Form -->
    <form id="uploadForm" method="POST" action="{{ route('operatori.upload.post', $operator->id) }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px;">
      @csrf

      <div style="border: 2px dashed rgba(255, 238, 0, 0.3); border-radius: 12px; padding: 40px 20px; text-align: center; background: rgba(255, 238, 0, 0.02); cursor: pointer; transition: all 0.2s;" id="dropZone" onmouseover="this.style.borderColor='rgba(255, 238, 0, 0.6)'; this.style.background='rgba(255, 238, 0, 0.05)'" onmouseout="this.style.borderColor='rgba(255, 238, 0, 0.3)'; this.style.background='rgba(255, 238, 0, 0.02)'">
        <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv" style="display: none;" onchange="updateFileName()">

        <div id="dropZoneContent" style="display: flex; flex-direction: column; align-items: center; gap: 12px;">
          <i class="fas fa-cloud-upload-alt" style="font-size: 40px; color: #ffee00;"></i>
          <div>
            <p style="color: #fff; margin: 0 0 6px 0; font-weight: 600; font-size: 16px;">
              Trage fișierul aici sau
            </p>
            <p style="color: #ffee00; margin: 0; font-weight: 700; font-size: 14px; cursor: pointer;" onclick="document.getElementById('fileInput').click();">
              ALEGE FIȘIERUL
            </p>
          </div>
          <p style="color: #9ca3af; margin: 8px 0 0 0; font-size: 12px;">
            (.xlsx, .xls, .csv - max 10 MB)
          </p>
        </div>

        <div id="dropZoneStatus" style="display:none; margin-top:12px; color:#9CA3AF; font-size:13px;"></div>
      </div>

      <div style="display: flex; align-items: center; gap: 12px;">
        <input type="checkbox" id="overwriteCheckbox" name="overwrite" value="1" style="width: 18px; height: 18px; cursor: pointer;">
        <label for="overwriteCheckbox" style="color: #9CA3AF; font-size: 13px; cursor: pointer; margin: 0;">
          Suprascriu datele existente pentru aceleași date (dacă nu este bifat, le actualizez)
        </label>
      </div>

      <div style="display: flex; gap: 12px;">
        <button type="submit" style="flex: 1; background: linear-gradient(135deg, #ffee00 0%, #FFEE00 100%); color: #000; border: none; padding: 14px; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(0,0,0,0.35);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.45)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.35)'">
          <i class="fas fa-upload"></i> Încarcă Vânzări
        </button>
        <a href="{{ route('operatori.show', $operator->id) }}" style="flex: 1; background: rgba(255, 255, 255, 0.1); color: #fff; border: 1px solid rgba(255, 255, 255, 0.2); padding: 14px; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 6px;" onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'">
          <i class="fas fa-times"></i> Anulează
        </a>
      </div>
    </form>

    <!-- Sample File Download -->
    <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
      <h3 style="color: #fff; margin: 0 0 12px 0; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-download"></i>Fișier Model
      </h3>
      <a href="{{ route('operatori.download-template') }}" style="display: inline-flex; align-items: center; gap: 8px; background: var(--brand-10); color: var(--brand); padding: 10px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; transition: all 0.2s; border: 1px solid var(--brand-30);" onmouseover="this.style.background='var(--brand-20)'" onmouseout="this.style.background='var(--brand-10)'">
        <i class="fas fa-file-download"></i>Descarcă template Excel
      </a>
    </div>
  </div>
</div>

<script>
  // Suppress a noisy browser-extension/promise error that may come from extensions.
  // This prevents: "A listener indicated an asynchronous response by returning true, but the message channel closed before a response was received"
  window.addEventListener('unhandledrejection', function (event) {
    // Log for debugging, but prevent the error from surfacing to console as uncaught
    console.warn('Suppressed unhandledrejection:', event.reason);
    try { event.preventDefault(); } catch (e) { /* ignore */ }
  });

  // Debug logging
  function debugLog(msg) {
    console.log('[DEBUG]', msg);
    const debugDiv = document.getElementById('debugLog') || createDebugLog();
    debugDiv.innerHTML += '<div>' + new Date().toLocaleTimeString() + ': ' + msg + '</div>';
    debugDiv.scrollTop = debugDiv.scrollHeight;
  }

  function createDebugLog() {
    const div = document.createElement('div');
    div.id = 'debugLog';
    div.style.cssText = 'position: fixed; bottom: 10px; right: 10px; width: 400px; height: 200px; background: #1F2937; color: #0f0; border: 2px solid #0f0; padding: 10px; font-family: monospace; font-size: 11px; overflow-y: auto; z-index: 10000; border-radius: 5px;';
    document.body.appendChild(div);
    return div;
  }

  // Drag and drop functionality
  const dropZone = document.getElementById('dropZone');
  const fileInput = document.getElementById('fileInput');
  const uploadForm = document.getElementById('uploadForm');

  debugLog('Pagina incarcata - dropZone: ' + (dropZone ? 'OK' : 'MISSING'));
  debugLog('fileInput: ' + (fileInput ? 'OK' : 'MISSING'));
  debugLog('uploadForm: ' + (uploadForm ? 'OK' : 'MISSING'));

  dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    e.stopPropagation();
    dropZone.style.borderColor = 'rgba(255, 238, 0, 0.6)';
    dropZone.style.background = 'rgba(255, 238, 0, 0.05)';
  });

  dropZone.addEventListener('dragleave', (e) => {
    e.preventDefault();
    e.stopPropagation();
    dropZone.style.borderColor = 'rgba(255, 238, 0, 0.3)';
    dropZone.style.background = 'rgba(255, 238, 0, 0.02)';
  });

  dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    e.stopPropagation();
    dropZone.style.borderColor = 'rgba(255, 238, 0, 0.3)';
    dropZone.style.background = 'rgba(255, 238, 0, 0.02)';
    
    const files = e.dataTransfer.files;
    debugLog('DROP EVENT - files count: ' + files.length);
    if (files && files.length > 0) {
      try {
        const dataTransfer = new DataTransfer();
        for (let i = 0; i < files.length; i++) {
          dataTransfer.items.add(files[i]);
          debugLog('File ' + i + ': ' + files[i].name + ' (' + files[i].size + ' bytes)');
        }
        fileInput.files = dataTransfer.files;
        debugLog('Files assigned to input - total: ' + fileInput.files.length);
        updateFileName();
      } catch (err) {
        debugLog('ERROR in drop: ' + err.message);
      }
    }
  });

  dropZone.addEventListener('click', () => {
    debugLog('Drop zone clicked - opening file picker');
    fileInput.click();
  });

  fileInput.addEventListener('change', () => {
    debugLog('FILE INPUT CHANGED - files count: ' + fileInput.files.length);
    if (fileInput.files.length > 0) {
      debugLog('File selected: ' + fileInput.files[0].name);
    }
    updateFileName();
  });

  function updateFileName() {
    const fileName = fileInput.files[0]?.name;
    debugLog('updateFileName() - fileName: ' + fileName + ', files.length: ' + fileInput.files.length);
    const status = document.getElementById('dropZoneStatus');
    const content = document.getElementById('dropZoneContent');
    if (fileName) {
      // show status, keep input in DOM
      if (content) content.style.display = 'none';
      if (status) {
        status.style.display = 'block';
        status.innerHTML = `
          <div style="display: flex; flex-direction: column; align-items: center; gap: 12px;">
            <i class="fas fa-check-circle" style="font-size: 40px; color: #10B981;"></i>
            <div>
              <p style="color: #10B981; margin: 0 0 6px 0; font-weight: 600; font-size: 14px;">✓ Fișier selectat</p>
              <p style="color: #9CA3AF; margin: 0; font-size: 12px; word-break: break-all;">${fileName}</p>
            </div>
          </div>
        `;
      }
    } else {
      if (status) { status.style.display = 'none'; status.innerHTML = ''; }
      if (content) content.style.display = 'flex';
    }
  }

  // Submit form normally - don't use AJAX
  uploadForm.addEventListener('submit', (e) => {
    debugLog('FORM SUBMIT TRIGGERED');
    debugLog('fileInput.files.length: ' + fileInput.files.length);
    if (!fileInput.files || fileInput.files.length === 0) {
      e.preventDefault();
      debugLog('ERROR: No files selected!');
      alert('Selectează un fișier!');
    } else {
      debugLog('Submitting form with file: ' + fileInput.files[0].name);
    }
  });
</script>

@endsection
