<?php
// core/handleForms.php
// Receives every form submission (create, update, delete), validates it, calls the
// PDO functions in models.php, saves a message in the session, and redirects back
// to a page. This file never prints HTML.

require_once __DIR__ . '/dbConfig.php';
require_once __DIR__ . '/models.php';

/* ---------- Small helpers ---------- */

function redirect(string $path)
{
    header('Location: ' . $path);
    exit;
}

// A one-time message ($type is 'success' or 'error') shown on the next page.
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Remembers the validation errors and what the user typed, so the form can be refilled.
function setFormState(array $errors, array $values): void
{
    $_SESSION['errors'] = array_values($errors);
    $_SESSION['old']    = $values;
}

// Sanitizing step: trim() removes spaces at both ends. Anything that is not a
// plain string (for example someone sending first_name[]=x) becomes ''.
function cleanInput($value): string
{
    return is_string($value) ? trim($value) : '';
}

// True for a well-formed http:// or https:// URL.
// (FILTER_VALIDATE_URL alone also accepts schemes such as javascript:)
function isHttpUrl(string $url): bool
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true);
}

// True for https://github.com/<something> (or www.github.com).
function isGithubProfileUrl(string $url): bool
{
    if (!isHttpUrl($url)) {
        return false;
    }
    $parts  = parse_url($url);
    $scheme = strtolower($parts['scheme'] ?? '');
    $host   = strtolower($parts['host'] ?? '');
    $path   = trim($parts['path'] ?? '', '/');

    return $scheme === 'https'
        && in_array($host, ['github.com', 'www.github.com'], true)
        && $path !== '';
}

/* ---------- Validation ---------- */

// Cleans the submitted values and checks every rule.
// Returns [$data, $errors]: $data holds the trimmed values (even if invalid, so the
// form can be refilled) and $errors maps a field name to its message.
function validateApplicant(array $input): array
{
    $data = [];
    foreach (APPLICANT_FIELDS as $field) {
        $data[$field] = cleanInput($input[$field] ?? '');
    }

    $errors = [];

    // First name and last name
    foreach (['first_name' => 'First name', 'last_name' => 'Last name'] as $field => $label) {
        if ($data[$field] === '') {
            $errors[$field] = $label . ' is required.';
        } elseif (mb_strlen($data[$field]) > 100) {
            $errors[$field] = $label . ' must be 100 characters or fewer.';
        } elseif (!preg_match('/^[\p{L}\p{M}][\p{L}\p{M} .\'’-]*$/u', $data[$field])) {
            $errors[$field] = $label . ' may contain only letters, spaces, periods, apostrophes, and hyphens.';
        }
    }

    // Email
    if ($data['email'] === '') {
        $errors['email'] = 'Email is required.';
    } elseif (mb_strlen($data['email']) > 150) {
        $errors['email'] = 'Email must be 150 characters or fewer.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }

    // Phone: digits, spaces, parentheses, hyphens, optional leading +; 7 to 15 digits
    $digits = preg_replace('/\D/', '', $data['phone']);
    if ($data['phone'] === '') {
        $errors['phone'] = 'Phone number is required.';
    } elseif (mb_strlen($data['phone']) > 30 || !preg_match('/^\+?[0-9 ()\-]+$/', $data['phone'])) {
        $errors['phone'] = 'Phone may contain only digits, spaces, parentheses, hyphens, and a leading +.';
    } elseif (strlen($digits) < 7 || strlen($digits) > 15) {
        $errors['phone'] = 'Phone number must contain 7 to 15 digits.';
    }

    // Education
    if ($data['education'] === '') {
        $errors['education'] = 'Education is required.';
    } elseif (mb_strlen($data['education']) > 150) {
        $errors['education'] = 'Education must be 150 characters or fewer.';
    }

    // Technical skills
    if ($data['technical_skills'] === '') {
        $errors['technical_skills'] = 'Technical skills are required.';
    } elseif (mb_strlen($data['technical_skills']) > 1000) {
        $errors['technical_skills'] = 'Technical skills must be 1000 characters or fewer.';
    }

    // Experience level must be one of the dropdown values
    if (!in_array($data['experience_level'], getExperienceLevels(), true)) {
        $errors['experience_level'] = 'Choose an experience level from the list.';
    }

    // Portfolio URL (optional)
    if ($data['portfolio_url'] !== '') {
        if (mb_strlen($data['portfolio_url']) > 255) {
            $errors['portfolio_url'] = 'Portfolio URL must be 255 characters or fewer.';
        } elseif (!isHttpUrl($data['portfolio_url'])) {
            $errors['portfolio_url'] = 'Portfolio URL must be a valid http:// or https:// address.';
        }
    }

    // GitHub URL (required)
    if ($data['github_url'] === '') {
        $errors['github_url'] = 'GitHub URL is required.';
    } elseif (mb_strlen($data['github_url']) > 255) {
        $errors['github_url'] = 'GitHub URL must be 255 characters or fewer.';
    } elseif (!isGithubProfileUrl($data['github_url'])) {
        $errors['github_url'] = 'GitHub URL must look like https://github.com/your-username.';
    }

    return [$data, $errors];
}

/* ---------- Controller ---------- */

// Opening this file in the browser (a GET request) does nothing but go back home.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../index.php');
}

