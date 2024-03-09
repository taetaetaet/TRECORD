<?php
$dbFile = "..\DCS_DB\DCS_database.db";
$conn = new SQLite3($dbFile);

if (!$conn) {
    die("Connection failed: " . $conn->lastErrorMsg());
}

// 로그인 처리
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $id = $_POST["idInput"];
    $pw = $_POST["pwInput"];

    // 사용자 조회를 위한 쿼리
    $query = "SELECT * FROM Members1 WHERE user_id=:user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':user_id', $id);
    $result = $stmt->execute();
    $user = $result->fetchArray();

    $id_active = $user['isActive'];
    if ($id_active == 0) {
        echo "해당 계정은 비활성화되어 있습니다.";
    } else if ($user && $pw === $user['user_pw']) {
        echo "로그인 성공";
    } else {
        echo "로그인 실패";
    }
}

// 연결 종료
$conn->close();
?>
