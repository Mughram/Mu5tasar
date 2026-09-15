<?php
require_once __DIR__ . '/../config/security.php';
?>    

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Mu5tasar</title>
    
    <style>
      html {
        scroll-behavior: smooth;
        scroll-padding-top: 80px; 
      }
      
      .bg-image {
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
      }
    </style>
  </head>
  
  <body class="p-0 m-0 border-0">
    <header>
          <?php include('includes/navbar.php'); ?>
      <div
        class="text-center bg-image d-flex align-items-center justify-content-center"
        style="
          background-image: url('quiz.jpg');
          height: 100vh; 
        "
      >
        <div class="mask" style="background-color: rgba(0, 0, 0, 0.6); width: 100%; height: 100%;">
          <div class="d-flex justify-content-center align-items-center h-100">
            <div class="text-white">
              <h1 class="mb-3">Welcome to <strong class="text-primary">Mu5tasar</strong></h1>
              <h4 class="mb-3">Test your knowledge today</h4>
              <a class="btn btn-outline-light btn-lg" href="createQuiz.php" role="button">Create Quiz</a>
              <a class="btn btn-outline-light btn-lg" href="showQuiz.php" role="button">Test yourself</a>
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="container py-5">
      
      <section id="about" class="mb-5">
        <div class="row">
          <div class="col-lg-8 mx-auto text-center">
            
              <h2 class="display-5 mb-4">About <strong class="text-primary">Mu5tasar</strong></h2>
            <p class="lead text-muted"></p>
            <hr class="w-50 mx-auto mb-5">
            
            <div class="text-start">
                <p>
                The ultimate platform for quick 5-question challenges. Fast, fun, and educational.
                </p>
                
                <h4 class="mt-5">Who We Are</h4>
                <p>
                We are Mughram Ayashi, Rayan Hakami, Alaihm Ayel and Mohammad Holbah a group of students from Jazan University / College of Engineering and Computer Scince.
                </p>
                
                
            </div>

          </div>
        </div>
      </section>

      

    </main>
      <footer class="bg-dark text-white pt-5 pb-4 mt-5">
    <div class="container text-center text-md-start">
        <div class="row">
            <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 font-weight-bold text-primary">Mu5tasar</h5>
                <p>The ultimate platform for quick 5-question challenges. Fast, fun, and educational.</p>
            </div>


            <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 font-weight-bold">Helpful Links</h5>
                <p><a href="index.php" class="text-white" style="text-decoration: none;">Home</a></p>
                <p><a href="#about" class="text-white" style="text-decoration: none;">About Us</a></p>
            </div>

            <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 font-weight-bold">Contact</h5>
                <p>Mu5tasar@gmail.com</p>
                <p>Based in Saudi Arabia</p>
            </div>
        </div>

        <hr class="mb-4">

        <div class="row align-items-center">
            <div class="col-md-7 col-lg-8">
                <p>© 2025 Copyright: <strong>Mu5tasar</strong></p>
            </div>
        </div>
    </div>
</footer>

    
  </body>
</html>
