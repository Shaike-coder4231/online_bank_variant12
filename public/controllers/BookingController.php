<?php
require_once __DIR__ . '/BaseController.php';

class BookingController extends BaseController {
    
    // 📋 Список записей с фильтрами
    public function listAction($filters = []) {
        $page = max(1, (int)($filters['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Фильтры
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(b.booking_datetime) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(b.booking_datetime) <= ?';
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['status']) && in_array($filters['status'], ['pending','confirmed','completed','cancelled'])) {
            $where[] = 'b.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['specialist_id'])) {
            $where[] = 'b.specialist_id = ?';
            $params[] = (int)$filters['specialist_id'];
        }
        if (!empty($filters['client_search'])) {
            $like = "%{$filters['client_search']}%";
            $where[] = '(c.last_name LIKE ? OR c.first_name LIKE ? OR c.phone LIKE ?)';
            $params = array_merge($params, [$like, $like, $like]);
        }
        
        $whereSql = implode(' AND ', $where);
        
        // Подсчёт
        $cnt = $this->pdo->prepare("SELECT COUNT(*) FROM bookings b JOIN clients c ON b.client_id = c.client_id WHERE $whereSql");
        $cnt->execute($params);
        $total = $cnt->fetchColumn();
        $totalPages = ceil($total / $limit);
        
        // Данные
        $stmt = $this->pdo->prepare("
            SELECT b.*, c.last_name, c.first_name, c.phone as client_phone,
                   s.service_name, s.duration, s.price,
                   sp.last_name as spec_last, sp.first_name as spec_first, sp.specialization
            FROM bookings b
            JOIN clients c ON b.client_id = c.client_id
            JOIN services s ON b.service_id = s.service_id
            JOIN specialists sp ON b.specialist_id = sp.specialist_id
            WHERE $whereSql
            ORDER BY b.booking_datetime DESC
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute($params);
        $bookings = $stmt->fetchAll();
        
        // Список специалистов для фильтра
        $specs = $this->pdo->query("SELECT specialist_id, last_name, first_name FROM specialists ORDER BY last_name")->fetchAll();
        
        include __DIR__ . '/../views/bookings/list.php';
    }
    
    // ➕ Страница создания записи
    public function createAction() {
        // Загружаем справочники
        $services = $this->pdo->query("SELECT service_id, service_name, duration, price FROM services WHERE is_active = 1 ORDER BY service_name")->fetchAll();
        $specialists = $this->pdo->query("SELECT specialist_id, last_name, first_name, specialization FROM specialists WHERE is_active = 1 ORDER BY last_name")->fetchAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCSRF();
            $data = $_POST;
            $errors = $this->validateBooking($data);
            
            if (empty($errors)) {
                // 🔒 Конкурентная проверка: слот ещё свободен?
                $check = $this->pdo->prepare("SELECT COUNT(*) FROM bookings WHERE specialist_id = ? AND DATE(booking_datetime) = ? AND TIME(booking_datetime) = ? AND status != 'cancelled'");
                $check->execute([$data['specialist_id'], $data['booking_date'], $data['booking_time']]);
                if ($check->fetchColumn() > 0) {
                    $errors['slot'] = 'К сожалению, это время только что занято. Пожалуйста, выберите другое.';
                } else {
                    try {
                        $this->pdo->beginTransaction();
                        
                        // Создаём запись
                        $stmt = $this->pdo->prepare("
                            INSERT INTO bookings (client_id, service_id, specialist_id, booking_datetime, status, total_price, booking_code)
                            VALUES (?, ?, ?, CONCAT(?, ' ', ?), 'pending', ?, ?)
                        ");
                        $stmt->execute([
                            (int)$data['client_id'],
                            (int)$data['service_id'],
                            (int)$data['specialist_id'],
                            $data['booking_date'],
                            $data['booking_time'],
                            floatval($data['total_price']),
                            '' // код сгенерируем после получения ID
                        ]);
                        $bookingId = $this->pdo->lastInsertId();
                        
                        // Генерируем и обновляем код бронирования
                        $code = $this->generateBookingCode($bookingId);
                        $upd = $this->pdo->prepare("UPDATE bookings SET booking_code = ? WHERE booking_id = ?");
                        $upd->execute([$code, $bookingId]);
                        
                        $this->pdo->commit();
                        
                        $this->setFlash('success', "Запись создана! Код бронирования: <strong>$code</strong>");
                        header('Location: index.php?page=bookings/view&id=' . $bookingId);
                        exit;
                    } catch (Exception $e) {
                        $this->pdo->rollBack();
                        $errors['db'] = 'Ошибка при сохранении: ' . $e->getMessage();
                    }
                }
            }
        } else {
            $data = [];
            $errors = [];
        }
        
        include __DIR__ . '/../views/bookings/create.php';
    }
    
    // 👁️ Просмотр деталей записи
    public function viewAction($id) {
        if ($id <= 0) { $this->setFlash('error', 'Неверный ID'); header('Location: index.php?page=bookings/list'); exit; }
        
        $stmt = $this->pdo->prepare("
            SELECT b.*, c.*, s.service_name, s.duration, s.price, sp.*,
                   DATE(b.booking_datetime) as b_date, TIME(b.booking_datetime) as b_time
            FROM bookings b
            JOIN clients c ON b.client_id = c.client_id
            JOIN services s ON b.service_id = s.service_id
            JOIN specialists sp ON b.specialist_id = sp.specialist_id
            WHERE b.booking_id = ?
        ");
        $stmt->execute([$id]);
        $booking = $stmt->fetch();
        
        if (!$booking) { $this->setFlash('error', 'Запись не найдена'); header('Location: index.php?page=bookings/list'); exit; }
        
        include __DIR__ . '/../views/bookings/view.php';
    }
    
    // ✏️ Перенос записи
    public function rescheduleAction($id) {
        if ($id <= 0) { $this->setFlash('error', 'Неверный ID'); header('Location: index.php?page=bookings/list'); exit; }
        
        $stmt = $this->pdo->prepare("SELECT * FROM bookings WHERE booking_id = ? AND status != 'cancelled' AND status != 'completed'");
        $stmt->execute([$id]);
        $booking = $stmt->fetch();
        if (!$booking) { $this->setFlash('error', 'Запись не найдена или не может быть перенесена'); header('Location: index.php?page=bookings/list'); exit; }
        
        $services = $this->pdo->query("SELECT service_id, service_name, duration, price FROM services WHERE is_active = 1")->fetchAll();
        $specialists = $this->pdo->query("SELECT specialist_id, last_name, first_name FROM specialists WHERE is_active = 1")->fetchAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCSRF();
            $data = $_POST;
            $errors = $this->validateBooking($data, true); // $isReschedule = true
            
            if (empty($errors)) {
                // Проверка: новый слот свободен (исключая текущую запись)
                $check = $this->pdo->prepare("SELECT COUNT(*) FROM bookings WHERE specialist_id = ? AND DATE(booking_datetime) = ? AND TIME(booking_datetime) = ? AND booking_id != ? AND status != 'cancelled'");
                $check->execute([$data['specialist_id'], $data['booking_date'], $data['booking_time'], $id]);
                if ($check->fetchColumn() > 0) {
                    $errors['slot'] = 'Это время уже занято другой записью.';
                } else {
                    $stmt = $this->pdo->prepare("UPDATE bookings SET specialist_id = ?, booking_datetime = CONCAT(?, ' ', ?), total_price = ? WHERE booking_id = ?");
                    $stmt->execute([(int)$data['specialist_id'], $data['booking_date'], $data['booking_time'], floatval($data['total_price']), $id]);
                    
                    $this->setFlash('success', 'Запись успешно перенесена!');
                    header('Location: index.php?page=bookings/view&id=' . $id);
                    exit;
                }
            }
        } else {
            $data = [
                'service_id' => $booking['service_id'],
                'specialist_id' => $booking['specialist_id'],
                'booking_date' => date('Y-m-d', strtotime($booking['booking_datetime'])),
                'booking_time' => date('H:i', strtotime($booking['booking_datetime'])),
            ];
            $errors = [];
        }
        
        include __DIR__ . '/../views/bookings/reschedule.php';
    }
    
    // ❌ Отмена записи
    public function cancelAction($id) {
        if ($id <= 0) { $this->setFlash('error', 'Неверный ID'); header('Location: index.php?page=bookings/list'); exit; }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCSRF();
            $stmt = $this->pdo->prepare("SELECT status FROM bookings WHERE booking_id = ?");
            $stmt->execute([$id]);
            $status = $stmt->fetchColumn();
            
            if ($status === 'completed') {
                $this->setFlash('error', 'Нельзя отменить завершённую запись.');
            } elseif ($status === 'cancelled') {
                $this->setFlash('error', 'Запись уже отменена.');
            } else {
                $stmt = $this->pdo->prepare("UPDATE bookings SET status = 'cancelled', cancelled_at = NOW() WHERE booking_id = ?");
                $stmt->execute([$id]);
                $this->setFlash('success', 'Запись отменена. Время освобождено.');
            }
            header('Location: index.php?page=bookings/list');
            exit;
        }
        
        $stmt = $this->pdo->prepare("SELECT b.*, c.last_name, c.first_name, s.service_name FROM bookings b JOIN clients c ON b.client_id=c.client_id JOIN services s ON b.service_id=s.service_id WHERE b.booking_id = ?");
        $stmt->execute([$id]);
        $booking = $stmt->fetch();
        
        include __DIR__ . '/../views/bookings/cancel.php';
    }
    
    // 🔄 Обновление статуса через AJAX
    public function updateStatusAction() {
        header('Content-Type: application/json');
        $this->verifyCSRF();
        
        $id = (int)($_POST['booking_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        
        if ($id <= 0 || !in_array($status, ['pending','confirmed','completed','cancelled'])) {
            echo json_encode(['success' => false, 'error' => 'Неверные параметры']);
            exit;
        }
        
        // Проверка допустимости перехода
        $stmt = $this->pdo->prepare("SELECT status FROM bookings WHERE booking_id = ?");
        $stmt->execute([$id]);
        $current = $stmt->fetchColumn();
        
        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];
        
        if (!in_array($status, $validTransitions[$current] ?? [])) {
            echo json_encode(['success' => false, 'error' => "Недопустимый переход: $current → $status"]);
            exit;
        }
        
        $stmt = $this->pdo->prepare("UPDATE bookings SET status = ?, updated_at = NOW() WHERE booking_id = ?");
        $stmt->execute([$status, $id]);
        
        echo json_encode(['success' => true, 'new_status' => $status]);
        exit;
    }
    
    // 🔍 Валидация записи
    private function validateBooking($data, $isReschedule = false) {
        $errors = [];
        
        if (empty($data['client_id']) || (int)$data['client_id'] <= 0) $errors['client_id'] = 'Выберите клиента';
        if (empty($data['service_id']) || (int)$data['service_id'] <= 0) $errors['service_id'] = 'Выберите услугу';
        if (empty($data['specialist_id']) || (int)$data['specialist_id'] <= 0) $errors['specialist_id'] = 'Выберите специалиста';
        if (empty($data['booking_date']) || !$this->isFutureDate($data['booking_date'])) $errors['booking_date'] = 'Дата не может быть в прошлом';
        if (empty($data['booking_time']) || !$this->isWorkingHours($data['booking_time'])) $errors['booking_time'] = 'Время вне рабочего графика (9:00-20:00, перерыв 14:00-15:00)';
        if (empty($data['total_price']) || !is_numeric($data['total_price']) || floatval($data['total_price']) < 0) $errors['total_price'] = 'Неверная сумма';
        
        // Проверка: не более 1 записи на ту же услугу к тому же специалисту в день
        if (!$isReschedule) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM bookings WHERE client_id = ? AND service_id = ? AND specialist_id = ? AND DATE(booking_datetime) = ? AND status != 'cancelled'");
            $stmt->execute([$data['client_id'], $data['service_id'], $data['specialist_id'], $data['booking_date']]);
            if ($stmt->fetchColumn() > 0) {
                $errors['slot'] = 'У вас уже есть запись на эту услугу к этому специалисту на выбранную дату.';
            }
        }
        
        return $errors;
    }
    
    // 🔁 Получение доступных слотов (для AJAX)
    public function getAvailableSlots($specialistId, $date, $serviceDuration) {
        $slots = [];
        $start = strtotime(WORK_START);
        $end = strtotime(WORK_END);
        $lunchS = strtotime(LUNCH_START);
        $lunchE = strtotime(LUNCH_END);
        $step = SLOT_STEP * 60; // в секундах
        
        // Получаем уже занятые слоты на эту дату у этого специалиста
        $stmt = $this->pdo->prepare("
            SELECT TIME(booking_datetime) as time, s.duration as booked_duration
            FROM bookings b
            JOIN services s ON b.service_id = s.service_id
            WHERE b.specialist_id = ? AND DATE(b.booking_datetime) = ? AND b.status != 'cancelled'
        ");
        $stmt->execute([$specialistId, $date]);
        $booked = $stmt->fetchAll();
        
        $bookedIntervals = [];
        foreach ($booked as $b) {
            $bStart = strtotime("$date {$b['time']}");
            $bEnd = $bStart + ($b['booked_duration'] * 60);
            $bookedIntervals[] = [$bStart, $bEnd];
        }
        
        // Генерируем слоты
        for ($t = $start; $t + ($serviceDuration * 60) <= $end; $t += $step) {
            // Пропускаем обед
            if (($t < $lunchE && $t + ($serviceDuration * 60) > $lunchS)) continue;
            
            $slotEnd = $t + ($serviceDuration * 60);
            $conflict = false;
            
            // Проверяем пересечение с занятыми интервалами
            foreach ($bookedIntervals as [$bStart, $bEnd]) {
                if ($t < $bEnd && $slotEnd > $bStart) {
                    $conflict = true;
                    break;
                }
            }
            
            if (!$conflict) {
                $slots[] = date('H:i', $t);
            }
        }
        
        return $slots;
    }
}