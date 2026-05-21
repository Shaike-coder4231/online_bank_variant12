<?php include __DIR__.'/../partials/header.php';?>
<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="index.php?entity=client&action=list">Клиенты</a></li><li class="breadcrumb-item active"><?=htmlspecialchars($c['last_name'].' '.$c['first_name'])?></li></ol></nav>
<div class="card mb-4"><div class="card-header bg-primary text-white">📋 Данные клиента</div><div class="card-body">
<dl class="row mb-0">
<dt class="col-sm-3">ФИО</dt><dd class="col-sm-9"><?=htmlspecialchars("{$c['last_name']} {$c['first_name']} {$c['patronymic']}")?></dd>
<dt class="col-sm-3">Паспорт</dt><dd class="col-sm-9"><span class="badge bg-secondary"><?=htmlspecialchars($c['passport_number'])?></span></dd>
<dt class="col-sm-3">Контакты</dt><dd class="col-sm-9"><?=htmlspecialchars($c['phone'])?> | <a href="mailto:<?=htmlspecialchars($c['email'])?>"><?=htmlspecialchars($c['email'])?></a></dd>
<dt class="col-sm-3">Дата рождения</dt><dd class="col-sm-9"><?=htmlspecialchars($c['birth_date'])?> (<?=date_diff(new DateTime($c['birth_date']),new DateTime())->y?> лет)</dd>
<dt class="col-sm-3">Адрес</dt><dd class="col-sm-9"><?=htmlspecialchars($c['registration_address']?:'—')?></dd>
</dl><a href="index.php?entity=client&action=edit&id=<?=$c['client_id']?>" class="btn btn-warning btn-sm mt-2">✏️ Редактировать</a>
<a href="index.php?entity=client&action=list" class="btn btn-secondary btn-sm mt-2">← Назад</a></div></div>
<div class="card"><div class="card-header bg-info text-white">📅 Записи на обслуживание (<?=count($bookings)?>)</div><div class="card-body">
<?php if(!$bookings):?><p class="text-muted">Нет записей.</p><?php else:?>
<table class="table table-sm"><thead><tr><th>Операция</th><th>Тип</th><th>Сотрудник</th><th>Дата/Время</th><th>Сумма</th><th>Статус</th></tr></thead><tbody>
<?php foreach($bookings as $b):?><tr>
<td><?=htmlspecialchars($b['operation_name'])?></td><td><span class="badge bg-secondary"><?=htmlspecialchars($b['operation_type'])?></span></td>
<td><?=htmlspecialchars($b['emp_last'].' '.$b['emp_first'])?></td><td><?=date('d.m.Y H:i',strtotime($b['booking_date'].' '.$b['booking_time']))?></td>
<td><?=htmlspecialchars($b['amount']?:'—')?> ₽</td><td><span class="badge bg-<?= $b['status']==='проведено'?'success':($b['status']==='отменено'?'danger':'warning') ?>"><?=htmlspecialchars($b['status'])?></span></td></tr><?php endforeach;?></tbody></table><?php endif;?></div></div>
<?php include __DIR__.'/../partials/footer.php';?>