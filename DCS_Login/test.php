<?php
$DB = new SQLite3('..\DCS_DB\DCS_database.db');
// $DB = new SQLite3('D:\project\dcs\DCS_Scheduler\database.db');
if (!$DB) {
    die("SQLite 연결 실패: " . $DB->lastErrorMsg());
}
date_default_timezone_set('Asia/Seoul');

// 사용자 ID를 받습니다.
$userID = $_GET['userID'] ?? '';

// 사용자 정보를 조회하기 위한 쿼리
$userQuery = "SELECT * FROM members WHERE user_id = :user_id";
$userStmt = $DB->prepare($userQuery);
$userStmt->bindValue(':user_id', $userID, SQLITE3_TEXT);
$userResult = $userStmt->execute();

// 사용자 정보 출력
while ($row = $userResult->fetchArray(SQLITE3_ASSOC)) {
    $id_account = $row['isAdmin'];
    $id_accesstype = $row['accessType'];
    $id_canlisten = $row['canListen'];
    $id_canDownload = $row['canDownload'];
    $id_exnumber = $row['ex_number'];
}
if ($id_account == 1){

} else{
    echo "<style>#process-member{ display:none; }</style>";
}

if ($id_canDownload == 1){

} else{
    echo "<style>.download-link{ display:none; }</style>";
}

// 한 페이지 당 표시될 항목 수
$itemsPerPage = isset($_GET['result_filter']) ? intval($_GET['result_filter']) : 30;

// 현재 페이지 번호
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;

$yesterday = date("Y-m-d", strtotime(date("Y-m-d") . " -1 day"));

// 검색 란의 입력값과 결과 변수 초기화
$search_date_start = '';
$search_date_end = '';

// 범위 날짜 검색
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search_date_start']) && isset($_GET['search_date_end'])) {
    $search_date_start = $_GET['search_date_start'];
    $search_date_end = $_GET['search_date_end'];
}
$search_date_start = !empty($search_date_start) ? $search_date_start : $yesterday;
$search_date_end = !empty($search_date_end) ? $search_date_end : $yesterday;

// 날짜 범위 지정
$dateCondition = "substr(start_time, 1, 8) >= '" . str_replace('-', '', $search_date_start) . "' AND substr(end_time, 1, 8) <= '" . str_replace('-', '', $search_date_end) . "'";

// 통화 구분 선택 폼
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['call_type'])) {
    $call_type = $_GET['call_type'] ?? 'all';
} else {
    $call_type = 'all';
}

// 통화 번호 입력란
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['call_number'])) {
    $call_number = $_GET['call_number'] ?? '';
} else {
    $call_number = '';
}

// accessType이 1인 경우 모든 정보를 출력
// accessType이 1이 아닌 경우 내선번호와 일치하는 정보만 출력
$ex_number_condition = '';
if ($id_accesstype != 1) {
    $ex_number_condition = "(susin_number = '$id_exnumber' OR balsin_number = '$id_exnumber')";
}


// 통화 구분 및 통화 번호 조건 설정
$call_number_condition = '';

if ($call_type === 'inbound') {
    if (!empty($call_number)) {
        $call_number_condition = "susin_number LIKE '%$call_number%'";
    }
} elseif ($call_type === 'outbound') {
    if (!empty($call_number)) {
        $call_number_condition = "balsin_number LIKE '%$call_number%'";
    }
} elseif ($call_type === 'all') {
    if (!empty($call_number)) {
        $call_number_condition = "((susin_number LIKE '%$call_number%') OR (balsin_number LIKE '%$call_number%'))";
    }
}


// 1분 이상만 보기 체크 여부 확인
$showOver1min = isset($_GET['show_over_1min']) && $_GET['show_over_1min'] === 'on';

// 통화시간이 1분 이상인 정보를 조회하기 위한 조건
$callTimeCondition = '';
if ($showOver1min) {
    $callTimeCondition = "call_time >= '00:01:00'"; // 1분 이상인 정보만 조회
}

// 검색 조건 조합
$search_conditions = [];
if (!empty($dateCondition)) {
    $search_conditions[] = $dateCondition;
}
if (!empty($call_type_condition)) {
    $search_conditions[] = $call_type_condition;
}
if (!empty($call_number_condition)) {
    $search_conditions[] = $call_number_condition;
}
if (!empty($ex_number_condition)) {
    $search_conditions[] = $ex_number_condition;
}
if (!empty($callTimeCondition)) {
    $search_conditions[] = $callTimeCondition;
}

