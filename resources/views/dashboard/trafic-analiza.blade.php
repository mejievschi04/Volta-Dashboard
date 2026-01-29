@extends('layouts.app')

@section('title', 'Analiză Detaliată Trafic – VOLTA')

@push('styles')
<link rel="stylesheet" href="{{ url('css/trafic.css') }}">
<style>
.analiza-page {
  padding: 30px;
  min-height: 100vh;
  background: linear-gradient(135deg, #111827 0%, #111827 50%, #111827 100%);
}

.analiza-page h1 {
  margin: 0 0 40px;
  font-size: 36px;
  font-weight: 900;
  color: #FFEE00;
  text-shadow: 0 0 30px rgba(255, 238, 0, 0.6), 0 0 60px rgba(255, 238, 0, 0.3);
  letter-spacing: -1px;
  position: relative;
  padding-bottom: 15px;
}

.analiza-page h1::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100px;
  height: 4px;
  background: linear-gradient(90deg, #FFEE00, transparent);
  border-radius: 2px;
}

.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  color: #FFEE00;
  text-decoration: none;
  font-weight: 700;
  margin-bottom: 25px;
  padding: 12px 20px;
  border-radius: 10px;
  background: linear-gradient(135deg, rgba(17, 24, 39, 0.5) 0%, rgba(31, 41, 55, 0.6) 100%);
  border: 2px solid rgba(255, 238, 0, 0.25);
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.back-btn:hover {
  background: linear-gradient(135deg, rgba(255, 238, 0, 0.15) 0%, rgba(255, 238, 0, 0.1) 100%);
  border-color: rgba(255, 238, 0, 0.5);
  transform: translateX(-5px);
  box-shadow: 0 6px 20px rgba(255, 238, 0, 0.3);
}

.back-btn i {
  transition: transform 0.3s ease;
}

.back-btn:hover i {
  transform: translateX(-3px);
}

.section {
  background: linear-gradient(145deg, rgba(31, 41, 55, 0.95) 0%, rgba(31, 41, 55, 0.98) 100%);
  border-radius: 20px;
  padding: 35px;
  margin-bottom: 30px;
  box-shadow: 
    0 10px 40px rgba(0, 0, 0, 0.6),
    0 0 30px rgba(255, 238, 0, 0.15),
    inset 0 1px 0 rgba(255, 238, 0, 0.1);
  border: 2px solid rgba(255, 238, 0, 0.2);
  backdrop-filter: blur(15px);
  transition: all 0.3s ease;
  animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.section:hover {
  border-color: rgba(255, 238, 0, 0.3);
  box-shadow: 
    0 10px 40px rgba(0, 0, 0, 0.6),
    0 0 40px rgba(255, 238, 0, 0.2),
    inset 0 1px 0 rgba(255, 238, 0, 0.15);
}

.section h2 {
  color: #FFEE00;
  margin: 0 0 30px;
  font-size: 26px;
  font-weight: 800;
  display: flex;
  align-items: center;
  gap: 18px;
  text-shadow: 0 0 20px rgba(255, 238, 0, 0.5);
  position: relative;
  padding-bottom: 15px;
}

.section h2::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 80px;
  height: 3px;
  background: linear-gradient(90deg, #FFEE00, transparent);
  border-radius: 2px;
}

.section h2 i {
  font-size: 30px;
  filter: drop-shadow(0 0 10px rgba(255, 238, 0, 0.6));
}

.data-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  margin-top: 25px;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.data-table th,
.data-table td {
  padding: 16px 20px;
  text-align: left;
  border-bottom: 1px solid rgba(255, 238, 0, 0.1);
}

.data-table th {
  background: var(--bg-soft);
  color: #FFEE00;
  font-weight: 800;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 1px;
  text-shadow: 0 0 10px rgba(255, 238, 0, 0.5);
  position: sticky;
  top: 0;
  z-index: 10;
}

.data-table td {
  color: #fff;
  background: var(--bg-soft);
  transition: all 0.2s ease;
}

.data-table tbody tr {
  transition: all 0.2s ease;
}

.data-table tbody tr:hover {
  background: var(--bg-soft);
  transform: scale(1.01);
  box-shadow: 0 2px 10px rgba(255, 238, 0, 0.2);
}

.data-table tbody tr:hover td {
  background: var(--bg-soft);
  color: #FFEE00;
}

.data-table tbody tr:last-child td {
  border-bottom: none;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 25px;
  margin-top: 25px;
}

.stat-card {
  background: linear-gradient(135deg, rgba(31, 41, 55, 0.6) 0%, rgba(31, 41, 55, 0.7) 100%);
  padding: 25px;
  border-radius: 15px;
  border-left: 5px solid #FFEE00;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(255, 238, 0, 0.05) 0%, transparent 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.stat-card:hover::before {
  opacity: 1;
}

.stat-card:hover {
  background: linear-gradient(135deg, rgba(255, 238, 0, 0.1) 0%, rgba(255, 238, 0, 0.05) 100%);
  transform: translateY(-5px) scale(1.02);
  box-shadow: 
    0 8px 25px rgba(255, 238, 0, 0.3),
    0 0 30px rgba(255, 238, 0, 0.2);
  border-left-color: #FFEE00;
  border-left-width: 6px;
}

.stat-card h4 {
  color: #9CA3AF;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin: 0 0 15px 0;
  position: relative;
  z-index: 1;
}

.stat-card .value {
  color: #FFEE00;
  font-size: 32px;
  font-weight: 900;
  margin: 0;
  text-shadow: 
    0 0 15px rgba(255, 238, 0, 0.6),
    0 0 30px rgba(255, 238, 0, 0.3);
  position: relative;
  z-index: 1;
  line-height: 1.2;
}

.loading {
  text-align: center;
  padding: 60px 40px;
  color: #9CA3AF;
  position: relative;
}

.loading i {
  font-size: 56px;
  color: #FFEE00;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
  filter: drop-shadow(0 0 15px rgba(255, 238, 0, 0.6));
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.loading p {
  color: #9CA3AF;
  font-size: 16px;
  font-weight: 600;
  margin-top: 15px;
}

.error {
  background: linear-gradient(135deg, rgba(255, 0, 0, 0.15) 0%, rgba(255, 0, 0, 0.1) 100%);
  border: 2px solid rgba(255, 0, 0, 0.4);
  color: #EF4444;
  padding: 20px 25px;
  border-radius: 12px;
  margin-top: 20px;
  box-shadow: 0 4px 15px rgba(255, 0, 0, 0.2);
  line-height: 1.6;
}

.error small {
  display: block;
  margin-top: 10px;
  font-size: 13px;
  opacity: 0.8;
}

.analiza-container {
  display: flex;
  gap: 30px;
  margin-top: 30px;
  align-items: flex-start;
}

.analiza-menu {
  width: 300px;
  background: linear-gradient(145deg, rgba(31, 41, 55, 0.95) 0%, rgba(31, 41, 55, 0.98) 100%);
  border-radius: 20px;
  padding: 25px;
  box-shadow: 
    0 10px 40px rgba(0, 0, 0, 0.6),
    0 0 30px rgba(255, 238, 0, 0.15),
    inset 0 1px 0 rgba(255, 238, 0, 0.1);
  border: 2px solid rgba(255, 238, 0, 0.2);
  backdrop-filter: blur(15px);
  height: fit-content;
  position: sticky;
  top: 20px;
  transition: all 0.3s ease;
}

.analiza-menu:hover {
  border-color: rgba(255, 238, 0, 0.3);
  box-shadow: 
    0 10px 40px rgba(0, 0, 0, 0.6),
    0 0 40px rgba(255, 238, 0, 0.2),
    inset 0 1px 0 rgba(255, 238, 0, 0.15);
}

.menu-header {
  margin-bottom: 25px;
  padding-bottom: 20px;
  border-bottom: 2px solid rgba(255, 238, 0, 0.2);
  position: relative;
}

.menu-header::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 60px;
  height: 2px;
  background: linear-gradient(90deg, #FFEE00, transparent);
}

.menu-header h3 {
  color: #FFEE00;
  margin: 0;
  font-size: 22px;
  font-weight: 800;
  text-align: center;
  text-transform: uppercase;
  letter-spacing: 1px;
  text-shadow: 0 0 15px rgba(255, 238, 0, 0.5);
}

.date-selector-menu {
  display: flex;
  flex-direction: column;
  gap: 18px;
  margin-bottom: 25px;
  padding: 20px;
  background: rgba(31, 41, 55, 0.3);
  border-radius: 12px;
  border: 1px solid rgba(255, 238, 0, 0.15);
  position: relative;
}

.date-selector-menu::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, transparent, #FFEE00, transparent);
  opacity: 0.3;
}

.date-selector-menu label {
  color: #FFEE00;
  font-weight: 700;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
  display: block;
}

.date-selector-menu .date-input {
  padding: 12px 18px;
  border-radius: 10px;
  border: 2px solid rgba(255, 238, 0, 0.25);
  background: rgba(31, 41, 55, 0.6);
  color: #FFEE00;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
  transition: all 0.3s ease;
  font-size: 14px;
}

.date-selector-menu .date-input:hover {
  border-color: rgba(255, 238, 0, 0.4);
  background: rgba(31, 41, 55, 0.7);
}

.date-selector-menu .date-input:focus {
  outline: none;
  border-color: #FFEE00;
  background: rgba(31, 41, 55, 0.8);
  box-shadow: 0 0 20px rgba(255, 238, 0, 0.4), inset 0 0 10px rgba(255, 238, 0, 0.1);
}

.date-selector-menu .date-input {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  touch-action: manipulation;
}

/* === Mobile Responsive === */
@media (max-width: 768px) {
  .analiza-page {
    padding: 12px;
    padding-top: 75px;
  }
  
  .analiza-page h1 {
    font-size: 20px;
    margin-bottom: 20px;
    padding-bottom: 10px;
  }
  
  .analiza-page h1 i {
    font-size: 18px;
    margin-right: 8px;
  }
  
  .back-btn {
    font-size: 12px;
    padding: 8px 12px;
    margin-bottom: 15px;
    gap: 6px;
  }
  
  .back-btn i {
    font-size: 12px;
  }
  
  .analiza-container {
    flex-direction: column;
    gap: 15px;
    margin-top: 15px;
  }
  
  .analiza-menu {
    width: 100%;
    position: relative;
    top: 0;
    padding: 15px;
  }
  
  .menu-header {
    margin-bottom: 15px;
    padding-bottom: 12px;
  }
  
  .menu-header h3 {
    font-size: 16px;
  }
  
  .date-selector-menu {
    flex-direction: column;
    gap: 10px;
    margin-bottom: 15px;
  }
  
  .date-selector-menu label {
    font-size: 11px;
  }
  
  .date-selector-menu .date-input {
    min-height: 42px;
    font-size: 14px;
    padding: 10px 12px;
    z-index: 10;
    position: relative;
  }
  
  .date-selector-menu .date-input:focus {
    -webkit-tap-highlight-color: rgba(255, 238, 0, 0.3);
  }
  
  .menu-buttons {
    gap: 8px;
  }
  
  .menu-btn {
    min-height: 44px;
    padding: 10px 14px;
    font-size: 12px;
    gap: 10px;
    touch-action: manipulation;
  }
  
  .menu-btn i {
    font-size: 16px;
    width: 22px;
  }
  
  .section {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 12px;
  }
  
  .section h2 {
    font-size: 18px;
    margin-bottom: 15px;
    padding-bottom: 10px;
    gap: 10px;
  }
  
  .section h2 i {
    font-size: 18px;
  }
  
  .data-table {
    font-size: 11px;
    margin-top: 15px;
  }
  
  .data-table th,
  .data-table td {
    padding: 8px 10px;
    font-size: 11px;
  }
  
  .data-table th {
    font-size: 9px;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-top: 15px;
  }
  
  .stat-card {
    padding: 12px;
  }
  
  .stat-card h4 {
    font-size: 10px;
    margin-bottom: 8px;
  }
  
  .stat-card .value {
    font-size: 20px;
  }
  
  .no-selection {
    padding: 60px 20px;
  }
  
  .no-selection i {
    font-size: 50px;
    margin-bottom: 15px;
  }
  
  .no-selection h2 {
    font-size: 20px;
    margin-bottom: 10px;
  }
  
  .no-selection p {
    font-size: 13px;
  }
  
  .btn-show-more {
    padding: 10px 18px;
    font-size: 11px;
    margin-top: 15px;
    gap: 6px;
  }
  
  .loading {
    padding: 40px 20px;
  }
  
  .loading i {
    font-size: 40px;
    margin-bottom: 15px;
  }
  
  .loading p {
    font-size: 13px;
  }
}

@media (max-width: 480px) {
  .analiza-page {
    padding: 10px;
    padding-top: 70px;
  }
  
  .analiza-page h1 {
    font-size: 18px;
    margin-bottom: 15px;
    padding-bottom: 8px;
  }
  
  .analiza-page h1 i {
    font-size: 16px;
    margin-right: 6px;
  }
  
  .back-btn {
    font-size: 11px;
    padding: 6px 10px;
    margin-bottom: 12px;
    gap: 5px;
  }
  
  .back-btn i {
    font-size: 11px;
  }
  
  .analiza-container {
    gap: 12px;
    margin-top: 12px;
  }
  
  .analiza-menu {
    padding: 12px;
  }
  
  .menu-header {
    margin-bottom: 12px;
    padding-bottom: 10px;
  }
  
  .menu-header h3 {
    font-size: 14px;
  }
  
  .date-selector-menu {
    gap: 8px;
    margin-bottom: 12px;
  }
  
  .date-selector-menu label {
    font-size: 10px;
  }
  
  .date-selector-menu .date-input {
    min-height: 40px;
    font-size: 13px;
    padding: 8px 10px;
  }
  
  .menu-buttons {
    gap: 6px;
  }
  
  .menu-btn {
    min-height: 40px;
    padding: 8px 12px;
    font-size: 11px;
    gap: 8px;
  }
  
  .menu-btn i {
    font-size: 14px;
    width: 20px;
  }
  
  .section {
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 10px;
  }
  
  .section h2 {
    font-size: 16px;
    margin-bottom: 12px;
    padding-bottom: 8px;
    gap: 8px;
  }
  
  .section h2 i {
    font-size: 16px;
  }
  
  .data-table {
    font-size: 10px;
    margin-top: 12px;
    display: block;
    overflow-x: auto;
  }
  
  .data-table th,
  .data-table td {
    padding: 6px 8px;
    font-size: 10px;
    white-space: nowrap;
  }
  
  .data-table th {
    font-size: 8px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 8px;
    margin-top: 12px;
  }
  
  .stat-card {
    padding: 10px;
  }
  
  .stat-card h4 {
    font-size: 9px;
    margin-bottom: 6px;
  }
  
  .stat-card .value {
    font-size: 18px;
  }
  
  .no-selection {
    padding: 40px 15px;
  }
  
  .no-selection i {
    font-size: 40px;
    margin-bottom: 12px;
  }
  
  .no-selection h2 {
    font-size: 18px;
    margin-bottom: 8px;
  }
  
  .no-selection p {
    font-size: 12px;
  }
  
  .btn-show-more {
    padding: 8px 14px;
    font-size: 10px;
    margin-top: 12px;
    gap: 5px;
  }
  
  .loading {
    padding: 30px 15px;
  }
  
  .loading i {
    font-size: 32px;
    margin-bottom: 12px;
  }
  
  .loading p {
    font-size: 12px;
  }
}

.menu-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.menu-btn {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 16px 22px;
  border-radius: 12px;
  border: 2px solid rgba(255, 238, 0, 0.2);
  background: linear-gradient(135deg, rgba(17, 24, 39, 0.5) 0%, rgba(31, 41, 55, 0.6) 100%);
  color: #FFEE00;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 15px;
  width: 100%;
  text-align: left;
  position: relative;
  overflow: hidden;
}

.menu-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 238, 0, 0.1), transparent);
  transition: left 0.5s ease;
}

