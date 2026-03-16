<?php
status_header(410);
$statusCode = 410;

if (function_exists('view')) {
    echo view('error-layout', ['statusCode' => $statusCode])->render();
    return;
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,follow">
    <title>Ошибка 410</title>
</head>
<body>
    <p>Страница удалена. <a href="<?php echo esc_url(home_url('/')); ?>">На главную</a></p>
</body>
</html>
