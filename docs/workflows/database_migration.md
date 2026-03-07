---
description: 如何安全地建立與執行資料庫遷移 (Migrations)
---

本流程說明在 Docker 環境中執行資料庫遷移的強制規範。

**關鍵規則：** 所有遷移指令**必須**在 `slim_app` 容器內執行。**嚴禁**在主機端 (Host Machine) 直接執行。

### 1. 檢查資料庫連線
確認容器正在運行且資料庫可供存取。

```bash
docker compose ps
```

### 2. 建立新的遷移檔案
在 `migrations/` 目錄下產生一個空白的遷移檔案。

```bash
docker exec -it slim_app vendor/bin/doctrine-migrations generate
```
*註：請使用 `slim_app` (容器名稱) 或 `docker compose exec app` (服務名稱)。*

### 3. 編輯遷移檔案
在 `migrations/VersionXXXXXXXXXXXXXX.php` 找到新建立的檔案。
編輯 `up()` 與 `down()` 方法來定義您的 SQL 架構變更。

**`up()` 範例：**
```php
public function up(Schema $schema): void
{
    $table = $schema->createTable('new_table');
    $table->addColumn('id', 'integer', ['autoincrement' => true]);
    $table->addColumn('name', 'string', ['length' => 100]);
    $table->setPrimaryKey(['id']);
}
```

### 4. 執行遷移 (Execute)
執行遷移以將變更套用到資料庫。

// turbo
```bash
docker exec -it slim_app vendor/bin/doctrine-migrations migrate --no-interaction
```

### 5. 驗證狀態
檢查遷移是否成功套用。

```bash
docker exec -it slim_app vendor/bin/doctrine-migrations status
```

### 6. (可選) 復原 (Rollback)
如果發生錯誤，可復原至上一個版本。

```bash
docker exec -it slim_app vendor/bin/doctrine-migrations migrate prev
```
