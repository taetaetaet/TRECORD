<?php
function INSERT_TABLE_DCS($DB, $baseDir, $savetime)
{
    // 디렉토리 열기
    if (!($handle = opendir($baseDir))) {
        echo "디렉토리를 열 수 없습니다.";
        echo $baseDir;
        return;
    }

    // DCS 테이블 생성 함수
    CREATE_TABLE_DCS($DB, "DCS");

    // 디렉토리에서 각 파일을 가져와 처리
    while (false !== ($folderName = readdir($handle))) {
        if ($folderName == "." || $folderName == "..") {
            continue;
        }

        // 폴더 경로 설정
        $dir = $baseDir . $folderName;

        $files = array();

        // 디렉토리 열기
        if (!($handleDir = opendir($dir))) {
            echo "디렉토리를 열 수 없습니다.";
            return;
        }

        // 파일 목록 가져오기
        while (false !== ($filename = readdir($handleDir))) {
            if ($filename == "." || $filename == "..") {
                continue;
            }

            if (is_file($dir . "/" . $filename)) {
                $files[] = $filename;
            }
        }

        // 파일 목록을 이용하여 작업 수행
        $arr_arr = custom($files);

  // 일괄 삽입을 위한 값을 담을 배열 초기화
$values = array();

// 파일 이름을 기반으로 중복 확인 및 삽입
foreach ($arr_arr as $i => $arr) {
    $file_names = $files[$i];

    // DB에 이미 같은 파일 이름이 있는지 확인
    $queryCheckFileName = $DB->query("SELECT file_names FROM DCS WHERE file_names = '$file_names'");
    $existingFileName = $queryCheckFileName->fetchArray();

    if ($existingFileName) {
        // 중복된 파일이 있으므로 건너뜀
        continue;
    }

    // 중복된 파일이 없으므로 파일 정보를 VALUES 배열에 추가
    $balsin = $arr[0];
    $susin = $arr[1];
    $starttime = implode("", array_slice($arr, 2, 6, true));
    $endtime = implode("", array_slice($arr, 8, 6, true));
    $file_dir = '/Record/'. $folderName . '/' .implode('_', $arr);
    $file_dir = preg_replace('/_([^_]*)$/', '.$1', $file_dir);
    $ext_type = $arr[14];
    
    // 통화시간 계산 (초 단위로 반환)
    $call_time_seconds = strtotime($endtime) - strtotime($starttime);

    // 시, 분, 초로 변환
    $hours = floor($call_time_seconds / 3600);
    $minutes = floor(($call_time_seconds - ($hours * 3600)) / 60);
    $seconds = $call_time_seconds % 60;

    // 통화시간을 HH:MM:SS 형식으로 변환
    $call_time = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

    // VALUES 배열에 추가
    $values[] = "('$balsin', '$susin', '$starttime', '$endtime', '$call_time', '$file_dir', '$file_names', '$ext_type', '$savetime')";
}

// VALUES 배열이 비어있지 않은 경우 DB에 일괄 삽입
if (!empty($values)) {
    // VALUES를 이용한 일괄 삽입
    $query = "INSERT INTO \"DCS\" ('balsin_number', 'susin_number', 'start_time', 'end_time', 'call_time', 'file_dir', 'file_names', 'ext_type', 'crtime') ";
    $query .= "VALUES " . implode(', ', $values);
    $DB->exec($query);
    echo $DB;
    exit;
}
        closedir($handleDir);
    }

    closedir($handle);
}

// 파일 명 분석 후 배열로 반환
function custom(array $files)
{
    $result = array();

    foreach ($files as $f) {
        $arr = preg_split("/\_|\./", $f);
        array_push($result, $arr);
    }
    return $result;
}
?>
