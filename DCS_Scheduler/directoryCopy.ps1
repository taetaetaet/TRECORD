# 사용자 폴더 가져오기
$userArr = Get-ChildItem -Path "C:\Users" -Directory

# DCS 파일이 생성되는 경로 찾기
foreach ($user in $userArr) {
    $dir = Join-Path -Path $user.FullName -ChildPath "AppData\Local\Temp\DCS"
    if (Test-Path $dir) {
        break
    }
}

# 복사할 주소를 변수에 저장
$sourceDir = $dir

$moveDir = "..\Record"

# "DCS" 폴더 안에 있는 하위 폴더들을 복사
Get-ChildItem -Path $sourceDir -Recurse | ForEach-Object {
    $destination = Join-Path -Path $moveDir -ChildPath $_.FullName.Substring($sourceDir.Length + 1)

    # 파일 또는 폴더의 중복 여부를 확인한 후에 복사 진행
    if (-not (Test-Path $destination)) {
        Copy-Item -Path $_.FullName -Destination $destination -Force
    }
}

# 이동된 파일이나 디렉토리 삭제
# Remove-Item -Path $sourceDir -Recurse -Force
