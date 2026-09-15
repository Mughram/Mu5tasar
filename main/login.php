<?php
require_once __DIR__ . '/../config/connection.php';

$message_success = '';
$message_error = '';
if ($method === 'POST') {
    $email = post_text('email');
    $password = post_text('password');
    if ($email !== '' && $password !== '') {
        $check = db()->prepare('SELECT email, password FROM public.users WHERE email = ?');
        $check->execute([$email]);
        $user = $check->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['email'] = $user['email'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: index.php');
            exit;
        }
        $message_error = $user ? 'incorrect password' : 'email not found';
    } else {
        $message_error = 'fill the email and password';
    }
}
?>    



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet"/>
</head>

<body class="d-flex justify-content-center align-items-center vh-100 bg-primary">
    
    <div class="container" style="max-width: 450px;">
        
        <div class="card shadow-5-strong p-5">
            <h1>Log in</h1>
            <form action="" method="POST">
                <?php echo csrf_field(); ?>
            
            <div data-mdb-input-init class="form-outline mb-4">
                <input type="email" id="form2Example1" name="email" class="form-control" />
                <label class="form-label" for="form2Example1">Email address</label>
            </div>

            <div data-mdb-input-init class="form-outline mb-4">
                <input type="password" id="form2Example2" name="password" class="form-control" />
                <label class="form-label" for="form2Example2">Password</label>
            </div>
                 <?php if(!empty($message_error)){
    					echo "<div class='alert alert-danger' role='alert'>" . e($message_error) . "</div>";
                   }
				?>

            
            <button type="submit" class="btn btn-primary btn-block mb-4">Log in</button>

            <div class="text-center">
                <p>Don't have an account? <a href="register.php">Create one</a></p>
            </div>
            </form>
        

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.umd.min.js"></script>    
</body>
</html>
