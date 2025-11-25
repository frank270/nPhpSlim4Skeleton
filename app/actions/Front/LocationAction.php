<?php
declare(strict_types=1);

namespace App\Actions\Front;

use App\Actions\BaseAction;
use App\Models\LocationStoresModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class LocationAction extends BaseAction
{
    private LocationStoresModel $storeModel;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->storeModel = new LocationStoresModel($this->conn);
    }

    /**
     * 取得前台門市列表（支援篩選）
     */
    public function apiList(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        
        // 前台強制只顯示開啟的門市
        $filters = [
            'county' => $params['county'] ?? null,
            'keyword' => $params['keyword'] ?? null,
            'status' => 'open',
        ];

        // 取得所有符合條件的門市（前台通常不分頁，或者分頁數較大）
        // 這裡設定較大的 limit 以顯示所有門市，或依需求調整
        $items = $this->storeModel->paginate($filters, 1000, 0);

        return $this->respondJson($response, [
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * 取得單一門市詳情
     */
    public function apiGetOne(Request $request, Response $response, array $args): Response
    {
        $store = $this->storeModel->findById((int)$args['id']);
        
        // 檢查是否存在且狀態為 open
        if (!$store || $store['status'] !== 'open') {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的門市',
            ], 404);
        }

        return $this->respondJson($response, [
            'success' => true,
            'data' => $store,
        ]);
    }

    /**
     * 取得有門市的縣市列表
     */
    public function apiGetCounties(Request $request, Response $response): Response
    {
        // 這裡可以考慮只回傳有 "open" 門市的縣市，目前 Model 的 getCounties 是回傳所有
        // 若需精確，可修改 Model 或在此過濾。暫時沿用 Model 邏輯。
        $counties = $this->storeModel->getCounties();

        return $this->respondJson($response, [
            'success' => true,
            'data' => $counties,
        ]);
    }
}
