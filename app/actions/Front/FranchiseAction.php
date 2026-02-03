<?php
declare(strict_types=1);

namespace App\Actions\Front;

use App\Actions\BaseAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FranchiseAction extends BaseAction
{
    public function index(Request $request, Response $response, array $args): Response
    {
        // 產生簡單數學驗證碼
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        $_SESSION['captcha_answer'] = $num1 + $num2;

        return $this->view->render($response, 'frontend/franchise/index.twig', [
            'title' => '加盟合作',
            'captcha_question' => "{$num1} + {$num2} = ?",
        ]);
    }
    public function submit(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? '';
        $subject = $data['subject'] ?? 'Franchise Inquiry';
        $messageBody = $data['message'] ?? '';
        $captcha = $data['captcha'] ?? '';

        // Simple validation
        if (empty($name) || empty($email) || empty($phone)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => '請填寫所有必填欄位']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // 驗證碼檢查
        if (!isset($_SESSION['captcha_answer']) || intval($captcha) !== $_SESSION['captcha_answer']) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => '驗證碼錯誤，請重新計算']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $settings = $this->container->get('settings')['smtp'];
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $settings['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $settings['username'];
            $mail->Password   = $settings['password'];
            $mail->SMTPSecure = $settings['secure'];
            $mail->Port       = $settings['port'];
            
            // CharSet
            $mail->CharSet = 'UTF-8';

            // Recipients
            $mail->setFrom($settings['from_email'], $settings['from_name']);
            $mail->addAddress($settings['from_email']); // Send to admin
            $mail->addBCC('1fbreakfastsrever@gmail.com'); // Add BCC
            $mail->addReplyTo($email, $name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "[加盟諮詢] " . $subject;
            $mail->Body    = "
                <h3>新加盟諮詢</h3>
                <p><strong>姓名:</strong> {$name}</p>
                <p><strong>信箱:</strong> {$email}</p>
                <p><strong>電話:</strong> {$phone}</p>
                <p><strong>主旨:</strong> {$subject}</p>
                <p><strong>訊息/備註:</strong><br>{$messageBody}</p>
            ";
            $mail->AltBody = "姓名: {$name}\n信箱: {$email}\n電話: {$phone}\n主旨: {$subject}\n備註:\n{$messageBody}";

            $mail->send();
            
            $response->getBody()->write(json_encode(['success' => true, 'message' => '訊息已發送，專人將盡快與您聯繫！']));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error("Mail Error: {$mail->ErrorInfo}");
            $response->getBody()->write(json_encode(['success' => false, 'message' => '發送失敗，請稍後再試或直接來電。']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
