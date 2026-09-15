<?php
require_once __DIR__ . '/../config/connection.php';

$message_success = '';
$message_error = '';
if ($method === 'POST') {
    $email = post_text('email');
    $password = post_text('password');
    if ($email !== '' && $password !== '') {
        $check = db()->prepare('SELECT email FROM public.users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $message_error = 'email already exixt';
        } elseif (strlen($email) > 5 && strlen($password) > 5) {
            $hashed_pass = password_hash($password, PASSWORD_BCRYPT);
            $query = db()->prepare('INSERT INTO public.users (email, password) VALUES (?, ?)');
            try {
                $query->execute([$email, $hashed_pass]);
                $message_success = 'Your account is created!';
            } catch (PDOException $error) {
                if ($error->getCode() !== '23505') {
                    throw $error;
                }
                $message_error = 'email already exixt';
            }
        } else {
            $message_error = ' email and password must be more than 5 charctares';
        }
    } else {
        $message_error = 'Fill in your email and password!';
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet"/>
</head>

<body class="d-flex justify-content-center align-items-center vh-100 bg-primary">

    <div class="container" style="max-width: 450px;">

        <div class="card shadow-5-strong p-5">

            <h1>Register </h1>

          
            <form action="register.php" method="post">
                <?php echo csrf_field(); ?>

                
                <div data-mdb-input-init class="form-outline mb-4">
                    <input type="email" id="email" name="email" class="form-control" />
                    <label class="form-label" for="email">Email</label>
                </div>

                <div data-mdb-input-init class="form-outline mb-4">
                    <input type="password" id="password" name="password" class="form-control" />
                    <label class="form-label" for="password">Password</label>
                </div>
                
                <?php if(!empty($message_error)){
    					echo "<div class='alert alert-danger' role='alert'>" . e($message_error) . "</div>";
                   }
                	  if(!empty($message_success)){
    					echo "<div class='alert alert-success' role='alert'>" . e($message_success) . "</div>";
                   }
                ?>
                <button type="submit" class="btn btn-primary btn-block mb-4">
                    Create Account
                </button>
                
			<div class="text-center">
                <p>Already have an account? <a href="login.php"> Log in</a></p>
            </div>
            </form>
        </div>

       
      

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.umd.min.js"></script>
</html>
</body>
                     
