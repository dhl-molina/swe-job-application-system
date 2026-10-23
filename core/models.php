<?php
// core/models.php
// All database (SQL) work lives in this file. Every CRUD function receives the $pdo
// connection created in dbConfig.php and uses PDO prepared statements.
// The small helpers that more than one page needs are here too.

// The nine fields the user fills in. (id and date_added are set by the database.)
const APPLICANT_FIELDS = [
    'first_name',
    'last_name',
    'email',
    'phone',
    'education',
    'technical_skills',
    'experience_level',
    'portfolio_url',
    'github_url',
];

/* ---------- Small helpers ---------- */

// Allowed values for the "Experience level" dropdown.
// The same list builds the <select> and validates what the server receives.
function getExperienceLevels(): array
{
    return ['Internship', 'Entry-Level', 'Experienced'];
}

// Escapes a value so it is safe to print inside HTML. Use it on EVERY value
// that came from a user or from the database.
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Escaped value of $values[$key] for an input's value="" attribute ('' if missing/NULL).
function oldValue(array $values, string $key): string
{
    return e($values[$key] ?? '');
}

// Returns the ID as an int if it is a whole number >= 1; otherwise returns 0.
function parseId($value): int
{
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? 0 : $id;
}

// True when a PDOException was caused by a duplicate value in a UNIQUE column
// (MariaDB/MySQL error code 1062). In this table that can only be the email column.
function isDuplicateEmailError(PDOException $e): bool
{
    return isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062;
}

// Turns the form data into the named values the SQL statements expect.
// An empty optional portfolio URL is saved as NULL instead of ''.
function applicantParams(array $data): array
{
    $portfolio = trim((string) ($data['portfolio_url'] ?? ''));

    return [
        ':first_name'       => $data['first_name'],
        ':last_name'        => $data['last_name'],
        ':email'            => $data['email'],
        ':phone'            => $data['phone'],
        ':education'        => $data['education'],
        ':technical_skills' => $data['technical_skills'],
        ':experience_level' => $data['experience_level'],
        ':portfolio_url'    => $portfolio === '' ? null : $portfolio,
        ':github_url'       => $data['github_url'],
    ];
}

/* ---------- One-time session messages ---------- */
// core/handleForms.php saves a message, then redirects. The next page "pulls" it:
// it is read once and removed, so it never appears twice.

function pullFlash()
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;   // ['type' => 'success'|'error', 'message' => '...'] or null
}

function pullErrors(): array
{
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['errors']);
    return $errors;  // list of validation messages
}

function pullOld(): array
{
    $old = $_SESSION['old'] ?? [];
    unset($_SESSION['old']);
    return $old;     // what the user typed, to refill the form
}

/* ---------- CRUD functions ---------- */

// READ (all): newest applications first.
function getAllApplicants(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM swe_applicants ORDER BY date_added DESC, id DESC');
    return $stmt->fetchAll();
}

// READ (one): returns the row as an array, or false if the id does not exist.
function getApplicantById(PDO $pdo, int $id)
{
    $stmt = $pdo->prepare('SELECT * FROM swe_applicants WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

// CREATE: inserts one applicant and returns the new id.
function insertApplicant(PDO $pdo, array $data): int
{
    $sql = 'INSERT INTO swe_applicants
                (first_name, last_name, email, phone, education,
                 technical_skills, experience_level, portfolio_url, github_url)
            VALUES
                (:first_name, :last_name, :email, :phone, :education,
                 :technical_skills, :experience_level, :portfolio_url, :github_url)';

    $stmt = $pdo->prepare($sql);
    $stmt->execute(applicantParams($data));

    return (int) $pdo->lastInsertId();
}

// UPDATE: changes ONE row. The WHERE id = :id part is what limits it to that row.
function updateApplicant(PDO $pdo, int $id, array $data): void
{
    $sql = 'UPDATE swe_applicants
            SET first_name       = :first_name,
                last_name        = :last_name,
                email            = :email,
                phone            = :phone,
                education        = :education,
                technical_skills = :technical_skills,
                experience_level = :experience_level,
                portfolio_url    = :portfolio_url,
                github_url       = :github_url
            WHERE id = :id';

    $params = applicantParams($data);
    $params[':id'] = $id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

// DELETE: removes ONE row and returns how many rows were deleted (1 or 0).
function deleteApplicant(PDO $pdo, int $id): int
{
    $stmt = $pdo->prepare('DELETE FROM swe_applicants WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->rowCount();
}
