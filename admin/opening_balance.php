<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// ✅ Detect user type
if (isset($_SESSION['admin_id'])) {
    $user_id = $_SESSION['admin_id'];
    $user_type = 'admin';
} elseif (isset($_SESSION['staff_id'])) {
    $user_id = $_SESSION['staff_id'];
    $user_type = 'staff';
} else {
    // No valid session, redirect
    header("Location: login.php");
    exit;
}

$shift_date = date('Y-m-d');
$err = $success = '';

// ✅ Check for existing open or closed balance for this user
$stmt = $mysqli->prepare("SELECT id, status FROM rpos_balances WHERE user_id = ? AND user_type = ? AND balance_date = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param('iss', $user_id, $user_type, $shift_date);
$stmt->execute();
$stmt->bind_result($balance_id, $status);
$stmt->fetch();
$stmt->close();

$need_new_entry = false;

if (!$balance_id) {
    $need_new_entry = true;
} elseif ($status == 'closed') {
    $need_new_entry = true;
} else {
    $need_new_entry = false;
}

if (isset($_POST['submit'])) {
    $opening_balance = floatval($_POST['opening_balance']);

    if ($opening_balance < 0) {
        $err = "Opening balance cannot be negative.";
    } else {
        if ($need_new_entry) {
            $stmt = $mysqli->prepare("INSERT INTO rpos_balances (balance_date, opening_balance, user_id, user_type, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'open', NOW(), NOW())");
            $stmt->bind_param('sdss', $shift_date, $opening_balance, $user_id, $user_type);
        } else {
            $stmt = $mysqli->prepare("UPDATE rpos_balances SET opening_balance = ?, status = 'open', updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('di', $opening_balance, $balance_id);
        }

        if ($stmt->execute()) {
            $success = "Opening balance saved!";
            // Redirect to respective dashboard
            if ($user_type === 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: staff/index.php");
            }
            exit;
        } else {
            $err = "Failed to save balance. Try again.";
        }
        $stmt->close();
    }
}
?>

<?php include('includes/header.php'); ?>

<body class="bg-dark">
    <div class="main-content">
        <div class="header bg-gradient-primary py-5">
            <div class="container text-center">
                <h1 class="text-white">Enter Opening Balance for Your Shift</h1>
            </div>
        </div>
        <div class="container mt--6 pb-5">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7">
                    <div class="card bg-secondary shadow border-0">
                        <div class="card-body px-lg-5 py-lg-5">
                            <?php if ($err)
                                echo "<div class='alert alert-danger'>$err</div>"; ?>
                            <?php if ($success)
                                echo "<div class='alert alert-success'>$success</div>"; ?>
                            <form method="POST">
                                <div class="form-group">
                                    <label>Opening Balance (Rs.)</label>
                                    <input type="number" step="0.01" min="0" name="opening_balance" class="form-control"
                                        required autofocus>
                                </div>
                                <button type="submit" name="submit" class="btn btn-primary btn-block">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>
    <?php include('includes/scripts.php'); ?>
</body>