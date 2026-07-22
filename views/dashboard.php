<div class="dashboard">
    <h1>Dashboard - Universe Zero</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="number"><?= $total_users ?? 0 ?></div>
            <div class="label">Usuarios Registrados</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $total_products ?? 0 ?></div>
            <div class="label">Productos Activos</div>
        </div>
        <div class="stat-card <?= ($low_stock ?? 0) > 0 ? 'stat-warning' : '' ?>">
            <div class="number"><?= $low_stock ?? 0 ?></div>
            <div class="label">Productos con Stock Bajo</div>
        </div>
        <div class="stat-card">
            <div class="number">$<?= number_format($monthly_revenue ?? 0, 2) ?></div>
            <div class="label">Ventas del Mes</div>
        </div>
    </div>

    <div class="dashboard-sections">
        <div class="section">
            <h2>Actividad Reciente</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>ID</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_activity ?? [])): ?>
                            <?php foreach ($recent_activity as $activity): ?>
                                <tr>
                                    <td><span class="badge badge-<?= $activity['type'] ?>"><?= ucfirst($activity['type']) ?></span></td>
                                    <td>#<?= $activity['id'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($activity['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No hay actividad reciente</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>