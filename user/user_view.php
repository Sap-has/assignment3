<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>user Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center">user Dashboard</h1>
        <div class="text-center mb-4">
            <p>Welcome, <?php echo isset($_SESSION['visitor_name']) ? $_SESSION['visitor_name'] : 'user'; ?>!</p>
        </div>

        <!-- link to make a request -->
        <div class="text-center mt-3">
            <a class="btn btn-primary" href="user_submit_request.php">Make a request</a>
        </div>

        <!-- link to view requests -->
        <div class="text-center mt-3">
            <a class="btn btn-primary" href="user_requests.php">View your requests</a>
        </div>

        <!-- link to logout -->
        <div class="text-center mt-3">
            <a class="btn btn-primary" href="user_logout.php">Log out</a>
        </div>
    </div>
</body>
</html>