// 검색 조건을 AND 조건으로 결합
$search_condition = implode(' AND ', $search_conditions);

// 정렬 순서 지정
$orderBy = "start_time DESC"; // 기본값: 시작 날짜를 기준으로 내림차순으로 정렬
if (isset($_GET['sort_filter'])) {
    if ($_GET['sort_filter'] === 'asc') {
        $orderBy = "start_time ASC"; // 오래된 순으로 정렬
    }
}

// DCS 테이블에서 검색
$query = "SELECT COUNT(*) FROM DCS";
if (!empty($search_condition)) {
    $query .= " WHERE $search_condition";
}
$totalCount = $DB->querySingle($query);
$totalPages = ceil($totalCount / $itemsPerPage);

// 현재 페이지의 항목을 얻어옴
$offset = ($currentPage - 1) * $itemsPerPage;
$query = "SELECT * FROM DCS";
if (!empty($search_condition)) {
    $query .= " WHERE $search_condition";
}
$query .= " ORDER BY $orderBy LIMIT $itemsPerPage OFFSET $offset"; // ORDER BY 절 중복
$result = $DB->query($query);

?>


<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/CSS/listen.css"> <!-- css 호출 -->
    <title>DCS 녹음 청취 페이지</title>
</head>
<body>
    <!-- 헤더 바 -->
    <div class="header">
        <div class="logo">
            <a href="">DCS 녹음 청취 페이지</a>
        </div>
    <!-- 오른쪽 메뉴 -->
<div class="right-menu">
    <div class="header-menu">
        <?php
        // 사용자 아이디 출력
        if (isset($_GET['userID'])) { // GET 매개변수에 사용자명이 설정되어 있는 경우
            $userID = htmlspecialchars($_GET['userID']);
            echo $userID . "님"; // 사용자명 뒤에 "님"을 붙여 출력
        } else {
            echo "Guest님"; // GET 매개변수에 사용자명이 없는 경우 "Guest" 출력
        }
        ?>
    </div>
    <a href="#" class="logout-link" onclick="logout()">로그아웃</a>
</div>
    </div>

    <!-- 왼쪽 사이드바 -->
<div class="sidebar">
    <h4>
        <span>통화녹취내역</span>
    </h4>

    <!-- 통화 녹취내역 링크 -->
    <a href="listen.php?userID=<?= urlencode($userID) ?>" class="sidebar-menu"><i class="fa fa-solid fa-file"></i> 통화 녹취내역</a>

    <h4>
        <span id="process-member"> 회원 관리</span>
    </h4>

    <!-- 회원 관리 링크 -->
    <a href="process_member.php?userID=<?= urlencode($userID) ?>" class="sidebar-menu" id="process-member"><i class="fa fa-solid fa-users"></i> 회원 관리</a>

</div>


    <!-- 메인 콘텐츠 -->
    <div class="maintable">
        <h1>통화 녹취 내역</h1>
        <br>

      <!-- 통화 녹취내역 검색 기간 선택 폼 -->
<form id="date-search-form" class="date-search-form" action="" method="get">
    <!-- 검색 기간 선택 -->
    <label for="search_date_start" class="search-title">검색기간 </label>
    <input class="input-start" type="text" id="search_date_start" name="search_date_start" value="<?= htmlspecialchars($search_date_start) ?>" placeholder="시작일">
    <span class="datepicker-icon" id="start_date_range_icon" style="font-size: 28px;"><i class="fa fa-calendar"></i></span>
    &nbsp;~&nbsp;
    <input class="input-end" type="text" id="search_date_end" name="search_date_end" value="<?= htmlspecialchars($search_date_end) ?>" placeholder="종료일">
    <span class="datepicker-icon" id="end_date_range_icon" style="font-size: 28px;"><i class="fa fa-calendar"></i></span>
    <button class="search" id="search-btn" type="submit">검색</button>
    <!-- 사용자 ID를 숨겨 전송 -->
    <input type="hidden" name="userID" value="<?= isset($_GET['userID']) ? htmlspecialchars($_GET['userID']) : '' ?>">
    <!-- 1분 이상만 보기 체크박스 -->
    <label for="show_over_1min" class="search-title">1분 이상만 보기</label>
    <input type="checkbox" id="show_over_1min" name="show_over_1min" <?= isset($_GET['show_over_1min']) && $_GET['show_over_1min'] === 'on' ? 'checked' : '' ?>>
