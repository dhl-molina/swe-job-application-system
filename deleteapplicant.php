<?php
// deleteapplicant.php
// Delete page: shows the record and asks "Are you sure?".
// Opening this page never deletes anything. Only the POST form below does,
// and that POST is handled by core/handleForms.php.

require_once __DIR__ . '/core/dbConfig.php';
require_once __DIR__ . '/core/models.php';

$id        = parseId($_GET['id'] ?? '');
$applicant = false;
$problem   = '';

if ($id === 0) {
    $problem = 'Invalid application ID.';
} else {
    try {
        $applicant = getApplicantById($pdo, $id);
        if (!$applicant) {
            $problem = 'Application #' . $id . ' was not found. It may have been deleted already.';
        }
    } catch (PDOException $e) {
        error_log('Could not load application: ' . $e->getMessage());
        $problem = 'The application could not be loaded because of a database error.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Application</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <h1 class="brand">Software Engineer Job Applications</h1>
            <nav><a href="index.php">All Applications</a></nav>
        </div>
    </header>

    <main class="container">
        <section class="card card-narrow">

            <?php if ($problem !== ''): ?>
                <div class="alert alert-error" role="alert"><?= e($problem) ?></div>
                <a href="index.php" class="btn btn-secondary">Back to all applications</a>

            <?php else: ?>
                <h2>Delete Application #<?= (int) $applicant['id'] ?>?</h2>
                <p class="warning-text">This permanently removes the application below. It cannot be undone.</p>

                <dl class="summary">
                    <dt>Applicant</dt>
                    <dd><?= e($applicant['first_name'] . ' ' . $applicant['last_name']) ?></dd>
                    <dt>Email</dt>
                    <dd><?= e($applicant['email']) ?></dd>
                    <dt>Education</dt>
                    <dd><?= e($applicant['education']) ?></dd>
                    <dt>Experience level</dt>
                    <dd><?= e($applicant['experience_level']) ?></dd>
                    <dt>Date added</dt>
                    <dd><?= e(date('M j, Y g:i A', strtotime($applicant['date_added']))) ?></dd>
                </dl>

                <!-- DELETE: only this POST form removes the record -->
                <form action="core/handleForms.php" method="POST" class="form-actions">
                    <input type="hidden" name="id" value="<?= (int) $applicant['id'] ?>">
                    <button type="submit" name="deleteApplicantBtn" class="btn btn-danger">Yes, delete</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            <?php endif; ?>

        </section>
    </main>

    <footer class="site-footer">
        <div class="container">Software Engineer Job Application Management System</div>
    </footer>
</body>
</html>
