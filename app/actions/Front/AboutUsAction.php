<?php
declare(strict_types=1);

namespace App\Actions\Front;

use App\Actions\BaseAction;
use App\Models\CmsPostModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class AboutUsAction extends BaseAction
{
    private CmsPostModel $postModel;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->postModel = new CmsPostModel($this->conn);
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        // 取得所有 'history' 類型的已發布文章
        // 定義 filter
        $filters = [
            'status' => 'published',
            'type'   => 'history'
        ];

        // 這裡假設數量不會太多，一次取 100 筆應該足夠顯示時間軸
        $limit = 100; 
        
        $posts = $this->postModel->paginate($filters, $limit, 0);

        // CmsPostModel 預設是 DESC (新的在前)，但時間軸通常是從過去到現在 (ASC)
        // 這裡手動反轉陣列與 history.html 的 1990 -> 2000 -> 2003 順序一致
        $posts = array_reverse($posts);

        return $this->view->render($response, 'frontend/about/history.twig', [
            'posts' => $posts,
            'title' => '關於我們', // 頁面標題
        ]);
    }
}
