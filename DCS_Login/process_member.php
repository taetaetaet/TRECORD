<?php
$database_file = "..\DCS_DB\DCS_database.db";
// 데이터베이스 연결
$db_connection = new SQLite3($database_file);
// 연결 확인
if (!$db_connection) {
    die("데이터베이스 연결 실패: " . $db_connection->lastErrorMsg());
}

// 회원 등록 폼에서 제출된 데이터 처리
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 폼 데이터 가져오기
    $username = $_POST["user_id"];
    $password = $_POST["user_pw"];
    $exNumber = $_POST["ex_number"];
    $isAdmin = $_POST["admin"];
    $accessType = $_POST["query_type"];
    $canListen = $_POST["listening"];
    $canDownload = $_POST["downloading"];
    $isActive = $_POST["usage"];

    // 아이디 중복 체크
    $checkQuery = "SELECT COUNT(*) AS count FROM Members WHERE user_id='$username'";
    $checkResult = $db_connection->querySingle($checkQuery);

    if ($checkResult > 0) {
        // 중복된 아이디인 경우
        echo "duplicate";
        exit;
    } else {
        // 중복되지 않은 경우, INSERT 쿼리 실행
        $insertQuery = "INSERT INTO Members (user_id, user_pw, ex_Number, isAdmin, accessType, canListen, canDownload, isActive, create_day) 
                        VALUES ('$username', '$password', '$exNumber', '$isAdmin', '$accessType', '$canListen', '$canDownload', '$isActive', date('now'))";
        echo "<script>  window.location.href = 'process_member.php?userID=<?php echo $userID; ?>';</script>";

        if (!$db_connection->exec($insertQuery)) {
            echo "회원 등록 실패: " . $db_connection->lastErrorMsg();
        } else {
            // 새로운 사용자의 아이디 반환
            echo $username;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/CSS/process_member.css">
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
            <span>회원 관리</span>
        </h4>

        <!-- 회원 관리 링크 -->
        <a href="process_member.php?userID=<?= urlencode($userID) ?>" class="sidebar-menu"><i class="fa fa-solid fa-users"></i> 회원 관리</a>
    </div>


    <div class="main-content">

        <!-- 회원 정보 출력 테이블 -->
        <h2 class="member-title">회원 관리</h2>
        <table class="member-table">
            <thead>
                <!-- 회원 등록 버튼 -->
                <button id="openModalBtn">회원 등록</button>
                <tr>
                    <th>#</th>
                    <th>아이디</th>
                    <th>비밀번호</th>
                    <th>내선번호</th>
                    <th>관리자 여부</th>
                    <th>조회 구분</th>
                    <th>청취 여부</th>
                    <th>다운로드 여부</th>
                    <th>계정 사용 여부</th>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // 회원 정보를 데이터베이스에서 가져와 출력
                $selectQuery = "SELECT * FROM Members";
                $result = $db_connection->query($selectQuery);

                if ($result->numColumns() > 0) {
                    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                        echo "<tr class='process' data-seq='" . $row["seq"] . "'>"; // 각 행에 data-seq 속성 추가
                        echo "<td>" . $row["seq"] . "</td>";
                        echo "<td>" . $row["user_id"] . "</td>";
                        echo "<td>" . $row["user_pw"] . "</td>";
                        echo "<td>" . $row["ex_number"] . "</td>";
                        echo "<td>" . ($row["isAdmin"] ? "관리자" : "일반 사용자") . "</td>";
                        echo "<td>" . ($row["accessType"] ? "전체" : "본인") . "</td>";
                        echo "<td>" . ($row["canListen"] ? "가능" : "불가능") . "</td>";
                        echo "<td>" . ($row["canDownload"] ? "가능" : "불가능") . "</td>";
                        echo "<td>" . ($row["isActive"] ? "활성화" : "비활성화") . "</td>";
                        echo "<td>" . "<button class='edit-btn' onclick='editRow(" . $row["seq"] . ")'>수정</button> " . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>등록된 회원이 없습니다.</td></tr>";
                }
                ?>
            </tbody>

        </table>
    </div>

    <!-- 등록 모달 -->
    <div id="myModal" class="modal">
        <!-- 모달 내용 -->
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close">x</button>
                <label for="title" class="modal-title">회원등록</label>
            </div>

            <div class="modal-body">
                <!-- 회원 등록 폼 -->
                <form id="joinForm" name="joinForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

                    <!-- 아이디 -->
                    <div class="form-group col" id="userid">
                        <label for="user_id">아이디</label>
                        <input type="text" class="form-control" id="user_id" name="user_id" placeholder="아이디 입력" required autofocus>

                    </div>
                    <!-- 비밀번호 -->
                    <div class="form-group col" id="userpw">
                        <label for="user_pw">비밀번호</label>
                        <input type="password" class="form-control" id="user_pw" name="user_pw" placeholder="비밀번호 입력" required>

                    </div>

                    <!-- 내선번호 -->
                    <div class="form-group col" id="exnumber">
                        <label for="ex_number">내선번호</label>
                        <input type="text" class="form-control" id="ex_number" name="ex_number" placeholder="내선번호 입력" required>

                    </div>

                    <!-- 관리자 여부 -->
                    <div class="form-group col" id="useradmin">
                        <label for="admin">관리자 여부</label>
                        <select class="form-control" id="admin" name="admin" required>
                            <option value="">선택</option>
                            <option value="1">관리자</option>
                            <option value="0">일반 사용자</option>
                        </select>
                    </div>


                    <!-- 조회 구분 -->
                    <div class="form-group col" id="usertype">
                        <label for="query_type">조회 구분</label>
                        <select class="form-control" id="query_type" name="query_type" required>
                            <option value="">선택</option>
                            <option value="1">전체</option>
                            <option value="0">본인</option>
                        </select>
                    </div>

                    <!-- 청취 여부 -->
                    <div class="form-group col" id="userlisten">
                        <label for="listening">청취 여부</label>
                        <select class="form-control" id="listening" name="listening" required>
                            <option value="">선택</option>
                            <option value="1">청취가능</option>
                            <option value="0">청취불가능</option>
                        </select>
                    </div>

                    <!-- 다운로드 여부 -->
                    <div class="form-group col" id="userdown">
                        <label for="downloading">다운로드 여부</label>
                        <select class="form-control" id="downloading" name="downloading" required>
                            <option value="">선택</option>
                            <option value="1">다운가능</option>
                            <option value="0">다운불가능</option>
                        </select>

                    </div>

                    <!-- 사용 여부 -->
                    <div class="form-group col" id="userage">
                        <label for="usage">계정 사용 여부</label>
                        <select class="form-control" id="usage" name="usage" required>
                            <option value="">선택</option>
                            <option value="1">활성화</option>
                            <option value="0">비활성화</option>
                        </select>
                    </div>
            </div>


            <div class="modal-bottom">
                <!-- 버튼 그룹 -->
                <div class="col text-right" id="btn">
                    <button type="submit" class="btn-process">회원 등록</button>
                    <button type="button" class="btn-cancel" onclick="closeModal()">취소</button>
                </div>
            </div>
            </form>
        </div>
    </div>
    <!-- 수정 모달 -->
    <div id="editModal" class="modal">
        <!-- 모달 내용 -->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" onclick="closeEditModal()">x</button>
                <label for="title" class="modal-title">회원 수정</label>
            </div>
            
            <div class="modal-body">
                <!-- 회원 수정 폼 -->
                <form id="editForm" name="editForm" method="post" action="edit_member.php">
                    <input type="hidden" id="userID" name="userID" value="<?php echo $userID; ?>">
                    <!-- 아이디 -->
                    <div class="form-group col" id="edit_userid">
                        <label for="edit_user_id">아이디</label>
                        <input type="text" class="form-control" id="edit_user_id" name="user_id" placeholder="아이디 입력" required autofocus readonly>
                    </div>

                    <!-- 비밀번호 -->
                    <div class="form-group col" id="edit_userpw">
                        <label for="edit_user_pw">새로운 비밀번호</label>
                        <input type="password" class="form-control" id="edit_user_pw" name="user_pw" placeholder="새로운 비밀번호 입력">
                        <small id="edit_userpw_error" class="error-message"></small> <!-- 비밀번호 입력 안내 메시지 -->
                    </div>
                    <!-- 내선번호 -->
                    <div class="form-group col" id="edit_exnumber">
                        <label for="edit_ex_number">새로운 내선번호</label>
                        <input type="text" class="form-control" id="edit_ex_number" name="ex_number" placeholder="새로운 내선번호 입력">
                        <small id="edit_exnumber_error" class="error-message"></small> <!-- 내선번호 입력 안내 메시지 -->
                    </div>
                    <!-- 관리자 여부 -->
                    <div class="form-group col" id="edit_useradmin">
                        <label for="edit_admin">관리자 여부</label>
                        <select class="form-control" id="edit_admin" name="admin" required>
                            <option value="">선택</option>
                            <option value="1">관리자</option>
                            <option value="0">일반 사용자</option>
                        </select>
                    </div>

                    <!-- 조회 구분 -->
                    <div class="form-group col" id="edit_usertype">
                        <label for="edit_query_type">조회 구분</label>
                        <select class="form-control" id="edit_query_type" name="query_type" required>
                            <option value="">선택</option>
                            <option value="1">전체</option>
                            <option value="0">본인</option>
                        </select>
                    </div>

                    <!-- 청취 여부 -->
                    <div class="form-group col" id="edit_userlisten">
                        <label for="edit_listening">청취 여부</label>
                        <select class="form-control" id="edit_listening" name="listening" required>
                            <option value="">선택</option>
                            <option value="1">청취가능</option>
                            <option value="0">청취불가능</option>
                        </select>
                    </div>

                    <!-- 다운로드 여부 -->
                    <div class="form-group col" id="edit_userdown">
                        <label for="edit_downloading">다운로드 여부</label>
                        <select class="form-control" id="edit_downloading" name="downloading" required>
                            <option value="">선택</option>
                            <option value="1">다운가능</option>
                            <option value="0">다운불가능</option>
                        </select>
                    </div>

                    <!-- 사용 여부 -->
                    <div class="form-group col" id="edit_userage">
                        <label for="edit_usage">계정 사용 여부</label>
                        <select class="form-control" id="edit_usage" name="usage" required>
                            <option value="">선택</option>
                            <option value="1">활성화</option>
                            <option value="0">비활성화</option>
                        </select>
                    </div>

                    <div class="modal-bottom">
                        <!-- 버튼 그룹 -->
                        <div class="col text-right" id="btn">
                            <button type="submit" class="btn-process">회원 등록</button>
                            <button type="button" class="btn-cancel" onclick="closeEditModal()">취소</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        // 모달과 모달 열기 버튼을 DOM으로 가져옵니다.
        var modal = document.getElementById("myModal");
        var editModal = document.getElementById("editModal");
        var openModalBtn = document.getElementById("openModalBtn");

        // 모달 닫기 버튼을 DOM으로 가져옵니다.
        var span = document.getElementsByClassName("close");

        // 모달 열기 버튼 클릭 이벤트 리스너를 추가합니다.
        openModalBtn.onclick = function() {
            modal.style.display = "block";
        }

        // 모든 모달 닫기 버튼 클릭 이벤트 리스너를 추가합니다.
        for (var i = 0; i < span.length; i++) {
            span[i].onclick = function() {
                modal.style.display = "none";
                editModal.style.display = "none";
            }
        }

        // 모달 외부 클릭 시 모달을 닫습니다.
        window.onclick = function(event) {
            if (event.target == modal || event.target == editModal) {
                modal.style.display = "none";
                editModal.style.display = "none";
            }
        }

        // 회원 등록 폼 제출 이벤트 리스너
        document.getElementById("joinForm").addEventListener("submit", function(event) {
            event.preventDefault(); // 폼 제출 기본 동작 막기

            // 폼 데이터 가져오기
            var formData = new FormData(this);

            // AJAX 요청 보내기
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>", true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // 요청이 성공하면 서버에서 받은 데이터 처리
                    if (xhr.responseText.trim() === "duplicate") {
                        // 중복된 아이디인 경우 알림창 띄우기
                        alert('이미 사용 중인 아이디입니다.');
                    } else {
                        // 중복되지 않은 경우 알림창 띄우고 리다이렉션 처리
                        alert('회원 등록이 완료되었습니다.');
                        window.location.href = "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?userID=" + xhr.responseText;
                        // 모달 닫기
                        modal.style.display = "none";
                    }
                }
            };
            xhr.send(formData);
        });


        // 비밀번호 입력란에 한글, 공백, 특수문자 입력 방지
        document.getElementById("user_pw").addEventListener("input", function(event) {
            var password = event.target.value;
            if (/[ㄱ-ㅎ|ㅏ-ㅣ|가-힣| |!@#$%^&*(),.?":{}|<>]/.test(password)) {
                event.target.value = password.replace(/[ㄱ-ㅎ|ㅏ-ㅣ|가-힣| |!@#$%^&*(),.?":{}|<>]/g, "");
            }
        });
    </script>


    <script>
        
        // 수정할 행의 정보를 가져와서 모달에 채워 넣는 함수
        function editRow(seq) {
            // 선택한 행의 정보를 가져옴
            var row = document.querySelector(".process[data-seq='" + seq + "']");
            var columns = row.getElementsByTagName("td");

            // 모달에 채워 넣기
            document.getElementById("edit_user_id").value = columns[1].textContent; // 아이디
            // 아이디는 수정할 수 없으므로 readonly 속성 추가
            document.getElementById("edit_user_id").readOnly = true;

            // 기존 비밀번호는 입력하지 않음
            document.getElementById("edit_user_pw").value = '';
            document.getElementById("edit_ex_number").value = '';

            // 기존 정보를 선택하도록 설정
            document.getElementById("edit_admin").value = columns[3].textContent === "관리자" ? "1" : "0"; // 관리자 여부
            document.getElementById("edit_query_type").value = columns[4].textContent === "전체" ? "1" : "0"; // 조회 구분
            document.getElementById("edit_listening").value = columns[5].textContent === "가능" ? "1" : "0"; // 청취 여부
            document.getElementById("edit_downloading").value = columns[6].textContent === "가능" ? "1" : "0"; // 다운로드 여부
            document.getElementById("edit_usage").value = columns[7].textContent === "활성화" ? "1" : "0"; // 계정 사용 여부

            // 수정 모달 열기
            document.getElementById("editModal").style.display = "block";
        }

        // 모달 닫기 함수
        function closeModal() {
            // 모달 닫기
            modal.style.display = "none";
            // 폼 초기화
            document.getElementById("joinForm").reset();
        }

        function closeEditModal() {
            // 모달 닫기
            editModal.style.display = "none";
            // 폼 초기화
            document.getElementById("editForm").reset();
        }

        // 회원 수정 폼 제출 이벤트 리스너
        document.getElementById("editForm").addEventListener("submit", function(event) {
            // 새 비밀번호 입력란 값 가져오기
            var newPassword = document.getElementById("edit_user_pw").value;
            var newExNumber = docunemt.getElementById("edit_ex_number").value;

            // 새 비밀번호가 비어있는 경우 알림창 표시 및 폼 제출 취소
            if (newPassword === "") {
                alert("새 비밀번호를 입력하세요.");
                event.preventDefault(); // 폼 제출 취소
            }
            if (newExNumber === "") {
                alert("새 내선번호를 입력하세요.");
                event.preventDefault();
            }
        });

        function logout() {
            // 로그아웃 여부를 확인하는 알림창 표시
            if (confirm('로그아웃 하시겠습니까?')) {
                // 확인을 누른 경우 register.php로 이동
                window.location.href = '/dcs/index.php';
            } else {
                // 취소를 누른 경우 아무 동작도 하지 않음
                return false;
            }
        }
    </script>
</body>

</html>