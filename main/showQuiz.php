<?php
require_once __DIR__ . '/../config/connection.php';
require_login();
$result = db()->query('SELECT id, catagorie, description FROM public.quiz');
$rows = $result->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Show Quizzes</title>
</head>
<body class="bg-dark">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-body fixed-top shadow-sm">
        <div class="container-fluid">
          <a class="navbar-brand" href="#"><strong class="text-primary">Mu5tasar</strong></a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
              <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="index.php">Home</a>
              </li>
              <li class="nav-item">
        
        		<?php
        			 if(isset($_SESSION['email'])){
                        echo '<form method="POST" action="logout.php" class="m-0">' . csrf_field() . '<button type="submit" class="nav-link border-0 bg-transparent">Logout</button></form>';
                    } else {
                        echo '<a class="nav-link" href="login.php">Log in</a>';
                    }
				?>
              </li>
              
              <li class="nav-item">
                <a class="nav-link" href="#about">About</a>
              </li>
              
            </ul>
          </div>
        </div>
      </nav>
<main class="container py-5" style="margin-top:100px;">
<h2 class="text-center text-light">All Quizzes</h2>

<div class="row">
    
<?php
if ($rows) {
    foreach ($rows as $row) {
        echo "<div class='col-md-4 mb-4 '>";
        echo "<div class='card h-100 shadow-sm bg-primary'>";
        echo "<div class='card-body'>";
        echo "<h5 class='card-title text-light'>Category: ".e($row['catagorie'])."</h5>";


        echo "<a href='quizAndResult.php?id=".e(rawurlencode((string) $row['id']))."' class='stretched-link'></a>";
        echo "<h5 class='card-title text-light'>description: ".e($row['description'])."</h5>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }
} else {
    echo "<p>No quizzes found.</p>";
}
?>
</div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
