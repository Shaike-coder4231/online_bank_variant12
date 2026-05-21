<?php
require_once __DIR__ . '/BaseController.php';
// 👇 АДАПТИРУЙТЕ ПОД ВАШ DAL! Если репозиторий требует PDO в конструкторе:
require_once __DIR__ . '/../../dal/src/Repositories/ClientRepository.php';

class ClientController extends BaseController {
    private $repo;
    public function __construct() {
        parent::__construct();
        $this->repo = new ClientRepository($this->pdo); // или просто new ClientRepository()
    }

    public function listAction($page, $sort, $order, $search) {
        $allowed = ['client_id','last_name','first_name','passport_number','phone','email','birth_date'];
        if (!in_array($sort, $allowed)) $sort = 'client_id';
        $limit = 10; $offset = ($page-1)*$limit; $like = "%{$search}%";
        
        $cnt = $this->pdo->prepare("SELECT COUNT(*) FROM clients WHERE last_name LIKE ? OR phone LIKE ? OR passport_number LIKE ?");
        $cnt->execute([$like,$like,$like]); $total = $cnt->fetchColumn(); $pages = ceil($total/$limit);
        
        $stmt = $this->pdo->prepare("SELECT * FROM clients WHERE last_name LIKE ? OR phone LIKE ? OR passport_number LIKE ? ORDER BY $sort $order LIMIT $limit OFFSET $offset");
        $stmt->execute([$like,$like,$like]); $clients = $stmt->fetchAll();
        include __DIR__ . '/../views/clients/list.php';
    }

    public function createAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCSRF(); $d = $_POST; $e = $this->validateClient($d);
            if (empty($e)) {
                try {
                    $this->repo->create([
                        'last_name'=>trim($d['last_name']),'first_name'=>trim($d['first_name']),
                        'patronymic'=>trim($d['patronymic']??''),'passport_number'=>trim($d['passport_number']),
                        'phone'=>trim($d['phone']),'email'=>trim($d['email']),
                        'birth_date'=>$d['birth_date'],'registration_address'=>trim($d['registration_address']??'')
                    ]);
                    $this->setFlash('success','Клиент добавлен!'); header('Location: index.php?entity=client&action=list'); exit;
                } catch(Exception $ex){ $e['db']='Ошибка: '.$ex->getMessage(); }
            }
        } else { $d=[]; $e=[]; }
        include __DIR__ . '/../views/clients/create.php';
    }

    public function editAction($id) {
        if($id<=0){$this->setFlash('error','Неверный ID');header('Location: index.php?entity=client&action=list');exit;}
        $c = $this->repo->findById($id); if(!$c){$this->setFlash('error','Не найден');header('Location: index.php?entity=client&action=list');exit;}
        if($_SERVER['REQUEST_METHOD']==='POST'){$this->verifyCSRF();$d=$_POST;$e=$this->validateClient($d,$id);
            if(empty($e)){$this->repo->update($id,['last_name'=>trim($d['last_name']),'first_name'=>trim($d['first_name']),
                'patronymic'=>trim($d['patronymic']??''),'passport_number'=>trim($d['passport_number']),
                'phone'=>trim($d['phone']),'email'=>trim($d['email']),'birth_date'=>$d['birth_date'],
                'registration_address'=>trim($d['registration_address']??'')]);
                $this->setFlash('success','Обновлено!');header('Location: index.php?entity=client&action=list');exit;}}
        else{$d=$c;$e=[];} include __DIR__ . '/../views/clients/edit.php';
    }

    public function deleteAction($id) {
        if($id<=0){$this->setFlash('error','Неверный ID');header('Location: index.php?entity=client&action=list');exit;}
        $c=$this->repo->findById($id); if(!$c){$this->setFlash('error','Не найден');header('Location: index.php?entity=client&action=list');exit;}
        if($_SERVER['REQUEST_METHOD']==='POST'){$this->verifyCSRF();
            $st=$this->pdo->prepare("SELECT COUNT(*) FROM bookings WHERE client_id=?");$st->execute([$id]);$cnt=$st->fetchColumn();
            if($cnt>0){$this->setFlash('error',"Нельзя удалить: $cnt записей на обслуживание. Сначала отмените их.");}
            else{$this->repo->delete($id);$this->setFlash('success','Удалён.');}
            header('Location: index.php?entity=client&action=list');exit;}
        include __DIR__ . '/../views/clients/delete.php';
    }

    public function viewAction($id) {
        if($id<=0){$this->setFlash('error','Неверный ID');header('Location: index.php?entity=client&action=list');exit;}
        $c=$this->repo->findById($id); if(!$c){$this->setFlash('error','Не найден');header('Location: index.php?entity=client&action=list');exit;}
        $st=$this->pdo->prepare("SELECT b.*, e.last_name as emp_last, e.first_name as emp_first, o.operation_name, o.operation_type 
            FROM bookings b JOIN employees e ON b.employee_id=e.employee_id JOIN operations o ON b.operation_id=o.operation_id 
            WHERE b.client_id=? ORDER BY b.booking_date DESC, b.booking_time DESC");
        $st->execute([$id]); $bookings=$st->fetchAll();
        include __DIR__ . '/../views/clients/view.php';
    }

    private function validateClient($d, $exId=null) {
        $e=[];
        if(empty(trim($d['last_name'])))$e['last_name']='Обязательно';
        if(empty(trim($d['first_name'])))$e['first_name']='Обязательно';
        if(!$this->isPassport($d['passport_number']??''))$e['passport_number']='Формат: 1234 567890';
        if(!$this->isPhone($d['phone']??''))$e['phone']='Неверный телефон';
        if(!$this->isEmail($d['email']??''))$e['email']='Неверный email';
        if(empty($d['birth_date'])||!$this->isPastDate($d['birth_date'])||!$this->isAdult($d['birth_date']))$e['birth_date']='Мин. 18 лет, не в будущем';
        // Уникальность
        $chk=$this->pdo->prepare("SELECT client_id FROM clients WHERE passport_number=? AND client_id!=?");
        $chk->execute([$d['passport_number']??'',$exId??0]); if($chk->fetch())$e['passport_number']='Уже есть';
        $chk=$this->pdo->prepare("SELECT client_id FROM clients WHERE email=? AND client_id!=?");
        $chk->execute([$d['email']??'',$exId??0]); if($chk->fetch())$e['email']='Email занят';
        $chk=$this->pdo->prepare("SELECT client_id FROM clients WHERE phone=? AND client_id!=?");
        $chk->execute([$d['phone']??'',$exId??0]); if($chk->fetch())$e['phone']='Телефон занят';
        return $e;
    }
}