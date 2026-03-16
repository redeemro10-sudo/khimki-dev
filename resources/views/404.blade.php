<?php
$statusCode = (int) http_response_code() === 410 ? 410 : 404;

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
    <title><?php echo $statusCode === 410 ? 'Ошибка 410' : 'Ошибка 404'; ?></title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }
        .wrap {
            max-width: 720px;
            margin: 40px auto;
            padding: 32px 20px;
            text-align: center;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
        }
        .code {
            font-size: 72px;
            font-weight: 800;
            color: #cbd5e1;
            line-height: 1;
        }
        .title {
            margin: 16px 0 12px;
            font-size: 32px;
            font-weight: 800;
        }
        .text {
            margin: 0 0 8px;
            color: #475569;
            font-size: 18px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="code"><?php echo $statusCode; ?></div>
        <h1 class="title"><?php echo $statusCode === 410 ? 'Страница удалена' : 'Страница не найдена'; ?></h1>
        <p class="text">
            <?php echo $statusCode === 410
                ? 'Эта страница была удалена и больше недоступна.'
                : 'Похоже, вы перешли по устаревшей или неверной ссылке.'; ?>
        </p>
        <p class="text"><a href="<?php echo esc_url(home_url('/')); ?>">На главную</a></p>
    </div>
</body>
</html>
