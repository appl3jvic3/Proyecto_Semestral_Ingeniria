<div class="reports-container">
    <h2>Generación de Reportes</h2>

    <div class="form-container">
        <form method="POST">
            <div class="form-group">
                <label class="required">Tipo de Reporte</label>
                <select name="report_type" required>
                    <option value="">Seleccionar...</option>
                    <option value="sales">Ventas por Periodo</option>
                    <option value="inventory">Inventario Bajo Stock</option>
                    <option value="users">Actividad de Usuarios</option>
                </select>
            </div>

            <div class="form-group">
                <label>Fecha de Inicio</label>
                <input type="date" name="start_date" value="<?= date('Y-m-01') ?>">
            </div>

            <div class="form-group">
                <label>Fecha de Fin</label>
                <input type="date" name="end_date" value="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label>Formato de Descarga</label>
                <select name="format">
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Generar Reporte</button>
                <a href="?controller=dashboard&action=index" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <?php if (isset($report_generated) && $report_generated): ?>
        <div class="report-success">
            <h3>Reporte Generado</h3>
            <p>El reporte ha sido generado correctamente.</p>
            <div class="actions">
                <a href="#" class="btn btn-success" onclick="downloadReport('pdf')">Descargar PDF</a>
                <a href="#" class="btn btn-success" onclick="downloadReport('excel')">Descargar Excel</a>
            </div>
        </div>
    <?php endif; ?>
</div>