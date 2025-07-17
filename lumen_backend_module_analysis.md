# 后端模块拆分方案 (Lumen + Symfony架构)

## 功能清单分析

### 核心业务模块识别

从功能清单中识别出以下核心业务领域：

1. **用户管理** - 用户信息、权限、角色管理
2. **内容管理** - 信息发布、分类、标签管理
3. **文档管理** - 文档上传、下载、版本管理
4. **审核流程** - 审核、驳回、流程管理
5. **系统管理** - 配置、日志、监控
6. **通知管理** - 消息推送、通知设置
7. **统计分析** - 数据统计、报表生成

## 后端模块架构设计

### 1. 认证授权模块 (Auth Module)

#### 功能范围
- 用户登录/登出
- JWT Token管理
- 权限验证
- 角色管理
- 密码重置

#### 数据库设计
```sql
-- 用户表
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    status TINYINT DEFAULT 1 COMMENT '1:正常 2:禁用',
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 角色表
CREATE TABLE roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 权限表
CREATE TABLE permissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    module VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 用户角色关联表
CREATE TABLE user_roles (
    user_id BIGINT,
    role_id BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role_id)
);

-- 角色权限关联表
CREATE TABLE role_permissions (
    role_id BIGINT,
    permission_id BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role_id, permission_id)
);
```

#### 核心类结构
```php
// app/Models/User.php
class User extends Model
{
    protected $fillable = ['username', 'email', 'password_hash', 'phone', 'status'];
    
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }
    
    public function hasPermission($permission)
    {
        return $this->roles()->whereHas('permissions', function($query) use ($permission) {
            $query->where('name', $permission);
        })->exists();
    }
}

// app/Services/AuthService.php
class AuthService
{
    public function login(array $credentials): array
    public function logout(string $token): bool
    public function refreshToken(string $token): array
    public function resetPassword(string $email): bool
}

// app/Http/Controllers/AuthController.php
class AuthController extends Controller
{
    public function login(Request $request)
    public function logout(Request $request)
    public function refresh(Request $request)
    public function profile(Request $request)
}
```

#### API接口设计
```php
// routes/auth.php
$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('login', 'AuthController@login');
    $router->post('logout', 'AuthController@logout');
    $router->post('refresh', 'AuthController@refresh');
    $router->get('profile', 'AuthController@profile');
    $router->post('reset-password', 'AuthController@resetPassword');
});
```

#### 工作量评估
- **数据库设计**: 1天
- **Model层开发**: 2天
- **Service层开发**: 3天
- **Controller层开发**: 2天
- **中间件开发**: 2天
- **单元测试**: 2天
- **总计**: 12天

---

### 2. 用户管理模块 (User Management Module)

#### 功能范围
- 用户CRUD操作
- 用户资料管理
- 批量导入/导出
- 用户状态管理
- 角色分配

#### 数据库设计
```sql
-- 用户详细信息表
CREATE TABLE user_profiles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,
    real_name VARCHAR(100),
    avatar VARCHAR(255),
    department VARCHAR(100),
    position VARCHAR(100),
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 用户设置表
CREATE TABLE user_settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,
    theme VARCHAR(20) DEFAULT 'light',
    language VARCHAR(10) DEFAULT 'zh-CN',
    timezone VARCHAR(50) DEFAULT 'Asia/Shanghai',
    notification_enabled BOOLEAN DEFAULT TRUE,
    email_notification BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### 核心类结构
```php
// app/Services/UserService.php
class UserService
{
    public function getUsers(array $filters, int $page = 1, int $limit = 10): array
    public function createUser(array $data): User
    public function updateUser(int $id, array $data): User
    public function deleteUser(int $id): bool
    public function batchImport(string $filePath): array
    public function exportUsers(array $filters): string
    public function assignRoles(int $userId, array $roleIds): bool
}

