<?php
// SQLite3 데이터베이스 연결
$DB = new SQLite3('..\DCS_DB\DCS_database.db');

// Login 테이블 생성 쿼리
$query = "CREATE TABLE IF NOT EXISTS Members (
    seq INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id TEXT NOT NULL,
    user_pw TEXT NOT NULL,
    ex_number TEXT NOT NULL,
    isAdmin INTEGER NOT NULL DEFAULT 0,
    accessType TEXT NOT NULL,
    canListen INTEGER NOT NULL DEFAULT 0,
    canDownload INTEGER NOT NULL DEFAULT 0,
    isActive INTEGER NOT NULL DEFAULT 1,
    create_day DATE NOT NULL,
    crtime TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// 쿼리 실행
if ($DB->exec($query)) {
} else {
    echo "테이블 생성 실패";
}

// 관리자 계정이 없는 경우에만 추가
$result = $DB->querySingle("SELECT COUNT(*) FROM Members WHERE user_id = 'admin'");
if ($result == 0) {
    $insertQuery = "INSERT INTO Members (user_id, user_pw, ex_number, isAdmin, accessType, canListen, canDownload, isActive, create_day)
                     VALUES ('admin', '1234', '0000', '1', '1', '1', '1', '1', date('now'))";
    if ($DB->exec($insertQuery)) {
       
    }
}

// 데이터베이스 연결 종료
$DB->close();
?>
