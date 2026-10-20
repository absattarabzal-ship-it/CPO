<?php
session_start();

define('RESUMES_DIR', __DIR__ . '/resumes/');
if (!is_dir(RESUMES_DIR)) {
    mkdir(RESUMES_DIR, 0777, true);
}

// Конфигурация и дефолтные расширенные данные (Вариант 13)
$default_data = [
    'full_name'       => 'Абдусаттар Абдулхамид',
    'city'            => 'Алматы, Казахстан',
    'email'           => 'abzal.abdusattar@student.atu.kz',
    'phone'           => '+7 (777) 123-45-67',
    'university'      => 'Алматинский технологический университет (АТУ)',
    'specialty'       => 'Информационные системы (Группа ИС-24-22)',
    'graduation_year' => '2028',
    'experience'      => 'Младший разработчик / Стажер (учебные проекты на PHP, Python, MongoDB, разработка скриптов автоматизации и университетских модулей).',
    'skills'          => "PHP, Python, Java, JavaScript\nPostgreSQL, MongoDB, Redis\nGit, Docker, VS Code, PhpStorm"
];

if (!isset($_SESSION['resume'])) {
    $_SESSION['resume'] = [
        'step' => 1,
        'personal' => [],
        'education' => [],
        'experience' => [],
        'skills' => []
    ];
}

