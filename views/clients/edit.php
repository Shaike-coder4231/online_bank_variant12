<?php include __DIR__ . '/../partials/header.php'; ?>

<h2 class="mb-4">✏️ Редактирование клиента #<?= (int)$client['client_id'] ?></h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Ошибки в форме:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $field => $message): ?>
                <li><?= htmlspecialchars($message) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST" class="needs-validation" novalidate>
    <input type="hidden" name="csrf_token" value="<?= (new BaseController())->initCSRF() ?>">
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">📋 Личные данные</div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Фамилия -->
                <div class="col-md-4">
                    <label class="form-label">Фамилия <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control <?= !empty($errors['last_name']) ? 'is-invalid' : '' ?>" 
                           value="<?= htmlspecialchars($data['last_name'] ?? '') ?>" required>
                    <?php if (!empty($errors['last_name'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['last_name']) ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Имя -->
                <div class="col-md-4">
                    <label class="form-label">Имя <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control <?= !empty($errors['first_name']) ? 'is-invalid' : '' ?>" 
                           value="<?= htmlspecialchars($data['first_name'] ?? '') ?>" required>
                    <?php if (!empty($errors['first_name'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['first_name']) ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Отчество -->
                <div class="col-md-4">
                    <label class="form-label">Отчество</label>
                    <input type="text" name="patronymic" class="form-control" 
                           value="<?= htmlspecialchars($data['patronymic'] ?? '') ?>">
                </div>
                
                <!-- Паспорт -->
                <div class="col-md-6">
                    <label class="form-label">Серия и номер паспорта <span class="text-danger">*</span></label>
                    <input type="text" name="passport_number" class="form-control <?= !empty($errors['passport_number']) ? 'is-invalid' : '' ?>" 
                           value="<?= htmlspecialchars($data['passport_number'] ?? '') ?>" 
                           pattern="^\d{4}\s?\d{6}$" placeholder="1234 567890" required>
                    <div class="form-text">Формат: 4 цифры, пробел, 6 цифр</div>
                    <?php if (!empty($errors['passport_number'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['passport_number']) ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Телефон -->
                <div class="col-md-6">
                    <label class="form-label">Телефон <span class="text-danger">*</span></label>
                    <input type="tel" name="phone" class="form-control <?= !empty($errors['phone']) ? 'is-invalid' : '' ?>" 
                           value="<?= htmlspecialchars($data['phone'] ?? '') ?>" 
                           placeholder="+7 (999) 123-45-67" required>
                    <?php if (!empty($errors['phone'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['phone']) ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Email -->
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>" 
                           value="<?= htmlspecialchars($data['email'] ?? '') ?>" required>
                    <?php if (!empty($errors['email'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Дата рождения -->
                <div class="col-md-6">
                    <label class="form-label">Дата рождения <span class="text-danger">*</span></label>
                    <input type="date" name="birth_date" class="form-control <?= !empty($errors['birth_date']) ? 'is-invalid' : '' ?>" 
                           value="<?= htmlspecialchars($data['birth_date'] ?? '') ?>" 
                           max="<?= date('Y-m-d', strtotime('-18 years')) ?>" required>
                    <div class="form-text">Минимальный возраст: 18 лет</div>
                    <?php if (!empty($errors['birth_date'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['birth_date']) ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Адрес регистрации -->
                <div class="col-12">
                    <label class="form-label">Адрес регистрации</label>
                    <textarea name="registration_address" class="form-control" rows="3"><?= htmlspecialchars($data['registration_address'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-warning">💾 Сохранить изменения</button>
        <a href="index.php?entity=client&action=list" class="btn btn-secondary">↩️ Отмена</a>
        <a href="index.php?entity=client&action=view&id=<?= (int)$client['client_id'] ?>" class="btn btn-info">👁️ Просмотр</a>
    </div>
</form>

<script>
// Простая клиентская валидация перед отправкой
document.querySelector('form').addEventListener('submit', function(e) {
    const phone = this.querySelector('[name="phone"]').value;
    const passport = this.querySelector('[name="passport_number"]').value;
    
    // Проверка телефона (базовая)
    if (phone && !/^[\+\d\s\-\(\)]{10,20}$/.test(phone)) {
        e.preventDefault();
        alert('Пожалуйста, введите корректный номер телефона');
        return false;
    }
    
    // Проверка паспорта
    if (passport && !/^\d{4}\s?\d{6}$/.test(passport.replace(/\s/g, ''))) {
        e.preventDefault();
        alert('Паспорт должен быть в формате: 1234 567890');
        return false;
    }
    
    return true;
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>