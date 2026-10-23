<?php
// editapplicant.php
// Update page: loads one application into a prefilled form. Saving is done by core/handleForms.php.

require_once __DIR__ . '/core/dbConfig.php';
require_once __DIR__ . '/core/models.php';

// Validation errors and typed values from a failed save (shown once).
$errors = pullErrors();
$old    = pullOld();

$id        = parseId($_GET['id'] ?? '');
$applicant = false;
$problem   = '';

if ($id === 0) {
    $problem = 'Invalid application ID.';
} else {
    try {
        $applicant = getApplicantById($pdo, $id);
        if (!$applicant) {
            $problem = 'Application #' . $id . ' was not found. It may have been deleted.';
        }
    } catch (PDOException $e) {
        error_log('Could not load application: ' . $e->getMessage());
        $problem = 'The application could not be loaded because of a database error.';
    }
}

// Values shown in the form: what the user just typed (after a failed save),
// otherwise the values saved in the database.
$form = $applicant ? (empty($old) ? $applicant : $old) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Application</title>
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

        <?php if ($problem !== ''): ?>
            <section class="card">
                <div class="alert alert-error" role="alert"><?= e($problem) ?></div>
                <a href="index.php" class="btn btn-secondary">Back to all applications</a>
            </section>
        <?php else: ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" role="alert">
                    <strong>Please fix the following:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- UPDATE: prefilled form -->
            <section class="card">
                <h2>Edit Application #<?= (int) $applicant['id'] ?></h2>
                <form action="core/handleForms.php" method="POST" class="form-grid">

                    <!-- Tells handleForms.php which record to update -->
                    <input type="hidden" name="id" value="<?= (int) $applicant['id'] ?>">

                    <div class="field">
                        <label for="first_name">First name</label>
                        <input type="text" id="first_name" name="first_name" maxlength="100" required
                               value="<?= oldValue($form, 'first_name') ?>">
                    </div>

                    <div class="field">
                        <label for="last_name">Last name</label>
                        <input type="text" id="last_name" name="last_name" maxlength="100" required
                               value="<?= oldValue($form, 'last_name') ?>">
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" maxlength="150" required
                               value="<?= oldValue($form, 'email') ?>">
                    </div>

                    <div class="field">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" maxlength="30" required
                               value="<?= oldValue($form, 'phone') ?>">
                    </div>

                    <div class="field">
                        <label for="education">Education</label>
                        <input type="text" id="education" name="education" maxlength="150" required
                               value="<?= oldValue($form, 'education') ?>">
                    </div>

                    <div class="field">
                        <label for="experience_level">Experience level</label>
                        <select id="experience_level" name="experience_level" required>
                            <option value="">Select a level</option>
                            <?php foreach (getExperienceLevels() as $level): ?>
                                <option value="<?= e($level) ?>"<?= (($form['experience_level'] ?? '') === $level) ? ' selected' : '' ?>><?= e($level) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field field-full">
                        <label for="technical_skills">Technical skills</label>
                        <textarea id="technical_skills" name="technical_skills" rows="4" maxlength="1000" required><?= oldValue($form, 'technical_skills') ?></textarea>
                    </div>

                    <div class="field">
                        <label for="portfolio_url">Portfolio URL <span class="optional">(optional)</span></label>
                        <input type="url" id="portfolio_url" name="portfolio_url" maxlength="255"
                               value="<?= oldValue($form, 'portfolio_url') ?>">
                    </div>

                    <div class="field">
                        <label for="github_url">GitHub URL</label>
                        <input type="url" id="github_url" name="github_url" maxlength="255" required
                               value="<?= oldValue($form, 'github_url') ?>">
                    </div>

                    <div class="form-actions field-full">
                        <button type="submit" name="updateApplicantBtn" class="btn btn-primary">Save Changes</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </section>

        <?php endif; ?>

    </main>

    <footer class="site-footer">
        <div class="container">Software Engineer Job Application Management System</div>
    </footer>
</body>
</html>