$error = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'step1') {
        // Шаг 1: Личные данные и место проживания
        $name = trim($_POST['full_name'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name) || empty($city) || empty($email) || empty($phone)) {
            $error = 'Пожалуйста, заполните все личные данные и место проживания!';
        } else {
            $_SESSION['resume']['personal'] = [
                'full_name' => htmlspecialchars($name),
                'city' => htmlspecialchars($city),
                'email' => htmlspecialchars($email),
                'phone' => htmlspecialchars($phone)
            ];
            $_SESSION['resume']['step'] = 2;
        }
    } elseif ($action === 'step2') {
        // Шаг 2: Образование
        $university = trim($_POST['university'] ?? '');
        $specialty = trim($_POST['specialty'] ?? '');
        $year = trim($_POST['graduation_year'] ?? '');

        if (empty($university) || empty($specialty) || empty($year)) {
            $error = 'Пожалуйста, заполните информацию об образовании!';
        } else {
            $_SESSION['resume']['education'] = [
                'university' => htmlspecialchars($university),
                'specialty' => htmlspecialchars($specialty),
                'graduation_year' => htmlspecialchars($year)
            ];
            $_SESSION['resume']['step'] = 3;
        }
    } elseif ($action === 'step3') {
        // Шаг 3: Опыт работы
        $experience = trim($_POST['experience'] ?? '');

        if (empty($experience)) {
            $error = 'Пожалуйста, укажите опыт работы или учебные проекты!';
        } else {
            $_SESSION['resume']['experience'] = [
                'description' => htmlspecialchars($experience)
            ];
            $_SESSION['resume']['step'] = 4;
        }
    } elseif ($action === 'step4') {
        // Шаг 4: Навыки и сохранение в JSON
        $skills = trim($_POST['skills'] ?? '');

        if (empty($skills)) {
            $error = 'Укажите хотя бы один навык!';
        } else {
            $_SESSION['resume']['skills'] = htmlspecialchars($skills);

            $resume_data = $_SESSION['resume'];
            unset($resume_data['step']);
            $resume_data['created_at'] = date('Y-m-d H:i:s');

            $filename = 'resume_' . uniqid() . '.json';
            file_put_contents(RESUMES_DIR . $filename, json_encode($resume_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $success_message = "Резюме с опытом работы и контактами успешно сохранено в JSON! (Файл: {$filename})";
            $_SESSION['resume']['step'] = 5; // Шаг готового резюме
        }
    } elseif ($action === 'reset') {
        unset($_SESSION['resume']);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

$current_step = $_SESSION['resume']['step'] ?? 1;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Расширенный генератор резюме (СРО PHP - Вариант 13)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f4f6f9; color: #333; }
        .container { max-width: 650px; margin: auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1, h2, h3 { color: #2c3e50; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #219653; }
        .btn-secondary { background: #7f8c8d; margin-right: 10px; }
        .btn-secondary:hover { background: #6c7a89; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .step-indicator { font-size: 14px; color: #7f8c8d; margin-bottom: 20px; text-transform: uppercase; font-weight: bold; }
        .resume-preview { background: #fafafa; border: 1px solid #e2e8f0; padding: 20px; border-radius: 6px; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Генератор резюме студента</h1>
    <p>Дисциплина: Программирование на PHP | Вариант №13 (АТУ, группа ИС-24-22)</p>
    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

    <?php if (!empty($error)): ?>
        <div class="alert-error"><?= $error ?></div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert-success"><?= $success_message ?></div>
    <?php endif; ?>

    <?php if ($current_step === 1): ?>
        <div class="step-indicator">Шаг 1 из 4: Личные данные и место проживания</div>
        <form method="POST">
            <input type="hidden" name="action" value="step1">
            <div class="form-group">
                <label>ФИО студента:</label>
                <input type="text" name="full_name" value="<?= $_SESSION['resume']['personal']['full_name'] ?? $default_data['full_name'] ?>" required>
            </div>
            <div class="form-group">
                <label>Место проживания (Город, Страна):</label>
                <input type="text" name="city" value="<?= $_SESSION['resume']['personal']['city'] ?? $default_data['city'] ?>" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="<?= $_SESSION['resume']['personal']['email'] ?? $default_data['email'] ?>" required>
            </div>
            <div class="form-group">
                <label>Телефон:</label>
                <input type="text" name="phone" value="<?= $_SESSION['resume']['personal']['phone'] ?? $default_data['phone'] ?>" required>
            </div>
            <button type="submit">Далее &rarr;</button>
        </form>

    <?php elseif ($current_step === 2): ?>
        <div class="step-indicator">Шаг 2 из 4: Образование</div>
        <form method="POST">
            <input type="hidden" name="action" value="step2">
            <div class="form-group">
                <label>Учебное заведение (ВУЗ):</label>
                <input type="text" name="university" value="<?= $_SESSION['resume']['education']['university'] ?? $default_data['university'] ?>" required>
            </div>
            <div class="form-group">
                <label>Специальность / Образовательная программа:</label>
                <input type="text" name="specialty" value="<?= $_SESSION['resume']['education']['specialty'] ?? $default_data['specialty'] ?>" required>
            </div>
            <div class="form-group">
                <label>Год окончания (ожидаемый):</label>
                <input type="text" name="graduation_year" value="<?= $_SESSION['resume']['education']['graduation_year'] ?? $default_data['graduation_year'] ?>" required>
            </div>
            <button type="submit" name="action" value="step1" class="btn-secondary" formnovalidate>&larr; Назад</button>
            <button type="submit">Далее &rarr;</button>
        </form>

    <?php elseif ($current_step === 3): ?>
        <div class="step-indicator">Шаг 3 из 4: Опыт работы / Проекты</div>
        <form method="POST">
            <input type="hidden" name="action" value="step3">
            <div class="form-group">
                <label>Опыт работы (или учебные проекты и достижения):</label>
                <textarea name="experience" rows="5" required><?= $_SESSION['resume']['experience']['description'] ?? $default_data['experience'] ?></textarea>
            </div>
            <button type="submit" onclick="this.form.action.value='step2'" class="btn-secondary" formnovalidate>&larr; Назад</button>
            <button type="submit">Далее &rarr;</button>
        </form>

    <?php elseif ($current_step === 4): ?>
        <div class="step-indicator">Шаг 4 из 4: Навыки и стек технологий</div>
        <form method="POST">
            <input type="hidden" name="action" value="step4">
            <div class="form-group">
                <label>Ключевые навыки:</label>
                <textarea name="skills" rows="5" required><?= $_SESSION['resume']['skills'] ?? $default_data['skills'] ?></textarea>
            </div>
            <button type="submit" onclick="this.form.action.value='step3'" class="btn-secondary" formnovalidate>&larr; Назад</button>
            <button type="submit">Сгенерировать и сохранить резюме</button>
        </form>

    <?php elseif ($current_step === 5): ?>
        <div class="step-indicator">Готовое резюме студента</div>
        <div class="resume-preview">
            <h2><?= $_SESSION['resume']['personal']['full_name'] ?></h2>
            <p><strong>Город:</strong> <?= $_SESSION['resume']['personal']['city'] ?> | <strong>Email:</strong> <?= $_SESSION['resume']['personal']['email'] ?> | <strong>Телефон:</strong> <?= $_SESSION['resume']['personal']['phone'] ?></p>
            <hr>
            <h3>Образование</h3>
            <p><strong>ВУЗ:</strong> <?= $_SESSION['resume']['education']['university'] ?></p>
            <p><strong>Специальность:</strong> <?= $_SESSION['resume']['education']['specialty'] ?> (Окончание: <?= $_SESSION['resume']['education']['graduation_year'] ?>)</p>
            <hr>
            <h3>Опыт работы и проекты</h3>
            <p><?= nl2br($_SESSION['resume']['experience']['description']) ?></p>
            <hr>
            <h3>Навыки</h3>
            <p><?= nl2br($_SESSION['resume']['skills']) ?></p>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="reset">
            <button type="submit" class="btn-secondary">Создать новое резюме</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>