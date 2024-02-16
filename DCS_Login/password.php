<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/CSS/bplogin_password.css">
    <title>비밀번호 변경</title>
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
        <form method="post" action="password_change.php">
            <!-- Hidden input 필드로 사용자 아이디 전달 -->
            <input type="hidden" name="userID" value="<?php echo $_GET['userID']; ?>">
            <div id="PW">
                <input name="currentPassword" class="pwInput" type="password" placeholder="기존 비밀번호">
            </div>
            <div id="NewPw">
                <input name="newPassword" class="newPwInput" type="password" placeholder="새 비밀번호">
            </div>
            <div id="NewPw_Confirm">
                <input name="confirmPassword" class="Confirm_Input" type="password" placeholder="새 비밀번호 확인">
            </div>
            <div id="BT">
                <button type="submit" class="bt">비밀번호 변경</button>
                <button type="button" class="cancel_bt" onclick="goBack()">취소</button>
            </div>
        </form>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>
