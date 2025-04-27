<!DOCTYPE html>
<html lang="en">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>

    <!-- Bootstrap CSS library https://getbootstrap.com/ -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
</head>
<body>
    <div style="margin-top: 20px" class="container">
        <h1 class="text-center">Welcome to the Home Directory</h1>

        <!-- link to make a request -->
        <div class="text-center">
            <a class="btn btn-primary" href="visitor/submit_request.php">Make a request</a>
        </div>

        <!-- link to Student System Login -->
        <div class="text-center mt-3">
            <a class="btn btn-primary" href="user/user_login.php">Go to Student Login</a>
        </div>

        <!-- link to UTEP HelpDesk -->
        <div class="text-center mt-3">
            <a class="btn btn-primary" href="staff/staff_login.php">Go to Staff Login</a>
        </div>
    </div>
</body>
</html>
