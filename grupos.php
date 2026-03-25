<?php
/**
 * grupos.php
 * Panel de administración de grupos consolidados
 * Requiere que funciones.php y config.php ya estén cargadas
 */

// El usuario debe estar autenticado (verificado por index.php)
if (!isset($_SESSION['id'])) {
    echo "No autorizado";
    exit();
}

$idFacilitador = $_SESSION['id'];
$nombreFacilitador = $_SESSION['nombre'] ?? 'Usuario';
?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #e8e8e8;
        min-height: 100vh;
        padding: 20px;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .header {
        background: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .header h1 {
        color: #333;
        margin-bottom: 10px;
        font-size: 28px;
    }

    .header p {
        color: #666;
        font-size: 14px;
        line-height: 1.5;
    }

    .info-box {
        background: #f8f8f8;
        border-left: 4px solid #2c3e50;
        padding: 15px;
        margin-top: 15px;
        border-radius: 5px;
        font-size: 13px;
        color: #555;
    }

    /* Grid de Cards */
    .cards-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
        padding: 20px;
        background: white;
        border-radius: 10px;
        min-height: 400px;
    }

    .card {
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        transition: all 0.3s ease;
        position: relative;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .card:hover {
        border-color: #2c3e50;
        box-shadow: 0 4px 12px rgba(44, 62, 80, 0.15);
        transform: translateY(-2px);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }

    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        flex: 1;
    }

    .card-badge {
        background: #e8e8e8;
        color: #2c3e50;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .consolidated-badge {
        background: #e8e8e8;
        color: #5a6c57;
    }

    .card-body {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .card-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .card-item-label {
        font-size: 12px;
        color: #999;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card-item-value {
        font-size: 14px;
        color: #333;
        font-weight: 500;
    }

    .search-section {
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
        align-items: center;
    }

    #searchFilter {
        padding: 12px 15px;
        border: 2px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.3s;
    }

    #searchFilter:focus {
        outline: none;
        border-color: #2c3e50;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 14px;
    }

    .btn-primary {
        background: #2c3e50;
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(44, 62, 80, 0.4);
    }

    .btn-secondary {
        background: #f0f0f0;
        color: #333;
    }

    .btn-secondary:hover {
        background: #e0e0e0;
    }

    .status-message {
        padding: 15px 20px;
        margin-bottom: 20px;
        border-radius: 6px;
        display: none;
        font-weight: 600;
    }

    .status-message.success {
        background: #e8f5e9;
        color: #5a6c57;
        display: block;
    }

    .status-message.error {
        background: #ffcdd2;
        color: #c62828;
        display: block;
    }

    .status-message.info {
        background: #bbdefb;
        color: #1565c0;
        display: block;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .empty-state h2 {
        color: #666;
        margin-bottom: 10px;
    }

    .main-content {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 20px;
        margin-bottom: 30px;
        position: relative;
    }

    .mobile-menu-button {
        display: none;
        position: fixed;
        bottom: 30px;
        left: 30px;
        background: #2c3e50;
        color: white;
        border: none;
        padding: 15px 20px;
        border-radius: 50px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        box-shadow: 0 4px 12px rgba(44, 62, 80, 0.4);
        z-index: 100;
        transition: all 0.3s ease;
    }

    .mobile-menu-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(44, 62, 80, 0.5);
    }

    .slider-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 200;
    }

    .slider-overlay.active {
        display: block;
    }

    .left-panel {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        height: 600px;
    }

    .left-panel-header {
        background: #2c3e50;
        color: white;
        padding: 12px 15px;
        font-weight: 600;
        font-size: 13px;
        flex-shrink: 0;
        height: 40px;
        display: flex;
        align-items: center;
    }

    .search-container {
        padding: 12px 15px;
        flex-shrink: 0;
        border-bottom: 2px solid #ebf5fb;
    }

    .search-input-left {
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 12px;
        outline: none;
        width: 100%;
        box-sizing: border-box;
        height: 36px;
    }

    .search-input-left:focus {
        border-color: #2c3e50;
        background: #f9f9f9;
    }

    .search-input-left::placeholder {
        color: #bbb;
    }

    .groups-list {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .group-item {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .group-item:hover {
        background: #f9f9f9;
        border-left: 4px solid #2c3e50;
        padding-left: 11px;
    }

    .group-item.selected {
        background: #f5f5f5;
        border-left: 4px solid #2c3e50;
        padding-left: 11px;
    }

    .group-item-title {
        font-weight: 600;
        color: #333;
        font-size: 14px;
        margin-bottom: 5px;
    }

    .group-item-info {
        font-size: 12px;
        color: #999;
        line-height: 1.4;
    }

    .right-panel {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        height: 600px;
        min-height: 600px;
    }

    .right-panel-header {
        background: #2c3e50;
        color: white;
        padding: 12px 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
        height: 40px;
    }

    .right-panel-header h3 {
        margin: 0;
        font-size: 13px;
        font-weight: 600;
    }

    .btn-new-report {
        background: white;
        color: #2c3e50;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 12px;
        transition: all 0.3s ease;
    }

    .btn-new-report:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .reports-content {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
    }

    .empty-message {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }

    .report-item {
        background: #f9f9f9;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.2s ease;
    }

    .report-item:hover {
        border-color: #2c3e50;
        box-shadow: 0 2px 8px rgba(44, 62, 80, 0.15);
    }

    .report-id {
        font-weight: 600;
        color: #333;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .report-details {
        font-size: 12px;
        color: #666;
        line-height: 1.5;
    }

    .tabs-container {
        display: flex;
        border-bottom: 2px solid #e0e0e0;
        flex-shrink: 0;
    }

    .tab-button {
        flex: 1;
        padding: 12px 15px;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        color: #666;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }

    .tab-button:hover {
        color: #333;
        background: #f9f9f9;
    }

    .tab-button.active {
        color: #2c3e50;
        border-bottom-color: #2c3e50;
    }

    .tab-content {
        display: none;
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        padding-right: 16px;
    }

    .tab-content.active {
        display: block;
    }

    .info-section {
        margin-bottom: 20px;
    }

    .info-label {
        font-weight: 600;
        color: #666;
        font-size: 12px;
        text-transform: uppercase;
        margin-bottom: 5px;
        letter-spacing: 0.5px;
    }

    .info-value {
        color: #333;
        font-size: 14px;
        padding: 8px 12px;
        background: #f9f9f9;
        border-radius: 4px;
        border-left: 3px solid #2c3e50;
    }

    .info-divider {
        height: 1px;
        background: #e0e0e0;
        margin: 20px 0;
    }

    .images-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 15px;
        margin-top: 10px;
    }

    .image-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .image-item:hover {
        transform: scale(1.05);
    }

    .report-image {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 6px;
        border: 2px solid #e0e0e0;
        transition: all 0.2s ease;
    }

    .report-image:hover {
        border-color: #2c3e50;
        box-shadow: 0 2px 8px rgba(44, 62, 80, 0.2);
    }

    .image-info {
        font-size: 11px;
        color: #666;
        text-align: center;
        word-break: break-word;
    }

    @media (max-width: 768px) {
        .header h1 {
            font-size: 20px;
        }

        .main-content {
            grid-template-columns: 1fr;
        }

        .left-panel {
            display: none;
            position: fixed !important;
            left: -100% !important;
            top: 0 !important;
            width: 100% !important;
            max-width: 320px !important;
            height: 100vh !important;
            z-index: 250 !important;
            border-radius: 0 !important;
            transition: left 0.3s ease !important;
            margin-top: 0 !important;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.15) !important;
        }

        .left-panel.active {
            display: flex !important;
            left: 0 !important;
        }

        .mobile-menu-button {
            display: block;
        }

        .right-panel {
            min-height: 400px;
            border-radius: 0;
            box-shadow: none;
        }
    }

    .edit-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 500;
        align-items: center;
        justify-content: center;
    }

    .edit-modal.active {
        display: flex;
    }

    .edit-modal-content {
        background: white;
        border-radius: 10px;
        padding: 30px;
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }

    .edit-modal-header {
        font-size: 20px;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .edit-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #999;
        padding: 0;
        width: 30px;
        height: 30px;
    }

    .edit-modal-close:hover {
        color: #333;
    }

    .edit-form-group {
        margin-bottom: 20px;
    }

    .edit-form-label {
        font-weight: 600;
        color: #333;
        font-size: 13px;
        text-transform: uppercase;
        margin-bottom: 8px;
        display: block;
    }

    .edit-form-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        box-sizing: border-box;
    }

    .edit-form-input:focus {
        outline: none;
        border-color: #2c3e50;
        box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
    }

    .edit-lideres-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
    }

    .edit-lider-tag {
        background: #f5f5f5;
        border: 1px solid #2c3e50;
        color: #333;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .edit-lider-tag button {
        background: none;
        border: none;
        color: #2c3e50;
        cursor: pointer;
        font-size: 16px;
        padding: 0;
        display: flex;
        align-items: center;
    }

    .edit-lider-tag button:hover {
        color: #e74c3c;
    }

    .edit-lider-input-group {
        display: flex;
        gap: 8px;
    }

    .edit-lider-input {
        flex: 1;
    }

    .edit-lider-add-btn {
        background: #2c3e50;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .edit-lider-add-btn:hover {
        background: #1a252f;
    }

    .edit-modal-buttons {
        display: flex;
        gap: 10px;
        margin-top: 30px;
    }

    .edit-modal-button {
        flex: 1;
        padding: 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .edit-modal-button.save {
        background: #2c3e50;
        color: white;
    }

    .edit-modal-button.save:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(44, 62, 80, 0.4);
    }

    .edit-modal-button.cancel {
        background: #f0f0f0;
        color: #333;
    }

    .edit-modal-button.cancel:hover {
        background: #e0e0e0;
    }

    .edit-modal-section {
        margin-bottom: 20px;
    }

    .edit-modal-section label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .edit-modal-section input[type="text"],
    .edit-modal-section input[type="date"],
    .edit-modal-section input[type="number"],
    .edit-modal-section textarea,
    .edit-modal-section select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: inherit;
        font-size: 14px;
    }

    .edit-modal-section input[type="text"]:focus,
    .edit-modal-section input[type="date"]:focus,
    .edit-modal-section input[type="number"]:focus,
    .edit-modal-section textarea:focus,
    .edit-modal-section select:focus {
        outline: none;
        border-color: #2c3e50;
        box-shadow: 0 0 5px rgba(44, 62, 80, 0.3);
    }

    .edit-modal-section textarea {
        resize: vertical;
        min-height: 80px;
    }

    .edit-lider-tag {
        display: inline-block;
        background: #f5f5f5;
        color: #2c3e50;
        padding: 8px 12px;
        border-radius: 20px;
        margin-right: 8px;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .edit-lider-tag button {
        background: none;
        border: none;
        color: #2c3e50;
        cursor: pointer;
        margin-left: 8px;
        font-weight: bold;
    }

    .edit-lider-add-btn {
        background: #2c3e50;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        margin-top: 10px;
    }

    .edit-lider-add-btn:hover {
        background: #1a252f;
    }

    /* Estilos para modal de nuevo reporte */
    .activity-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .activity-modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .activity-modal-content {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        max-width: 500px;
        width: 90%;
    }

    .activity-modal-header {
        font-size: 20px;
        font-weight: bold;
        color: #333;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .activity-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #999;
    }

    .activity-modal-close:hover {
        color: #333;
    }

    .activity-options {
        display: grid;
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .activity-button {
        padding: 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: left;
    }

    .activity-button:hover {
        border-color: #2c3e50;
        background: #f5f5f5;
        transform: translateY(-2px);
    }

    .activity-button.evangelismo {
        border-left: 4px solid #5a6c57;
    }

    .activity-button.gran-celebracion {
        border-left: 4px solid #7f8c8d;
    }

    .activity-button.bautizo {
        border-left: 4px solid #2c3e50;
    }

    .activity-button.reunion {
        border-left: 4px solid #7f8c8d;
    }

    .activity-button strong {
        display: block;
        font-size: 16px;
        margin-bottom: 5px;
        color: #333;
    }

    .activity-button span {
        font-size: 13px;
        color: #666;
    }

    /* Estilos para formulario de nuevo reporte */
    .new-report-form {
        display: none;
    }

    .new-report-form.active {
        display: block;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
        font-size: 13px;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 13px;
        font-family: inherit;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #2c3e50;
        box-shadow: 0 0 5px rgba(44, 62, 80, 0.3);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .form-row.full {
        grid-template-columns: 1fr;
    }

    .attendance-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-top: 10px;
    }

    .attendance-input {
        display: flex;
        flex-direction: column;
    }

    .attendance-input label {
        font-size: 12px;
        margin-bottom: 3px;
    }

    .attendance-input input {
        margin-bottom: 0;
    }

    .modal-button-group {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
    }

    .modal-button {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .modal-button.primary {
        background: #2c3e50;
        color: white;
    }

    .modal-button.primary:hover {
        background: #1a252f;
    }

    .modal-button.secondary {
        background: #f0f0f0;
        color: #333;
    }

    .modal-button.secondary:hover {
        background: #e0e0e0;
    }
</style>

<div class="container">
    <!-- Header -->
    <div class="header">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
            <div>
                <h1>📊 Mis Grupos</h1>
                <p>Visualiza y gestiona todos tus grupos con sus datos actuales.</p>
            </div>
            <button class="btn btn-primary" onclick="openCreateGroupModal()" title="Crear un nuevo grupo" style="white-space: nowrap;">
                ➕ Crear Nuevo Grupo
            </button>
        </div>
        <div class="info-box">
            <strong>ℹ️ Información:</strong>
            Este panel muestra todos los grupos que has registrado. Puedes ver información detallada de cada grupo incluyendo nombre, ubicación, dirección, grupo madre y líderes.
        </div>
    </div>

    <!-- Mensaje de estado -->
    <div id="statusMessage" class="status-message"></div>

    <!-- Botón de menú móvil -->
    <button class="mobile-menu-button" onclick="toggleMobileMenu()">
        ☰ Grupos
    </button>

    <!-- Overlay para móvil -->
    <div class="slider-overlay" id="sliderOverlay" onclick="closeMobileMenu()"></div>

    <!-- Main Content - Dos Columnas -->
    <div class="main-content">
        <!-- Left Panel - Lista de Grupos -->
        <div class="left-panel" id="mobileSlider">
            <div class="left-panel-header">
                📋 Mis Grupos (${gruposCount})
            </div>
            <div class="search-container">
                <input
                    type="text"
                    id="searchFilter"
                    placeholder="🔍 Buscar grupo..."
                    class="search-input-left"
                    onkeyup="filterGroups()"
                />
            </div>
            <div class="groups-list" id="groupsList">
                <!-- Los grupos se cargarán aquí -->
            </div>
        </div>

        <!-- Right Panel - Información y Reportes -->
        <div class="right-panel">
            <div class="right-panel-header">
                <h3 id="groupPanelTitle">📋 Información del Grupo</h3>
                <button class="btn-new-report" onclick="newReport()" id="btnNewReport">
                    + Nuevo
                </button>
            </div>

            <!-- Tabs -->
            <div class="tabs-container">
                <button class="tab-button active" onclick="switchTab('info')">
                    ℹ️ Información General
                </button>
                <button class="tab-button" onclick="switchTab('reports')">
                    📄 Reportes
                </button>
            </div>

            <!-- Tab: Información General -->
            <div id="tab-info" class="tab-content active">
                <div class="empty-message">
                    <p>Selecciona un grupo para ver su información</p>
                </div>
            </div>

            <!-- Tab: Reportes -->
            <div id="tab-reports" class="tab-content">
                <div class="empty-message">
                    <p>Selecciona un grupo para ver sus reportes</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="buttons-section">
        <button class="btn btn-primary" onclick="window.history.back()">
            ← Volver
        </button>
    </div>
</div>

<!-- Modal de Edición -->
<div class="edit-modal" id="editModal">
    <div class="edit-modal-content">
        <div class="edit-modal-header">
            <span>✏️ Editar Grupo</span>
            <button class="edit-modal-close" onclick="closeEditModal()">✕</button>
        </div>

        <form id="editForm" onsubmit="saveGroupChanges(event)">
            <div class="edit-form-group">
                <label class="edit-form-label">Nombre del Grupo</label>
                <input type="text" id="editNombre" class="edit-form-input" placeholder="Nombre del grupo">
            </div>

            <div class="edit-form-group">
                <label class="edit-form-label">Ciudad</label>
                <input type="text" id="editCiudad" class="edit-form-input" placeholder="Ciudad">
            </div>

            <div class="edit-form-group">
                <label class="edit-form-label">Barrio</label>
                <input type="text" id="editBarrio" class="edit-form-input" placeholder="Barrio">
            </div>

            <div class="edit-form-group">
                <label class="edit-form-label">Dirección</label>
                <input type="text" id="editDireccion" class="edit-form-input" placeholder="Dirección">
            </div>

            <div class="edit-form-group">
                <label class="edit-form-label">¿Tiene Grupo Madre?</label>
                <div style="display: flex; gap: 20px; margin-top: 10px;">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="editTieneGrupoMadre" value="no" checked onchange="toggleEditGrupoMadreSelect()">
                        No (será Generación 0)
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="editTieneGrupoMadre" value="si" onchange="toggleEditGrupoMadreSelect()">
                        Sí
                    </label>
                </div>
            </div>

            <div class="edit-form-group" id="editGrupoMadreSelect" style="display: none;">
                <label class="edit-form-label">Seleccionar Grupo Madre *</label>
                <select id="editGrupoMadreDropdown" class="edit-form-input">
                    <option value="">-- Cargando grupos --</option>
                </select>
                <div id="editGeneracionInfo" style="margin-top: 10px; padding: 10px; background: #e8f4f8; border-radius: 4px; display: none;">
                    <small>La nueva generación será: <strong id="editGeneracionDisplay">-</strong></small>
                </div>
            </div>

            <div class="edit-form-group">
                <label class="edit-form-label">Generación</label>
                <input type="number" id="editGeneracion" class="edit-form-input" placeholder="Generación" min="0" max="5" readonly style="background-color: #f0f0f0; cursor: not-allowed;">
            </div>

            <div class="edit-form-group">
                <label class="edit-form-label">Líderes</label>
                <div class="edit-lideres-container" id="lideresContainer"></div>
                <div class="edit-lider-input-group">
                    <input type="text" id="liderInput" class="edit-form-input edit-lider-input" placeholder="Agregar nuevo líder">
                    <button type="button" class="edit-lider-add-btn" onclick="addLider()">Agregar</button>
                </div>
            </div>

            <div class="edit-modal-buttons">
                <button type="button" class="edit-modal-button cancel" onclick="closeEditModal()">Cancelar</button>
                <button type="submit" class="edit-modal-button save">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Crear Nuevo Grupo -->
<div class="edit-modal" id="createGroupModal">
    <div class="edit-modal-content">
        <div class="edit-modal-header">
            <span>➕ Crear Nuevo Grupo</span>
            <button class="edit-modal-close" onclick="closeCreateGroupModal()">✕</button>
        </div>

        <form id="createGroupForm" onsubmit="saveNewGroup(event)">
            <!-- INFORMACIÓN DEL GRUPO -->
            <h4 style="margin-top: 15px; margin-bottom: 10px; color: #333;">📋 Información del Grupo</h4>

            <div class="edit-modal-section">
                <label>Nombre del Grupo *</label>
                <input type="text" id="newGroupName" name="nombre" required placeholder="Ej: Grupo de Jóvenes">
            </div>

            <div class="edit-modal-section">
                <label>Descripción</label>
                <textarea id="newGroupDescription" name="descripcion" placeholder="Descripción del grupo" rows="3"></textarea>
            </div>

            <div class="edit-modal-section">
                <label>¿Tiene Grupo Madre?</label>
                <div style="display: flex; gap: 20px; margin-top: 10px;">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="tieneGrupoMadre" value="no" checked onchange="toggleGrupoMadreSelect()">
                        No (será Generación 0)
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="tieneGrupoMadre" value="si" onchange="toggleGrupoMadreSelect()">
                        Sí
                    </label>
                </div>
            </div>

            <div class="edit-modal-section" id="grupoMadreSelect" style="display: none;">
                <label>Seleccionar Grupo Madre *</label>
                <select id="grupoMadreDropdown" name="grupoMadre">
                    <option value="">-- Cargando grupos --</option>
                </select>
                <div id="generacionInfo" style="margin-top: 10px; padding: 10px; background: #e8f4f8; border-radius: 4px; display: none;">
                    <small>La nueva generación será: <strong id="generacionDisplay">-</strong></small>
                </div>
            </div>

            <div class="edit-modal-section">
                <label>Ubicación (Ciudad, Barrio)</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="newGroupCiudad" name="ciudad" placeholder="Ciudad" style="flex: 1;">
                    <input type="text" id="newGroupBarrio" name="barrio" placeholder="Barrio" style="flex: 1;">
                </div>
            </div>

            <div class="edit-modal-section">
                <label>Dirección</label>
                <input type="text" id="newGroupDireccion" name="direccion" placeholder="Dirección del lugar de reunión">
            </div>

            <div class="edit-modal-section">
                <label>Líder del Grupo</label>
                <input type="text" id="newGroupLider" name="lider" placeholder="Nombre del líder">
            </div>

            <!-- DATOS DEL PRIMER REPORTE -->
            <h4 style="margin-top: 20px; margin-bottom: 10px; color: #333;">📊 Primer Reporte (Datos Iniciales)</h4>

            <div class="edit-modal-section">
                <p style="font-size: 13px; color: #666; margin: 0 0 10px 0;">
                    📌 Se creará como: <strong>Reunión Cotidiana</strong> (Gen <span id="newGroupGenDefault">0</span>)
                </p>
                <!-- Campo oculto para pasar la actividad -->
                <input type="hidden" name="actividad" value="reunion_cotidiana">
            </div>

            <div class="edit-modal-section">
                <label>Fecha del Primer Encuentro *</label>
                <input type="date" id="newGroupFecha" name="fecha" required>
            </div>

            <div class="edit-modal-section">
                <label>Asistencia en el Primer Encuentro</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label style="font-size: 13px;">👨 Hombres</label>
                        <input type="number" id="newGroupAsisHom" name="asistencia_hom" min="0" value="0" onchange="calculateTotalAsistencia()">
                    </div>
                    <div>
                        <label style="font-size: 13px;">👩 Mujeres</label>
                        <input type="number" id="newGroupAsisMuj" name="asistencia_muj" min="0" value="0" onchange="calculateTotalAsistencia()">
                    </div>
                    <div>
                        <label style="font-size: 13px;">👦 Jóvenes</label>
                        <input type="number" id="newGroupAsisJov" name="asistencia_jov" min="0" value="0" onchange="calculateTotalAsistencia()">
                    </div>
                    <div>
                        <label style="font-size: 13px;">🧒 Niños</label>
                        <input type="number" id="newGroupAsisNin" name="asistencia_nin" min="0" value="0" onchange="calculateTotalAsistencia()">
                    </div>
                </div>
                <div style="margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 4px;">
                    <small style="color: #666;">Total: <strong id="totalAsistenciaDisplay">0</strong></small>
                </div>
            </div>

            <div class="edit-modal-buttons">
                <button type="button" class="edit-modal-button cancel" onclick="closeCreateGroupModal()">Cancelar</button>
                <button type="submit" class="edit-modal-button save">Crear Grupo</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Nuevo Reporte -->
<div class="activity-modal" id="activityModal">
    <div class="activity-modal-content">
        <!-- Vista de selección de actividad -->
        <div id="activitySelection">
            <div class="activity-modal-header">
                <span>📝 Nuevo Reporte</span>
                <button class="activity-modal-close" onclick="closeActivityModal()">✕</button>
            </div>

            <p style="color: #666; margin-bottom: 20px;">Selecciona el tipo de actividad a reportar:</p>

            <div class="activity-options">
                <button type="button" class="activity-button evangelismo" onclick="selectActivity('evangelismo')">
                    <strong>🌍 Evangelismo</strong>
                    <span>Actividad de evangelización realizada</span>
                </button>

                <button type="button" class="activity-button gran-celebracion" onclick="selectActivity('gran_celebracion')">
                    <strong>🎉 Gran Celebración</strong>
                    <span>Evento especial de celebración</span>
                </button>

                <button type="button" class="activity-button bautizo" onclick="selectActivity('bautizo')">
                    <strong>💧 Bautizo</strong>
                    <span>Actividad de bautismo con evidencia fotográfica</span>
                </button>

                <button type="button" class="activity-button reunion" onclick="selectActivity('reunion_cotidiana')">
                    <strong>🤝 Reunión Cotidiana</strong>
                    <span>Reunión regular del grupo</span>
                </button>
            </div>
        </div>

        <!-- Vista de formulario de nuevo reporte -->
        <div id="reportForm" style="display: none;">
            <div class="activity-modal-header">
                <span id="formTitle">📝 Nuevo Reporte</span>
                <button class="activity-modal-close" onclick="closeActivityModal()">✕</button>
            </div>

            <form id="newReportForm" onsubmit="saveNewReport(event)" style="max-height: 500px; overflow-y: auto;">
                <!-- Campo de Fecha de Actividad -->
                <div class="form-group">
                    <label>Fecha de la Actividad</label>
                    <input type="date" id="fechaActividad" required>
                </div>

                <!-- Sección de Asistencia -->
                <div class="form-group">
                    <label id="asistenciaLabel">Asistencia</label>
                    <div class="attendance-grid">
                        <div class="attendance-input">
                            <label>Hombres</label>
                            <input type="number" id="asistencia_hom" min="0" value="0">
                        </div>
                        <div class="attendance-input">
                            <label>Mujeres</label>
                            <input type="number" id="asistencia_muj" min="0" value="0">
                        </div>
                        <div class="attendance-input">
                            <label>Jóvenes</label>
                            <input type="number" id="asistencia_jov" min="0" value="0">
                        </div>
                        <div class="attendance-input">
                            <label>Niños</label>
                            <input type="number" id="asistencia_nin" min="0" value="0">
                        </div>
                    </div>
                </div>

                <!-- Campo opcional: Bautizados -->
                <div class="form-group" id="bautizadosSection" style="display: none;">
                    <label>Cantidad de Bautizados</label>
                    <input type="number" id="bautizados" min="0" value="0">
                </div>

                <!-- Campo opcional: Decisiones -->
                <div class="form-group" id="decisionesSection" style="display: none;">
                    <label>Decisiones para Cristo</label>
                    <input type="number" id="desiciones" min="0" value="0">
                </div>

                <!-- Campo opcional: Comentarios -->
                <div class="form-group" id="comentariosSection" style="display: none;">
                    <label>Comentarios</label>
                    <textarea id="comentario" rows="3" placeholder="Detalles adicionales..."></textarea>
                </div>

                <!-- Sección de Evidencia Fotográfica -->
                <div class="form-group">
                    <label>📸 Evidencia Fotográfica (Opcional)</label>
                    <input type="file" id="fotosEvidencia" multiple accept="image/jpeg,image/png,image/jpg,image/webp" style="display: block; margin-top: 8px;">
                    <small style="color: #666; display: block; margin-top: 8px;">Máximo 5 MB por imagen. Formatos: JPG, PNG, WebP</small>
                    <div id="fotosPreview" style="margin-top: 12px; display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px;"></div>
                </div>

                <!-- Sección de Mapeos (solo para Reunión Cotidiana) -->
                <div id="mapeosSection" style="display: none;">
                    <h4 style="margin: 20px 0 15px 0; color: #333; border-bottom: 2px solid #2c3e50; padding-bottom: 10px;">Funciones Realizadas</h4>

                    <div class="form-group">
                        <label>Oración</label>
                        <select id="mapeo_oracion" class="mapeo-select">
                            <option value="0">Selecciona una opción</option>
                            <option value="1">No realiza la tarea</option>
                            <option value="2">Realiza en compañía del facilitador</option>
                            <option value="3">Realiza pero este mes no lo hizo</option>
                            <option value="4">Realiza autónomamente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Compañerismo</label>
                        <select id="mapeo_companerismo" class="mapeo-select">
                            <option value="0">Selecciona una opción</option>
                            <option value="1">No realiza la tarea</option>
                            <option value="2">Realiza en compañía del facilitador</option>
                            <option value="3">Realiza pero este mes no lo hizo</option>
                            <option value="4">Realiza autónomamente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Adoración</label>
                        <select id="mapeo_adoracion" class="mapeo-select">
                            <option value="0">Selecciona una opción</option>
                            <option value="1">No realiza la tarea</option>
                            <option value="2">Realiza en compañía del facilitador</option>
                            <option value="3">Realiza pero este mes no lo hizo</option>
                            <option value="4">Realiza autónomamente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Aplicar la Biblia</label>
                        <select id="mapeo_biblia" class="mapeo-select">
                            <option value="0">Selecciona una opción</option>
                            <option value="1">No realiza la tarea</option>
                            <option value="2">Realiza en compañía del facilitador</option>
                            <option value="3">Realiza pero este mes no lo hizo</option>
                            <option value="4">Realiza autónomamente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Evangelizar</label>
                        <select id="mapeo_evangelizar" class="mapeo-select">
                            <option value="0">Selecciona una opción</option>
                            <option value="1">No realiza la tarea</option>
                            <option value="2">Realiza en compañía del facilitador</option>
                            <option value="3">Realiza pero este mes no lo hizo</option>
                            <option value="4">Realiza autónomamente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Cena del Señor</label>
                        <select id="mapeo_cena" class="mapeo-select">
                            <option value="0">Selecciona una opción</option>
                            <option value="1">No realiza la tarea</option>
                            <option value="2">Realiza en compañía del facilitador</option>
                            <option value="3">Realiza pero este mes no lo hizo</option>
                            <option value="4">Realiza autónomamente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Dar (Ofrendas)</label>
                        <select id="mapeo_dar" class="mapeo-select">
                            <option value="0">Selecciona una opción</option>
                            <option value="1">No realiza la tarea</option>
                            <option value="2">Realiza en compañía del facilitador</option>
                            <option value="3">Realiza pero este mes no lo hizo</option>
                            <option value="4">Realiza autónomamente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Bautizar</label>
                        <select id="mapeo_bautizar" class="mapeo-select">
                            <option value="0">Selecciona una opción</option>
                            <option value="1">No realiza la tarea</option>
                            <option value="2">Realiza en compañía del facilitador</option>
                            <option value="3">Realiza pero este mes no lo hizo</option>
                            <option value="4">Realiza autónomamente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Entrenar Nuevos Líderes</label>
                        <select id="mapeo_trabajadores" class="mapeo-select">
                            <option value="0">Selecciona una opción</option>
                            <option value="1">No realiza la tarea</option>
                            <option value="2">Realiza en compañía del facilitador</option>
                            <option value="3">Realiza pero este mes no lo hizo</option>
                            <option value="4">Realiza autónomamente</option>
                        </select>
                    </div>

                    <!-- Gráfica de Mapeo en tiempo real -->
                    <div id="mapeoChartContainer" style="margin-top: 20px; text-align: center;">
                        <h4 style="color: #333; border-bottom: 2px solid #2c3e50; padding-bottom: 10px;">Imagen del Mapeo</h4>
                        <canvas id="mapeoCanvas" width="550" height="550" style="max-width: 100%; border: 1px solid #ddd; border-radius: 8px; background: #fff;"></canvas>
                    </div>
                </div>

                <input type="hidden" id="tipoActividad">
                <input type="hidden" id="reporteIds">

                <div class="modal-button-group">
                    <button type="button" class="modal-button secondary" onclick="backToActivitySelection()">Atrás</button>
                    <button type="submit" class="modal-button primary">Guardar Reporte</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let grupos = [];
    let filteredGrupos = [];
    let selectedGrupo = null;

    function toggleMobileMenu() {
        const mobileSlider = document.getElementById('mobileSlider');
        const sliderOverlay = document.getElementById('sliderOverlay');
        mobileSlider.classList.toggle('active');
        sliderOverlay.classList.toggle('active');
    }

    function closeMobileMenu() {
        const mobileSlider = document.getElementById('mobileSlider');
        const sliderOverlay = document.getElementById('sliderOverlay');
        mobileSlider.classList.remove('active');
        sliderOverlay.classList.remove('active');
    }

    function showStatusMessage(message, type = 'info') {
        const statusDiv = document.getElementById('statusMessage');
        statusDiv.textContent = message;
        statusDiv.className = `status-message ${type}`;
        setTimeout(() => {
            statusDiv.className = 'status-message';
        }, 5000);
    }

    function formatearLider(lider) {
        if (!lider) return 'No especificado';
        try {
            const parsed = JSON.parse(lider);
            if (Array.isArray(parsed)) {
                return parsed.filter(l => l && typeof l === 'string').join(', ') || 'No especificado';
            }
        } catch (e) {
            // Si no es JSON, retornar como está
        }
        return lider;
    }

    function selectGrupo(grupoData, element) {
        selectedGrupo = grupoData;

        // Actualizar estilos
        document.querySelectorAll('.group-item').forEach(item => {
            item.classList.remove('selected');
        });
        element.classList.add('selected');

        // Actualizar panel derecho
        updateGroupPanel(grupoData);

        // Cerrar slider en móvil
        if (window.innerWidth <= 768) {
            closeMobileMenu();
        }
    }

    function switchTab(tabName) {
        // Actualizar botones de pestaña
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');

        // Actualizar contenido de pestañas
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`tab-${tabName}`).classList.add('active');
    }

    function updateGroupPanel(grupoData) {
        const groupPanelTitle = document.getElementById('groupPanelTitle');
        const tabInfo = document.getElementById('tab-info');
        const tabReports = document.getElementById('tab-reports');

        groupPanelTitle.textContent = `📋 ${grupoData.nombre_exacto}`;

        // Actualizar tab de información
        updateInfoTab(grupoData, tabInfo);

        // Actualizar tab de reportes
        updateReportsTab(grupoData, tabReports);
    }

    function updateInfoTab(grupoData, tabInfo) {
        let generacionText = 'No especificada';
        if (grupoData.generacion) {
            generacionText = grupoData.generacion;
        }

        let infoHTML = `
            <button class="btn btn-primary" style="width: 100%; margin-bottom: 20px;" onclick="openEditModal()" title="Editar información del grupo">
                ✏️ Editar Información
            </button>

            <div class="info-section">
                <div class="info-label">Nombre del Grupo</div>
                <div class="info-value">${grupoData.nombre_exacto || 'No especificado'}</div>
            </div>
            <div class="info-section">
                <div class="info-label">Generación</div>
                <div class="info-value">${generacionText}</div>
            </div>
            <div class="info-section">
                <div class="info-label">Ubicación</div>
                <div class="info-value">${grupoData.ubicacion || 'No especificada'}</div>
            </div>
            <div class="info-section">
                <div class="info-label">Dirección</div>
                <div class="info-value">${grupoData.direccion || 'No especificada'}</div>
            </div>
            <div class="info-section">
                <div class="info-label">Barrio</div>
                <div class="info-value">${grupoData.barrio || 'No especificado'}</div>
            </div>
            <div class="info-section">
                <div class="info-label">Grupo Madre</div>
                <div class="info-value">${grupoData.grupo_madre || 'No especificado'}</div>
            </div>
            <div class="info-section">
                <div class="info-label">Líder</div>
                <div class="info-value">${formatearLider(grupoData.lider)}</div>
            </div>
            <div class="info-section">
                <div class="info-label">Total de Reportes</div>
                <div class="info-value">${grupoData.reportes || 0}</div>
            </div>
        `;

        // Agregar sección de imágenes si hay reportes
        if (grupoData.reportes_ids && grupoData.reportes_ids.length > 0) {
            infoHTML += `
                <div class="info-divider"></div>
                <div class="info-section">
                    <div class="info-label">📸 Imágenes de Reportes</div>
                    <div id="imagesContainer" class="images-grid">
                        <div style="text-align: center; color: #999; padding: 20px;">Cargando imágenes...</div>
                    </div>
                </div>
            `;
        }

        tabInfo.innerHTML = infoHTML;

        // Cargar imágenes si existen reportes
        if (grupoData.reportes_ids && grupoData.reportes_ids.length > 0) {
            loadImagesInInfoSection(grupoData.reportes_ids);
        }
    }

    function loadImagesInInfoSection(reporteIds) {
        console.log('loadImagesInInfoSection llamado con reporteIds:', reporteIds);

        fetch('obtener_imagenes_reportes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                reporteIds: reporteIds
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Datos recibidos en loadImagesInInfoSection:', data);

            const container = document.getElementById('imagesContainer');
            if (!container) {
                console.warn('Contenedor imagesContainer no encontrado');
                return;
            }

            if (data.success && data.imagenes && data.imagenes.length > 0) {
                console.log('Imágenes recibidas:', data.imagenes);

                const imagesHTML = data.imagenes.map((img, index) => {
                    const thumbnail = img.rutaThumbnail || img.ruta;
                    return `
                        <div class="image-item" style="position: relative; width: 100%; aspect-ratio: 1; border-radius: 4px; overflow: hidden; cursor: pointer; border: 1px solid #ddd; background: #f5f5f5;">
                            <img src="${thumbnail}" alt="${img.nombre}"
                                 style="width: 100%; height: 100%; object-fit: cover;"
                                 title="Reporte ${img.reporte_id}">
                            <div style="position: absolute; inset: 0; background: rgba(0,0,0,0); transition: background 0.2s;"
                                 onmouseover="this.style.background='rgba(0,0,0,0.2)'"
                                 onmouseout="this.style.background='rgba(0,0,0,0)'"></div>
                        </div>
                    `;
                }).join('');

                container.innerHTML = imagesHTML;

                // Agregar event listeners para abrir modal
                container.querySelectorAll('.image-item').forEach((item, idx) => {
                    item.addEventListener('click', function() {
                        const allImages = data.imagenes.map(i => i.ruta);
                        openImageModal(idx, allImages);
                    });
                });

                console.log('Imágenes cargadas en info section');
            } else {
                container.innerHTML = '<div style="text-align: center; color: #999; padding: 20px;">No hay imágenes disponibles</div>';
            }
        })
        .catch(error => {
            console.error('Error al cargar imágenes en info section:', error);
            const container = document.getElementById('imagesContainer');
            if (container) {
                container.innerHTML = '<div style="text-align: center; color: #f44; padding: 20px;">Error al cargar imágenes</div>';
            }
        });
    }

    function loadReportImagesAndInfo(reporteIds) {
        console.log('loadReportImagesAndInfo llamado con reporteIds:', reporteIds);

        // Fetch para obtener las imágenes e información de los reportes
        fetch('obtener_imagenes_reportes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                reporteIds: reporteIds
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos de obtener_imagenes_reportes.php:', data);

            if (data.success && data.imagenes) {
                console.log('Imágenes recibidas:', data.imagenes);

                // Agrupar imágenes por reporte
                const imagesByReport = {};
                data.imagenes.forEach(img => {
                    if (!imagesByReport[img.reporte_id]) {
                        imagesByReport[img.reporte_id] = [];
                    }
                    imagesByReport[img.reporte_id].push(img);
                });

                console.log('Imágenes agrupadas por reporte:', imagesByReport);

                // Llenar los contenedores de cada reporte
                Object.keys(imagesByReport).forEach(reporteId => {
                    console.log(`Buscando contenedor para reporte ${reporteId}`);
                    const container = document.getElementById(`images-${reporteId}`);
                    if (container) {
                        console.log(`Contenedor encontrado para reporte ${reporteId}`);
                        const imagesArray = imagesByReport[reporteId].map(i => i.ruta);
                        const imagesHTML = imagesByReport[reporteId].map((img, index) => {
                            const thumbnail = img.rutaThumbnail || img.ruta;
                            return `
                                <div class="image-thumbnail" data-index="${index}" data-report="${reporteId}" style="position: relative; width: 100%; aspect-ratio: 1; border-radius: 4px; overflow: hidden; cursor: pointer; border: 1px solid #ddd; background: #f5f5f5;">
                                    <img src="${thumbnail}" alt="${img.nombre}"
                                         style="width: 100%; height: 100%; object-fit: cover;"
                                         title="${img.nombre}">
                                    <div style="position: absolute; inset: 0; background: rgba(0,0,0,0); transition: background 0.2s;"
                                         onmouseover="this.style.background='rgba(0,0,0,0.2)'"
                                         onmouseout="this.style.background='rgba(0,0,0,0)'"></div>
                                </div>
                            `;
                        }).join('');
                        container.innerHTML = imagesHTML;
                        console.log(`Imágenes HTML insertadas en contenedor ${reporteId}`);

                        // Agregar event listeners a las miniaturas
                        container.querySelectorAll('.image-thumbnail').forEach((thumb, idx) => {
                            thumb.addEventListener('click', function() {
                                openImageModal(idx, imagesArray);
                            });
                        });
                    } else {
                        console.warn(`Contenedor no encontrado para reporte ${reporteId}`);
                    }
                });

                // Cargar información de los reportes desde la BD
                if (data.reportes) {
                    console.log('Reportes recibidos:', data.reportes);
                    data.reportes.forEach(reporte => {
                        console.log(`Procesando reporte ${reporte.id}`);
                        const infoContainer = document.getElementById(`report-info-${reporte.id}`);
                        if (infoContainer) {
                            console.log(`Contenedor info encontrado para reporte ${reporte.id}`);
                            const tiposActividad = {
                                '77': '🌍 Evangelismo',
                                '8': '🎉 Gran Celebración',
                                '99': '💧 Bautizo',
                                '1': '🤝 Reunión Cotidiana (1)',
                                '2': '🤝 Reunión Cotidiana (2)',
                                '3': '🤝 Reunión Cotidiana (3)',
                                '4': '🤝 Reunión Cotidiana (4)',
                                '5': '🤝 Reunión Cotidiana (5)'
                            };
                            const tipo = tiposActividad[reporte.generacionNumero] || 'Desconocido';
                            const fecha = new Date(reporte.fechaInicio).toLocaleDateString('es-CO', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                            infoContainer.innerHTML = `<strong>${tipo}</strong> • ${fecha} • Asistencia: ${reporte.asistencia_total}`;

                            // Agregar botón "Ver Mapeo" para reportes de Reunión Cotidiana (generación 1-5)
                            const gen = parseInt(reporte.generacionNumero);
                            if (gen >= 1 && gen <= 5) {
                                const btnContainer = document.getElementById(`mapeo-btn-${reporte.id}`);
                                if (btnContainer) {
                                    btnContainer.innerHTML = `<button onclick="toggleMapeoChart(${reporte.id})" class="btn btn-sm btn-info" style="margin-top: 8px; font-size: 11px; padding: 4px 10px; border-radius: 4px;">📊 Ver Mapeo</button>`;
                                    // Guardar datos de mapeo en el botón para usarlos al renderizar
                                    btnContainer.dataset.mapeo = JSON.stringify({
                                        mapeo_oracion: parseInt(reporte.mapeo_oracion) || 0,
                                        mapeo_companerismo: parseInt(reporte.mapeo_companerismo) || 0,
                                        mapeo_adoracion: parseInt(reporte.mapeo_adoracion) || 0,
                                        mapeo_biblia: parseInt(reporte.mapeo_biblia) || 0,
                                        mapeo_evangelizar: parseInt(reporte.mapeo_evangelizar) || 0,
                                        mapeo_cena: parseInt(reporte.mapeo_cena) || 0,
                                        mapeo_dar: parseInt(reporte.mapeo_dar) || 0,
                                        mapeo_bautizar: parseInt(reporte.mapeo_bautizar) || 0,
                                        mapeo_trabajadores: parseInt(reporte.mapeo_trabajadores) || 0
                                    });
                                }
                            }
                        } else {
                            console.warn(`Contenedor info no encontrado para reporte ${reporte.id}`);
                        }
                    });
                } else {
                    console.warn('No se recibieron reportes en data.reportes');
                }
            } else {
                console.warn('Data no es success o imagenes vacío:', data);
            }
        })
        .catch(error => {
            console.error('Error al cargar imágenes:', error);
        });
    }

    function loadReportImages(reporteIds) {
        // Mantener función antigua para compatibilidad
        loadReportImagesAndInfo(reporteIds);
    }

    function openImageModal(currentIndex, imagesArray) {
        let currentImageIndex = currentIndex;
        const images = imagesArray;
        const totalImages = images.length;

        // Crear modal de imagen
        const modal = document.createElement('div');
        modal.className = 'image-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            overflow: auto;
        `;

        function updateImage() {
            const imgContainer = modal.querySelector('.image-container');
            const imgElement = modal.querySelector('.modal-image');
            const counterElement = modal.querySelector('.image-counter');

            imgElement.src = images[currentImageIndex];
            counterElement.textContent = `${currentImageIndex + 1} de ${totalImages}`;

            // Habilitar/deshabilitar botones
            modal.querySelector('.btn-prev').disabled = currentImageIndex === 0;
            modal.querySelector('.btn-next').disabled = currentImageIndex === totalImages - 1;
        }

        function nextImage() {
            if (currentImageIndex < totalImages - 1) {
                currentImageIndex++;
                updateImage();
            }
        }

        function prevImage() {
            if (currentImageIndex > 0) {
                currentImageIndex--;
                updateImage();
            }
        }

        function closeModal() {
            document.removeEventListener('keydown', handleKeypress);
            modal.remove();
        }

        function handleKeypress(e) {
            if (e.key === 'Escape') closeModal();
            if (e.key === 'ArrowRight') nextImage();
            if (e.key === 'ArrowLeft') prevImage();
        }

        modal.innerHTML = `
            <div style="position: relative; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px 20px 20px;">
                <div class="image-container" style="display: flex; align-items: center; justify-content: center; position: relative; flex: 1; width: 100%; max-width: 95vw; overflow: auto;">
                    <img class="modal-image" src="${images[currentImageIndex]}" style="max-width: 95vw; max-height: 75vh; width: auto; height: auto; border-radius: 8px; object-fit: contain;">
                </div>

                <div style="display: flex; align-items: center; gap: 15px; margin-top: 20px; width: 100%; justify-content: center; flex-shrink: 0;">
                    <button class="btn-prev" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4); padding: 8px 12px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 16px; transition: all 0.2s;">◀ Anterior</button>
                    <span class="image-counter" style="color: white; font-size: 14px; min-width: 80px; text-align: center;">${currentImageIndex + 1} de ${totalImages}</span>
                    <button class="btn-next" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4); padding: 8px 12px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 16px; transition: all 0.2s;">Siguiente ▶</button>
                </div>

                <button class="btn-close" style="position: absolute; top: 10px; right: 10px; background: rgba(255,255,255,0.95); border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.3); z-index: 1001; transition: all 0.2s;">✕ Cerrar</button>
            </div>
        `;

        // Agregar event listeners
        modal.querySelector('.btn-next').addEventListener('click', nextImage);
        modal.querySelector('.btn-prev').addEventListener('click', prevImage);
        modal.querySelector('.btn-close').addEventListener('click', closeModal);

        // Cerrar al hacer clic fuera de la imagen
        modal.addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        // Agregar listener para teclado
        document.addEventListener('keydown', handleKeypress);

        document.body.appendChild(modal);
        updateImage();
    }

    function updateReportsTab(grupoData, tabReports) {
        if (grupoData.reportes_ids && grupoData.reportes_ids.length > 0) {
            const reportsHTML = grupoData.reportes_ids.map((reporteId, index) => `
                <div class="report-item" style="display: flex; gap: 15px; padding: 12px; border: 1px solid #e0e0e0; border-radius: 6px; background: #fafafa; margin-bottom: 10px;">
                    <div style="flex-shrink: 0;">
                        <div style="background: #2c3e50; color: white; width: 60px; height: 60px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 20px;">
                            #${reporteId}
                        </div>
                    </div>
                    <div style="flex-grow: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <strong style="color: #333;">Reporte ${index + 1} de ${grupoData.reportes_ids.length}</strong>
                            <span style="background: #e8e8e8; color: #2c3e50; padding: 4px 8px; border-radius: 4px; font-size: 12px;">ID: ${reporteId}</span>
                        </div>
                        <div id="report-info-${reporteId}" style="font-size: 12px; color: #666; margin-bottom: 8px;">
                            Cargando información...
                        </div>
                        <div id="images-${reporteId}" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(60px, 1fr)); gap: 6px; margin-top: 8px;">
                            <!-- Las imágenes se cargarán aquí -->
                        </div>
                        <div id="mapeo-btn-${reporteId}"></div>
                        <div id="mapeo-chart-${reporteId}" style="display: none; text-align: center; margin-top: 10px;">
                            <canvas id="mapeo-canvas-${reporteId}" width="400" height="400" style="max-width: 100%; border: 1px solid #ddd; border-radius: 8px; background: #fff;"></canvas>
                        </div>
                    </div>
                </div>
            `).join('');

            tabReports.innerHTML = reportsHTML;

            // Cargar información e imágenes de los reportes
            if (grupoData.reportes_ids && grupoData.reportes_ids.length > 0) {
                loadReportImagesAndInfo(grupoData.reportes_ids);
            }
        } else {
            tabReports.innerHTML = `
                <div class="empty-message">
                    <p>Este grupo no tiene reportes registrados</p>
                </div>
            `;
        }
    }

    function renderGroups() {
        const groupsList = document.getElementById('groupsList');
        const leftPanelHeader = document.querySelector('.left-panel-header');

        if (filteredGrupos.length === 0) {
            groupsList.innerHTML = `
                <div class="empty-message">
                    <p>No se encontraron grupos</p>
                </div>
            `;
            return;
        }

        leftPanelHeader.textContent = `📋 Mis Grupos (${filteredGrupos.length})`;

        const groupsHTML = filteredGrupos.map((grupoData) => {
            return `
                <div class="group-item">
                    <div class="group-item-title">${grupoData.nombre_exacto}</div>
                    <div class="group-item-info">
                        <div>📍 ${grupoData.ubicacion || 'Sin ubicación'}</div>
                        <div>📊 ${grupoData.reportes} reporte(s)</div>
                    </div>
                </div>
            `;
        }).join('');

        groupsList.innerHTML = groupsHTML;

        // Agregar listeners de click a los items
        document.querySelectorAll('.group-item').forEach((item, index) => {
            item.addEventListener('click', function() {
                selectGrupo(filteredGrupos[index], this);
            });
        });
    }

    function filterGroups() {
        const searchTerm = document.getElementById('searchFilter').value.toLowerCase();
        filteredGrupos = grupos.filter(grupo => {
            const nombre = (grupo.nombre_exacto || '').toLowerCase();
            const ubicacion = (grupo.ubicacion || '').toLowerCase();
            const lider = (grupo.lider || '').toLowerCase();
            return nombre.includes(searchTerm) || ubicacion.includes(searchTerm) || lider.includes(searchTerm);
        });
        renderGroups();
    }

    function newReport() {
        if (!selectedGrupo) {
            showStatusMessage('Por favor selecciona un grupo primero', 'error');
            return;
        }

        // Abrir modal de selección de actividad
        openActivityModal();
    }

    function openActivityModal() {
        const modal = document.getElementById('activityModal');
        modal.classList.add('active');

        // Reset form
        document.getElementById('activitySelection').style.display = 'block';
        document.getElementById('reportForm').style.display = 'none';

        // Guardar IDs de reportes del grupo seleccionado
        if (selectedGrupo) {
            document.getElementById('reporteIds').value = JSON.stringify(selectedGrupo.reportes_ids);
        }
    }

    function closeActivityModal() {
        const modal = document.getElementById('activityModal');
        modal.classList.remove('active');
    }

    function selectActivity(tipoActividad) {
        // Guardar tipo de actividad
        document.getElementById('tipoActividad').value = tipoActividad;

        // Establecer fecha actual como valor por defecto
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('fechaActividad').value = today;

        // Mostrar/ocultar campos según tipo de actividad
        const bautizadosSection = document.getElementById('bautizadosSection');
        const decisionesSection = document.getElementById('decisionesSection');
        const comentariosSection = document.getElementById('comentariosSection');
        const mapeosSection = document.getElementById('mapeosSection');
        const asistenciaLabel = document.getElementById('asistenciaLabel');

        // Ocultar todos por defecto
        bautizadosSection.style.display = 'none';
        decisionesSection.style.display = 'none';
        comentariosSection.style.display = 'none';
        mapeosSection.style.display = 'none';

        // Mostrar según tipo
        if (tipoActividad === 'bautizo') {
            bautizadosSection.style.display = 'block';
            asistenciaLabel.textContent = 'Asistencia (opcional)';
        } else if (tipoActividad === 'gran_celebracion') {
            comentariosSection.style.display = 'block';
            asistenciaLabel.textContent = 'Asistencia';
        } else if (tipoActividad === 'evangelismo') {
            asistenciaLabel.textContent = 'Alcanzados';
        } else if (tipoActividad === 'reunion_cotidiana') {
            decisionesSection.style.display = 'block';
            mapeosSection.style.display = 'block';
            asistenciaLabel.textContent = 'Asistencia';
        }

        // Cambiar título del formulario
        const titles = {
            'evangelismo': '🌍 Nuevo Reporte - Evangelismo',
            'gran_celebracion': '🎉 Nuevo Reporte - Gran Celebración',
            'bautizo': '💧 Nuevo Reporte - Bautizo',
            'reunion_cotidiana': '🤝 Nuevo Reporte - Reunión Cotidiana'
        };
        document.getElementById('formTitle').textContent = titles[tipoActividad] || 'Nuevo Reporte';

        // Cambiar a vista de formulario
        document.getElementById('activitySelection').style.display = 'none';
        document.getElementById('reportForm').style.display = 'block';
    }

    function backToActivitySelection() {
        document.getElementById('activitySelection').style.display = 'block';
        document.getElementById('reportForm').style.display = 'none';
    }

    // Manejar vista previa de imágenes y validación
    document.addEventListener('DOMContentLoaded', function() {
        const fotosInput = document.getElementById('fotosEvidencia');
        const fotosPreview = document.getElementById('fotosPreview');

        if (fotosInput) {
            fotosInput.addEventListener('change', function(e) {
                fotosPreview.innerHTML = '';
                const files = Array.from(this.files);
                const MAX_SIZE = 5 * 1024 * 1024; // 5 MB
                const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

                files.forEach((file, index) => {
                    // Validar tipo
                    if (!ALLOWED_TYPES.includes(file.type)) {
                        showStatusMessage(`❌ Archivo ${file.name}: formato no permitido`, 'error');
                        return;
                    }

                    // Validar tamaño
                    if (file.size > MAX_SIZE) {
                        showStatusMessage(`❌ Archivo ${file.name}: excede 5 MB`, 'error');
                        return;
                    }

                    // Crear vista previa
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const preview = document.createElement('div');
                        preview.style.cssText = `
                            position: relative;
                            width: 100%;
                            aspect-ratio: 1;
                            border-radius: 6px;
                            overflow: hidden;
                            background: #f0f0f0;
                            border: 2px solid #2c3e50;
                        `;
                        preview.innerHTML = `
                            <img src="${event.target.result}" style="width: 100%; height: 100%; object-fit: cover;">
                            <div style="position: absolute; top: 4px; right: 4px; background: rgba(0,0,0,0.6); color: white; font-size: 11px; padding: 2px 6px; border-radius: 3px;">${index + 1}</div>
                        `;
                        fotosPreview.appendChild(preview);
                    };
                    reader.readAsDataURL(file);
                });
            });
        }
    });

    function saveNewReport(event) {
        event.preventDefault();

        if (!selectedGrupo) {
            showStatusMessage('No hay grupo seleccionado', 'error');
            return;
        }

        const tipoActividad = document.getElementById('tipoActividad').value;
        const reporteIds = JSON.parse(document.getElementById('reporteIds').value || '[]');

        const datosReporte = {
            tipoActividad: tipoActividad,
            reporteIds: reporteIds,
            fechaActividad: document.getElementById('fechaActividad').value,
            asistencia_hom: parseInt(document.getElementById('asistencia_hom').value) || 0,
            asistencia_muj: parseInt(document.getElementById('asistencia_muj').value) || 0,
            asistencia_jov: parseInt(document.getElementById('asistencia_jov').value) || 0,
            asistencia_nin: parseInt(document.getElementById('asistencia_nin').value) || 0
        };

        // Agregar campos opcionales si están visibles
        if (document.getElementById('bautizadosSection').style.display !== 'none') {
            datosReporte.bautizados = parseInt(document.getElementById('bautizados').value) || 0;
        }
        if (document.getElementById('decisionesSection').style.display !== 'none') {
            datosReporte.desiciones = parseInt(document.getElementById('desiciones').value) || 0;
        }
        if (document.getElementById('comentariosSection').style.display !== 'none') {
            datosReporte.comentario = document.getElementById('comentario').value;
        }
        if (document.getElementById('mapeosSection').style.display !== 'none') {
            datosReporte.mapeo_oracion = parseInt(document.getElementById('mapeo_oracion').value) || 0;
            datosReporte.mapeo_companerismo = parseInt(document.getElementById('mapeo_companerismo').value) || 0;
            datosReporte.mapeo_adoracion = parseInt(document.getElementById('mapeo_adoracion').value) || 0;
            datosReporte.mapeo_biblia = parseInt(document.getElementById('mapeo_biblia').value) || 0;
            datosReporte.mapeo_evangelizar = parseInt(document.getElementById('mapeo_evangelizar').value) || 0;
            datosReporte.mapeo_cena = parseInt(document.getElementById('mapeo_cena').value) || 0;
            datosReporte.mapeo_dar = parseInt(document.getElementById('mapeo_dar').value) || 0;
            datosReporte.mapeo_bautizar = parseInt(document.getElementById('mapeo_bautizar').value) || 0;
            datosReporte.mapeo_trabajadores = parseInt(document.getElementById('mapeo_trabajadores').value) || 0;
        }

        console.log('Datos del reporte:', datosReporte);

        // Mostrar estado de carga
        const btnSubmit = event.target.querySelector('button[type="submit"]');
        const textOriginal = btnSubmit.textContent;
        btnSubmit.textContent = 'Guardando...';
        btnSubmit.disabled = true;

        fetch('crear_reporte.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(datosReporte)
        })
        .then(response => response.text().then(text => {
            console.log('Response status:', response.status);
            console.log('Response text:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                throw new Error('Invalid JSON response: ' + text.substring(0, 100));
            }
        }))
        .then(data => {
            btnSubmit.textContent = textOriginal;
            btnSubmit.disabled = false;

            if (data.success) {
                // Reporte creado exitosamente
                const nuevoReporteId = data.nuevoReporteId;

                // Procesar imágenes si existen
                const fotosInput = document.getElementById('fotosEvidencia');
                if (fotosInput && fotosInput.files.length > 0) {
                    btnSubmit.textContent = 'Guardando imágenes...';
                    btnSubmit.disabled = true;
                    uploadReportImages(nuevoReporteId, fotosInput.files, btnSubmit, textOriginal);
                } else {
                    showStatusMessage(`✅ Reporte creado correctamente`, 'success');
                    closeActivityModal();

                    // Recargar reportes del grupo
                    if (selectedGrupo) {
                        updateGroupPanel(selectedGrupo);
                    }
                }
            } else {
                showStatusMessage('Error: ' + (data.message || 'No se pudo crear el reporte'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btnSubmit.textContent = textOriginal;
            btnSubmit.disabled = false;
            showStatusMessage('Error de conexión al guardar el reporte: ' + error.message, 'error');
        });
    }

    function uploadReportImages(reporteId, files, btnSubmit, textOriginal) {
        const formData = new FormData();
        formData.append('reporteId', reporteId);

        // Validar y agregar imágenes
        let validFiles = 0;
        const MAX_SIZE = 5 * 1024 * 1024;
        const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

        for (let i = 0; i < files.length; i++) {
            if (ALLOWED_TYPES.includes(files[i].type) && files[i].size <= MAX_SIZE) {
                formData.append(`imagenes[]`, files[i]);
                validFiles++;
            }
        }

        if (validFiles === 0) {
            showStatusMessage('No hay imágenes válidas para guardar', 'error');
            btnSubmit.textContent = textOriginal;
            btnSubmit.disabled = false;
            return;
        }

        fetch('guardar_imagenes_reporte.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.text().then(text => {
                console.log('Response text:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    console.error('Raw response:', text);
                    throw new Error('Invalid JSON response: ' + text.substring(0, 200));
                }
            });
        })
        .then(data => {
            btnSubmit.textContent = textOriginal;
            btnSubmit.disabled = false;

            if (data.success) {
                showStatusMessage(`✅ Reporte creado con ${data.imagesCount} imagen(s)`, 'success');
                closeActivityModal();

                // Recargar reportes del grupo
                if (selectedGrupo) {
                    updateGroupPanel(selectedGrupo);
                }
            } else {
                showStatusMessage('Error al guardar imágenes: ' + (data.message || 'Error desconocido'), 'error');
            }
        })
        .catch(error => {
            console.error('Error al subir imágenes:', error);
            console.error('Error details:', error.message);
            btnSubmit.textContent = textOriginal;
            btnSubmit.disabled = false;
            showStatusMessage('Error al guardar imágenes: ' + error.message, 'error');
        });
    }

    function loadGrupos() {
        console.log('🔄 Cargando grupos del facilitador...');

        fetch('obtener_variantes_grupos_facilitador.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                idFacilitador: <?php echo $idFacilitador; ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('✅ Datos recibidos:', data);

            if (data.success) {
                // Traer todos los grupos del facilitador
                grupos = data.grupos || [];
                filteredGrupos = [...grupos];

                console.log(`📊 Se encontraron ${grupos.length} grupos`);

                if (grupos.length === 0) {
                    showStatusMessage('No hay grupos para mostrar.', 'info');
                } else {
                    showStatusMessage(`Se cargaron ${grupos.length} grupos correctamente`, 'success');
                }

                renderGroups();
            } else {
                showStatusMessage('Error: ' + (data.message || 'No se pudieron cargar los grupos'), 'error');
                console.error('Error:', data.message);
            }
        })
        .catch(error => {
            console.error('Error al cargar grupos:', error);
            showStatusMessage('Error al conectar con el servidor', 'error');
        });
    }

    // Cargar grupos al iniciar
    document.addEventListener('DOMContentLoaded', () => {
        loadGrupos();
    });

    // ===== FUNCIONES DE EDICIÓN =====
    let editFormData = {
        lideresArray: []
    };

    function openEditModal() {
        if (!selectedGrupo) {
            showStatusMessage('Por favor selecciona un grupo primero', 'info');
            return;
        }

        // Llenar formulario con datos actuales del grupo
        document.getElementById('editNombre').value = selectedGrupo.nombre_exacto || '';
        document.getElementById('editCiudad').value = selectedGrupo.ciudad || '';
        document.getElementById('editBarrio').value = selectedGrupo.barrio || '';
        document.getElementById('editDireccion').value = selectedGrupo.direccion || '';
        document.getElementById('editGeneracion').value = selectedGrupo.generacion || '0';

        // Establecer radio buttons para grupo madre
        // Si generación es 0, entonces no tiene grupo madre (debe ser "no aplica")
        const tieneGrupoMadre = (selectedGrupo.generacion === 0 || selectedGrupo.grupo_madre === 'no aplica' || !selectedGrupo.grupo_madre) ? 'no' : 'si';
        document.querySelector('input[name="editTieneGrupoMadre"][value="' + tieneGrupoMadre + '"]').checked = true;

        // Mostrar u ocultar selector de grupo madre según corresponda
        const grupoMadreSelect = document.getElementById('editGrupoMadreSelect');
        if (tieneGrupoMadre === 'si') {
            grupoMadreSelect.style.display = 'block';
            loadAvailableGroupsForEditModal(selectedGrupo.id_unico);
            // Esperar a que se carguen los grupos para establecer el seleccionado
            setTimeout(() => {
                const dropdown = document.getElementById('editGrupoMadreDropdown');
                // Encontrar la opción que corresponde al grupo madre actual
                for (let i = 0; i < dropdown.options.length; i++) {
                    if (dropdown.options[i].textContent.includes(selectedGrupo.grupo_madre)) {
                        dropdown.selectedIndex = i;
                        updateEditGeneracionDisplay();
                        break;
                    }
                }
            }, 300);
        } else {
            grupoMadreSelect.style.display = 'none';
            document.getElementById('editGrupoMadreDropdown').value = '';
        }

        // Procesar líderes (pueden ser JSON array o string)
        editFormData.lideresArray = [];
        if (selectedGrupo.lider) {
            try {
                const liderParsed = JSON.parse(selectedGrupo.lider);
                if (Array.isArray(liderParsed)) {
                    editFormData.lideresArray = liderParsed.filter(l => l && typeof l === 'string');
                }
            } catch (e) {
                // Si no es JSON, tratarlo como string simple
                editFormData.lideresArray = [selectedGrupo.lider];
            }
        }

        renderLideresUI();
        document.getElementById('liderInput').value = '';

        // Mostrar modal
        document.getElementById('editModal').classList.add('active');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
        editFormData.lideresArray = [];
    }

    function renderLideresUI() {
        const container = document.getElementById('lideresContainer');
        container.innerHTML = editFormData.lideresArray.map((lider, index) => `
            <div class="edit-lider-tag">
                ${lider}
                <button type="button" onclick="removeLider(${index})">✕</button>
            </div>
        `).join('');
    }

    function addLider() {
        const liderInput = document.getElementById('liderInput');
        const nuevoLider = liderInput.value.trim();

        if (nuevoLider && !editFormData.lideresArray.includes(nuevoLider)) {
            editFormData.lideresArray.push(nuevoLider);
            liderInput.value = '';
            renderLideresUI();
        } else if (editFormData.lideresArray.includes(nuevoLider)) {
            showStatusMessage('Este líder ya existe', 'info');
        }
    }

    function removeLider(index) {
        editFormData.lideresArray.splice(index, 1);
        renderLideresUI();
    }

    // ============= FUNCIONES PARA CREAR NUEVO GRUPO =============

    function openCreateGroupModal() {
        console.log('Abriendo modal para crear grupo');
        const modal = document.getElementById('createGroupModal');
        modal.classList.add('active');

        // Cargar grupos disponibles como grupo madre
        loadAvailableGroupsForParent();

        // Limpiar formulario
        document.getElementById('createGroupForm').reset();
        document.getElementById('grupoMadreSelect').style.display = 'none';
    }

    function closeCreateGroupModal() {
        document.getElementById('createGroupModal').classList.remove('active');
        document.getElementById('createGroupForm').reset();
        document.getElementById('grupoMadreSelect').style.display = 'none';
    }

    function toggleGrupoMadreSelect() {
        const tieneGrupoMadre = document.querySelector('input[name="tieneGrupoMadre"]:checked').value;
        const grupoMadreSelect = document.getElementById('grupoMadreSelect');
        const grupoMadreDropdown = document.getElementById('grupoMadreDropdown');

        if (tieneGrupoMadre === 'si') {
            grupoMadreSelect.style.display = 'block';
            grupoMadreDropdown.required = true;
        } else {
            grupoMadreSelect.style.display = 'none';
            grupoMadreDropdown.required = false;
            grupoMadreDropdown.value = '';
            // Establecer generación a 0 cuando no tiene grupo madre
            document.getElementById('newGroupGenDefault').textContent = '0';
            document.getElementById('generacionInfo').style.display = 'none';
        }
    }

    function loadAvailableGroupsForParent() {
        console.log('Cargando grupos disponibles para ser grupo madre');

        fetch('obtener_variantes_grupos_facilitador.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                idFacilitador: <?php echo $idFacilitador; ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Grupos recibidos:', data);

            if (data.success && data.grupos) {
                // Filtrar grupos que NO sean generación 5
                const gruposValidos = data.grupos.filter(g => g.generacion < 5);

                const dropdown = document.getElementById('grupoMadreDropdown');
                dropdown.innerHTML = '<option value="">-- Seleccionar grupo madre --</option>';

                gruposValidos.forEach(grupo => {
                    const option = document.createElement('option');
                    option.value = grupo.id_unico;
                    const grupoMadreInfo = grupo.grupo_madre ? ` (${grupo.grupo_madre})` : ' (Raíz)';
                    option.textContent = `${grupo.nombre_exacto} (Gen ${grupo.generacion})${grupoMadreInfo} - ${grupo.lider}`;
                    option.dataset.generacion = grupo.generacion;
                    dropdown.appendChild(option);
                });

                // Agregar event listener para actualizar generación
                dropdown.addEventListener('change', function() {
                    updateGeneracionDisplay();
                });
            }
        })
        .catch(error => console.error('Error al cargar grupos madre:', error));
    }

    function updateGeneracionDisplay() {
        const dropdown = document.getElementById('grupoMadreDropdown');
        const selectedOption = dropdown.options[dropdown.selectedIndex];
        const generacionInfo = document.getElementById('generacionInfo');
        const generacionDisplay = document.getElementById('generacionDisplay');
        const generacionDefault = document.getElementById('newGroupGenDefault');

        if (dropdown.value) {
            const generacionMadre = parseInt(selectedOption.dataset.generacion);
            const generacionNueva = generacionMadre + 1;
            generacionDisplay.textContent = generacionNueva;
            generacionDefault.textContent = generacionNueva;
            generacionInfo.style.display = 'block';
        } else {
            generacionInfo.style.display = 'none';
            generacionDefault.textContent = '0';
        }
    }

    function toggleEditGrupoMadreSelect() {
        const tieneGrupoMadre = document.querySelector('input[name="editTieneGrupoMadre"]:checked').value;
        const grupoMadreSelect = document.getElementById('editGrupoMadreSelect');
        const grupoMadreDropdown = document.getElementById('editGrupoMadreDropdown');

        if (tieneGrupoMadre === 'si') {
            grupoMadreSelect.style.display = 'block';
            grupoMadreDropdown.required = true;
            loadAvailableGroupsForEditModal(selectedGrupo.id_unico);
        } else {
            grupoMadreSelect.style.display = 'none';
            grupoMadreDropdown.required = false;
            grupoMadreDropdown.value = '';
            // Establecer generación a 0 cuando no tiene grupo madre
            document.getElementById('editGeneracion').value = '0';
            document.getElementById('editGeneracionInfo').style.display = 'none';
        }
    }

    function loadAvailableGroupsForEditModal(currentGroupId) {
        console.log('Cargando grupos disponibles para editar grupo madre, excluyendo:', currentGroupId);

        fetch('obtener_variantes_grupos_facilitador.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                idFacilitador: <?php echo $idFacilitador; ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Grupos recibidos para edición:', data);

            if (data.success && data.grupos) {
                // Filtrar grupos que NO sean generación 5 y que NO sean el grupo actual
                const gruposValidos = data.grupos.filter(g =>
                    g.generacion < 5 && g.id_unico !== currentGroupId
                );

                const dropdown = document.getElementById('editGrupoMadreDropdown');
                dropdown.innerHTML = '<option value="">-- Seleccionar grupo madre --</option>';

                gruposValidos.forEach(grupo => {
                    const option = document.createElement('option');
                    option.value = grupo.id_unico;
                    const grupoMadreInfo = grupo.grupo_madre ? ` (${grupo.grupo_madre})` : ' (Raíz)';
                    option.textContent = `${grupo.nombre_exacto} (Gen ${grupo.generacion})${grupoMadreInfo} - ${grupo.lider}`;
                    option.dataset.generacion = grupo.generacion;
                    dropdown.appendChild(option);
                });

                // Agregar event listener para actualizar generación
                dropdown.addEventListener('change', function() {
                    updateEditGeneracionDisplay();
                });
            }
        })
        .catch(error => console.error('Error al cargar grupos madre para edición:', error));
    }

    function updateEditGeneracionDisplay() {
        const dropdown = document.getElementById('editGrupoMadreDropdown');
        const generacionInfo = document.getElementById('editGeneracionInfo');
        const generacionDisplay = document.getElementById('editGeneracionDisplay');
        const generacionInput = document.getElementById('editGeneracion');

        if (dropdown.value && dropdown.selectedIndex > 0) {
            const selectedOption = dropdown.options[dropdown.selectedIndex];
            const generacionMadre = parseInt(selectedOption.dataset.generacion);
            const generacionNueva = generacionMadre + 1;
            generacionDisplay.textContent = generacionNueva;
            generacionInput.value = generacionNueva;
            generacionInfo.style.display = 'block';
        } else {
            generacionInput.value = '0';
            generacionInfo.style.display = 'none';
        }
    }

    function calculateTotalAsistencia() {
        const hom = parseInt(document.getElementById('newGroupAsisHom').value) || 0;
        const muj = parseInt(document.getElementById('newGroupAsisMuj').value) || 0;
        const jov = parseInt(document.getElementById('newGroupAsisJov').value) || 0;
        const nin = parseInt(document.getElementById('newGroupAsisNin').value) || 0;
        const total = hom + muj + jov + nin;
        document.getElementById('totalAsistenciaDisplay').textContent = total;
    }

    function saveNewGroup(event) {
        event.preventDefault();
        console.log('Guardando nuevo grupo');

        // Datos del grupo
        const nombre = document.getElementById('newGroupName').value.trim();
        const descripcion = document.getElementById('newGroupDescription').value.trim();
        const ciudad = document.getElementById('newGroupCiudad').value.trim();
        const barrio = document.getElementById('newGroupBarrio').value.trim();
        const direccion = document.getElementById('newGroupDireccion').value.trim();
        const lider = document.getElementById('newGroupLider').value.trim();
        const tieneGrupoMadre = document.querySelector('input[name="tieneGrupoMadre"]:checked').value;
        const grupoMadreId = document.getElementById('grupoMadreDropdown').value;

        // Datos del primer reporte
        const actividad = 'reunion_cotidiana'; // Siempre es reunión cotidiana
        const fecha = document.getElementById('newGroupFecha').value;
        const asistencia_hom = parseInt(document.getElementById('newGroupAsisHom').value) || 0;
        const asistencia_muj = parseInt(document.getElementById('newGroupAsisMuj').value) || 0;
        const asistencia_jov = parseInt(document.getElementById('newGroupAsisJov').value) || 0;
        const asistencia_nin = parseInt(document.getElementById('newGroupAsisNin').value) || 0;

        if (!nombre) {
            showStatusMessage('El nombre del grupo es obligatorio', 'error');
            return;
        }

        if (tieneGrupoMadre === 'si' && !grupoMadreId) {
            showStatusMessage('Debes seleccionar un grupo madre', 'error');
            return;
        }

        if (!fecha) {
            showStatusMessage('Debes seleccionar la fecha del primer encuentro', 'error');
            return;
        }

        const datosNuevoGrupo = {
            // Datos del grupo
            nombre: nombre,
            descripcion: descripcion,
            ciudad: ciudad,
            barrio: barrio,
            direccion: direccion,
            lider: lider,
            tieneGrupoMadre: tieneGrupoMadre,
            grupoMadreId: grupoMadreId,
            // Datos del primer reporte
            tipoActividad: actividad,
            fechaActividad: fecha,
            asistencia_hom: asistencia_hom,
            asistencia_muj: asistencia_muj,
            asistencia_jov: asistencia_jov,
            asistencia_nin: asistencia_nin
        };

        console.log('Datos del nuevo grupo:', datosNuevoGrupo);

        fetch('crear_grupo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datosNuevoGrupo)
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Response text raw:', text);
            console.log('Response text length:', text.length);
            console.log('First 500 chars:', text.substring(0, 500));
            try {
                const data = JSON.parse(text);
                console.log('Respuesta de crear_grupo.php:', data);

                if (data.success) {
                    showStatusMessage('Grupo creado exitosamente', 'success');
                    closeCreateGroupModal();

                    // Recargar lista de grupos
                    setTimeout(() => {
                        loadGrupos();
                    }, 500);
                } else {
                    showStatusMessage('Error: ' + (data.message || 'No se pudo crear el grupo'), 'error');
                }
            } catch (parseError) {
                console.error('Error al parsear JSON:', parseError);
                console.error('Respuesta cruda completa:', text);
                console.error('Empieza con:', text.charAt(0));
                showStatusMessage('Error del servidor', 'error');
            }
        })
        .catch(error => {
            console.error('Error al crear grupo:', error);
            showStatusMessage('Error al crear el grupo', 'error');
        });
    }

    function saveGroupChanges(event) {
        event.preventDefault();

        if (!selectedGrupo) {
            showStatusMessage('Error: No hay grupo seleccionado', 'error');
            return;
        }

        // Obtener el valor de grupo madre del selector
        const tieneGrupoMadre = document.querySelector('input[name="editTieneGrupoMadre"]:checked').value;
        let grupoMadreValue = ''; // Inicializar como string vacío

        if (tieneGrupoMadre === 'si') {
            const dropdown = document.getElementById('editGrupoMadreDropdown');
            if (dropdown.selectedIndex > 0) {
                // Extraer el nombre del grupo madre del texto de la opción seleccionada
                const selectedOption = dropdown.options[dropdown.selectedIndex];
                const textContent = selectedOption.textContent;
                // El formato es "Nombre (Gen X)(Padre) - Líder"
                const parenthesisIndex = textContent.indexOf('(');
                if (parenthesisIndex > 0) {
                    grupoMadreValue = textContent.substring(0, parenthesisIndex).trim();
                }
            }
        }
        // Si es 'no', grupoMadreValue permanece como string vacío

        const datosActualizacion = {
            reporteIds: selectedGrupo.reportes_ids,
            nombre_exacto: document.getElementById('editNombre').value || null,
            ciudad: document.getElementById('editCiudad').value || null,
            barrio: document.getElementById('editBarrio').value || null,
            direccion: document.getElementById('editDireccion').value || null,
            grupo_madre: grupoMadreValue, // Ahora siempre es un string (vacío o con nombre)
            generacion: parseInt(document.getElementById('editGeneracion').value) || 0,
            lider: editFormData.lideresArray.length > 0 ? editFormData.lideresArray : null
        };

        // Mostrar estado de carga
        const btnSave = event.target.querySelector('.edit-modal-button.save');
        const textOriginal = btnSave.textContent;
        btnSave.textContent = 'Guardando...';
        btnSave.disabled = true;

        fetch('actualizar_grupo_consolidado.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(datosActualizacion)
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.text().then(text => {
                console.log('Response text:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    console.error('Raw response:', text);
                    throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                }
            });
        })
        .then(data => {
            btnSave.textContent = textOriginal;
            btnSave.disabled = false;

            if (data.success) {
                showStatusMessage(`✅ Grupo actualizado correctamente. ${data.reportes_actualizados} reportes fueron actualizados.`, 'success');

                // Actualizar los datos del grupo seleccionado
                selectedGrupo.nombre_exacto = datosActualizacion.nombre_exacto || selectedGrupo.nombre_exacto;
                selectedGrupo.ciudad = datosActualizacion.ciudad || selectedGrupo.ciudad;
                selectedGrupo.barrio = datosActualizacion.barrio || selectedGrupo.barrio;
                selectedGrupo.direccion = datosActualizacion.direccion || selectedGrupo.direccion;
                selectedGrupo.grupo_madre = datosActualizacion.grupo_madre || selectedGrupo.grupo_madre;
                if (datosActualizacion.lider) {
                    selectedGrupo.lider = datosActualizacion.lider;
                }

                // Actualizar la vista
                updateGroupPanel(selectedGrupo);

                // Cerrar modal
                closeEditModal();
            } else {
                showStatusMessage('Error: ' + (data.message || 'No se pudo actualizar el grupo'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btnSave.textContent = textOriginal;
            btnSave.disabled = false;
            showStatusMessage('Error de conexión al guardar cambios', 'error');
        });
    }

    // ========== GRÁFICA DE MAPEO ==========
    const _mapeoImageCache = {};
    const _mapeoPositions = [
        { id: 'mapeo_evangelizar',   x: 430, y: 35  },
        { id: 'mapeo_biblia',        x: 200, y: 185 },
        { id: 'mapeo_cena',          x: 650, y: 185 },
        { id: 'mapeo_adoracion',     x: 50,  y: 355 },
        { id: 'mapeo_trabajadores',  x: 430, y: 355 },
        { id: 'mapeo_dar',           x: 800, y: 355 },
        { id: 'mapeo_companerismo',  x: 200, y: 520 },
        { id: 'mapeo_bautizar',      x: 650, y: 520 },
        { id: 'mapeo_oracion',       x: 430, y: 670 }
    ];

    function _loadMapeoImage(src) {
        return new Promise((resolve, reject) => {
            if (_mapeoImageCache[src]) { resolve(_mapeoImageCache[src]); return; }
            const img = new Image();
            img.onload = function() { _mapeoImageCache[src] = img; resolve(img); };
            img.onerror = reject;
            img.src = src;
        });
    }

    // Renderiza la gráfica de mapeo en un canvas dado, con los valores proporcionados
    async function renderMapeoOnCanvas(canvasId, valores) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const size = canvas.width;
        const scale = size / 1024;
        const iconSize = Math.round(150 * scale);
        const yInicial = Math.round(100 * scale);
        const ctx = canvas.getContext('2d');

        ctx.clearRect(0, 0, size, size);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, size, size);

        try {
            const bgImg = await _loadMapeoImage('mapeo_img/compromiso_si.png');
            ctx.drawImage(bgImg, 0, 0, size, size);
        } catch(e) {
            ctx.beginPath();
            ctx.arc(size/2, size/2, size/2 - 10, 0, Math.PI * 2);
            ctx.strokeStyle = '#333';
            ctx.lineWidth = 2;
            ctx.stroke();
        }

        for (const field of _mapeoPositions) {
            const val = parseInt(valores[field.id]) || 0;
            if (val === 0) continue;
            const imgSrc = 'mapeo_img/' + field.id + val + '.png';
            try {
                const img = await _loadMapeoImage(imgSrc);
                const x = Math.round(field.x * scale);
                const y = Math.round(field.y * scale) + yInicial;
                ctx.drawImage(img, x, y, iconSize, iconSize);
            } catch(e) {
                console.warn('No se pudo cargar:', imgSrc);
            }
        }
    }

    // Renderiza la gráfica del formulario leyendo los selects
    function renderMapeoChart() {
        const valores = {};
        _mapeoPositions.forEach(function(f) {
            const sel = document.getElementById(f.id);
            valores[f.id] = sel ? parseInt(sel.value) || 0 : 0;
        });
        renderMapeoOnCanvas('mapeoCanvas', valores);
    }

    // Toggle de la gráfica de mapeo en reportes guardados
    function toggleMapeoChart(reporteId) {
        const chartDiv = document.getElementById('mapeo-chart-' + reporteId);
        if (!chartDiv) return;

        if (chartDiv.style.display === 'none') {
            chartDiv.style.display = 'block';
            const btnContainer = document.getElementById('mapeo-btn-' + reporteId);
            const datos = JSON.parse(btnContainer.dataset.mapeo || '{}');
            renderMapeoOnCanvas('mapeo-canvas-' + reporteId, datos);
            btnContainer.querySelector('button').textContent = '📊 Ocultar Mapeo';
        } else {
            chartDiv.style.display = 'none';
            const btnContainer = document.getElementById('mapeo-btn-' + reporteId);
            btnContainer.querySelector('button').textContent = '📊 Ver Mapeo';
        }
    }

    // Escuchar cambios en selects del formulario
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.mapeo-select').forEach(function(select) {
            select.addEventListener('change', renderMapeoChart);
        });
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(m) {
                if (m.attributeName === 'style') {
                    const section = document.getElementById('mapeosSection');
                    if (section && section.style.display !== 'none') {
                        renderMapeoChart();
                    }
                }
            });
        });
        const mapeosEl = document.getElementById('mapeosSection');
        if (mapeosEl) {
            observer.observe(mapeosEl, { attributes: true });
        }
    });
</script>

<!-- Fin de grupos.php -->
