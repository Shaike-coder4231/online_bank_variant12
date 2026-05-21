<?php include __DIR__ . '/../partials/header.php'; ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?page=bookings/list">Записи</a></li>
        <li class="breadcrumb-item active">Запись #<?= $booking['booking_id'] ?></li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <span>📋 Детали записи</span>
                <span class="badge bg-<?= $booking['status']==='confirmed'?'success':($booking['status']==='pending'?'warning':($booking['status']==='completed'?'secondary':'danger')) ?>">
                    <?= $booking['status']==='pending'?'Ожидает':($booking['status']==='confirmed'?'Подтверждено':($booking['status']==='completed'?'Завершено':'Отменено')) ?>
                </span>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Код бронирования</dt>
                    <dd class="col-sm-8"><strong class="fs-5"><?= $this->e($booking['booking_code']) ?></strong></dd>
                    
                    <dt class="col-sm-4">Дата и время</dt>
                    <dd class="col-sm-8"><?= date('d.m.Y (l) H:i', strtotime($booking['booking_datetime'])) ?></dd>
                    
                    <dt class="col-sm-4">Клиент</dt>
                    <dd class="col-sm-8">
                        <?= $this->e("$booking[last_name] $booking[first_name] $booking[patronymic]") ?><br>
                        <small class="text-muted">📞 <?= $this->e($booking['phone']) ?> | ✉️ <?= $this->e($booking['email']) ?></small>
                    </dd>
                    
                    <dt class="col-sm-4">Услуга</dt>
                    <dd class="col-sm-8">
                        <?= $this->e($booking['service_name']) ?><br>
                        <small class="text-muted">⏱️ <?= $booking['duration'] ?> мин | 💰 <?= number_format($booking['price'], 0, '.', ' ') ?> ₽</small>
                    </dd>
                    
                    <dt class="col-sm-4">Специалист</dt>
                    <dd class="col-sm-8">
                        <?= $this->e("$booking[spec_last] $booking[spec_first]") ?><br>
                        <small class="text-muted">🎓 <?= $this->e($booking['specialization']) ?></small>
                    </dd>
                    
                    <dt class="col-sm-4">Итоговая сумма</dt>
                    <dd class="col-sm-8 fs-5 text-primary"><?= number_format($booking['total_price'], 0, '.', ' ') ?> ₽</dd>
                    
                    <?php if ($booking['status'] === 'cancelled'): ?>
                        <dt class="col-sm-4">Отменена</dt>
                        <dd class="col-sm-8 text-danger"><?= date('d.m.Y H:i', strtotime($booking['cancelled_at'])) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
        
        <!-- Кнопки действий -->
        <div class="d-flex gap-2 mb-4">
            <?php if ($booking['status'] !== 'cancelled' && $booking['status'] !== 'completed'): ?>
                <a href="index.php?page=bookings/reschedule&id=<?= $booking['booking_id'] ?>" class="btn btn-warning">🔁 Перенести</a>
                <a href="index.php?page=bookings/cancel&id=<?= $booking['booking_id'] ?>" class="btn btn-danger" onclick="return confirm('Отменить запись?')">❌ Отменить</a>
            <?php endif; ?>
            <a href="index.php?page=bookings/list" class="btn btn-secondary">← Назад к списку</a>
        </div>
    </div>
    
    <!-- Информация для клиента -->
    <div class="col-lg-4">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title">ℹ️ Информация</h6>
                <ul class="list-unstyled small mb-0">
                    <li>⏰ Приходите за 10 минут до записи</li>
                    <li>📱 Перенос/отмена — не позднее чем за 2 часа</li>
                    <li>💳 Оплата на месте или по ссылке</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>