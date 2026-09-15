<?php
require_once __DIR__ . '/../config/connection.php';
require_login();

$quiz_id = $_GET['id'] ?? null;
if (!is_string($quiz_id) || !preg_match('/\\A[0-9]+\\z/', $quiz_id) ||
    strlen($quiz_id) > 10 || (float) $quiz_id > 2147483647) {
    abort_request(400, 'Invalid quiz ID.');
}
$statement = db()->prepare('SELECT * FROM public.quiz WHERE id = ?');
$statement->execute([$quiz_id]);
$quiz = $statement->fetch();
if (!$quiz) {
    abort_request(404, 'Quiz not found.');
}

$score = null;
if ($method === 'POST') {
    $score = 0;
    for ($i = 1; $i <= 5; $i++) {
        if (post_text("q$i") === $quiz["correct$i"]) {
            $score++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz: <?php echo e($quiz['catagorie']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">

<div class="container py-5">
    
    <div class="row justify-content-center mb-4">
        <div class="col-md-8 text-center">
            <h1 class="display-5 fw-bold text-primary mb-2"><?php echo e($quiz['catagorie']); ?> Quiz</h1>
            <p class="text-secondary small text-uppercase tracking-widest">5 Questions • 1 Correct Answer Each</p>
        </div>
    </div>

    <?php if($score !== null): ?>
        <div class="row justify-content-center mb-4">
            <div class="col-md-8">
                <div class="alert alert-primary border-0 shadow-lg text-center p-4">
                    <h2 class="alert-heading fw-bold">Result: <?php echo $score; ?> / 5</h2>
                    <p class="mb-3">Great effort! Your score is recorded.</p>
                    <a href="showQuiz.php" class="btn btn-light fw-bold px-4">Back to All Quizzes</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <form method="POST">
                <?php echo csrf_field(); ?>
                <?php for($i=1; $i<=5; $i++): ?>
                    <div class="card bg-secondary bg-opacity-10 border-secondary border-opacity-25 mb-5 shadow-sm">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h3 class="badge rounded-pill bg-primary mb-2">Question <?php echo $i; ?></h3>
                            <h4 class="card-title fw-normal text-light"><?php echo e($quiz["q$i"]); ?></h4>
                        </div>

                        <?php $imageUrl = image_url($quiz["img$i"]); if ($imageUrl !== null): ?>
                            <div class="px-4 text-center">
                                <img src="<?php echo e($imageUrl); ?>" 
                                     class="img-fluid rounded border border-secondary border-opacity-50 mb-3" 
                                     style="max-height: 300px; width: auto;" 
                                     alt="Question <?php echo $i; ?> Image">
                            </div>
                        <?php endif; ?>
                        <div class="card-body px-4 pb-4">
                            <div class="list-group list-group-flush border-0">
                                <?php for($j=1; $j<=4; $j++): ?>
                                    <label class="list-group-item list-group-item-action bg-transparent text-light border-0 py-3 px-0">
                                        <div class="form-check">
                                            <input class="form-check-input border-secondary shadow-none" type="radio" 
                                                   name="q<?php echo $i; ?>" 
                                                   value="<?php echo e($quiz["option{$j}_{$i}"]); ?>" 
                                                   id="q<?php echo $i; ?>o<?php echo $j; ?>" required>
                                            <span class="ms-2">
                                                <?php echo e($quiz["option{$j}_{$i}"]); ?>
                                            </span>
                                        </div>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>

                <div class="d-grid gap-2 mb-5">
                    <button type="submit" name="submit" class="btn btn-primary btn-lg py-3 fw-bold shadow">
                        Submit Quiz
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