// app/Http/Controllers/UserController.php
class UserController extends Controller
{
    public function index(Request $request)
    public function store(Request $request)
    public function show(int $id)
    public function update(Request $request, int $id)
    public function destroy(int $id)
    public function batchImport(Request $request)
    public function export(Request $request)
}
```

#### API接口设计
```php
// routes/users.php
$router->group(['prefix' => 'users', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/', 'UserController@index');
    $router->post('/', 'UserController@store');
    $router->get('/{id}', 'UserController@show');
    $router->put('/{id}', 'UserController@update');
    $router->delete('/{id}', 'UserController@destroy');
    $router->post('/batch-import', 'UserController@batchImport');
    $router->get('/export', 'UserController@export');
    $router->post('/{id}/assign-roles', 'UserController@assignRoles');
});
```

#### 工作量评估
- **数据库设计**: 1天
- **Model层开发**: 2天
- **Service层开发**: 4天
- **Controller层开发**: 3天
- **导入导出功能**: 3天
- **单元测试**: 3天
- **总计**: 16天

---

### 3. 内容管理模块 (Content Management Module)

#### 功能范围
- 信息发布管理
- 分类管理
- 标签管理
- 内容审核
- 版本控制

#### 数据库设计
```sql
-- 分类表
CREATE TABLE categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    parent_id BIGINT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id)
);

-- 内容表
CREATE TABLE contents (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content LONGTEXT,
    summary TEXT,
    category_id BIGINT,
    author_id BIGINT NOT NULL,
    status TINYINT DEFAULT 1 COMMENT '1:草稿 2:待审核 3:已发布 4:已下线',
    publish_at TIMESTAMP NULL,
    view_count INT DEFAULT 0,
    like_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (author_id) REFERENCES users(id)
);

-- 标签表
CREATE TABLE tags (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    color VARCHAR(7) DEFAULT '#1890ff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 内容标签关联表
CREATE TABLE content_tags (
    content_id BIGINT,
    tag_id BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (content_id, tag_id)
);

-- 内容审核表
CREATE TABLE content_audits (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    content_id BIGINT NOT NULL,
    auditor_id BIGINT NOT NULL,
    status TINYINT NOT NULL COMMENT '1:通过 2:驳回',
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (content_id) REFERENCES contents(id),
    FOREIGN KEY (auditor_id) REFERENCES users(id)
);
```

#### 核心类结构
```php
// app/Services/ContentService.php
class ContentService
{
    public function getContents(array $filters, int $page = 1, int $limit = 10): array
    public function createContent(array $data): Content
    public function updateContent(int $id, array $data): Content
    public function deleteContent(int $id): bool
    public function publishContent(int $id): bool
    public function auditContent(int $id, int $auditorId, int $status, string $reason = ''): bool
    public function getContentsByCategory(int $categoryId): array
}

// app/Services/CategoryService.php
class CategoryService
{
    public function getCategories(): array
    public function getCategoryTree(): array
    public function createCategory(array $data): Category
    public function updateCategory(int $id, array $data): Category
    public function deleteCategory(int $id): bool
}
```

#### API接口设计
```php
// routes/content.php
$router->group(['prefix' => 'contents', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/', 'ContentController@index');
    $router->post('/', 'ContentController@store');
    $router->get('/{id}', 'ContentController@show');
    $router->put('/{id}', 'ContentController@update');
    $router->delete('/{id}', 'ContentController@destroy');
    $router->post('/{id}/publish', 'ContentController@publish');
    $router->post('/{id}/audit', 'ContentController@audit');
});

$router->group(['prefix' => 'categories', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/', 'CategoryController@index');
    $router->get('/tree', 'CategoryController@tree');
    $router->post('/', 'CategoryController@store');
    $router->put('/{id}', 'CategoryController@update');
    $router->delete('/{id}', 'CategoryController@destroy');
});
```

#### 工作量评估
- **数据库设计**: 2天
- **Model层开发**: 3天
- **Service层开发**: 5天
- **Controller层开发**: 4天
- **审核流程**: 3天
- **单元测试**: 4天
- **总计**: 21天

---

### 4. 文档管理模块 (Document Management Module)

#### 功能范围
- 文档上传/下载
- 文档分类管理
- 版本控制
- 文档预览
- 批量操作

#### 数据库设计
```sql
-- 文档分类表
CREATE TABLE document_categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    allowed_extensions TEXT,
    max_file_size BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 文档表
CREATE TABLE documents (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    original_name VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT NOT NULL,
    mime_type VARCHAR(100),
    file_hash VARCHAR(64),
    category_id BIGINT,
    uploader_id BIGINT NOT NULL,
    download_count INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    version VARCHAR(20) DEFAULT '1.0',
    parent_id BIGINT NULL COMMENT '父文档ID，用于版本控制',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES document_categories(id),
    FOREIGN KEY (uploader_id) REFERENCES users(id),
    FOREIGN KEY (parent_id) REFERENCES documents(id)
);

-- 文档权限表
CREATE TABLE document_permissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    document_id BIGINT NOT NULL,
    user_id BIGINT NULL,
    role_id BIGINT NULL,
    permission_type TINYINT NOT NULL COMMENT '1:查看 2:下载 3:编辑',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (role_id) REFERENCES roles(id)
);
```

#### 核心类结构
```php
// app/Services/DocumentService.php
class DocumentService
{
    public function uploadDocument(array $fileData, int $categoryId, int $uploaderId): Document
    public function downloadDocument(int $id, int $userId): string
    public function getDocuments(array $filters, int $page = 1, int $limit = 10): array
    public function deleteDocument(int $id): bool
    public function createNewVersion(int $parentId, array $fileData): Document
    public function getDocumentVersions(int $documentId): array
    public function setDocumentPermissions(int $documentId, array $permissions): bool
}

