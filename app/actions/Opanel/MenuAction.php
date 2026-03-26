<?php
declare(strict_types=1);

namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use App\Models\MenuCategoryModel;
use App\Models\MenuItemModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class MenuAction extends BaseAction
{
    private MenuCategoryModel $categoryModel;
    private MenuItemModel $itemModel;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->categoryModel = new MenuCategoryModel($this->conn);
        $this->itemModel = new MenuItemModel($this->conn);
    }

    // --- Page Rendering ---

    public function pageCategories(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'opanel/menu/categories.twig', [
            'title' => '菜單分類管理',
            'apiBase' => '/opanel/menu/categories'
        ]);
    }

    public function pageItems(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'opanel/menu/items.twig', [
            'title' => '菜單商品管理',
            'apiBase' => '/opanel/menu/items',
            'apiCategoryBase' => '/opanel/menu/categories', // For dropdowns
        ]);
    }

    // --- Category Actions ---

    public function listCategories(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = isset($params['limit']) ? (int)$params['limit'] : 20;
        $offset = ($page - 1) * $limit;

        $filters = [
            'keyword' => $params['keyword'] ?? null,
        ];

        $items = $this->categoryModel->paginate($filters, $limit, $offset);
        $total = $this->categoryModel->count($filters);

        return $this->respondJson($response, [
            'success' => true,
            'data' => $items,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit),
            ],
        ]);
    }

    public function getCategory(Request $request, Response $response, array $args): Response
    {
        $category = $this->categoryModel->findById((int)$args['id']);
        if (!$category) {
            return $this->respondJson($response, ['success' => false, 'message' => 'Category not found'], 404);
        }
        return $this->respondJson($response, ['success' => true, 'data' => $category]);
    }

    public function createCategory(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        // DEBUG: Check what's received
        error_log('DEBUG MenuAction createCategory: ParsedBody: ' . print_r($data, true));
        error_log('DEBUG MenuAction createCategory: $_POST: ' . print_r($_POST, true));
        error_log('DEBUG MenuAction createCategory: Content-Type: ' . $request->getHeaderLine('Content-Type'));
        // Validation (Basic)
        if (empty($data['name']) || empty($data['slug'])) {
            return $this->respondJson($response, ['success' => false, 'message' => 'Name and Slug are required'], 400);
        }

        try {
            $id = $this->categoryModel->create($data);
            return $this->respondJson($response, ['success' => true, 'data' => ['id' => $id]]);
        } catch (\Exception $e) {
            return $this->respondJson($response, ['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateCategory(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        try {
            $updated = $this->categoryModel->update((int)$args['id'], $data);
            if (!$updated) {
                 return $this->respondJson($response, ['success' => false, 'message' => 'No changes made or record not found']);
            }
            return $this->respondJson($response, ['success' => true]);
        } catch (\Exception $e) {
             return $this->respondJson($response, ['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteCategory(Request $request, Response $response, array $args): Response
    {
        try {
            $deleted = $this->categoryModel->delete((int)$args['id']);
             if (!$deleted) {
                 return $this->respondJson($response, ['success' => false, 'message' => 'Record not found']);
            }
            return $this->respondJson($response, ['success' => true]);
        } catch (\Exception $e) {
             return $this->respondJson($response, ['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // --- Item Actions ---

    public function listItems(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = isset($params['limit']) ? (int)$params['limit'] : 20;
        $offset = ($page - 1) * $limit;

        $filters = [
            'keyword' => $params['keyword'] ?? null,
            'category_id' => $params['category_id'] ?? null,
            'status' => $params['status'] ?? null,
        ];

        $items = $this->itemModel->paginate($filters, $limit, $offset);
        $total = $this->itemModel->count($filters);

        return $this->respondJson($response, [
            'success' => true,
            'data' => $items,
             'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit),
            ],
        ]);
    }

    public function getItem(Request $request, Response $response, array $args): Response
    {
        $item = $this->itemModel->findById((int)$args['id']);
        if (!$item) {
             return $this->respondJson($response, ['success' => false, 'message' => 'Item not found'], 404);
        }
        return $this->respondJson($response, ['success' => true, 'data' => $item]);
    }

    public function createItem(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        if (empty($data['name']) || empty($data['category_id'])) {
             return $this->respondJson($response, ['success' => false, 'message' => 'Name and Category are required'], 400);
        }

        // Sanitize price_sale
        if (isset($data['price_sale']) && $data['price_sale'] === '') {
            $data['price_sale'] = null;
        }

        try {
            $id = $this->itemModel->create($data);
            return $this->respondJson($response, ['success' => true, 'data' => ['id' => $id]]);
        } catch (\Exception $e) {
            return $this->respondJson($response, ['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateItem(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        
        // Sanitize price_sale
        if (isset($data['price_sale']) && $data['price_sale'] === '') {
            $data['price_sale'] = null;
        }

        try {
            $updated = $this->itemModel->update((int)$args['id'], $data);
            if (!$updated) {
                 return $this->respondJson($response, ['success' => false, 'message' => 'No changes made or record not found']);
            }
            return $this->respondJson($response, ['success' => true]);
        } catch (\Exception $e) {
             return $this->respondJson($response, ['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteItem(Request $request, Response $response, array $args): Response
    {
        try {
            $deleted = $this->itemModel->delete((int)$args['id']);
            if (!$deleted) {
                 return $this->respondJson($response, ['success' => false, 'message' => 'Record not found']);
            }
            return $this->respondJson($response, ['success' => true]);
        } catch (\Exception $e) {
             return $this->respondJson($response, ['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAllCategories(Request $request, Response $response): Response
    {
        $categories = $this->categoryModel->getAll();
        return $this->respondJson($response, ['success' => true, 'data' => $categories]);
    }

    public function batchUploadCsv(Request $request, Response $response): Response
    {
        $uploadedFiles = $request->getUploadedFiles();
        if (empty($uploadedFiles['file'])) {
            return $this->respondJson($response, ['success' => false, 'message' => 'No file uploaded'], 400);
        }

        $file = $uploadedFiles['file'];
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return $this->respondJson($response, ['success' => false, 'message' => 'File upload error: ' . $file->getError()], 400);
        }

        $stream = $file->getStream();
        $stream->rewind();
        
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $stream->getContents());
        rewind($handle);

        $isHeader = true;
        $headers = [];
        $added = 0;
        $updated = 0;
        $errors = [];
        $row = 1;

        while (($data = fgetcsv($handle)) !== false) {
            if ($isHeader) {
                // remove BOM if present and trim
                $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0]);
                $headers = array_map('trim', $data);
                $isHeader = false;
                continue;
            }
            $row++;
            
            $rowData = [];
            foreach ($headers as $index => $header) {
                $rowData[$header] = isset($data[$index]) ? trim($data[$index]) : null;
            }

            $itemCode = $rowData['item_code'] ?? null;
            $name = $rowData['name'] ?? null;
            $categoryId = $rowData['category_id'] ?? null;

            if (!$itemCode || !$name || !$categoryId) {
                $errors[] = "Row {$row}: Missing required fields (item_code, name, category_id)";
                continue;
            }

            $existing = $this->itemModel->findByItemCode($itemCode);
            
            $saveData = [
                'item_code' => $itemCode,
                'name' => $name,
                'category_id' => $categoryId,
                'description' => $rowData['description'] ?? null,
                'price_original' => $rowData['price_original'] ?? 0,
                'price_sale' => (isset($rowData['price_sale']) && $rowData['price_sale'] !== '') ? $rowData['price_sale'] : null,
                'tags' => $rowData['tags'] ?? null,
                'status' => $rowData['status'] ?? 'draft',
                'is_best_seller' => isset($rowData['is_best_seller']) && $rowData['is_best_seller'] === '1' ? 1 : 0,
            ];

            try {
                if ($existing) {
                    $this->itemModel->update($existing['id'], $saveData);
                    $updated++;
                } else {
                    $this->itemModel->create($saveData);
                    $added++;
                }
            } catch (\Exception $e) {
                $errors[] = "Row {$row}: DB Error -> " . $e->getMessage();
            }
        }
        fclose($handle);

        return $this->respondJson($response, [
            'success' => true,
            'data' => [
                'added' => $added,
                'updated' => $updated,
                'errors' => $errors
            ]
        ]);
    }

    public function batchUploadZip(Request $request, Response $response): Response
    {
        $uploadedFiles = $request->getUploadedFiles();
        if (empty($uploadedFiles['file'])) {
            return $this->respondJson($response, ['success' => false, 'message' => 'No file uploaded'], 400);
        }

        $file = $uploadedFiles['file'];
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return $this->respondJson($response, ['success' => false, 'message' => 'File upload error'], 400);
        }

        $tempBase = sys_get_temp_dir();
        $tempDir = $tempBase . '/menu_zip_' . time() . '_' . rand(100, 999);
        mkdir($tempDir, 0777, true);
        
        $tempZip = $tempDir . '/upload.zip';
        $file->moveTo($tempZip);

        $zip = new \ZipArchive;
        if ($zip->open($tempZip) === true) {
            $extractDir = $tempDir . '/extracted';
            mkdir($extractDir, 0777, true);
            $zip->extractTo($extractDir);
            $zip->close();
            
            $matched = 0;
            $unmatched = [];

            // Identify the correct public upload directory
            $basePublic = realpath(__DIR__ . '/../../../public');
            if (!$basePublic) {
                $basePublic = '/Users/n/Projects/1fBreakFast/www/public';
            }
            $menuUploadDir = $basePublic . '/upload/menu';
            if (!is_dir($menuUploadDir)) {
                mkdir($menuUploadDir, 0777, true);
            }

            // Iterate over all extracted files (including inside subdirectories)
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($extractDir));
            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile() && !in_array($fileInfo->getFilename(), ['.DS_Store', '__MACOSX'])) {
                    // Filter out macOS hidden files completely if any slipped through
                    if (strpos($fileInfo->getPathname(), '__MACOSX') !== false || substr($fileInfo->getFilename(), 0, 2) === '._') {
                        continue;
                    }

                    $ext = strtolower($fileInfo->getExtension());
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $itemCode = pathinfo($fileInfo->getFilename(), PATHINFO_FILENAME);
                        
                        $existing = $this->itemModel->findByItemCode($itemCode);
                        if ($existing) {
                            $newFilename = $itemCode . '_' . time() . '.' . $ext;
                            $destination = $menuUploadDir . '/' . $newFilename;
                            
                            // move file
                            copy($fileInfo->getPathname(), $destination);
                            
                            // update DB
                            $imagePath = '/upload/menu/' . $newFilename;
                            $this->itemModel->update((int)$existing['id'], ['image_path' => $imagePath]);
                            $matched++;
                        } else {
                            $unmatched[] = $fileInfo->getFilename();
                        }
                    }
                }
            }
            
            // Clean up temp
            $this->rrmdir($tempDir);

            return $this->respondJson($response, [
                'success' => true,
                'data' => [
                    'matched' => $matched,
                    'unmatched' => $unmatched
                ]
            ]);

        } else {
             $this->rrmdir($tempDir);
             return $this->respondJson($response, ['success' => false, 'message' => 'Failed to open ZIP file'], 400);
        }
    }

    private function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                        $this->rrmdir($dir. DIRECTORY_SEPARATOR .$object);
                    else
                        unlink($dir. DIRECTORY_SEPARATOR .$object);
                }
            }
            rmdir($dir);
        }
    }

    public function downloadTemplate(Request $request, Response $response): Response
    {
        // Add BOM for Excel compatibility with UTF-8
        $bom = "\xEF\xBB\xBF";
        $csvData = $bom . "item_code,category_id,name,description,price_original,price_sale,tags,is_best_seller,status\n";
        $csvData .= "M001,1,招牌總匯三明治,美味豐富的總匯,85,80,推薦,1,published\n";
        $csvData .= "M002,2,大杯冰紅茶,,25,,涼爽,0,published\n";
        
        $response->getBody()->write($csvData);
        return $response
            ->withHeader('Content-Type', 'text/csv; charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment; filename="menu_template.csv"');
    }
}