</form>


        <!-- 통화 구분 선택 폼 -->
        <form class="call-type-form" id="call_type_form" action="" method="get">
            <!-- 통화 구분 선택 -->
            <label for="call_type" class="call-title">검색필터 </label>
            <select id="call_types" class="call-types" name="call_type">
                <option value="all">전체</option>
                <option value="inbound">수신</option>
                <option value="outbound">발신</option>
            </select>
            <!-- 통화 번호 입력 -->
            <input type="text" id="call_number" class="call-number" name="call_number" placeholder="검색어 입력">
            <!-- 검색 버튼 -->
            <button class="search" id="filter-btn" type="submit">검색</button>
            <!-- 사용자 ID를 숨겨 전송 -->
            <input type="hidden" name="userID" value="<?= isset($_GET['userID']) ? htmlspecialchars($_GET['userID']) : '' ?>">
        </form>


        <!-- 검색 결과 테이블 -->
        <?php if ($result && $result->numColumns() > 0) : ?>
            <div class="control-count">
                총 <?= $totalCount ?> 건
            </div>
            <div class="control-filter">
                
            <select class="limit-filter" id="result_filter" name="result_filter">
                    <option value="20" <?= ($_GET['result_filter'] ?? 30) == 20 ? 'selected' : '' ?>>20건씩 보기</option>
                    <option value="30" <?= ($_GET['result_filter'] ?? 30) == 30 ? 'selected' : '' ?>>30건씩 보기</option>
                    <option value="40" <?= ($_GET['result_filter'] ?? 30) == 40 ? 'selected' : '' ?>>40건씩 보기</option>
                    <option value="50" <?= ($_GET['result_filter'] ?? 30) == 50 ? 'selected' : '' ?>>50건씩 보기</option>
                    <option value="100" <?= ($_GET['result_filter'] ?? 30) == 100 ? 'selected' : '' ?>>100건씩 보기</option>
                </select>
                <!-- 정렬 필터 -->
                <select class="sort-filter" id="sort_filter" name="sort_filter">
                    <option value="desc" <?= ($_GET['sort_filter'] ?? 'desc') == 'desc' ? 'selected' : '' ?>>최신순</option>
                    <option value="asc" <?= ($_GET['sort_filter'] ?? 'desc') == 'asc' ? 'selected' : '' ?>>오래된순</option>
                </select>
            </div>
            <table id="call_history_table" class="table">
                <!-- 테이블 헤더 -->
                <thead>
                    <tr class="tr">
                        <th>발신번호</th>
                        <th>수신번호</th>
                        <th>통화시작시간</th>
                        <th>통화종료시간</th>
                        <th>총 통화 시간</th>
                        <th style="width: 150px;">녹취파일</th> <!-- 너비 조정 -->
                    </tr>
                </thead>
                <!-- 테이블 내용 -->
                <tbody class="tbody">
                    <?php while ($row = $result->fetchArray()) : ?>
                        <tr>
                            <td><?= $row['balsin_number'] ?></td>
                            <td><?= $row['susin_number'] ?></td>
                            <td><?= date('Y-m-d H:i:s', strtotime($row['start_time'])) ?></td>
                            <td><?= date('Y-m-d H:i:s', strtotime($row['end_time'])) ?></td>
                            <td><?= date('H:i:s', strtotime($row['call_time'])) ?></td>
                            <td style="max-width: 350px; max-height:5px; overflow: hidden; text-overflow: ellipsis;">
                                <div class="audio-controls">
                                <audio controls>
                                    <source src="<?= ($id_canlisten == 1) ? $row['file_dir'] : '' ?>" type="audio/wav">
                                </audio>
                                <a class="download-link" href="download.php?file=<?= ('/project'.$row['file_dir'])?>"><i class="fa fa-regular fa-download"></i></a>
                        </div>
                    </td>
                </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>

       <!-- 페이지 네이션 -->
