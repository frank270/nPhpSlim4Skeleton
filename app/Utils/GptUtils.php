<?php
namespace App\Utils;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class GptUtils
{
    /**
     * 呼叫 GPT API 處理提示詞
     *
     * @param string $prompt 主要提示詞內容
     * @param array $options 選項設定
     *   - model: GPT 模型 (預設: gpt-4o)
     *   - systemRole: 系統角色設定
     *   - temperature: 溫度參數 (預設: 0.7)
     *   - logger: 日誌記錄器
     * @return array|null 處理結果，失敗時返回 null
     */
    public static function callGptApi(string $prompt, array $options = []): ?array
    {
        // 預設選項
        $options = array_merge([
            'model' => 'gpt-4o',
            'systemRole' => '你是一個專業的助手',
            'temperature' => 0.7,
            'logger' => null,
        ], $options);
        
        // 記錄開始時間
        $startTime = microtime(true);
        self::log($options['logger'], 'info', 'GPT API 請求開始', [
            'start_time' => date('Y-m-d H:i:s')
        ]);
        
        // 記錄請求內容
        self::log($options['logger'], 'debug', 'GPT API 請求內容', [
            'prompt' => $prompt
        ]);
        
        try {
            // 建立 HTTP 客戶端
            $client = new Client();
            
            // 記錄 API 呼叫前時間
            $apiCallStartTime = microtime(true);
            self::log($options['logger'], 'info', 'GPT API 呼叫開始', [
                'api_call_start_time' => date('Y-m-d H:i:s')
            ]);
            
            // 呼叫 GPT API
            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $_ENV['OPENAI_API_KEY'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $options['model'],
                    'messages' => [
                        ['role' => 'system', 'content' => $options['systemRole']],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => $options['temperature'],
                ],
            ]);
            
            // 記錄 API 呼叫完成時間和耗時
            $apiCallEndTime = microtime(true);
            $apiCallDuration = $apiCallEndTime - $apiCallStartTime;
            self::log($options['logger'], 'info', 'GPT API 呼叫完成', [
                'api_call_end_time' => date('Y-m-d H:i:s'),
                'api_call_duration_seconds' => round($apiCallDuration, 2)
            ]);
            
            // 解析回應
            $result = json_decode($response->getBody()->getContents(), true);
            $content = $result['choices'][0]['message']['content'] ?? '';
            
            // 記錄回應內容
            self::log($options['logger'], 'debug', 'GPT API 回應內容', [
                'response_content' => $content
            ]);
            
            // 記錄完成時間和總耗時
            $endTime = microtime(true);
            $totalDuration = $endTime - $startTime;
            self::log($options['logger'], 'info', 'GPT API 處理完成', [
                'end_time' => date('Y-m-d H:i:s'),
                'total_duration_seconds' => round($totalDuration, 2)
            ]);
            
            return [
                'content' => $content,
                'duration' => round($totalDuration, 2),
                'api_duration' => round($apiCallDuration, 2)
            ];
            
        } catch (\Throwable $e) {
            self::log($options['logger'], 'error', 'GPT API 呼叫失敗', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * 記錄日誌
     */
    private static function log(?LoggerInterface $logger, string $level, string $message, array $context = []): void
    {
        if ($logger) {
            $logger->$level($message, $context);
        }
    }
}