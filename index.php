<?php
// include("register_table.php");
$dbFile = ".\DCS_DB\DCS_database.db";
$conn = new SQLite3($dbFile);

if (!$conn) {
    die("Connection failed: " . $conn->lastErrorMsg());
}
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/CSS/bplogin.css">
    <title>DCS 녹음 청취 페이지</title>
    <script>
        function validateInput(inputElement) {
            // 정규 표현식을 사용하여 올바른 형식인지 확인
            var regex = /^[a-zA-Z0-9]+$/;

            // 입력이 정규 표현식과 일치하지 않는 경우
            if (!regex.test(inputElement.value)) {
                // 입력이 초기화되지 않도록 입력 차단
                inputElement.value = inputElement.value.replace(/[^a-zA-Z0-9]/g, '');
            }
        }
        function loginUser() {
            var id = document.getElementById("idInput").value;
            var pw = document.getElementById("pwInput").value;

            fetch('./DCS_Login/register_login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'login=true&idInput=' + encodeURIComponent(id) + '&pwInput=' + encodeURIComponent(pw),
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('서버 응답 에러: ' + response.status);
                }
                return response.text();
            })
            .then(data => {
                alert(data);
                if (data.includes("로그인 성공")) {
                    // 비밀번호가 "1234"인 경우 password.php로 이동
                    if (pw === "1234") {
                        // 비밀번호 변경 페이지로 사용자 아이디 전달하여 이동
                        window.location.href = "./DCS_Login/password.php?userID=" + id;
                    } else {
                        // 비밀번호가 "1234"가 아닌 경우에는 다른 페이지로 이동
                        window.location.href = "./DCS_Login/listen.php?userID="+id;
                    }
                }
            })
            .catch(error => {
                alert(error.message);
            });
        }
    </script>
</head>
<body class="App">
    <div>
        <div id="logo">
            <?php
                // PHP에서 동적으로 경로 생성
                $imagePath = '/assets/images/bpLogo.png';
                echo '<img src="' . $imagePath . '" alt="">';
            ?>
        </div>
        <div id="ID">
            <input id="idInput" class="idInput" type="text" placeholder="아이디" oninput="validateInput(this)">
        </div>
        <div id="PW">
            <input id="pwInput" class="pwInput" type="password" placeholder="비밀번호" oninput="validateInput(this)">
        </div>
        <div id="BT">
            <button class="bt" onclick="loginUser()">로그인</button>
        </div>
    </div>
</body>
</html>
