<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-danger mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">⚠️ Подтверждение удаления</h5>
            </div>
            <div class="card-body">
                <p class="lead">Вы действительно хотите удалить клиента?</p>
                
                <div class="alert alert-secondary">
                    <dl class="row mb-0 small">
                        <dt class="col-4">ФИО</dt>
                        <dd class="col-8"><strong><?= htmlspecialchars($client['last_name'] . ' ' . $client['first_name'] . ' ' . ($client['patronymic'] ?? '')) ?></strong></dd>
                        
                        <dt class="col-4">Паспорт</dt>
                        <dd class="col-8"><?= htmlspecialchars($client['passport_number']) ?></dd>
                        
                        <dt class="col-4">Телефон</dt>
                        <dd class="col-8"><?= htmlspecialchars($client['phone']) ?></dd>
                        
                        <dt class="col-4">Email</dt>
                        <dd class="col-8"><?= htmlspecialchars($client['email']) ?></dd>
                    </dl>
                </div>
                
                <div class="alert alert-warning">
                    <strong>Внимание!</strong> Это действие нельзя отменить.
                    <?php
                    // Предварительная проверка связей для информирования пользователя
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE client_id = ?");
                    $stmt->execute([$client['client_id']]);
                    $count = $stmt->fetchColumn();
                    if ($count > 0): ?>
                        <br>У клиента есть <strong><?= $count ?> записей на обслуживание</strong>. 
                        Они будут потеряны или потребуют переназначения.
                    <?php endif; ?>
                </div>
                
                <form method="POST" onsubmit="return confirm('Вы уверены? Это действие необратимо.');">
                    <input type="hidden" name="csrf_token" value="<?= (new BaseController())->initCSRF() ?>">
                    <button type="submit" class="btn btn-danger">🗑️ Да, удалить</button>
                    <a href="index.php?entity=client&action=list" class="btn btn-secondary">↩️ Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>