<?php
namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use App\Models\ContentBlockModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

class ContentBlockAction extends BaseAction
{
    private ContentBlockModel $model;
    private string $uploadRoot;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->model = new ContentBlockModel($this->conn);
        $this->uploadRoot = dirname(__DIR__, 3) . '/public/upload/blocks';
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'opanel/content-blocks/index.twig', [
            'title' => '頁面區塊管理',
            'apiBase' => '/opanel/content-blocks'
        ]);
    }

    public function apiList(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = max(1, min(100, (int)($params['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $filters = [
            'keyword' => $params['keyword'] ?? null,
            'group' => $params['group'] ?? null,
            'type' => $params['type'] ?? null,
            'status' => $params['status'] ?? null,
        ];

        $items = $this->model->paginate($filters, $limit, $offset);
        $total = $this->model->count($filters);
        $groups = $this->model->getGroups();

        return $this->respondJson($response, [
            'success' => true,
            'data' => $items,
            'groups' => $groups,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_items' => $total,
            ]
        ]);
    }

    public function apiCreate(Request $request, Response $response): Response
    {
        $data = $this->parseRequestData($request);

        if (empty($data['short_code'])) {
            return $this->respondJson($response, ['success' => false, 'message' => 'Short Code is required'], 400);
        }

        if ($this->model->shortCodeExists($data['short_code'])) {
            return $this->respondJson($response, ['success' => false, 'message' => 'Short Code 已經存在'], 400);
        }

        try {
            $id = $this->model->create($data);
            return $this->respondJson($response, ['success' => true, 'id' => $id]);
        } catch (\Exception $e) {
            return $this->respondJson($response, ['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiUpdate(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $data = $this->parseRequestData($request);

        // Check unique short_code (exclude self)
        if (!empty($data['short_code'])) {
            if ($this->model->shortCodeExists($data['short_code'], $id)) {
                return $this->respondJson($response, ['success' => false, 'message' => 'Short Code 已經存在'], 400);
            }
        }

        if ($this->model->update($id, $data)) {
            return $this->respondJson($response, ['success' => true]);
        }

        return $this->respondJson($response, ['success' => false, 'message' => 'Update failed'], 500);
    }

    public function apiDelete(Request $request, Response $response, array $args): Response
    {
        if ($this->model->delete((int)$args['id'])) {
            return $this->respondJson($response, ['success' => true]);
        }
        return $this->respondJson($response, ['success' => false, 'message' => 'Delete failed'], 500);
    }

    public function apiToggleStatus(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $item = $this->model->find($id);
        
        if (!$item) {
            return $this->respondJson($response, ['success' => false, 'message' => 'Not found'], 404);
        }

        $newStatus = $item['status'] == 1 ? 0 : 1;
        $this->model->update($id, ['status' => $newStatus]);
        
        return $this->respondJson($response, ['success' => true, 'new_status' => $newStatus]);
    }

    public function apiUpload(Request $request, Response $response): Response
    {
        $files = $request->getUploadedFiles();
        if (empty($files['file'])) {
            return $this->respondJson($response, ['success' => false, 'message' => 'No file uploaded'], 400);
        }

        /** @var UploadedFileInterface $file */
        $file = $files['file'];
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return $this->respondJson($response, ['success' => false, 'message' => 'Upload failed'], 500);
        }

        try {
            $url = $this->storeUploadedFile($file);
            return $this->respondJson($response, ['success' => true, 'url' => $url]);
        } catch (\Exception $e) {
            return $this->respondJson($response, ['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function parseRequestData(Request $request): array
    {
        $contentType = $request->getHeaderLine('Content-Type');
        
        if (strstr($contentType, 'application/json')) {
             $contents = json_decode(file_get_contents('php://input'), true);
             if (json_last_error() === JSON_ERROR_NONE) {
                 return $contents;
             }
        }
         
        // Fallback or explicit form data
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);
        return $data ?? (array)$request->getParsedBody();
    }

    private function storeUploadedFile(UploadedFileInterface $file): string
    {
        $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);
        $subDir = date('Y/m');
        
        $directory = $this->uploadRoot . '/' . $subDir;
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return '/upload/blocks/' . $subDir . '/' . $filename;
    }
}
