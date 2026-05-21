<?php include __DIR__.'/../partials/header.php';?>
<div class="d-flex justify-content-between align-items-center mb-3"><h2>👥 Клиенты</h2>
<a href="index.php?entity=client&action=create" class="btn btn-primary">+ Добавить</a></div>
<form method="GET" class="mb-3"><input type="hidden" name="entity" value="client"><input type="hidden" name="action" value="list">
<div class="input-group"><input type="text" name="search" class="form-control" placeholder="Поиск: фамилия, телефон, паспорт..." value="<?=htmlspecialchars($search)?>">
<button class="btn btn-outline-secondary" type="submit">🔍</button></div></form>
<div class="table-responsive"><table class="table table-striped table-hover align-middle">
<thead class="table-dark"><tr>
<?php foreach(['client_id'=>'ID','last_name'=>'Фамилия','first_name'=>'Имя','passport_number'=>'Паспорт','phone'=>'Телефон','email'=>'Email','birth_date'=>'Д.Р.'] as $col=>$lbl):?>
<th><a href="?entity=client&action=list&sort=<?=$col?>&order=<?=$sort===$col&&$order==='ASC'?'DESC':'ASC'?>&search=<?=urlencode($search)?>" class="text-white text-decoration-none"><?=$lbl?> ⇅</a></th><?php endforeach;?>
<th>Действия</th></tr></thead><tbody>
<?php foreach($clients as $c):?><tr>
<td><?=htmlspecialchars($c['client_id'])?></td><td><?=htmlspecialchars($c['last_name'])?></td><td><?=htmlspecialchars($c['first_name'])?></td>
<td><?=htmlspecialchars($c['passport_number'])?></td><td><?=htmlspecialchars($c['phone'])?></td><td><?=htmlspecialchars($c['email'])?></td>
<td><?=htmlspecialchars($c['birth_date'])?></td>
<td><a href="index.php?entity=client&action=view&id=<?=$c['client_id']?>" class="btn btn-sm btn-info">👁️</a>
<a href="index.php?entity=client&action=edit&id=<?=$c['client_id']?>" class="btn btn-sm btn-warning">✏️</a>
<a href="index.php?entity=client&action=delete&id=<?=$c['client_id']?>" class="btn btn-sm btn-danger">🗑️</a></td></tr><?php endforeach;?></tbody></table></div>
<?php if($pages>1):?><nav><ul class="pagination"><?php for($i=1;$i<=$pages;$i++):?>
<li class="page-item <?=$i==$page?'active':''?>"><a class="page-link" href="?entity=client&action=list&page=<?=$i?>&sort=<?=$sort?>&order=<?=$order?>&search=<?=urlencode($search)?>"><?=$i?></a></li><?php endfor;?></ul></nav><?php endif;?>
<?php include __DIR__.'/../partials/footer.php';?>