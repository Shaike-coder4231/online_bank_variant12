<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>📋 Записи</h2>
    <a href="index.php?page=bookings/create" class="btn btn-primary">+ Новая запись</a>
</div>

<!-- Фильтры -->
<form method="GET" class="card mb-3">
    <input type="hidden" name="page" value="bookings/list">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small">С даты</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?= $this->e($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small">По дату</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?= $this->e($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Статус</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Все</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Ожидает</option>
                    <option value="confirmed" <?= ($filters['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Подтверждено</option>
                    <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Завершено</option>
                    <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Отменено</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Специалист</label>
                <select name="specialist_id" class="form-select form-select-sm">
                    <option value="">Все</option>
                    <?php foreach ($specs as $sp): ?>
                        <option value="<?= $sp['specialist_id'] ?>" <?= (isset($filters['specialist_id']) && $filters['specialist_id'] == $sp['specialist_id']) ? 'selected' : '' ?>>
                            <?= $this->e("$sp[last_name] $sp[first_name]") ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label small">Поиск клиента</label>
                <input type="text" name="client_search" class="form-control form-control-sm" placeholder="ФИО или телефон" value="<?= $this->e($filters['client_search'] ?? '') ?>">
            </div>
        </div>
        <div class="mt-2">
            <button type="submit" class="btn btn-sm btn-primary">🔍 Применить</button>
            <a href="index.php?page=bookings/list" class="btn btn-sm btn-secondary">Сбросить</a>
        </div>
    </div>
</form>

<!-- Таблица -->
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Дата/Время</th>
                <th>Клиент</th>
                <th>Услуга</th>
                <th>Специалист</th>
                <th>Сумма</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $b): ?>
            <tr>
                <td><?= date('d.m.Y H:i', strtotime($b['booking_datetime'])) ?></td>
                <td>
                    <a href="index.php?page=clients/view&id=<?= $b['client_id'] ?>">
                        <?= $this->e("$b[last_name] $b[first_name]") ?>
                    </a><br>
                    <small class="text-muted"><?= $this->e($b['client_phone']) ?></small>
                </td>
                <td><?= $this->e($b['service_name']) ?><br><small class="text-muted"><?= $b['duration'] ?> мин</small></td>
                <td><?= $this->e("$b[spec_last] $b[spec_first]") ?><br><small class="text-muted"><?= $this->e($b['specialization']) ?></small></td>
                <td><?= number_format($b['total_price'], 0, '.', ' ') ?> ₽</td>
                <td>
                    <span class="badge status-badge bg-<?= 
                        $b['status'] === 'confirmed' ? 'success' : 
                        ($b['status'] === 'pending' ? 'warning text-dark' : 
                        ($b['status'] === 'completed' ? 'secondary' : 'danger')) 
                    ?>">
                        <?= $b['status'] === 'pending' ? 'Ожидает' : 
                           ($b['status'] === 'confirmed' ? 'Подтверждено' : 
                           ($b['status'] === 'completed' ? 'Завершено' : 'Отменено')) ?>
                    </span>
                    <!-- AJAX смена статуса -->
                    <select class="form-select form-select-sm mt-1 status-select" data-id="<?= $b['booking_id'] ?>" data-original="<?= $b['status'] ?>">
                        <option value="pending" <?= $b['status']==='pending'?'selected':'' ?>>Ожидает</option>
                        <option value="confirmed" <?= $b['status']==='confirmed'?'selected':'' ?>>Подтверждено</option>
                        <option value="completed" <?= $b['status']==='completed'?'selected':'' ?>>Завершено</option>
                        <option value="cancelled" <?= $b['status']==='cancelled'?'selected':'' ?>>Отменено</option>
                    </select>
                </td>
                <td>
                    <a href="index.php?page=bookings/view&id=<?= $b['booking_id'] ?>" class="btn btn-sm btn-info">👁️</a>
                    <?php if ($b['status'] !== 'cancelled' && $b['status'] !== 'completed'): ?>
                        <a href="index.php?page=bookings/reschedule&id=<?= $b['booking_id'] ?>" class="btn btn-sm btn-warning">🔁</a>
                        <a href="index.php?page=bookings/cancel&id=<?= $b['booking_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Отменить запись?')">❌</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Пагинация -->
<?php if ($totalPages > 1): ?>
<nav>
    <ul class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=bookings/list&page=<?= $i ?><?= 
                    !empty($filters['date_from']) ? '&date_from='.$filters['date_from'] : '' ?><?= 
                    !empty($filters['date_to']) ? '&date_to='.$filters['date_to'] : '' ?><?= 
                    !empty($filters['status']) ? '&status='.$filters['status'] : '' ?><?= 
                    !empty($filters['specialist_id']) ? '&specialist_id='.$filters['specialist_id'] : '' ?><?= 
                    !empty($filters['client_search']) ? '&client_search='.urlencode($filters['client_search']) : '' 
                ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>