// app/Services/FileStorageService.php
class FileStorageService
{
    public function store(UploadedFile $file, string $path): string
    public function delete(string $path): bool
    public function getUrl(string $path): string
    public function getFileInfo(string $path): array
}
```

#### API接口设计
```php
// routes/documents.php
$router->group(['prefix' => 'documents', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/', 'DocumentController@index');
    $router->post('/upload', 'DocumentController@upload');
    $router->get('/{id}', 'DocumentController@show');
    $router->get('/{id}/download', 'DocumentController@download');
    $router->delete('/{id}', 'DocumentController@destroy');
    $router->post('/{id}/new-version', 'DocumentController@createNewVersion');
    $router->get('/{id}/versions', 'DocumentController@getVersions');
    $router->post('/{id}/permissions', 'DocumentController@setPermissions');
});
```

#### 工作量评估
- **数据库设计**: 2天
- **文件存储服务**: 3天
- **Model层开发**: 2天
- **Service层开发**: 4天
- **Controller层开发**: 3天
- **版本控制**: 3天
- **权限控制**: 2天
- **单元测试**: 3天
- **总计**: 22天

---

### 5. 审核流程模块 (Audit Workflow Module)

#### 功能范围
- 审核流程配置
- 审核任务管理
- 审核历史记录
- 流程状态跟踪

#### 数据库设计
```sql
-- 审核流程表
CREATE TABLE audit_workflows (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    module VARCHAR(50) NOT NULL COMMENT '适用模块',
    steps JSON NOT NULL COMMENT '审核步骤配置',
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 审核任务表
CREATE TABLE audit_tasks (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    workflow_id BIGINT NOT NULL,
    target_type VARCHAR(50) NOT NULL COMMENT '审核对象类型',
    target_id BIGINT NOT NULL COMMENT '审核对象ID',
    current_step INT DEFAULT 1,
    status TINYINT DEFAULT 1 COMMENT '1:待审核 2:审核中 3:已通过 4:已拒绝',
    submitter_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workflow_id) REFERENCES audit_workflows(id),
    FOREIGN KEY (submitter_id) REFERENCES users(id)
);

-- 审核记录表
CREATE TABLE audit_records (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    task_id BIGINT NOT NULL,
    step INT NOT NULL,
    auditor_id BIGINT NOT NULL,
    action TINYINT NOT NULL COMMENT '1:通过 2:拒绝 3:退回',
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES audit_tasks(id),
    FOREIGN KEY (auditor_id) REFERENCES users(id)
);
```

#### 核心类结构
```php
// app/Services/AuditWorkflowService.php
class AuditWorkflowService
{
    public function createWorkflow(array $data): AuditWorkflow
    public function getWorkflows(): array
    public function updateWorkflow(int $id, array $data): AuditWorkflow
    public function deleteWorkflow(int $id): bool
}

