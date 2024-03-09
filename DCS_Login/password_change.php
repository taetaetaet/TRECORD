<?php
// 기존 비밀번호와 새 비밀번호를 받아옴
$currentPassword = $_POST['currentPassword'];
$newPassword = $_POST['newPassword'];
$confirmPassword = $_POST['confirmPassword'];
$userID = $_POST['userID']; // password.php에서 전달된 사용자 아이디

// 새 비밀번호와 새 비밀번호 확인이 일치하는지 확인
if ($newPassword !== $confirmPassword) {
    echo "<script>alert('새 비밀번호와 비밀번호 확인이 일치하지 않습니다.');</script>";
    echo "<script>window.history.back();</script>";
    exit(); // 종료
}

// SQLite3 DB 파일 경로
$dbFile = "..\DCS_DB\DCS_database.db";

// SQLite3 연결
$db = new SQLite3($dbFile);

// 사용자 아이디를 이용하여 DB에서 기존 비밀번호 가져오기
$query = $db->prepare("SELECT user_pw FROM Members1 WHERE user_id = :user_id");
$query->bindValue(':user_id', $userID, SQLITE3_TEXT);
$result = $query->execute();

// 결과 확인
if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    // 사용자가 입력한 기존 비밀번호와 DB에서 가져온 비밀번호를 비교
    $plainPasswordFromDB = $row['user_pw'];
    
    // 비밀번호 일치 여부 확인
    if ($currentPassword === $plainPasswordFromDB) {
        // 비밀번호가 일치하는 경우, 새 비밀번호를 데이터베이스에 업데이트
        $updateQuery = $db->prepare("UPDATE Members1 SET user_pw = :password WHERE user_id = :user_id");
        $updateQuery->bindValue(':password', $newPassword, SQLITE3_TEXT);
        $updateQuery->bindValue(':user_id', $userID, SQLITE3_TEXT);
        
        if ($updateQuery->execute()) {
            // 비밀번호 변경 성공 시 알림창 표시
            echo "<script>alert('비밀번호가 성공적으로 변경되었습니다.');</script>";
            // register.php로 리다이렉션
            header("refresh:0.1; url=/dcs/index.php");
        } else {
            echo "비밀번호 변경 실패: " . $db->lastErrorMsg();
        }
    } else {
        echo "<script>alert('기존 비밀번호가 일치하지 않습니다.');</script>";
        echo "<script>window.history.back();</script>";
    }
} else {
    echo "<script>alert('사용자를 찾을 수 없습니다.');</script>";
    echo "<script>window.history.back();</script>";
}

// 연결 종료
$db->close();
?>