<div class="pagination">
    <!-- 처음 페이지 링크 -->
    <a href="?page=1&search_date_start=<?= urlencode($search_date_start) ?>&search_date_end=<?= urlencode($search_date_end) ?>" class="pagemove">처음</a>

    <!-- 이전 페이지 링크 -->
    <a href="?page=<?= ($currentPage - 1) ?>&search_date_start=<?= urlencode($search_date_start) ?>&search_date_end=<?= urlencode($search_date_end) ?>" class="pagemove">이전</a>

    <!-- 페이지 번호 링크 -->
    <?php
    // 페이지 출력 제한 설정
    if ($totalPages > 10 && $currentPage > 10) {
        $startPage = $currentPage - 1;
        $endPage = min($totalPages, $currentPage + 8);
    } else {
        $startPage = 1;
        $endPage = min($totalPages, 10);
    }
    for ($i = $startPage; $i <= $endPage; $i++) : ?>
        <a href="?page=<?= $i ?>&search_date_start=<?= urlencode($search_date_start) ?>&search_date_end=<?= urlencode($search_date_end) ?>"><?= $i ?></a>
    <?php endfor; ?>

    <!-- 다음 페이지 링크 -->
    <a href="?page=<?= ($currentPage + 1) ?>&search_date_start=<?= urlencode($search_date_start) ?>&search_date_end=<?= urlencode($search_date_end) ?>" class="pagemove">다음</a>

    <!-- 마지막 페이지 링크 -->
    <a href="?page=<?= $totalPages ?>&search_date_start=<?= urlencode($search_date_start) ?>&search_date_end=<?= urlencode($search_date_end) ?>" class="pagemove">마지막</a>
</div>

        <!-- flatpickr 관련 스크립트 -->
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ko.js"></script>
        <script>
            let search_date_start = document.getElementById('search_date_start').value;
            let search_date_end = document.getElementById('search_date_end').value;

            // flatpickr 설정
            flatpickr("#search_date_start", {
                enableTime: false,
                dateFormat: "Y-m-d", // yyyy-mm-dd 형식으로 날짜 설정
                locale: "ko",
                defaultDate: search_date_start,
                // maxDate: new Date().setHours(-24)
            });

            flatpickr("#search_date_end", {
                enableTime: false,
                dateFormat: "Y-m-d", // yyyy-mm-dd 형식으로 날짜 설정
                locale: "ko",
                defaultDate: search_date_end,
                // maxDate: new Date().setHours(-24)
            });

            // 달력 아이콘 클릭 시 flatpickr 표시
            document.getElementById('start_date_range_icon').addEventListener('click', function() {
                document.getElementById('search_date_start')._flatpickr.open();
            });

            document.getElementById('end_date_range_icon').addEventListener('click', function() {
                document.getElementById('search_date_end')._flatpickr.open();
            });

            // 출력 개수 필터가 변경될 때 자동으로 페이지 새로고침
document.getElementById('result_filter').addEventListener('change', function() {
    var selectedValue = this.value; // 선택된 출력 개수 값 가져오기
    var currentUrl = window.location.href; // 현재 URL 가져오기
    var hasQueryString = currentUrl.indexOf('?') !== -1; // 쿼리 스트링이 있는지 확인
    var separator = hasQueryString ? '&' : '?'; // URL에 파라미터 추가할 때 구분자 결정

    // 선택한 값으로 URL 업데이트
    var updatedUrl = currentUrl;
    if (hasQueryString) {
        // 쿼리 스트링이 있는 경우
        if (currentUrl.includes('result_filter')) {
            // result_filter 파라미터가 있는 경우 값 변경
            updatedUrl = currentUrl.replace(/result_filter=\d+/, 'result_filter=' + selectedValue);
        } else {
            // result_filter 파라미터가 없는 경우 파라미터 추가
            updatedUrl += separator + 'result_filter=' + selectedValue;
        }
    } else {
        // 쿼리 스트링이 없는 경우 새로운 쿼리 스트링 추가
        updatedUrl += separator + 'result_filter=' + selectedValue;
    }

    // 업데이트된 URL로 페이지 새로고침
    window.location.href = updatedUrl;
});

