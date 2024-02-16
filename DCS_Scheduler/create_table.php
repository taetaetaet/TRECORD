<?php

// DCS 테이블 생성 함수 유니크 선언으로 중복 방지
function CREATE_TABLE_DCS($DB)
{
    $query  = "CREATE TABLE IF NOT EXISTS DCS (";
    $query .= "    seq INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, ";
    $query .= "    balsin_number VARCHAR(11) NULL DEFAULT '', ";
    $query .= "    susin_number VARCHAR(11) NULL DEFAULT '', ";
    $query .= "    start_time CHAR(14) NULL DEFAULT '', ";
    $query .= "    end_time CHAR(14) NULL DEFAULT '', ";
    $query .= "    call_time CHAR(8) NULL DEFAULT '', "; 
    $query .= "    file_dir VARCHAR(50) NULL DEFAULT '', ";
    $query .= "    file_names VARCHAR(50) NULL DEFAULT '', ";
    $query .= "    ext_type CHAR(3) NULL DEFAULT '', ";
    $query .= "    crtime CHAR(14) NULL DEFAULT '', ";
    $query .= "    UNIQUE(balsin_number, susin_number, start_time, end_time, call_time, file_dir, file_names, ext_type) ON CONFLICT IGNORE";
    $query .= ");";

    $DB->exec($query);
}
include("../DCS_Login/register_table.php");

?>