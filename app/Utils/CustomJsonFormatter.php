<?php
declare(strict_types=1);

namespace App\Utils;

use Monolog\Formatter\FormatterInterface;

class CustomJsonFormatter implements FormatterInterface
{
    /**
     * 格式化日誌記錄
     *
     * @param array $record 日誌記錄
     * @return string
     */
    public function format(array $record): string
    {
        // 取得基本資訊
        $timestamp = date('Y-m-d H:i:s');
        $userId = $record['extra']['user_id'] ?? 0;
        $username = $record['extra']['username'] ?? 'system';
        $action = $record['context']['action'] ?? '';
        $target = $record['context']['target'] ?? '';
        $oldData = $record['context']['old_data'] ?? null;
        $newData = $record['context']['new_data'] ?? null;
        $memo = $record['context']['memo'] ?? '';
        
        // 建立標準格式的日誌
        $logData = [
            'timestamp' => $timestamp,
            'user_id' => $userId,
            'username' => $username,
            'action' => $action,
            'target' => $target,
            'old_data' => $oldData,
            'new_data' => $newData,
            'memo' => $memo,
            'ip_address' => $record['extra']['ip_address'] ?? '0.0.0.0',
            'user_agent' => $record['extra']['user_agent'] ?? 'Unknown',
            'request_id' => $record['extra']['request_id'] ?? uniqid(),
        ];
        
        // 轉換為 JSON 格式
        return json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    }
    
    /**
     * 格式化批次日誌記錄
     *
     * @param array $records 日誌記錄陣列
     * @return string
     */
    public function formatBatch(array $records): string
    {
        $formatted = '';
        foreach ($records as $record) {
            $formatted .= $this->format($record);
        }
        
        return $formatted;
    }
}
