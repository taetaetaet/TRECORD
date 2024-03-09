<?php
 $database_file = "..\DCS_DB\DCS_database.db";
 // 데이터베이스 연결
 $db_connection = new SQLite3($database_file);
 // 연결 확인
 if (!$db_connection) {
     die("데이터베이스 연결 실패: " . $db_connection->lastErrorMsg());
 }
 // edit_member.php
 $userID = $_POST['userID'];


// POST 요청에서 받은 데이터 가져오기
$user_id = $_POST["user_id"];
$user_pw = $_POST["user_pw"];
$ex_number = $_POST["ex_number"];
$teamName = $_POST["team_name"];
$admin = $_POST["admin"];
$query_type = $_POST["query_type"];
$listening = $_POST["listening"];
$downloading = $_POST["downloading"];
$usage = $_POST["usage"];


// 데이터베이스에서 해당 아이디의 회원 정보 업데이트
$updateQuery = "UPDATE Members1 SET user_pw='$user_pw', ex_number='$ex_number', teamName='$teamName', isAdmin='$admin', accessType='$query_type',
 canListen='$listening', canDownload='$downloading', isActive='$usage' WHERE user_id='$user_id'";

// 쿼리 실행
$result = $db_connection->exec($updateQuery);

if ($result !== false) {
    // 수정 성공한 경우
    echo "<script>alert('회원 정보가 성공적으로 수정되었습니다.');</script>"; // 수정 성공을 클라이언트에게 알림
} else {
    // 수정 실패한 경우
    echo "<script>alert('회원 정보 수정을 실패했습니다.');</script>"; // 클라이언트에게 실패 메시지를 알림
}
// 데이터베이스 연결 종료
$db_connection->close();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>회원 수정 결과</title>
</head>
<body>
    <script>
        window.location.href = 'process_member.php?userID=<?php echo $userID; ?>';
    </script>
</body>
</html>