// app/Services/AuditTaskService.php
class AuditTaskService
{
    public function submitForAudit(string $targetType, int $targetId, int $workflowId, int $submitterId): AuditTask
    public function processAudit(int $taskId, int $auditorId, int $action, string $comment = ''): bool
    public function getMyTasks(int $auditorId): array
    public function getTaskHistory(int $taskId): array
}
```

#### 工作量评估
- **数据库设计**: 2天
- **Model层开发**: 2天
- **Service层开发**: 5天
- **Controller层开发**: 3天
- **流程引擎**: 4天
- **单元测试**: 3天
- **总计**: 19天

---

### 6. 系统管理模块 (System Management Module)

#### 功能范围
- 系统配置管理
- 操作日志记录
- 系统监控
- 数据备份

#### 数据库设计
```sql
-- 系统配置表
CREATE TABLE system_configs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT,
    description TEXT,
    config_type VARCHAR(20) DEFAULT 'string',
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 操作日志表
CREATE TABLE operation_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    module VARCHAR(50) NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50),
    target_id BIGINT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    request_data JSON,
    response_data JSON,
    execution_time INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 系统监控表
CREATE TABLE system_monitors (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 核心类结构
```php
// app/Services/SystemConfigService.php
class SystemConfigService
{
    public function getConfigs(): array
    public function getConfig(string $key): mixed
    public function setConfig(string $key, mixed $value): bool
    public function updateConfigs(array $configs): bool
}

// app/Services/OperationLogService.php
class OperationLogService
{
    public function log(int $userId, string $module, string $action, array $data = []): void
    public function getLogs(array $filters, int $page = 1, int $limit = 10): array
    public function getStatistics(array $filters): array
}
```

#### 工作量评估
- **数据库设计**: 1天
- **Model层开发**: 2天
- **Service层开发**: 3天
- **Controller层开发**: 2天
- **监控功能**: 3天
- **单元测试**: 2天
- **总计**: 13天

---

### 7. 通知管理模块 (Notification Module)

#### 功能范围
- 系统通知
- 邮件通知
- 短信通知
- 通知模板管理

#### 数据库设计
```sql
-- 通知模板表
CREATE TABLE notification_templates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(20) NOT NULL COMMENT 'system/email/sms',
    subject VARCHAR(200),
    content TEXT NOT NULL,
    variables JSON COMMENT '模板变量',
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 通知表
CREATE TABLE notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    type VARCHAR(20) NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    data JSON,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 通知发送记录表
CREATE TABLE notification_sends (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    notification_id BIGINT NOT NULL,
    channel VARCHAR(20) NOT NULL COMMENT 'system/email/sms',
    recipient VARCHAR(255) NOT NULL,
    status TINYINT DEFAULT 1 COMMENT '1:发送中 2:成功 3:失败',
    error_message TEXT,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES notifications(id)
);
```

#### 核心类结构
```php
// app/Services/NotificationService.php
class NotificationService
{
    public function sendNotification(int $userId, string $type, string $title, string $content, array $data = []): bool
    public function sendBulkNotification(array $userIds, string $type, string $title, string $content): bool
    public function getNotifications(int $userId, int $page = 1, int $limit = 10): array
    public function markAsRead(int $notificationId): bool
    public function deleteNotification(int $notificationId): bool
}

// app/Services/NotificationTemplateService.php
class NotificationTemplateService
{
    public function getTemplates(): array
    public function createTemplate(array $data): NotificationTemplate
    public function updateTemplate(int $id, array $data): NotificationTemplate
    public function deleteTemplate(int $id): bool
    public function renderTemplate(int $templateId, array $variables): string
}
```

#### 工作量评估
- **数据库设计**: 1天
- **Model层开发**: 2天
- **Service层开发**: 4天
- **Controller层开发**: 2天
- **邮件/短信集成**: 3天
- **单元测试**: 2天
- **总计**: 14天

---

### 8. 统计分析模块 (Analytics Module)

#### 功能范围
- 数据统计
- 报表生成
- 图表展示
- 数据导出

#### 数据库设计
```sql
-- 统计指标表
CREATE TABLE analytics_metrics (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    metric_name VARCHAR(100) NOT NULL,
    metric_type VARCHAR(50) NOT NULL,
    metric_value DECIMAL(15,2) NOT NULL,
    dimensions JSON COMMENT '维度信息',
    date_key DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_metric_date (metric_name, date_key)
);

-- 报表配置表
CREATE TABLE report_configs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    config JSON NOT NULL COMMENT '报表配置',
    created_by BIGINT NOT NULL,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

#### 核心类结构
```php
// app/Services/AnalyticsService.php
class AnalyticsService
{
    public function getMetrics(array $filters): array
    public function generateReport(int $configId, array $params = []): array
    public function exportReport(int $configId, string $format = 'excel'): string
    public function getDashboardData(): array
}

// app/Services/ReportService.php
class ReportService
{
    public function createReport(array $data): ReportConfig
    public function getReports(): array
    public function updateReport(int $id, array $data): ReportConfig
    public function deleteReport(int $id): bool
    public function executeReport(int $id, array $params = []): array
}
```

#### 工作量评估
- **数据库设计**: 1天
- **Model层开发**: 2天
- **Service层开发**: 4天
- **Controller层开发**: 2天
- **报表生成**: 4天
- **图表功能**: 3天
- **单元测试**: 2天
- **总计**: 18天

---

## 技术架构说明

### Lumen框架配置

#### 项目结构
```
project/
├── app/
│   ├── Console/
│   ├── Events/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Jobs/
│   ├── Listeners/
│   ├── Models/
│   ├── Providers/
│   └── Services/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
└── vendor/
```

#### 核心配置文件

##### bootstrap/app.php
```php
<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();
$app->withEloquent();

// 注册服务提供者
$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);

