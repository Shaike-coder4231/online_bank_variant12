<?php
require_once __DIR__ . '/BaseController.php';

class ReportController extends BaseController {
    
    public function indexAction() {
        $period = $_GET['period'] ?? 'month';
        $month = $_GET['month'] ?? date('Y-m');
        
        $reports = [];
        
        // Отчёт 1: Записи и выручка по дням
        $stmt = $this->pdo->prepare("
            SELECT DATE(booking_datetime) as day, COUNT(*) as count, SUM(total_price) as revenue
            FROM bookings
            WHERE DATE_FORMAT(booking_datetime, '%Y-%m') = ? AND status != 'cancelled'
            GROUP BY day
            ORDER BY day
        ");
        $stmt->execute([$month]);
        $reports['daily'] = $stmt->fetchAll();
        
        // Отчёт 2: Рейтинг специалистов
        $stmt = $this->pdo->prepare("
            SELECT sp.specialist_id, sp.last_name, sp.first_name, 
                   COUNT(b.booking_id) as bookings_count, SUM(b.total_price) as revenue
            FROM specialists sp
            LEFT JOIN bookings b ON sp.specialist_id = b.specialist_id AND b.status != 'cancelled'
            WHERE sp.is_active = 1
            GROUP BY sp.specialist_id
            ORDER BY bookings_count DESC, revenue DESC
        ");
        $stmt->execute();
        $reports['specialists'] = $stmt->fetchAll();
        
        // Отчёт 3: Отменённые записи
        $stmt = $this->pdo->prepare("
            SELECT DATE(b.booking_datetime) as day, COUNT(*) as cancelled_count,
                   GROUP_CONCAT(CONCAT(c.last_name, ' ', LEFT(c.first_name,1), '.')) as clients
            FROM bookings b
            JOIN clients c ON b.client_id = c.client_id
            WHERE b.status = 'cancelled' AND DATE_FORMAT(b.booking_datetime, '%Y-%m') = ?
            GROUP BY day
            ORDER BY day DESC
        ");
        $stmt->execute([$month]);
        $reports['cancelled'] = $stmt->fetchAll();
        
        include __DIR__ . '/../views/reports/index.php';
    }
    
    public function exportAction() {
        $type = $_GET['type'] ?? 'daily';
        $month = $_GET['month'] ?? date('Y-m');
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=report_' . $type . '_' . $month . '.csv');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM для Excel
        
        if ($type === 'daily') {
            fputcsv($output, ['Дата', 'Количество записей', 'Выручка (₽)']);
            $stmt = $this->pdo->prepare("SELECT DATE(booking_datetime) as day, COUNT(*) as count, SUM(total_price) as revenue FROM bookings WHERE DATE_FORMAT(booking_datetime, '%Y-%m') = ? AND status != 'cancelled' GROUP BY day ORDER BY day");
            $stmt->execute([$month]);
            foreach ($stmt->fetchAll() as $row) {
                fputcsv($output, [$row['day'], $row['count'], number_format($row['revenue'], 2, '.', '')]);
            }
        } elseif ($type === 'specialists') {
            fputcsv($output, ['Специалист', 'Записей', 'Выручка (₽)']);
            $stmt = $this->pdo->prepare("SELECT CONCAT(sp.last_name, ' ', sp.first_name) as name, COUNT(b.booking_id) as count, SUM(b.total_price) as revenue FROM specialists sp LEFT JOIN bookings b ON sp.specialist_id = b.specialist_id AND b.status != 'cancelled' WHERE sp.is_active = 1 GROUP BY sp.specialist_id ORDER BY count DESC");
            $stmt->execute();
            foreach ($stmt->fetchAll() as $row) {
                fputcsv($output, [$row['name'], $row['count'], number_format($row['revenue'], 2, '.', '')]);
            }
        }
        
        fclose($output);
        exit;
    }
}