<?php
session_start();
require_once('../config.php');

$error_message = '';

if (isset($_POST['Submit-login'])) {
    $email = isset($_POST['email']) ? $_POST['email'] : "";
    
    if (empty($email)) {
        $error_message = "Please enter your email.";
    } else {
        $query = "SELECT * FROM Visitor WHERE VEmail = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $visitor = $result->fetch_assoc();
            $_SESSION['visitor_id'] = $visitor['VId'];
            $_SESSION['visitor_name'] = $visitor['VFname'] . " " . $visitor['VLName'];
            
            // Redirect to request form
            header("Location: submit_request.php");
            exit();
        } else {
            $error_message = "Email not found. Please check your email or register as a new visitor.";
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Visitor Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h2 class="text-center">Visitor Login</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form action="visitor_login.php" method="post">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input class="form-control" type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group text-center">
                        <input class="btn btn-primary" name="Submit-login" type="submit" value="Login">
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p>New visitor? <a href="submit_request.php">Submit a request</a> to register.</p>
                </div>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="../index.php">Back to Home</a>
        </div>
    </div>
</body>
</html>