<?php
namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use App\Models\MediaAssetsModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

class MediaAssetAction extends BaseAction
{
    private MediaAssetsModel $mediaAssets;
    private string $uploadRoot;
    private string $publicPrefix = '/upload';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->mediaAssets = new MediaAssetsModel($this->conn);
        $this->uploadRoot = dirname(__DIR__, 3) . '/public/upload';
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'opanel/media-assets/index.twig', [
            'title' => '媒體資源庫',
            'apiBase' => '/opanel/media-assets'
        ]);
    }

    public function apiList(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = max(1, min(100, (int)($params['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;

        $filters = [
            'status' => $params['status'] ?? null,
            'disk' => $params['disk'] ?? null,
            'keyword' => $params['keyword'] ?? null,
            'with_deleted' => filter_var($params['with_deleted'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'only_deleted' => filter_var($params['only_deleted'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];

        $items = array_map(function (array $asset) {
            return $this->transformAsset($asset);
        }, $this->mediaAssets->paginate($filters, $perPage, $offset));

        $total = $this->mediaAssets->count($filters);

        return $this->respondJson($response, [
            'success' => true,
            'data' => $items,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function apiGetOne(Request $request, Response $response, array $args): Response
    {
        $asset = $this->mediaAssets->findById((int) $args['id']);
        if (!$asset) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的媒體資產',
            ], 404);
        }

        return $this->respondJson($response, [
            'success' => true,
            'data' => $this->transformAsset($asset),
        ]);
    }

    public function apiUpload(Request $request, Response $response): Response
    {
        $files = $request->getUploadedFiles();
        if (empty($files['file'])) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '請選擇要上傳的檔案',
            ], 422);
        }

        /** @var UploadedFileInterface $file */
        $file = $files['file'];
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '檔案上傳失敗，請稍後再試',
            ], 422);
        }

        $postData = $request->getParsedBody() ?? [];
        $altText = isset($postData['alt_text']) ? trim((string)$postData['alt_text']) : null;
        $caption = isset($postData['caption']) ? trim((string)$postData['caption']) : null;
        $status = isset($postData['status']) ? trim((string)$postData['status']) : 'active';
        if (!in_array($status, ['draft', 'active', 'archived'], true)) {
            $status = 'active';
        }

        try {
            $this->ensureDirectory($this->uploadRoot);
            $stored = $this->storeUploadedFile($file);

            $assetUuid = $stored['uuid'];
            $id = $this->mediaAssets->create([
                'uuid' => $assetUuid,
                'disk' => $_ENV['MEDIA_DISK'] ?? 'public',
                'path' => $stored['relative_path'],
                'original_name' => $stored['original_name'],
                'mime_type' => $stored['mime_type'],
                'size_bytes' => $stored['size_bytes'],
                'width' => $stored['dimensions']['width'] ?? null,
                'height' => $stored['dimensions']['height'] ?? null,
                'alt_text' => $altText ?: null,
                'caption' => $caption ?: null,
                'status' => $status,
                'meta' => [
                    'extension' => $stored['extension'],
                    'uploaded_from' => $postData['uploaded_from'] ?? 'opanel',
                ],
            ]);

            $asset = $this->mediaAssets->findById($id);
            $this->logAction('新增媒體資產', '媒體資源庫', null, $asset);

            return $this->respondJson($response, [
                'success' => true,
                'message' => '媒體資產已上傳',
                'data' => $this->transformAsset($asset),
            ], 201);
        } catch (\Throwable $e) {
            $this->logger->error('媒體資產上傳失敗', [
                'error' => $e->getMessage(),
            ]);

            return $this->respondJson($response, [
                'success' => false,
                'message' => '媒體資產上傳失敗，請稍後再試',
            ], 500);
        }
    }

    public function apiUpdate(Request $request, Response $response, array $args): Response
    {
        $assetId = (int) $args['id'];
        $asset = $this->mediaAssets->findById($assetId);
        if (!$asset) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的媒體資產',
            ], 404);
        }

        $data = $this->parseRequestData($request);
        $update = [];

        if (array_key_exists('alt_text', $data)) {
            $update['alt_text'] = $data['alt_text'] !== null ? trim((string)$data['alt_text']) : null;
        }
        if (array_key_exists('caption', $data)) {
            $update['caption'] = $data['caption'] !== null ? trim((string)$data['caption']) : null;
        }
        if (array_key_exists('status', $data)) {
            $status = trim((string)$data['status']);
            if (in_array($status, ['draft', 'active', 'archived'], true)) {
                $update['status'] = $status;
            }
        }
        if (array_key_exists('meta', $data) && is_array($data['meta'])) {
            $update['meta'] = $data['meta'];
        }

        if (empty($update)) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '沒有任何可更新的欄位',
            ], 422);
        }

        $this->mediaAssets->update($assetId, $update);
        $updated = $this->mediaAssets->findById($assetId);
        $this->logAction('更新媒體資產', '媒體資源庫', $asset, $updated);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '媒體資產已更新',
            'data' => $this->transformAsset($updated),
        ]);
    }

    public function apiToggleStatus(Request $request, Response $response, array $args): Response
    {
        $assetId = (int) $args['id'];
        $asset = $this->mediaAssets->findById($assetId);
        if (!$asset) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的媒體資產',
            ], 404);
        }

        $newStatus = $asset['status'] === 'active' ? 'archived' : 'active';
        $payload = $this->parseRequestData($request);
        if (!empty($payload['status']) && in_array($payload['status'], ['draft', 'active', 'archived'], true)) {
            $newStatus = $payload['status'];
        }

        $this->mediaAssets->update($assetId, ['status' => $newStatus]);
        $updated = $this->mediaAssets->findById($assetId);
        $this->logAction('切換媒體資產狀態', '媒體資源庫', $asset, $updated);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '媒體資產狀態已更新',
            'data' => $this->transformAsset($updated),
        ]);
    }

    public function apiDelete(Request $request, Response $response, array $args): Response
    {
        $assetId = (int) $args['id'];
        $asset = $this->mediaAssets->findById($assetId);
        if (!$asset) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的媒體資產',
            ], 404);
        }

        $this->mediaAssets->softDelete($assetId);
        $this->logAction('刪除媒體資產', '媒體資源庫', $asset, null);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '媒體資產已標記為刪除',
        ]);
    }

    private function parseRequestData(Request $request): array
    {
        $data = $request->getParsedBody();
        if (is_array($data) && !empty($data)) {
            return $data;
        }

        $contentType = $request->getHeaderLine('Content-Type');
        if (stripos($contentType, 'application/json') !== false) {
            $payload = (string) $request->getBody();
            if ($payload !== '') {
                $decoded = json_decode($payload, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        return is_array($data) ? $data : [];
    }

    private function ensureDirectory(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('無法建立目錄：%s', $path));
        }
    }

    private function storeUploadedFile(UploadedFileInterface $file): array
    {
        $uuid = $this->generateUuid();
        $originalName = $file->getClientFilename() ?? $uuid;
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $subDirectory = date('Y/m');

        $targetDirectory = rtrim($this->uploadRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $subDirectory;
        $this->ensureDirectory($targetDirectory);

        $filename = $uuid . ($extension ? '.' . $extension : '');
        $absolutePath = $targetDirectory . DIRECTORY_SEPARATOR . $filename;
        $file->moveTo($absolutePath);

        $size = $file->getSize();
        if ($size === null || $size === 0) {
            $size = filesize($absolutePath);
        }

        $dimensions = null;
        $mimeType = $file->getClientMediaType() ?: mime_content_type($absolutePath);
        if ($mimeType && str_starts_with($mimeType, 'image/')) {
            $info = @getimagesize($absolutePath);
            if ($info) {
                $dimensions = [
                    'width' => $info[0],
                    'height' => $info[1],
                ];
            }
        }

        return [
            'uuid' => $uuid,
            'relative_path' => 'upload/' . $subDirectory . '/' . $filename,
            'original_name' => $originalName,
            'extension' => $extension,
            'mime_type' => $mimeType,
            'size_bytes' => (int) $size,
            'dimensions' => $dimensions ?? [],
        ];
    }

    private function transformAsset(array $asset): array
    {
        if (isset($asset['meta']) && is_string($asset['meta'])) {
            $decoded = json_decode($asset['meta'], true);
            $asset['meta'] = is_array($decoded) ? $decoded : null;
        }
        $asset['url'] = '/' . ltrim($asset['path'], '/');

        return $asset;
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
