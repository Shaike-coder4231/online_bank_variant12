<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>🏦 Банк — Справочники</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body><nav class="navbar navbar-dark bg-dark mb-4"><div class="container">
<a class="navbar-brand" href="index.php">🏦 Онлайн-банк</a><div class="d-flex gap-2">
<a href="index.php?entity=client&action=list" class="btn btn-outline-light btn-sm">Клиенты</a>
<a href="index.php?entity=operation&action=list" class="btn btn-outline-light btn-sm">Операции</a>
<a href="index.php?entity=employee&action=list" class="btn btn-outline-light btn-sm">Сотрудники</a>
</div></div></nav><div class="container">
<?php $f=(new BaseController())->getFlash(); if($f):?>
<div class="alert alert-<?= $f['type']==='success'?'success':'danger' ?> alert-dismissible fade show">
<?=htmlspecialchars($f['message'],ENT_QUOTES,'UTF-8')?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif;?>