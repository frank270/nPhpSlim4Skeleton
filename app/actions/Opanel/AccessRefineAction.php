<?php
namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use App\Utils\GptUtils;

class AccessRefineAction extends BaseAction
{
    public function handleGet(Request $request, Response $response): Response
    {
        return $this->displayRefinePage($response);
    }
    
    public function handlePost(Request $request, Response $response): Response
    {
        return $this->processPostRequest($request, $response);
    }
    
    private function displayRefinePage(Response $response): Response
    {
        $permissions = $this->conn->createQueryBuilder()
            ->select('id', 'code', 'name', 'controller', 'method', 'type', 'created_at')
            ->from('permissions_ctrl_func')
            ->orderBy('id', 'ASC')
            ->fetchAllAssociative();

        // 將權限資料轉為 JSON 格式
        $permissionsJson = json_encode($permissions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return $this->view->render($response, 'opanel/access/refine-names.twig', [
            'permissions' => $permissions,
            'permissionsJson' => $permissionsJson,
            'hasApiKey' => !empty($_ENV['OPENAI_API_KEY'])
        ]);
    }

    private function processPostRequest(Request $request, Response $response): Response
    {
        $action = $request->getParsedBody()['action'] ?? '';
        
        if ($action === 'update_from_json') {
            // 從JSON更新權限名稱
            $payload = $request->getParsedBody()['json'] ?? '';
            $items = json_decode($payload, true);
            return $this->updateFromJson($items, $response);
        } elseif ($action === 'generate_with_gpt') {
            // 使用GPT生成優化的名稱
            return $this->generateWithGpt($response);
        }
        
        // 預設返回錯誤訊息
        $this->flash->addMessage('error', '未知的操作類型');
        return $response
            ->withHeader('Location', '/opanel/access/refine-names')
            ->withStatus(302);
    }
    
    /**
     * 從JSON更新權限名稱
     */
    private function updateFromJson(array $items, Response $response): Response
    {
        $success = 0;
        $error = 0;

        if (is_array($items)) {
            foreach ($items as $item) {
                if (!isset($item['id'], $item['name'])) {
                    $error++;
                    continue;
                }

                try {
                    $this->conn->update('permissions_ctrl_func', [
                        'name' => $item['name'],
                    ], [
                        'id' => $item['id'],
                    ]);
                    $success++;
                } catch (\Throwable $e) {
                    $this->logger->error('權限名稱更新失敗', [
                        'id' => $item['id'],
                        'error' => $e->getMessage()
                    ]);
                    $error++;
                }
            }
        }

        $this->flash->addMessage('info', "更新完成：成功 $success 筆，失敗 $error 筆");

        return $response
            ->withHeader('Location', '/opanel/access/refine-names')
            ->withStatus(302);
    }
    
    /**
     * 使用GPT生成優化的名稱
     */
    private function generateWithGpt(Response $response): Response
    {
        try {
            // 檢查 API Key 是否存在
            if (empty($_ENV['OPENAI_API_KEY'])) {
                $this->flash->addMessage('error', '未設定 OpenAI API Key，請在 .env 檔案中設定 OPENAI_API_KEY');
                return $response
                    ->withHeader('Location', '/opanel/access/refine-names')
                    ->withStatus(302);
            }
            
            // 獲取選擇的權限ID
            $permissionIds = $_POST['permission_ids'] ?? [];
            
            // 如果沒有選擇任何權限，顯示提示訊息
            if (empty($permissionIds)) {
                $this->flash->addMessage('warning', '請至少選擇一個權限項目');
                return $response
                    ->withHeader('Location', '/opanel/access/refine-names')
                    ->withStatus(302);
            }
            
            $this->logger->info('選擇的權限 ID', ['ids' => $permissionIds]);
            
            // 從資料庫取得選擇的權限
            $placeholders = implode(',', array_fill(0, count($permissionIds), '?'));
            $sql = "SELECT id, code, name, controller, method FROM permissions_ctrl_func 
                    WHERE id IN ($placeholders) ORDER BY id ASC";
            $permissions = $this->conn->fetchAllAssociative($sql, $permissionIds);
            
            if (empty($permissions)) {
                $this->flash->addMessage('warning', '沒有找到需要優化的權限名稱');
                return $response
                    ->withHeader('Location', '/opanel/access/refine-names')
                    ->withStatus(302);
            }
            
            $this->logger->info('準備優化的權限數量', ['count' => count($permissions)]);
            
            // 呼叫GPT API
            $optimizedNames = $this->callGptApi($permissions);
            
            // 更新資料庫
            $success = 0;
            $error = 0;
            
            foreach ($optimizedNames as $item) {
                try {
                    $this->conn->update('permissions_ctrl_func', [
                        'name' => $item['optimized_name'],
                    ], [
                        'id' => $item['id'],
                    ]);
                    $success++;
                } catch (\Throwable $e) {
                    $this->logger->error('GPT優化權限名稱更新失敗', [
                        'id' => $item['id'],
                        'error' => $e->getMessage()
                    ]);
                    $error++;
                }
            }
            
            $this->flash->addMessage('success', "GPT優化完成：成功 $success 筆，失敗 $error 筆");
            
        } catch (\Throwable $e) {
            $this->logger->error('GPT API呼叫失敗', [
                'error' => $e->getMessage()
            ]);
            $this->flash->addMessage('error', 'GPT API呼叫失敗：' . $e->getMessage());
        }
        
        return $response
            ->withHeader('Location', '/opanel/access/refine-names')
            ->withStatus(302);
    }
    
    /**
     * 呼叫GPT API優化權限名稱
     */
    private function callGptApi(array $permissions): array
    {
        $this->logger->info('GPT API 請求開始', [
            'permissions_count' => count($permissions)
        ]);
        
        // 準備GPT API請求內容
        $prompt = "請優化以下PHP權限功能的名稱，使其更清晰易懂，但保持簡潔。每個名稱應該清楚表達該功能的用途。\n\n";
        $prompt .= "請注意：\n";
        $prompt .= "1. 必須使用正體中文為功能命名，不要使用英文或拼音\n";
        $prompt .= "2. 名稱應簡潔，不超過10個字\n";
        $prompt .= "3. 避免重複使用「功能」、「管理」等詞彙\n";
        $prompt .= "4. 避免與其他權限功能名稱過於雷同\n\n";
        
        $prompt .= "原始權限列表：\n";
        
        foreach ($permissions as $p) {
            $prompt .= "ID: {$p['id']}, 代碼: {$p['code']}, 名稱: {$p['name']}, 控制器: {$p['controller']}, 方法: {$p['method']}\n";
        }
        
        $prompt .= "\n請以JSON格式回傳優化後的名稱，格式為：[{\"id\": 數字, \"optimized_name\": \"優化後的名稱\"}]";
        
        // 使用 GptUtils 工具類別呼叫 GPT API
        $result = GptUtils::callGptApi($prompt, [
            'model' => 'gpt-4o',
            'systemRole' => '你是一個專業的程式命名專家，專長於優化功能名稱使其更清晰易懂。你必須使用正體中文（台灣版）為功能命名，不要使用英文或拼音。',
            'temperature' => 0.7,
            'logger' => $this->logger
        ]);
        
        if (!$result) {
            throw new \RuntimeException('GPT API 呼叫失敗');
        }
        
        // 從回應中提取JSON
        preg_match('/\[.*\]/s', $result['content'], $matches);
        $jsonContent = $matches[0] ?? '';
        
        // 解析JSON
        $optimizedNames = json_decode($jsonContent, true);
        
        if (!is_array($optimizedNames)) {
            throw new \RuntimeException('無法解析GPT回傳的JSON資料');
        }
        
        $this->logger->info('GPT 優化完成', [
            'optimized_names_count' => count($optimizedNames),
            'duration' => $result['duration']
        ]);
        
        return $optimizedNames;
    }
}