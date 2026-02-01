<?php
declare(strict_types=1);

namespace App\Actions\Front;

use App\Actions\BaseAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ContactAction extends BaseAction
{
    public function index(Request $request, Response $response, array $args): Response
    {
        // 產生簡單數學驗證碼
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        $_SESSION['captcha_answer'] = $num1 + $num2;
        
        return $this->view->render($response, 'frontend/contact/index.twig', [
            'captcha_question' => "{$num1} + {$num2} = ?",
        ]);
    }

    public function submit(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? '';
        $subject = $data['subject'] ?? 'Contact Form Submission';
        $messageBody = $data['message'] ?? '';
        $captcha = $data['captcha'] ?? '';

        if (empty($name) || empty($email) || empty($messageBody)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => '請填寫所有必填欄位']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // 驗證碼檢查
        if (!isset($_SESSION['captcha_answer']) || intval($captcha) !== $_SESSION['captcha_answer']) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => '驗證碼錯誤，請重新計算']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // 驗證成功後，清除 (但考慮到 AJAX 失敗可能要重試，這裡可以選擇是否清除，或是在成功寄出後清除)
        // 策略: 若只要防機器人，保持 Session 直到過期也行，但為了安全最好更新
        // 這裡暫不清除，讓使用者如果信件發送失敗還可以重試，不需要重新刷頁面看新題目

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
            $mail->addReplyTo($email, $name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "[官網聯絡] " . $subject;
            $mail->Body    = "
                <h3>新聯絡表單訊息</h3>
                <p><strong>姓名:</strong> {$name}</p>
                <p><strong>信箱:</strong> {$email}</p>
                <p><strong>電話:</strong> {$phone}</p>
                <p><strong>主旨:</strong> {$subject}</p>
                <p><strong>訊息:</strong><br>{$messageBody}</p>
            ";
            $mail->AltBody = "姓名: {$name}\n信箱: {$email}\n電話: {$phone}\n主旨: {$subject}\n訊息:\n{$messageBody}";

            $mail->send();
            
            $response->getBody()->write(json_encode(['success' => true, 'message' => '訊息已發送，我們將盡快聯繫您！']));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $this->logger->error("Mail Error: {$mail->ErrorInfo}");
            $response->getBody()->write(json_encode(['success' => false, 'message' => '發送失敗，請稍後再試或直接來電。']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }


}
