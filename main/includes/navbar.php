<?php require_once __DIR__ . '/../../config/security.php'; ?>
<nav class="navbar navbar-expand-lg bg-body fixed-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <strong class="text-primary">Mu5tasar</strong>
        </a>
        
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
