<?php
require_once '../_base.php';
require '../config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login first");
    exit();
}

// Check if order_id is passed from checkout
if (!isset($_GET['order_id'])) {
    header("Location: checkout.php?error=Invalid order");
    exit();
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Verify this order belongs to the user
$stmt = $pdo->prepare("
    SELECT o.*, m.username, m.email, m.address 
    FROM orders o
    JOIN member m ON o.user_id = m.user_id
    WHERE o.order_id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: checkout.php?error=Order not found");
    exit();
}

// Get order items
$stmt = $pdo->prepare("
    SELECT od.*, p.name, p.img_front 
    FROM order_details od
    JOIN product p ON od.product_id = p.product_id
    WHERE od.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate payment method
    if (empty($_POST['payment_method'])) {
        $errors[] = "Payment method is required";
    } else {
        $payment_method = $_POST['payment_method'];
        
        // Validate based on payment method
        if ($payment_method === 'Credit Card') {
            // Validate credit card details
            if (empty($_POST['card_number'])) {
                $errors[] = "Card number is required";
            } elseif (!preg_match('/^[0-9]{16}$/', str_replace(' ', '', $_POST['card_number']))) {
                $errors[] = "Invalid card number (16 digits required)";
            }
            
            if (empty($_POST['expiry_date'])) {
                $errors[] = "Expiry date is required";
            } elseif (!preg_match('/^(0[1-9]|1[0-2])\/?([0-9]{2})$/', $_POST['expiry_date'])) {
                $errors[] = "Invalid expiry date (MM/YY format required)";
            }
            
            if (empty($_POST['cvv'])) {
                $errors[] = "CVV is required";
            } elseif (!preg_match('/^[0-9]{3,4}$/', $_POST['cvv'])) {
                $errors[] = "Invalid CVV (3 or 4 digits required)";
            }
            
            if (empty($_POST['card_name'])) {
                $errors[] = "Card holder name is required";
            }
        } 
        elseif ($payment_method === 'Bank Transfer') {
            // Validate bank transfer details
            if (empty($_POST['bank_name'])) {
                $errors[] = "Bank name is required";
            }
            
            if (empty($_POST['bank_number'])) {
                $errors[] = "Bank account number is required";
            } elseif (!preg_match('/^[0-9]{10,16}$/', str_replace(' ', '', $_POST['bank_number']))) {
                $errors[] = "Invalid bank account number (10-16 digits required)";
            }
            
            if (empty($_POST['account_name'])) {
                $errors[] = "Account holder name is required";
            }
        }
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                UPDATE orders 
                SET order_status = 'Paid',
                    payment_method = ?
                WHERE order_id = ?
            ");
            $stmt->execute([
                $payment_method,
                $order_id
            ]);
            
            $pdo->commit();
            
            // Redirect to order details page
            header("Location: history_detail.php?order_id=$order_id&product_id=" . $order_items[0]['product_id']);
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Payment processing failed: " . $e->getMessage();
        }
    }
}

$_title = 'Payment | EZ';
include '../_head.php';
?>
<link rel="stylesheet" href="/css/payment.css">
<?php include '../_nav.php'; ?>

