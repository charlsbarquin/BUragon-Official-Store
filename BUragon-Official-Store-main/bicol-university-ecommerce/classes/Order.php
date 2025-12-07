<?php
// classes/Order.php
require_once __DIR__ . '/../includes/db_connect.php';

class Order {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDbConnection();
    }
    
    /**
     * Create a new order with order items
     * @param int $userId
     * @param array $cartItems
     * @param float $totalAmount
     * @param string $shippingAddress
     * @param string $paymentMethod
     * @return array|false
     */
    public function createOrder($userId, $cartItems, $totalAmount, $shippingAddress, $paymentMethod = 'cash_on_delivery') {
        try {
            $this->pdo->beginTransaction();
            
            // Generate unique order number
            $orderNumber = $this->generateOrderNumber();
            
            // Insert order
            $stmt = $this->pdo->prepare("
                INSERT INTO orders (user_id, order_number, total_amount, shipping_address, payment_method, status, payment_status) 
                VALUES (?, ?, ?, ?, ?, 'pending', 'pending')
            ");
            $stmt->execute([$userId, $orderNumber, $totalAmount, $shippingAddress, $paymentMethod]);
            
            $orderId = $this->pdo->lastInsertId();
            
            // Insert order items
            foreach ($cartItems as $item) {
                $this->addOrderItem($orderId, $item['product_id'], $item['quantity'], $item['unit_price']);
                
                // Update product stock
                $this->updateProductStock($item['product_id'], $item['quantity']);
            }
            
            $this->pdo->commit();
            
            return [
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'status' => 'success'
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Order creation failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add an item to an order
     * @param int $orderId
     * @param int $productId
     * @param int $quantity
     * @param float $unitPrice
     * @return bool
     */
    private function addOrderItem($orderId, $productId, $quantity, $unitPrice) {
        $totalPrice = $quantity * $unitPrice;
        
        $stmt = $this->pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([$orderId, $productId, $quantity, $unitPrice, $totalPrice]);
    }
    
    /**
     * Update product stock after order
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    private function updateProductStock($productId, $quantity) {
        $stmt = $this->pdo->prepare("
            UPDATE products 
            SET stock_quantity = stock_quantity - ?, 
                status = CASE 
                    WHEN stock_quantity - ? <= 0 THEN 'out_of_stock' 
                    ELSE status 
                END
            WHERE id = ?
        ");
        
        return $stmt->execute([$quantity, $quantity, $productId]);
    }
    
    /**
     * Generate unique order number
     * @return string
     */
    private function generateOrderNumber() {
        $year = date('Y');
        $timestamp = time();
        $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        return "ORD-{$year}-{$timestamp}-{$random}";
    }
    
    /**
     * Get order by ID with items
     * @param int $orderId
     * @return array|false
     */
    public function getOrderById($orderId) {
        try {
            // Get order details
            $stmt = $this->pdo->prepare("
                SELECT o.*, u.username, u.email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?
            ");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            
            if (!$order) {
                return false;
            }
            
            // Get order items
            $stmt = $this->pdo->prepare("
                SELECT oi.*, p.name as product_name, p.image as product_image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$orderId]);
            $order['items'] = $stmt->fetchAll();
            
            return $order;
            
        } catch (Exception $e) {
            error_log("Error fetching order: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get orders by user ID
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getOrdersByUserId($userId, $limit = 10, $offset = 0) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM orders 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$userId, $limit, $offset]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error fetching user orders: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update order status
     * @param int $orderId
     * @param string $status
     * @return bool
     */
    public function updateOrderStatus($orderId, $status) {
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$status, $orderId]);
            
        } catch (Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update payment status
     * @param int $orderId
     * @param string $paymentStatus
     * @return bool
     */
    public function updatePaymentStatus($orderId, $paymentStatus) {
        $validStatuses = ['pending', 'paid', 'failed'];
        
        if (!in_array($paymentStatus, $validStatuses)) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$paymentStatus, $orderId]);
            
        } catch (Exception $e) {
            error_log("Error updating payment status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all orders (for admin)
     * @param int $limit
     * @param int $offset
     * @param string $status
     * @return array
     */
    public function getAllOrders($limit = 20, $offset = 0, $status = null) {
        try {
            $sql = "
                SELECT o.*, u.username, u.email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id
            ";
            
            $params = [];
            
            if ($status) {
                $sql .= " WHERE o.status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error fetching all orders: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get order statistics
     * @return array
     */
    public function getOrderStats() {
        try {
            $stats = [];
            
            // Total orders
            $stmt = $this->pdo->query("SELECT COUNT(*) as total_orders FROM orders");
            $stats['total_orders'] = $stmt->fetch()['total_orders'];
            
            // Total revenue
            $stmt = $this->pdo->query("SELECT SUM(total_amount) as total_revenue FROM orders WHERE payment_status = 'paid'");
            $stats['total_revenue'] = $stmt->fetch()['total_revenue'] ?? 0;
            
            // Orders by status
            $stmt = $this->pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
            $statusCounts = $stmt->fetchAll();
            foreach ($statusCounts as $status) {
                $stats['status_' . $status['status']] = $status['count'];
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error fetching order stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Clear user cart after successful order
     * @param int $userId
     * @return bool
     */
    public function clearUserCart($userId) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            return $stmt->execute([$userId]);
            
        } catch (Exception $e) {
            error_log("Error clearing user cart: " . $e->getMessage());
            return false;
        }
    }
}