.menu-btn:hover::before {
  left: 100%;
}

.menu-btn:hover {
  background: linear-gradient(135deg, rgba(255, 238, 0, 0.1) 0%, rgba(255, 238, 0, 0.05) 100%);
  border-color: rgba(255, 238, 0, 0.5);
  transform: translateX(8px);
  box-shadow: 
    0 6px 20px rgba(255, 238, 0, 0.25),
    0 0 15px rgba(255, 238, 0, 0.1);
}

.menu-btn.active {
  background: linear-gradient(135deg, rgba(255, 238, 0, 0.25) 0%, rgba(255, 238, 0, 0.15) 100%);
  border-color: #FFEE00;
  box-shadow: 
    0 0 25px rgba(255, 238, 0, 0.4),
    0 0 50px rgba(255, 238, 0, 0.2),
    inset 0 0 20px rgba(255, 238, 0, 0.1);
  transform: translateX(5px);
}

.menu-btn.active::after {
  content: '';
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  width: 8px;
  height: 8px;
  background: #FFEE00;
  border-radius: 50%;
  box-shadow: 0 0 10px rgba(255, 238, 0, 0.8);
}

.menu-btn i {
  font-size: 20px;
  width: 28px;
  text-align: center;
  transition: transform 0.3s ease;
}