<div class="container payment-container">
    <h1>Payment</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card order-summary">
                <div class="card-header">
                    <h3>Order Summary</h3>
                </div>
                <div class="card-body">
                    <div class="order-info">
                        <p><strong>Order ID:</strong> <?= htmlspecialchars($order_id) ?></p>
                        <p><strong>Date:</strong> <?= date('F j, Y', strtotime($order['created_at'])) ?></p>
                        <p><strong>Total:</strong> <?= number_format($order['total_amount'], 2) ?> MYR</p>
                    </div>
                    
                    <ul class="order-items">
                        <?php foreach ($order_items as $item): ?>
                            <li class="order-item">
                                <div class="item-image">
                                    <?php if (!empty($item['img_front'])): ?>
                                        <img src="../images/<?= htmlspecialchars($item['img_front']) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="item-details">
                                    <h5><?= htmlspecialchars($item['name']) ?></h5>
                                    <p>Qty: <?= $item['quantity'] ?></p>
                                    <p>Size: <?= htmlspecialchars($item['size']) ?></p>
                                </div>
                                <div class="item-price">
                                    <?= number_format($item['price'] * $item['quantity'], 2) ?> MYR
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        
                 <div class="card">
                    <div class="card-header">
                        <h3>Delivery Address</h3>
                    </div>
                    <div class="card-body">
                    <div class="address-selection">
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="address_option" 
                                        id="current_address" value="current" checked>
                                <label class="form-check-label" for="current_address">
                                    Current Address: <?= htmlspecialchars($order['address']) ?>
                                </label>
                                
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="address_option" 
                                        id="new_address" value="new">
                                <label class="form-check-label" for="new_address">
                                    Add New Address
                                </label>
                            </div>
                        </div>

                        <!-- New Address Input Field (hidden by default) -->
                        <div id="new-address-field" class="form-group" style="display: none;">
                            <label for="new_address_text">New Delivery Address</label>
                            <textarea class="form-control" id="new_address_text" name="new_address" 
                                rows="3" placeholder="Enter your new address"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>       

        <div class="col-md-6">
            <form method="POST" class="payment-form" id="paymentForm">
                <div class="card payment-methods">
                    <div class="card-header">
                        <h3>Payment Method</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="payment-option">
                                <input type="radio" id="credit-card" name="payment_method" value="Credit Card" checked>
                                <label for="credit-card">
                                    <span class="payment-icon"><i class="fas fa-credit-card"></i></span>
                                    Credit/Debit Card
                                </label>
                            </div>
                            
                            <div id="credit-card-details" class="payment-details">
                                <div class="form-group">
                                    <label for="card_number">Card Number</label>
                                    <input type="text" id="card_number" name="card_number" 
                                           class="form-control" placeholder="1234 5678 9012 3456"
                                           pattern="[0-9\s]{16,19}" 
                                           title="16-digit card number">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="expiry_date">Expiry Date</label>
                                            <input type="text" id="expiry_date" name="expiry_date" 
                                                   class="form-control" placeholder="MM/YY"
                                                   pattern="(0[1-9]|1[0-2])\/?([0-9]{2})"
                                                   title="MM/YY format">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="cvv">CVV</label>
                                            <input type="text" id="cvv" name="cvv" 
                                                   class="form-control" placeholder="123"
                                                   pattern="[0-9]{3}"
                                                   title="3 digit CVV">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="card_name">Card Holder</label>
                                    <input type="text" id="card_name" name="card_name" 
                                           class="form-control" placeholder="John Doe" required>
                                </div>
                            </div>
                        </div>

                            <div class="form-group">
                                <div class="payment-option">
                                    <input type="radio" id="tng" name="payment_method" value="TNG">
                                    <label for="tng">
                                        <span class="payment-icon"></span>
                                        TNG E-Wallet
                                    </label>
                                </div>

                            <div id="tng-details" class="payment-details" style="display: none;">
                                    <img src="../images/tng.png" width="30%">
                                </div>
                            </div>
                        
                        
                            <div class="form-group">
                                <div class="payment-option">
                                    <input type="radio" id="bank-transfer" name="payment_method" value="Bank Transfer">
                                    <label for="bank-transfer">
                                        <span class="payment-icon"></span>
                                        Bank Transfer
                                    </label>
                                </div>

                                <div id="bank-details" class="payment-details" style="display: none;">
                                    <div class="form-group">
                                        <label for="bank_name">Bank Name</label>
                                        <input type="text" id="bank_name" name="bank_name" 
                                               class="form-control" placeholder="Hong Leong Bank" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="bank_number">Bank Account Number</label>
                                        <input type="text" id="bank_number" name="bank_number" 
                                               class="form-control" placeholder="1234 5678 9012 3456"
                                               pattern="[0-9\s]{10,16}"
                                               title="10-16 digit account number">
                                    </div>
                                    <div class="form-group">
                                        <label for="account_name">Account Holder Name</label>
                                        <input type="text" id="account_name" name="account_name" 
                                               class="form-control" placeholder="John Doe" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                
                <div class="payment-buttons">
                    <button type="submit" class="btn btn-primary btn-lg payment-btn">
                        Complete Payment
                    </button>
                    <a href="../index.php" class="btn btn-outline-primary btn-lg btn-block pay-later-btn">
                        Pay Later
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../js/payment.js"></script>
<?php include '../_foot.php'; ?>