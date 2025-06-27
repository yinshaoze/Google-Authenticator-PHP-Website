<?php
require 'utils.php';
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['user'];
$userDir = USER_DIR . '/' . sanitize($username);

array_map('unlink', glob("$userDir/*"));
rmdir($userDir);

session_destroy();

//echo "账户已删除。<a href='index.php'>返回登录</a>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>账户已删除</title>
    <script src="./tailwindcss.css"></script>
</head>
<body>
    <!-- 注销成功提示弹窗 -->
<div id="deletedModal" class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl animate-fadeIn relative">
        <div class="flex items-center space-x-3 mb-4">
            <span class="text-green-500 text-2xl">✅</span>
            <h3 class="text-lg font-semibold text-gray-800">账户已注销</h3>
        </div>
        <p class="text-gray-600 mb-6">您的账户及所有验证码信息已成功删除。</p>
        <div class="flex justify-end">
            <a href="index.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">返回首页</a>
        </div>
        <button onclick="closeDeletedModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700">✕</button>
    </div>
</div>
<script>
    window.onload = function() {
        openDeletedModal();
    }
    function openDeletedModal() {
        document.getElementById('deletedModal').classList.remove('hidden');
    }
    function closeDeletedModal() {
        document.getElementById('deletedModal').classList.add('hidden');
    }
</script>
</body>
</html>
