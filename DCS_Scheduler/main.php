<?php
// DCS 테이블 생성
include 'create_table.php';
// 파일 파싱 작업 후 테이블 INSERT 작업
include 'insert_data.php';

$DB = new SQLite3('../DCS_DB/DCS_database.db');

date_default_timezone_set('Asia/Seoul');
$savetime = date('Ymdhis', time());

// 녹취 저장폴더 경로 지정 
$baseDir = "/project/Record/";
// 데이터 삽입
INSERT_TABLE_DCS($DB, $baseDir, $savetime);

?>
