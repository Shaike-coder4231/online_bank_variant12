<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../dal/src/Repositories/OperationRepository.php';

class OperationController extends BaseController {
    private $repo;
    public function __construct(){parent::__construct();$this->repo=new OperationRepository($this->pdo);}
    
    public function listAction($page,$sort,$order,$search){
        $allowed=['operation_id','operation_name','operation_type','min_amount','max_amount'];
        if(!in_array($sort,$allowed))$sort='operation_id';$limit=10;$offset=($page-1)*$limit;$like="%{$search}%";
        $cnt=$this->pdo->prepare("SELECT COUNT(*) FROM operations WHERE operation_name LIKE ? OR operation_type LIKE ?");
        $cnt->execute([$like,$like]);$total=$cnt->fetchColumn();$pages=ceil($total/$limit);
        $st=$this->pdo->prepare("SELECT * FROM operations WHERE operation_name LIKE ? OR operation_type LIKE ? ORDER BY $sort $order LIMIT $limit OFFSET $offset");
        $st->execute([$like,$like]);$ops=$st->fetchAll();include __DIR__.'/../views/operations/list.php';
    }
    public function createAction(){
        if($_SERVER['REQUEST_METHOD']==='POST'){$this->verifyCSRF();$d=$_POST;$e=$this->validateOp($d);
            if(empty($e)){$this->repo->create(['operation_name'=>trim($d['operation_name']),'operation_type'=>$d['operation_type'],
                'min_amount'=>($d['min_amount']??null)===''?null:floatval($d['min_amount']),'max_amount'=>($d['max_amount']??null)===''?null:floatval($d['max_amount'])]);
                $this->setFlash('success','Операция добавлена!');header('Location: index.php?entity=operation&action=list');exit;}}
        include __DIR__.'/../views/operations/create.php';
    }
    public function editAction($id){/* аналогично ClientController, адаптируйте поля */}
    public function deleteAction($id){
        if($_SERVER['REQUEST_METHOD']==='POST'){$this->verifyCSRF();
            $st=$this->pdo->prepare("SELECT COUNT(*) FROM bookings WHERE operation_id=?");$st->execute([$id]);if($st->fetchColumn()>0){
                $this->setFlash('error','Есть связанные записи!');header('Location: index.php?entity=operation&action=list');exit;}
            $this->repo->delete($id);$this->setFlash('success','Удалено!');header('Location: index.php?entity=operation&action=list');exit;}
        $op=$this->repo->findById($id);include __DIR__.'/../views/operations/delete.php';
    }
    public function viewAction($id){
        $op=$this->repo->findById($id);if(!$op){$this->setFlash('error','Не найдено');header('Location: index.php?entity=operation&action=list');exit;}
        $st=$this->pdo->prepare("SELECT b.*, c.last_name as cli_last, c.first_name as cli_first, e.last_name as emp_last 
            FROM bookings b JOIN clients c ON b.client_id=c.client_id JOIN employees e ON b.employee_id=e.employee_id WHERE b.operation_id=? ORDER BY b.booking_date DESC");
        $st->execute([$id]);$bookings=$st->fetchAll();include __DIR__.'/../views/operations/view.php';
    }
    private function validateOp($d){$e=[];
        if(empty(trim($d['operation_name'])))$e['operation_name']='Обязательно';
        if(!in_array($d['operation_type']??'',['вклад','консультация','кредит','другое']))$e['operation_type']='Неверный тип';
        $min=$d['min_amount']??null;$max=$d['max_amount']??null;
        if($min!==''&&!is_numeric($min))$e['min_amount']='Число';if($max!==''&&!is_numeric($max))$e['max_amount']='Число';
        if($min!==''&&$max!==''&&floatval($min)>floatval($max))$e['max_amount']='Макс. >= Мин.';
        $chk=$this->pdo->prepare("SELECT operation_id FROM operations WHERE operation_name=? AND operation_id!=?");
        $chk->execute([$d['operation_name']??'',$d['operation_id']??0]);if($chk->fetch())$e['operation_name']='Уже есть';
        return $e;}
}