
<?php
require 'utils.php';
require 'libs/PHPGangsta/GoogleAuthenticator.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['user'];
$ga = new PHPGangsta_GoogleAuthenticator();

$secrets = getSecrets($username);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['label'], $_POST['secret'])) {
    $label = trim($_POST['label']);
    $secret = trim(strtoupper($_POST['secret']));
    if ($label !== '' && $secret !== '') {
        if (array_key_exists($label, $secrets)) {
            $showConflictModal = true;
        } else {
            $secrets[$label] = $secret;
            saveSecrets($username, $secrets);
            header("Location: dashboard.php");
            exit;
        }
    } else {
        $error = "名称和密钥不能为空。";
    }
}

if (isset($_GET['delete'])) {
    $labelToDelete = $_GET['delete'];
    if (isset($secrets[$labelToDelete])) {
        unset($secrets[$labelToDelete]);
        saveSecrets($username, $secrets);
        header("Location: dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>验证码管理</title>
    <script src="./tailwindcss.css"></script>
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="max-w-3xl mx-auto py-10 px-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">欢迎，<?= htmlspecialchars($username) ?></h2>
            <div class="space-x-4">
                <a href="logout.php" class="text-blue-500 hover:underline">退出登录</a>
                <button onclick="openLogoutModal()" class="text-red-500 hover:underline">注销账户</button>
            </div>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <h3 class="text-xl font-semibold mb-4">您的验证码</h3>
            <ul class="space-y-2">
                <?php foreach ($secrets as $label => $secret): ?>
                    <?php $code = $ga->getCode($secret); ?>
                    <li class="flex justify-between items-center border p-3 rounded">
                        <div>
                            <strong><?= htmlspecialchars($label) ?></strong>: 
                            <span class="code text-blue-600 font-mono"><?= $code ?></span> 
                            <span class="text-sm text-gray-500">(剩余 <span class="countdown"></span> 秒)</span>
                        </div>
                        <a href="?delete=<?= urlencode($label) ?>" onclick="return confirm('确认删除？');" class="text-red-500 hover:underline">删除</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- 添加按钮 -->
        <div class="mt-6">
            <button onclick="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">➕ 添加验证码</button>
        </div>
    </div>

    <!-- 弹出窗口 -->
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">添加新的验证码</h3>
            <?php if (!empty($error)): ?>
                <p class="text-red-600 mb-2"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="block mb-1">名称(Name):</label>
                    <input name="label" required class="w-full border px-3 py-2 rounded" />
                </div>
                <div class="mb-4">
                    <label class="block mb-1">密钥(Key):</label>
                    <input name="secret" required class="w-full border px-3 py-2 rounded" />
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded hover:bg-gray-100">取消</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">添加</button>
                </div>
            </form>
        </div>
    </div>


<!-- 注销确认模态 -->
<div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl animate-fadeIn relative">
        <div class="flex items-center space-x-3 mb-4">
            <span class="text-yellow-500 text-2xl">⚠️</span>
            <h3 class="text-lg font-semibold text-gray-800">确认注销账户？</h3>
        </div>
        <p class="text-gray-600 mb-6">此操作将永久删除您的账户和所有验证码，无法恢复。</p>
        <div class="flex justify-end space-x-3">
            <button onclick="closeLogoutModal()" class="px-4 py-2 border rounded hover:bg-gray-100">取消</button>
            <a href="delete_user.php" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">确认注销</a>
        </div>
        <button onclick="closeLogoutModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700">✕</button>
    </div>
</div>

<!-- 冲突提示模态 -->
<div id="conflictModal" class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl animate-fadeIn relative">
        <div class="flex items-center space-x-3 mb-4">
            <span class="text-red-500 text-2xl">❌</span>
            <h3 class="text-lg font-semibold text-gray-800">名称已存在</h3>
        </div>
        <p class="text-gray-600 mb-6">该验证码名称已存在，请使用不同名称。</p>
        <div class="flex justify-end">
            <button onclick="closeConflictModal()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">知道了</button>
        </div>
        <button onclick="closeConflictModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700">✕</button>
    </div>
</div>


    <script>
        function updateTimers() {
            const now = Math.floor(Date.now() / 1000);
            const remain = 30 - (now % 30);
            document.querySelectorAll('.countdown').forEach(el => {
                el.textContent = remain;
            });
        }
        updateTimers();
        setInterval(updateTimers, 1000);

        function openModal() {
            document.getElementById('modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }

        function openLogoutModal() {
            document.getElementById('logoutModal').classList.remove('hidden');
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
        }

        function openConflictModal() {
            document.getElementById('conflictModal').classList.remove('hidden');
        }
        function closeConflictModal() {
            document.getElementById('conflictModal').classList.add('hidden');
        }

    </script>
    <?php if (!empty($showConflictModal)): ?>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
        openConflictModal();
        
    });
    </script>
    <?php endif; ?>
</body>
</html>