// ----- CREATE -----
if (isset($_POST['insertApplicantBtn'])) {
    [$data, $errors] = validateApplicant($_POST);

    if (!empty($errors)) {
        setFormState($errors, $data);
        redirect('../index.php');
    }

    try {
        insertApplicant($pdo, $data);
        setFlash('success', 'Application from ' . $data['first_name'] . ' ' . $data['last_name'] . ' was submitted.');
    } catch (PDOException $ex) {
        if (isDuplicateEmailError($ex)) {
            setFormState(['An application with this email address already exists.'], $data);
        } else {
            error_log('Insert failed: ' . $ex->getMessage());
            setFlash('error', 'The application could not be saved. Please try again.');
            setFormState([], $data);
        }
    }
    redirect('../index.php');
}

// ----- UPDATE -----
if (isset($_POST['updateApplicantBtn'])) {
    $id = parseId($_POST['id'] ?? null);
    if ($id === 0) {
        setFlash('error', 'Invalid application ID.');
        redirect('../index.php');
    }

    [$data, $errors] = validateApplicant($_POST);

    if (!empty($errors)) {
        setFormState($errors, $data);
        redirect('../editapplicant.php?id=' . $id);
    }

    try {
        if (!getApplicantById($pdo, $id)) {
            setFlash('error', 'Application not found. It may already have been deleted.');
        } else {
            updateApplicant($pdo, $id, $data);
            setFlash('success', 'Application from ' . $data['first_name'] . ' ' . $data['last_name'] . ' was updated.');
        }
    } catch (PDOException $ex) {
        if (isDuplicateEmailError($ex)) {
            setFormState(['Another application already uses this email address.'], $data);
            redirect('../editapplicant.php?id=' . $id);
        }
        error_log('Update failed: ' . $ex->getMessage());
        setFlash('error', 'The application could not be updated. Please try again.');
    }
    redirect('../index.php');
}

// ----- DELETE -----
if (isset($_POST['deleteApplicantBtn'])) {
    $id = parseId($_POST['id'] ?? null);
    if ($id === 0) {
        setFlash('error', 'Invalid application ID.');
        redirect('../index.php');
    }

    try {
        $applicant = getApplicantById($pdo, $id);

        if (!$applicant || deleteApplicant($pdo, $id) !== 1) {
            setFlash('error', 'Application not found. It may already have been deleted.');
        } else {
            setFlash('success', 'Application from ' . $applicant['first_name'] . ' ' . $applicant['last_name'] . ' was deleted.');
        }
    } catch (PDOException $ex) {
        error_log('Delete failed: ' . $ex->getMessage());
        setFlash('error', 'The application could not be deleted. Please try again.');
    }
    redirect('../index.php');
}

// A POST that matched none of the buttons above: go back home.
redirect('../index.php');
