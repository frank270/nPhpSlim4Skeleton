<?php
declare(strict_types=1);

namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class AuthAction extends BaseAction
{
    public function showLogin(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'Opanel/login.twig');
    }
    public function login(Request $request, Response $response): Response
    {
        $params = (array) $request->getParsedBody();
        $username = trim($params['account'] ?? '');
        $password = trim($params['password'] ?? '');

        // 驗證欄位
        if ($username === '' || $password === '') {
            $this->flash->addMessage('info', '請輸入帳號與密碼');
            return $response->withHeader('Location', '/opanel/login')->withStatus(302);
        }

        // 查詢帳號
        $user = $this->conn->fetchAssociative(
            'SELECT * FROM admin_users WHERE username = ? LIMIT 1',
            [$username]
        );
        //var_dump($user);
        //var_dump( $params);
        //var_dump(password_verify('test1234', '$2y$10$WZtLMrV1v2rbQbiHhzzuqOAKdTr2JZzKNCpZYm89X6FZuMFTQcvce')); // 從資料庫複製 hash

        //var_dump(password_verify($password, $user['password_hash']));
        if (!$user || !password_verify($password, $user['password_hash'])) {
            error_log('🚫 登入失敗，觸發 flash');
            $this->flash->addMessage('danger', '帳號或密碼錯誤');
            return $response->withHeader('Location', '/opanel/login')->withStatus(302);
        }

        // 登入成功，記錄 session
        $_SESSION['opanel_user'] = [
            'id'            => $user['id'],
            'username'      => $user['username'],
            'display_name'  => $user['display_name'],
            'group_id'      => $user['group_id'],
            'login_time'    => date('Y-m-d H:i:s'),
        ];

        // 更新登入時間
        $this->conn->update('admin_users', [
            'last_login_at' => date('Y-m-d H:i:s'),
        ], ['id' => $user['id']]);

        // 導向後台首頁（可替換成 dashboard）
        return $response->withHeader('Location', '/opanel/dashboard')->withStatus(302);
    }
    public function logout(Request $request, Response $response): Response
    {
        unset($_SESSION['opanel_user']);
        $this->flash->addMessage('success', '已登出');
        // 添加日誌以便追蹤
    $this->logger->info('用戶登出', [
        'flash_messages' => $this->flash->getMessages()
    ]);
        return $response->withHeader('Location', '/opanel/login')->withStatus(302);
    }

}