.menu-btn:hover i {
  transform: scale(1.1);
}

.menu-btn.active i {
  transform: scale(1.15);
  text-shadow: 0 0 10px rgba(255, 238, 0, 0.8);
}

.analiza-content {
  flex: 1;
  min-width: 0;
}

.no-selection {
  text-align: center;
  padding: 120px 40px;
  background: linear-gradient(145deg, rgba(31, 41, 55, 0.9) 0%, rgba(31, 41, 55, 0.95) 100%);
  border-radius: 20px;
  box-shadow: 
    0 10px 40px rgba(0, 0, 0, 0.6),
    0 0 30px rgba(255, 238, 0, 0.15),
    inset 0 1px 0 rgba(255, 238, 0, 0.1);
  border: 2px solid rgba(255, 238, 0, 0.2);
  backdrop-filter: blur(15px);
  position: relative;
  overflow: hidden;
}

.no-selection::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255, 238, 0, 0.05) 0%, transparent 70%);
  animation: pulse 4s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 0.5; }
  50% { transform: scale(1.1); opacity: 0.8; }
}

.no-selection i {
  font-size: 80px;
  color: #FFEE00;
  margin-bottom: 25px;
  opacity: 0.6;
  animation: float 3s ease-in-out infinite;
  filter: drop-shadow(0 0 20px rgba(255, 238, 0, 0.5));
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

.no-selection h2 {
  color: #FFEE00;
  margin-bottom: 15px;
  font-size: 28px;
  font-weight: 800;
  text-shadow: 0 0 20px rgba(255, 238, 0, 0.5);
}

.no-selection p {
  color: #9CA3AF;
  font-size: 16px;
  line-height: 1.6;
}

.section.hidden {
  display: none;
}

.btn-show-more {
  margin-top: 20px;
  padding: 14px 28px;
  border-radius: 10px;
  border: 2px solid rgba(255, 238, 0, 0.3);
  background: linear-gradient(135deg, rgba(255, 238, 0, 0.15) 0%, rgba(255, 238, 0, 0.1) 100%);
  color: #FFEE00;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.btn-show-more:hover {
  background: linear-gradient(135deg, rgba(255, 238, 0, 0.25) 0%, rgba(255, 238, 0, 0.15) 100%);
  border-color: #FFEE00;
  box-shadow: 
    0 6px 25px rgba(255, 238, 0, 0.4),
    0 0 30px rgba(255, 238, 0, 0.2);
  transform: translateY(-2px);
}

.btn-show-more i {
  transition: none;
  transform: none !important;
  animation: none !important;
}

.btn-show-more:hover i {
  transform: none !important;
  animation: none !important;
}
</style>
@endpush

@section('content')
<div class="analiza-page">
  <a href="{{ route('trafic') }}" class="back-btn">
    <i class="fas fa-arrow-left"></i>
    <span>Înapoi la Trafic</span>
  </a>
  
  <h1><i class="fas fa-chart-bar" style="margin-right: 15px;"></i>Analiză Detaliată Trafic</h1>
  
  <div class="analiza-container">
    <!-- Meniu lateral -->
    <div class="analiza-menu">
      <div class="menu-header">
        <h3>Meniu</h3>
      </div>
      
      <div class="date-selector-menu">
        <label for="selectYear">An:</label>
        <select id="selectYear" class="date-input" onchange="handleDateChange()">
          @for($year = date('Y'); $year >= 2020; $year--)
            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
          @endfor
        </select>
        
        <label for="selectMonth">Lună:</label>
        <select id="selectMonth" class="date-input" onchange="handleDateChange()">
          <option value="all" selected>An întreg</option>
          @for($month = 1; $month <= 12; $month++)
            <option value="{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}">
              {{ \Carbon\Carbon::create()->month($month)->locale('ro')->monthName }}
            </option>
          @endfor
        </select>
      </div>
      
      <div class="menu-buttons">
        <button class="menu-btn" onclick="showSection('users', this)">
          <i class="fas fa-users"></i>
          <span>Utilizatori</span>
        </button>
        <button class="menu-btn" onclick="showSection('devices', this)">
          <i class="fas fa-mobile-alt"></i>
          <span>Dispozitive</span>
        </button>
        <button class="menu-btn" onclick="showSection('geo', this)">
          <i class="fas fa-globe"></i>
          <span>Geografie</span>
        </button>
        <button class="menu-btn" onclick="showSection('content', this)">
          <i class="fas fa-file-alt"></i>
          <span>Conținut</span>
        </button>
        <button class="menu-btn" onclick="showSection('ecommerce', this)">
          <i class="fas fa-shopping-cart"></i>
          <span>E-commerce</span>
        </button>
        <button class="menu-btn" onclick="showSection('campaigns', this)">
          <i class="fas fa-bullhorn"></i>
          <span>Campanii</span>
        </button>
      </div>
    </div>
    
    <!-- Conținut -->
    <div class="analiza-content">
      <div id="noSelection" class="no-selection">
        <i class="fas fa-mouse-pointer" style="font-size: 64px; color: #FFEE00; margin-bottom: 20px; opacity: 0.5;"></i>
        <h2 style="color: #FFEE00; margin-bottom: 10px;">Selectează o secțiune</h2>
        <p style="color: #9CA3AF;">Alege o opțiune din meniul lateral pentru a vedea datele</p>
      </div>
  
      <!-- Utilizatori -->
      <div class="section hidden" id="usersSection" data-section="users">
        <h2><i class="fas fa-users"></i> Utilizatori</h2>
        <div id="usersContent" class="loading">
          <i class="fas fa-spinner"></i>
          <p>Se încarcă datele...</p>
        </div>
      </div>
      
      <!-- Dispozitive -->
      <div class="section hidden" id="devicesSection" data-section="devices">
        <h2><i class="fas fa-mobile-alt"></i> Dispozitive</h2>
        <div id="devicesContent" class="loading">
          <i class="fas fa-spinner"></i>
          <p>Se încarcă datele...</p>
        </div>
      </div>
      
      <!-- Geografie -->
      <div class="section hidden" id="geoSection" data-section="geo">
        <h2><i class="fas fa-globe"></i> Geografie</h2>
        <div id="geoContent" class="loading">
          <i class="fas fa-spinner"></i>
          <p>Se încarcă datele...</p>
        </div>
      </div>
      
      <!-- Conținut -->
      <div class="section hidden" id="contentSection" data-section="content">
        <div id="contentContent" class="loading">
          <i class="fas fa-spinner"></i>
          <p>Se încarcă datele...</p>
        </div>
      </div>
      
      <!-- E-commerce -->
      <div class="section hidden" id="ecommerceSection" data-section="ecommerce">
        <h2><i class="fas fa-shopping-cart"></i> E-commerce</h2>
        <div id="ecommerceContent" class="loading">
          <i class="fas fa-spinner"></i>
          <p>Se încarcă datele...</p>
        </div>
      </div>
      
      <!-- Campanii -->
      <div class="section hidden" id="campaignsSection" data-section="campaigns">
        <h2><i class="fas fa-bullhorn"></i> Campanii</h2>
        <div id="campaignsContent" class="loading">
          <i class="fas fa-spinner"></i>
          <p>Se încarcă datele...</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function formatNumber(val) {
  const num = parseFloat(val) || 0;
  return new Intl.NumberFormat('ro-RO').format(num);
}

function formatDuration(seconds) {
  const secs = parseFloat(seconds) || 0;
  if (!secs) return '0s';
  const mins = Math.floor(secs / 60);
  const secsRemainder = Math.floor(secs % 60);
  return mins > 0 ? `${mins}m ${secsRemainder}s` : `${secsRemainder}s`;
}

function formatPercentage(val) {
  const num = parseFloat(val) || 0;
  return num.toFixed(2) + '%';
}

function getDateRange() {
  const year = document.getElementById('selectYear').value;
  const month = document.getElementById('selectMonth').value;
  
  // Dacă este selectat "An întreg"
  if (month === 'all') {
    const startDate = `${year}-01-01`;
    const endDate = `${year}-12-31`;
    return { startDate, endDate };
  }
  
  // Altfel, pentru o lună specifică
  const startDate = `${year}-${month}-01`;
  const lastDay = new Date(year, month, 0).getDate();
  const endDate = `${year}-${month}-${lastDay}`;
  return { startDate, endDate };
}

function handleDateChange() {
  // Dacă există o secțiune activă, reîncărcăm datele
  const activeButton = document.querySelector('.menu-btn.active');
  if (activeButton) {
    // Extragem numele secțiunii din onclick
    const onclickAttr = activeButton.getAttribute('onclick');
    if (onclickAttr) {
      const match = onclickAttr.match(/showSection\(['"]([^'"]+)['"]/);
      if (match && match[1]) {
        const sectionName = match[1];
        const { startDate, endDate } = getDateRange();
        
        // Resetăm conținutul la loading
        const contentId = `${sectionName}Content`;
        const contentElement = document.getElementById(contentId);
        if (contentElement) {
          contentElement.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>Se încarcă datele...</p></div>';
          loadSectionData(sectionName, startDate, endDate);
        }
      }
    }
  }
}

function showSection(sectionName, button) {
  // Ascundem mesajul "Selectează o secțiune"
  document.getElementById('noSelection').style.display = 'none';
  
  // Ascundem toate secțiunile
  document.querySelectorAll('.section').forEach(section => {
    section.classList.add('hidden');
  });
  
  // Dezactivăm toate butoanele
  document.querySelectorAll('.menu-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  
  // Activăm butonul selectat
  if (button) {
    button.classList.add('active');
  }
  
  // Afișăm secțiunea selectată
  const section = document.getElementById(`${sectionName}Section`);
  if (section) {
    section.classList.remove('hidden');
    
    // Resetăm conținutul la loading
    const contentId = `${sectionName}Content`;
    document.getElementById(contentId).innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>Se încarcă datele...</p></div>';
    
    // Încărcăm datele
    const { startDate, endDate } = getDateRange();
    loadSectionData(sectionName, startDate, endDate);
  }
}

function loadSectionData(sectionName, startDate, endDate) {
  switch(sectionName) {
    case 'users':
      loadUsersData(startDate, endDate);
      break;
    case 'devices':
      loadDevicesData(startDate, endDate);
      break;
    case 'geo':
      loadGeoData(startDate, endDate);
      break;
    case 'content':
      loadContentData(startDate, endDate);
      break;
    case 'ecommerce':
      loadEcommerceData(startDate, endDate);
      break;
    case 'campaigns':
      loadCampaignsData(startDate, endDate);
      break;
  }
}

// Funcția loadAllData nu mai este necesară - fiecare buton încarcă propriile date

async function loadUsersData(startDate, endDate) {
  try {
    const response = await fetch(`{{ route('api.ga.users') }}?start_date=${startDate}&end_date=${endDate}`);
    const result = await response.json();
    
    if (result.success && result.data && result.data.rows) {
      let html = '<div class="stats-grid">';
      let totals = { 
        activeUsers: 0, 
        newUsers: 0, 
        returningUsers: 0,
        sessions: 0, 
        avgDuration: 0, 
        bounceRate: 0, 
        engagementRate: 0 
      };
      
      // Calculăm totalurile
      result.data.rows.forEach(row => {
        totals.activeUsers += parseInt(row.metricValues[0]?.value || 0, 10);
        totals.newUsers += parseInt(row.metricValues[1]?.value || 0, 10);
        totals.sessions += parseInt(row.metricValues[2]?.value || 0, 10);
        totals.avgDuration += parseFloat(row.metricValues[3]?.value || 0);
        totals.bounceRate += parseFloat(row.metricValues[4]?.value || 0);
        totals.engagementRate += parseFloat(row.metricValues[5]?.value || 0);
      });
      
      const count = result.data.rows.length;
      
      // Calculăm utilizatorii fideli totali
      // Utilizatorii fideli = Utilizatori activi totali - Utilizatori noi totali
      // Aceasta evită dublarea când un utilizator fidel vine în mai multe zile
      const totalReturningUsers = Math.max(0, totals.activeUsers - totals.newUsers);
      
      html += `
        <div class="stat-card"><h4>Utilizatori Activi</h4><div class="value">${formatNumber(totals.activeUsers)}</div></div>
        <div class="stat-card"><h4>Utilizatori Noi</h4><div class="value">${formatNumber(totals.newUsers)}</div></div>
        <div class="stat-card"><h4>Utilizatori Fideli</h4><div class="value">${formatNumber(totalReturningUsers)}</div></div>
        <div class="stat-card"><h4>Total Sesiuni</h4><div class="value">${formatNumber(totals.sessions)}</div></div>
        <div class="stat-card"><h4>Durată Medie</h4><div class="value">${formatDuration(count > 0 ? totals.avgDuration / count : 0)}</div></div>
        <div class="stat-card"><h4>Rata Respingere</h4><div class="value">${formatPercentage(count > 0 ? totals.bounceRate / count : 0)}</div></div>
        <div class="stat-card"><h4>Rata Angajament</h4><div class="value">${formatPercentage(count > 0 ? totals.engagementRate / count : 0)}</div></div>
      `;
      
      html += '</div>';
      document.getElementById('usersContent').innerHTML = html;
    } else {
      document.getElementById('usersContent').innerHTML = '<div class="error">Nu sunt date disponibile sau eroare: ' + (result.error || 'Necunoscută') + '</div>';
    }
  } catch (error) {
    console.error('Eroare la încărcarea datelor utilizatori:', error);
    document.getElementById('usersContent').innerHTML = '<div class="error">Eroare: ' + error.message + '</div>';
  }
}

async function loadDevicesData(startDate, endDate, showAll = false) {
  try {
    const response = await fetch(`{{ route('api.ga.devices') }}?start_date=${startDate}&end_date=${endDate}`);
    const result = await response.json();
    
    if (result.success && result.data && result.data.rows) {
      const rows = showAll ? result.data.rows : result.data.rows.slice(0, 10);
      const hasMore = result.data.rows.length > 10 && !showAll;
      
      let html = '<table class="data-table"><thead><tr><th>Dispozitiv</th><th>OS</th><th>Browser</th><th>Sesiuni</th><th>Utilizatori</th><th>Pagini</th></tr></thead><tbody>';
      
      rows.forEach(row => {
        html += `<tr>
          <td>${row.dimensionValues[0]?.value || '-'}</td>
          <td>${row.dimensionValues[1]?.value || '-'}</td>
          <td>${row.dimensionValues[2]?.value || '-'}</td>
          <td>${formatNumber(row.metricValues[0]?.value || 0)}</td>
          <td>${formatNumber(row.metricValues[1]?.value || 0)}</td>
          <td>${formatNumber(row.metricValues[2]?.value || 0)}</td>
        </tr>`;
      });
      
      html += '</tbody></table>';
      
      if (hasMore) {
        html += `<button class="btn-show-more" onclick="loadDevicesData('${startDate}', '${endDate}', true); this.remove();">
          <i class="fas fa-chevron-down"></i> Vezi mai detaliat (${result.data.rows.length - 10} în plus)
        </button>`;
      }
      
      document.getElementById('devicesContent').innerHTML = html;
    } else {
      document.getElementById('devicesContent').innerHTML = '<div class="error">Nu sunt date disponibile</div>';
    }
  } catch (error) {
    document.getElementById('devicesContent').innerHTML = '<div class="error">Eroare: ' + error.message + '</div>';
  }
}

async function loadGeoData(startDate, endDate, showAll = false) {
  try {
    const response = await fetch(`{{ route('api.ga.geo') }}?start_date=${startDate}&end_date=${endDate}`);
    const result = await response.json();
    
    if (result.success && result.data && result.data.rows) {
      const rows = showAll ? result.data.rows : result.data.rows.slice(0, 10);
      const hasMore = result.data.rows.length > 10 && !showAll;
      
      let html = '<table class="data-table"><thead><tr><th>Țară</th><th>Oraș</th><th>Sesiuni</th><th>Utilizatori</th></tr></thead><tbody>';
      
      rows.forEach(row => {
        html += `<tr>
          <td>${row.dimensionValues[0]?.value || '-'}</td>
          <td>${row.dimensionValues[1]?.value || '-'}</td>
          <td>${formatNumber(row.metricValues[0]?.value || 0)}</td>
          <td>${formatNumber(row.metricValues[1]?.value || 0)}</td>
        </tr>`;
      });
      
      html += '</tbody></table>';
      
      if (hasMore) {
        html += `<button class="btn-show-more" onclick="loadGeoData('${startDate}', '${endDate}', true); this.remove();">
          <i class="fas fa-chevron-down"></i> Vezi mai detaliat (${result.data.rows.length - 10} în plus)
        </button>`;
      }
      
      document.getElementById('geoContent').innerHTML = html;
    } else {
      document.getElementById('geoContent').innerHTML = '<div class="error">Nu sunt date disponibile</div>';
    }
  } catch (error) {
    document.getElementById('geoContent').innerHTML = '<div class="error">Eroare: ' + error.message + '</div>';
  }
}

async function loadContentData(startDate, endDate, showAll = false) {
  try {
    const response = await fetch(`{{ route('api.ga.content') }}?start_date=${startDate}&end_date=${endDate}`);
    const result = await response.json();
    
    if (result.success && result.data && result.data.rows) {
      const rows = showAll ? result.data.rows : result.data.rows.slice(0, 10);
      const hasMore = result.data.rows.length > 10 && !showAll;
      
      let html = '<table class="data-table"><thead><tr><th>Pagină</th><th>Titlu</th><th>Vizualizări</th><th>Durată Medie</th><th>Rata Respingere</th></tr></thead><tbody>';
      
      rows.forEach(row => {
        html += `<tr>
          <td>${(row.dimensionValues[0]?.value || '-').substring(0, 50)}</td>
          <td>${(row.dimensionValues[1]?.value || '-').substring(0, 50)}</td>
          <td>${formatNumber(row.metricValues[0]?.value || 0)}</td>
          <td>${formatDuration(row.metricValues[1]?.value || 0)}</td>
          <td>${formatPercentage(row.metricValues[2]?.value || 0)}</td>
        </tr>`;
      });
      
      html += '</tbody></table>';
      
      if (hasMore) {
        html += `<button class="btn-show-more" onclick="loadContentData('${startDate}', '${endDate}', true); this.remove();">
          <i class="fas fa-chevron-down"></i> Vezi mai detaliat (${result.data.rows.length - 10} în plus)
        </button>`;
      }
      
      document.getElementById('contentContent').innerHTML = html;
    } else {
      document.getElementById('contentContent').innerHTML = '<div class="error">Nu sunt date disponibile</div>';
    }
  } catch (error) {
    document.getElementById('contentContent').innerHTML = '<div class="error">Eroare: ' + error.message + '</div>';
  }
}

async function loadEcommerceData(startDate, endDate) {
  try {
    const response = await fetch(`{{ route('api.ga.ecommerce') }}?start_date=${startDate}&end_date=${endDate}`);
    const result = await response.json();
    
    if (result.success && result.data && result.data.rows && result.data.rows.length > 0) {
      let html = '<div class="stats-grid">';
      let totals = { revenue: 0, purchasers: 0, transactions: 0, items: 0, avgRevenue: 0 };
      
      result.data.rows.forEach(row => {
        totals.revenue += parseFloat(row.metricValues[0]?.value || 0);
        totals.purchasers += parseInt(row.metricValues[1]?.value || 0, 10);
        totals.transactions += parseInt(row.metricValues[2]?.value || 0, 10);
        totals.items += parseInt(row.metricValues[3]?.value || 0, 10);
        totals.avgRevenue += parseFloat(row.metricValues[4]?.value || 0);
      });
      
      const count = result.data.rows.length;
      const avgRevenue = count > 0 ? totals.avgRevenue / count : 0;
      
      html += `
        <div class="stat-card"><h4>Venit Total</h4><div class="value">${formatNumber(totals.revenue.toFixed(2))} RON</div></div>
        <div class="stat-card"><h4>Cumpărători</h4><div class="value">${formatNumber(totals.purchasers)}</div></div>
        <div class="stat-card"><h4>Tranzacții</h4><div class="value">${formatNumber(totals.transactions)}</div></div>
        <div class="stat-card"><h4>Articole</h4><div class="value">${formatNumber(totals.items)}</div></div>
        <div class="stat-card"><h4>Venit Mediu</h4><div class="value">${formatNumber(avgRevenue.toFixed(2))} RON</div></div>
      `;
      
      html += '</div>';
      document.getElementById('ecommerceContent').innerHTML = html;
    } else {
      document.getElementById('ecommerceContent').innerHTML = '<div class="error">E-commerce nu este configurat în Google Analytics sau nu există date pentru perioada selectată.<br><small>Pentru a activa e-commerce, trebuie să configurezi Enhanced E-commerce în GA4.</small></div>';
    }
  } catch (error) {
    document.getElementById('ecommerceContent').innerHTML = '<div class="error">Eroare: ' + error.message + '<br><small>E-commerce poate să nu fie configurat în Google Analytics.</small></div>';
  }
}

async function loadCampaignsData(startDate, endDate, showAll = false) {
  try {
    const response = await fetch(`{{ route('api.ga.campaigns') }}?start_date=${startDate}&end_date=${endDate}`);
    const result = await response.json();
    
    if (result.success && result.data && result.data.rows) {
      const rows = showAll ? result.data.rows : result.data.rows.slice(0, 10);
      const hasMore = result.data.rows.length > 10 && !showAll;
      
      let html = '<table class="data-table"><thead><tr><th>Campanie</th><th>Sursă/Mediu</th><th>Sesiuni</th><th>Utilizatori Activi</th></tr></thead><tbody>';
      
      rows.forEach(row => {
        html += `<tr>
          <td>${row.dimensionValues[0]?.value || '-'}</td>
          <td>${row.dimensionValues[1]?.value || '-'}</td>
          <td>${formatNumber(row.metricValues[0]?.value || 0)}</td>
          <td>${formatNumber(row.metricValues[1]?.value || 0)}</td>
        </tr>`;
      });
      
      html += '</tbody></table>';
      
      if (hasMore) {
        html += `<button class="btn-show-more" onclick="loadCampaignsData('${startDate}', '${endDate}', true); this.remove();">
          <i class="fas fa-chevron-down"></i> Vezi mai detaliat (${result.data.rows.length - 10} în plus)
        </button>`;
      }
      
      document.getElementById('campaignsContent').innerHTML = html;
    } else {
      document.getElementById('campaignsContent').innerHTML = '<div class="error">Nu sunt date disponibile pentru campanii sau eroare: ' + (result.error || 'Necunoscută') + '</div>';
    }
  } catch (error) {
    document.getElementById('campaignsContent').innerHTML = '<div class="error">Eroare: ' + error.message + '</div>';
  }
}

// Nu încărcăm automat datele - utilizatorul trebuie să apese butonul "Încarcă Date"
// sau să selecteze secțiuni și să apese butonul
</script>
@endpush