// 정렬 필터가 변경될 때 자동으로 페이지 새로고침
document.getElementById('sort_filter').addEventListener('change', function() {
    var selectedValue = this.value; // 선택된 정렬 값 가져오기
    console.log("Selected value:", selectedValue);
    var currentUrl = window.location.href; // 현재 URL 가져오기
    console.log("Current URL:", currentUrl);
    var hasQueryString = currentUrl.indexOf('?') !== -1; // 쿼리 스트링이 있는지 확인
    var separator = hasQueryString ? '&' : '?'; // URL에 파라미터 추가할 때 구분자 결정

    // 선택한 값으로 URL 업데이트
    var updatedUrl = currentUrl;
    if (hasQueryString) {
        // 쿼리 스트링이 있는 경우
        if (currentUrl.includes('sort_filter')) {
            // sort_filter 파라미터가 있는 경우 값 변경
            updatedUrl = currentUrl.replace(/sort_filter=(desc|asc)/, 'sort_filter=' + selectedValue);
        } else {
            // sort_filter 파라미터가 없는 경우 파라미터 추가
            updatedUrl += separator + 'sort_filter=' + selectedValue;
        }
    } else {
        // 쿼리 스트링이 없는 경우 새로운 쿼리 스트링 추가
        updatedUrl += separator + 'sort_filter=' + selectedValue;
    }

    // 업데이트된 URL로 페이지 새로고침
    window.location.href = updatedUrl;
});

            // 검색 폼 제출 시 자바스크립트 함수 호출
            document.getElementById('date-search-form').addEventListener('submit', function(event) {
                event.preventDefault(); // 기본 동작 방지
                applySearch(); // 검색 적용 함수 호출
            });

            document.getElementById('call_type_form').addEventListener('submit', function(event) {
                event.preventDefault(); // 기본 동작 방지
                applySearch(); // 검색 적용 함수 호출
            });

            // 검색 적용 함수
            function applySearch() {
                var startDate = document.getElementById('search_date_start').value;
    var endDate = document.getElementById('search_date_end').value;
    var callType = document.getElementById('call_types').value;
    var callNumber = document.getElementById('call_number').value;
    var showOver1min = document.getElementById('show_over_1min').checked; // 1분 이상만 보기 체크 여부 확인
    var userID = getUrlParameter('userID'); // 현재 URL에서 사용자 ID 가져오기

    // 검색 기간 및 통화 구분, 통화 번호를 URL에 추가하여 페이지 새로고침
    var queryParams = new URLSearchParams(window.location.search);
    queryParams.set('search_date_start', startDate);
    queryParams.set('search_date_end', endDate);
    queryParams.set('call_type', callType);
    queryParams.set('call_number', callNumber);
    queryParams.set('userID', userID); // 사용자 ID를 URL에 추가
    if (showOver1min) {
        queryParams.set('show_over_1min', 'on'); // 1분 이상만 보기 체크가 되어 있으면 파라미터 추가
    } else {
        queryParams.delete('show_over_1min'); // 체크가 해제되면 파라미터 삭제
    }

    var newUrl = window.location.pathname + '?' + queryParams.toString();
    window.location.href = newUrl;
            }
           // 페이지 로드 시 URL에서 매개변수 추출하는 함수
function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    var results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
}
            // 페이지 네이션 링크 클릭 시 검색 상태를 유지하고 해당 페이지로 이동
            document.querySelectorAll('.pagination a').forEach(function(link) {
                link.addEventListener('click', function(event) {
                    event.preventDefault(); // 링크 기본 동작 방지

                    var currentPage = parseInt(this.getAttribute('href').match(/page=(\d+)/)[1]); // 클릭한 페이지 번호 가져오기
                    var queryParams = new URLSearchParams(window.location.search); // 현재 쿼리 매개변수 가져오기

                    // 검색 기간 및 통화 구분을 현재 쿼리 매개변수에서 가져와 새 링크의 쿼리 매개변수에 추가
                    var startDate = queryParams.get('search_date_start');
                    var endDate = queryParams.get('search_date_end');
                    var callType = queryParams.get('call_type');
                    var callNumber = queryParams.get('call_number');

                    // 새로운 페이지 번호를 쿼리 매개변수에 추가
                    queryParams.set('page', currentPage);

                    // 페이지 이동할 URL 생성
                    var newUrl = window.location.pathname + '?' + queryParams.toString();

                    // 페이지 이동
                    window.location.href = newUrl;
                });
            });
            function logout() {
    // 로그아웃 여부를 확인하는 알림창 표시
    if (confirm('로그아웃 하시겠습니까?')) {
        // 확인을 누른 경우 register.php로 이동
        window.location.href = '/dcs/index.php';
        console.log("ddd");
    } else {
        // 취소를 누른 경우 로그아웃 취소
        return false;
    }
}

        </script>
</body>
</html>