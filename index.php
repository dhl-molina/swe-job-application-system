<?php
// index.php
// Main page: the application form (Create) and the table of all applications (Read).

require_once __DIR__ . '/core/dbConfig.php';
require_once __DIR__ . '/core/models.php';

// Messages saved by core/handleForms.php. Each one is shown once.
$flash  = pullFlash();   // success / error message after an action
$errors = pullErrors();  // validation errors from a failed submission
$old    = pullOld();     // what the user typed, so the form can be refilled

$applicants = [];
$loadError  = '';

try {
    $applicants = getAllApplicants($pdo);
} catch (PDOException $e) {
    error_log('Could not load applications: ' . $e->getMessage());
    $loadError = 'The applications could not be loaded. Check that schema.sql was imported.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Software Engineer Job Applications</title>
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

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
        <?php endif; ?>

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

        <!-- CREATE: application form -->
        <section class="card">
            <h2>New Application</h2>
            <form action="core/handleForms.php" method="POST" class="form-grid">

                <div class="field">
                    <label for="first_name">First name</label>
                    <input type="text" id="first_name" name="first_name" maxlength="100" required
                           value="<?= oldValue($old, 'first_name') ?>">
                </div>

                <div class="field">
                    <label for="last_name">Last name</label>
                    <input type="text" id="last_name" name="last_name" maxlength="100" required
                           value="<?= oldValue($old, 'last_name') ?>">
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" maxlength="150" required
                           value="<?= oldValue($old, 'email') ?>">
                </div>

                <div class="field">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" maxlength="30" required
                           placeholder="+63 917 123 4567"
                           value="<?= oldValue($old, 'phone') ?>">
                </div>

                <div class="field">
                    <label for="education">Education</label>
                    <input type="text" id="education" name="education" maxlength="150" required
                           placeholder="BS Computer Science, 4th Year"
                           value="<?= oldValue($old, 'education') ?>">
                </div>

                <div class="field">
                    <label for="experience_level">Experience level</label>
                    <select id="experience_level" name="experience_level" required>
                        <option value="">Select a level</option>
                        <?php foreach (getExperienceLevels() as $level): ?>
                            <option value="<?= e($level) ?>"<?= (($old['experience_level'] ?? '') === $level) ? ' selected' : '' ?>><?= e($level) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field field-full">
                    <label for="technical_skills">Technical skills</label>
                    <textarea id="technical_skills" name="technical_skills" rows="4" maxlength="1000" required
                              placeholder="PHP, MySQL, JavaScript, Git"><?= oldValue($old, 'technical_skills') ?></textarea>
                </div>

                <div class="field">
                    <label for="portfolio_url">Portfolio URL <span class="optional">(optional)</span></label>
                    <input type="url" id="portfolio_url" name="portfolio_url" maxlength="255"
                           placeholder="https://example.com"
                           value="<?= oldValue($old, 'portfolio_url') ?>">
                </div>

                <div class="field">
                    <label for="github_url">GitHub URL</label>
                    <input type="url" id="github_url" name="github_url" maxlength="255" required
                           placeholder="https://github.com/your-username"
                           value="<?= oldValue($old, 'github_url') ?>">
                </div>

                <div class="form-actions field-full">
                    <button type="submit" name="insertApplicantBtn" class="btn btn-primary">Submit Application</button>
                </div>
            </form>
        </section>

        <!-- READ: table of all applications -->
        <section class="card">
            <h2>Submitted Applications <span class="count"><?= count($applicants) ?></span></h2>

            <?php if ($loadError !== ''): ?>
                <div class="alert alert-error" role="alert"><?= e($loadError) ?></div>
            <?php elseif (empty($applicants)): ?>
                <p class="empty">No applications yet. Submit the form above to add the first one.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="applications-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Applicant</th>
                                <th>Contact</th>
                                <th>Education</th>
                                <th>Technical Skills</th>
                                <th>Level</th>
                                <th>Links</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applicants as $row): ?>
                                <tr>
                                    <td><?= (int) $row['id'] ?></td>
                                    <td class="applicant-name"><?= e($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                    <td>
                                        <?= e($row['email']) ?><br>
                                        <span class="muted"><?= e($row['phone']) ?></span>
                                    </td>
                                    <td><?= e($row['education']) ?></td>
                                    <td class="skills"><?= e($row['technical_skills']) ?></td>
                                    <td><span class="badge"><?= e($row['experience_level']) ?></span></td>
                                    <td class="links">
                                        <?php if (!empty($row['portfolio_url'])): ?>
                                            <a href="<?= e($row['portfolio_url']) ?>" target="_blank" rel="noopener noreferrer">Portfolio</a><br>
                                        <?php endif; ?>
                                        <a href="<?= e($row['github_url']) ?>" target="_blank" rel="noopener noreferrer">GitHub</a>
                                    </td>
                                    <td class="nowrap"><?= e(date('M j, Y g:i A', strtotime($row['date_added']))) ?></td>
                                    <td class="actions nowrap">
                                        <a class="btn btn-small btn-edit" href="editapplicant.php?id=<?= (int) $row['id'] ?>">Edit</a>
                                        <a class="btn btn-small btn-delete" href="deleteapplicant.php?id=<?= (int) $row['id'] ?>">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

    </main>

    <footer class="site-footer">
        <div class="container">Software Engineer Job Application Management System</div>
    </footer>
</body>
</html>
