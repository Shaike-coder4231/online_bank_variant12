<?php include __DIR__ . '/../partials/header.php'; ?>
<h2 class="mb-4">📊 Отчёты</h2>

<form method="GET" class="card mb-4">
    <input type="hidden" name="page" value="reports">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Период</label>
                <select name="period" class="form-select">
                    <option value="month" <?= ($period ?? '') === 'month' ? 'selected' : '' ?>>Текущий месяц</option>
                    <option value="week" <?= ($period ?? '') === 'week' ? 'selected' : '' ?>>Текущая неделя</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Месяц</label>
                <input type="month" name="month" class="form-control" value="<?= $this->e($month) ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">Показать</button>
                <a href="?page=reports&month=<?= $month ?>&type=daily" class="btn btn-success" download>📥 Экспорт CSV</a>
            </div>
        </div>
    </div>
</form>

<!-- Отчёт 1: По дням -->
<div class="card mb-4">
    <div class="card-header">📅 Записи и выручка по дням</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Дата</th><th>Записей</th><th>Выручка</th></tr></thead>
                <tbody>
                    <?php foreach ($reports['daily'] as $r): ?>
                    <tr>
                        <td><?= date('d.m.Y', strtotime($r['day'])) ?></td>
                        <td><?= $r['count'] ?></td>
                        <td><?= number_format($r['revenue'], 0, '.', ' ') ?> ₽</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td><strong>Итого</strong></td>
                        <td><strong><?= array_sum(array_column($reports['daily'], 'count')) ?></strong></td>
                        <td><strong><?= number_format(array_sum(array_column($reports['daily'], 'revenue')), 0, '.', ' ') ?> ₽</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Отчёт 2: Специалисты -->
<div class="card mb-4">
    <div class="card-header">👩‍🦰 Рейтинг специалистов</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Специалист</th><th>Записей</th><th>Выручка</th></tr></thead>
                <tbody>
                    <?php foreach ($reports['specialists'] as $r): ?>
                    <tr>
                        <td><?= $this->e("$r[last_name] $r[first_name]") ?></td>
                        <td><?= $r['bookings_count'] ?></td>
                        <td><?= number_format($r['revenue'] ?? 0, 0, '.', ' ') ?> ₽</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Отчёт 3: Отмены -->
<div class="card">
    <div class="card-header">❌ Отменённые записи</div>
    <div class="card-body">
        <?php if (empty($reports['cancelled'])): ?>
            <p class="text-muted">Нет отменённых записей за выбранный период.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Дата</th><th>Отмен</th><th>Клиенты</th></tr></thead>
                    <tbody>
                        <?php foreach ($reports['cancelled'] as $r): ?>
                        <tr>
                            <td><?= date('d.m.Y', strtotime($r['day'])) ?></td>
                            <td><?= $r['cancelled_count'] ?></td>
                            <td><small><?= $this->e($r['clients']) ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>