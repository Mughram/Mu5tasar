<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../config/uploads.php';
require_login();

if ($method === 'POST') {
    $values = [post_text('catagorie'), post_text('description')];
    $answers = [];
    for ($i = 1; $i <= 5; $i++) {
        $correctIndex = post_text("correct$i");
        if (!in_array($correctIndex, ['1', '2', '3', '4'], true)) {
            abort_request(400, 'Choose a valid correct answer for every question.');
        }
        $answers[$i] = [post_text("q$i")];
        for ($j = 1; $j <= 4; $j++) {
            $answers[$i][] = post_text("option{$j}_{$i}");
        }
        $answers[$i][] = post_text("option{$correctIndex}_{$i}");
    }
    $uploadedFiles = [];
    try {
        for ($i = 1; $i <= 5; $i++) {
            $image = save_quiz_image($_FILES["img$i"] ?? null);
            if ($image !== '') {
                $uploadedFiles[] = $image;
            }
            array_push($values, ...$answers[$i]);
            $values[] = $image;
        }
        $sql = 'INSERT INTO public.quiz (
            catagorie, description,
            q1, option1_1, option2_1, option3_1, option4_1, correct1, img1,
            q2, option1_2, option2_2, option3_2, option4_2, correct2, img2,
            q3, option1_3, option2_3, option3_3, option4_3, correct3, img3,
            q4, option1_4, option2_4, option3_4, option4_4, correct4, img4,
            q5, option1_5, option2_5, option3_5, option4_5, correct5, img5
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        ) RETURNING id';
        $statement = db()->prepare($sql);
        $statement->execute($values);
        $quizId = (int) $statement->fetchColumn();
        $success = 'The quiz was successfully saved.';
    } catch (Throwable $exception) {
        foreach ($uploadedFiles as $filename) {
            unlink(__DIR__ . '/uploads/' . $filename);
        }
        if (!$exception instanceof InvalidArgumentException) {
            throw $exception;
        }
        http_response_code(400);
        $error = $exception->getMessage();
    }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Create Quiz</title>
</head>

<body class="bg-primary">

<?php include('includes/navbar.php'); ?>

<main class="container py-5" style="margin-top:100px;">
<h2 class="text-light">Create Quiz</h2>

<?php
if(isset($success)) echo "<div class='alert alert-success'>" . e($success) . "</div>";
if(isset($error)) echo "<div class='alert alert-danger'>" . e($error) . "</div>";
?>

<form method="POST" enctype="multipart/form-data">
<?php echo csrf_field(); ?>

<input type='text' class='form-control mb-2' name='catagorie' placeholder='catagorie' required>
<input type='text' class='form-control mb-2' name='description' placeholder='description' required>

<?php
for($i=1; $i<=5; $i++){
    echo "<h4 class='text-light mt-4'>Question $i</h4>";
    echo "<input type='text' class='form-control mb-2' name='q$i' placeholder='Question $i' required>";
    
    // Added File Input for Image
    echo "<input type='file' class='form-control mb-2' name='img$i' accept='image/*'>";

    for($j=1; $j<=4; $j++){
        echo "<input type='text' class='form-control mb-2' name='option{$j}_{$i}' placeholder='Option $j' required>";
    }

    echo "<select class='form-select mb-3' name='correct$i' required>
            <option value='' disabled selected>Choose correct answer</option>
            <option value='1'>Option 1</option>
            <option value='2'>Option 2</option>
            <option value='3'>Option 3</option>
            <option value='4'>Option 4</option>
          </select>";

    echo "<hr class='text-light'>";
}
?>

<button type="submit" name="save" class="btn btn-light">Create Quiz</button>

</form>
</main>

</body>
</html>
