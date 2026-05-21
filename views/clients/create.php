<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="row">
    <div class="col-lg-8 mx-auto">
        <h2 class="mb-4">📅 Запись на услугу</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="booking-form">
            <input type="hidden" name="csrf_token" value="<?= (new BaseController())->initCSRF() ?>">
            <input type="hidden" name="booking_time" id="hidden-time">
            <input type="hidden" name="total_price" id="hidden-price">
            
            <!-- Шаг 1: Клиент -->
            <div class="card mb-3">
                <div class="card-header">👤 Клиент</div>
                <div class="card-body">
                    <select name="client_id" class="form-select" required>
                        <option value="">Выберите клиента</option>
                        <?php 
                        $clients = $pdo->query("SELECT client_id, last_name, first_name, phone FROM clients ORDER BY last_name")->fetchAll();
                        foreach ($clients as $c): ?>
                            <option value="<?= $c['client_id'] ?>" <?= (isset($data['client_id']) && $data['client_id'] == $c['client_id']) ? 'selected' : '' ?>>
                                <?= $this->e("$c[last_name] $c[first_name] — $c[phone]") ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Шаг 2: Услуга -->
            <div class="card mb-3">
                <div class="card-header">✂️ Услуга</div>
                <div class="card-body">
                    <select name="service_id" class="form-select" required>
                        <option value="">Выберите услугу</option>
                        <?php foreach ($services as $s): ?>
                            <option value="<?= $s['service_id'] ?>" data-duration="<?= $s['duration'] ?>" data-price="<?= $s['price'] ?>"
                                <?= (isset($data['service_id']) && $data['service_id'] == $s['service_id']) ? 'selected' : '' ?>>
                                <?= $this->e("$s[service_name] — {$s['duration']} мин — {$s['price']} ₽") ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="mt-2 small text-muted">
                        Длительность: <span id="duration-display">—</span> | 
                        Стоимость: <span id="price-display">—</span>
                    </div>
                </div>
            </div>
            
            <!-- Шаг 3: Специалист -->
            <div class="card mb-3">
                <div class="card-header">👩‍🦰 Специалист</div>
                <div class="card-body">
                    <select name="specialist_id" class="form-select" required disabled>
                        <option value="">Сначала выберите услугу</option>
                    </select>
                </div>
            </div>
            
            <!-- Шаг 4: Дата и время -->
            <div class="card mb-3">
                <div class="card-header">🗓️ Дата и время</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Дата *</label>
                            <input type="date" name="booking_date" class="form-control" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Время *</label>
                            <div id="slots-container" class="d-flex flex-wrap gap-2">
                                <p class="text-muted small">Выберите услугу и специалиста</p>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($errors['slot'])): ?>
                        <div class="text-danger small mt-2"><?= $errors['slot'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">✅ Подтвердить запись</button>
                <a href="index.php?page=bookings/list" class="btn btn-secondary">↩️ К списку записей</a>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/booking.js"></script>
<?php include __DIR__ . '/../partials/footer.php'; ?>