// 注册中间件
$app->middleware([
    App\Http\Middleware\CorsMiddleware::class,
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'permission' => App\Http\Middleware\PermissionMiddleware::class,
]);

// 注册路由
$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
```

### Symfony组件集成

#### 验证组件
```php
// app/Http/Requests/BaseRequest.php
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class BaseRequest
{
    protected $validator;
    
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }
    
    public function validate(array $data, array $constraints): array
    {
        $violations = $this->validator->validate($data, new Assert\Collection($constraints));
        
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            throw new ValidationException($errors);
        }
        
        return $data;
    }
}
```

#### 事件调度器
```php
// app/Services/EventDispatcher.php
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventDispatcherService
{
    private $dispatcher;
    
    public function __construct()
    {
        $this->dispatcher = new EventDispatcher();
    }
    
    public function dispatch($event, string $eventName = null)
    {
        return $this->dispatcher->dispatch($event, $eventName);
    }
    
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->dispatcher->addSubscriber($subscriber);
    }
}
```

### 数据库配置

#### .env配置
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

JWT_SECRET=your-secret-key
JWT_TTL=1440

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

## 项目排期计划

### 第一阶段：基础架构 (2周)
- 项目初始化和环境配置
- 数据库设计和迁移
- 认证授权模块开发
- 基础中间件开发

### 第二阶段：核心功能 (4周)
- 用户管理模块
- 内容管理模块
- 文档管理模块
- 基础API接口

### 第三阶段：业务功能 (3周)
- 审核流程模块
- 通知管理模块
- 系统管理模块

### 第四阶段：数据分析 (2周)
- 统计分析模块
- 报表生成功能
- 数据导出功能

### 第五阶段：测试优化 (2周)
- 单元测试完善
- 集成测试
- 性能优化
- 文档编写

## 总工作量评估

| 模块 | 工作量(天) | 优先级 |
|------|-----------|--------|
| 认证授权模块 | 12 | 高 |
| 用户管理模块 | 16 | 高 |
| 内容管理模块 | 21 | 高 |
| 文档管理模块 | 22 | 中 |
| 审核流程模块 | 19 | 中 |
| 系统管理模块 | 13 | 中 |
| 通知管理模块 | 14 | 低 |
| 统计分析模块 | 18 | 低 |
| **总计** | **135天** | |

**预计开发周期**: 约6-7个月 (按1人开发计算)
**建议团队规模**: 3-4人 (缩短至2-3个月)

## 技术风险评估

### 高风险项
1. **审核流程复杂度**: 需要灵活的工作流引擎
2. **文件存储性能**: 大文件上传下载优化
3. **权限系统复杂性**: 细粒度权限控制

### 中风险项
1. **数据库性能**: 大数据量查询优化
2. **缓存策略**: Redis缓存设计
3. **并发处理**: 高并发场景处理

### 建议措施
1. 采用成熟的工作流引擎或自研简化版本
2. 使用对象存储服务(OSS)处理文件
3. 实施分层权限模型
4. 数据库分库分表准备
5. 引入消息队列处理异步任务