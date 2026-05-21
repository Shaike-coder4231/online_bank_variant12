<?php include __DIR__.'/../partials/header.php';?>
<h2>➕ Новый клиент</h2>
<?php if(!empty($errors)):?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $er):?><li><?=htmlspecialchars($er)?></li><?php endforeach;?></ul></div><?php endif;?>
<form method="POST" novalidate><input type="hidden" name="csrf_token" value="<?= (new BaseController())->initCSRF() ?>">
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Фамилия *</label><input type="text" name="last_name" class="form-control <?=!empty($errors['last_name'])?'is-invalid':''?>" value="<?=htmlspecialchars($d['last_name']??'')?>" required></div>
<div class="col-md-4"><label class="form-label">Имя *</label><input type="text" name="first_name" class="form-control <?=!empty($errors['first_name'])?'is-invalid':''?>" value="<?=htmlspecialchars($d['first_name']??'')?>" required></div>
<div class="col-md-4"><label class="form-label">Отчество</label><input type="text" name="patronymic" class="form-control" value="<?=htmlspecialchars($d['patronymic']??'')?>"></div>
<div class="col-md-4"><label class="form-label">Паспорт *</label><input type="text" name="passport_number" class="form-control <?=!empty($errors['passport_number'])?'is-invalid':''?>" value="<?=htmlspecialchars($d['passport_number']??'')?>" pattern="^\d{4}\s?\d{6}$" placeholder="1234 567890" required></div>
<div class="col-md-4"><label class="form-label">Телефон *</label><input type="tel" name="phone" class="form-control <?=!empty($errors['phone'])?'is-invalid':''?>" value="<?=htmlspecialchars($d['phone']??'')?>" required></div>
<div class="col-md-4"><label class="form-label">Email *</label><input type="email" name="email" class="form-control <?=!empty($errors['email'])?'is-invalid':''?>" value="<?=htmlspecialchars($d['email']??'')?>" required></div>
<div class="col-md-4"><label class="form-label">Дата рождения *</label><input type="date" name="birth_date" class="form-control <?=!empty($errors['birth_date'])?'is-invalid':''?>" value="<?=htmlspecialchars($d['birth_date']??'')?>" max="<?=date('Y-m-d',strtotime('-18 years'))?>" required></div>
<div class="col-12"><label class="form-label">Адрес регистрации</label><textarea name="registration_address" class="form-control" rows="2"><?=htmlspecialchars($d['registration_address']??'')?></textarea></div>
</div><button type="submit" class="btn btn-success mt-3">Создать</button>
<a href="index.php?entity=client&action=list" class="btn btn-secondary mt-3">Отмена</a></form>
<script>document.querySelector('form').addEventListener('submit',e=>e.target.checkValidity()||e.preventDefault());</script>
<?php include __DIR__.'/../partials/footer.php';?>