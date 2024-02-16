<?php
if (isset($_GET['file'])) {
    $file = urldecode($_GET['file']);

    if (file_exists($file)) {
        $filename = basename($file);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($file);
        exit;
    } else {
        $errorMessage = '파일을 찾을 수 없습니다.';
    }
} else {
    $errorMessage = '파일을 찾을 수 없습니다2.';
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>다운로드 오류</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            text-align: center;
            padding: 50px;
        }

        .error-message {
            color: #d9534f;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="error-message"><?php echo $errorMessage; ?></div>
</body>
</html>
