# 后端模块重新设计方案

## 基于业务领域的模块拆分

### 1. 用户认证授权模块 (Authentication & Authorization Module)

#### 数据库表设计
```sql
-- 用户表
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    status TINYINT DEFAULT 1 COMMENT '1:正常 0:禁用',
    last_login_time TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 角色表
CREATE TABLE roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 权限表
CREATE TABLE permissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    resource VARCHAR(100) NOT NULL,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 用户角色关联表
CREATE TABLE user_roles (
    user_id BIGINT,
    role_id BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- 角色权限关联表
CREATE TABLE role_permissions (
    role_id BIGINT,
    permission_id BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id)
);
```

#### 实体类设计
```java
@Entity
@Table(name = "users")
public class User {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;
    
    @Column(unique = true, nullable = false)
    private String username;
    
    @Column(unique = true, nullable = false)
    private String email;
    
    @Column(name = "password_hash", nullable = false)
    private String passwordHash;
    
    private String phone;
    
    @Enumerated(EnumType.ORDINAL)
    private UserStatus status;
    
    @Column(name = "last_login_time")
    private LocalDateTime lastLoginTime;
    
    @ManyToMany(fetch = FetchType.LAZY)
    @JoinTable(
        name = "user_roles",
        joinColumns = @JoinColumn(name = "user_id"),
        inverseJoinColumns = @JoinColumn(name = "role_id")
    )
    private Set<Role> roles = new HashSet<>();
    
    // getters and setters
}
```

#### 主要接口设计
```java
@RestController
@RequestMapping("/api/auth")
public class AuthController {
    
    // 用户登录
    @PostMapping("/login")
    public ResponseEntity<LoginResponse> login(@Valid @RequestBody LoginRequest request);
    
    // 用户登出
    @PostMapping("/logout")
    public ResponseEntity<Void> logout();
    
    // 刷新令牌
    @PostMapping("/refresh")
    public ResponseEntity<RefreshTokenResponse> refreshToken(@Valid @RequestBody RefreshTokenRequest request);
    
    // 获取当前用户信息
    @GetMapping("/profile")
    public ResponseEntity<UserProfileResponse> getCurrentUser();
    
    // 修改密码
    @PutMapping("/password")
    public ResponseEntity<Void> changePassword(@Valid @RequestBody ChangePasswordRequest request);
}

@RestController
@RequestMapping("/api/roles")
public class RoleController {
    
    // 获取角色列表
    @GetMapping
    public ResponseEntity<Page<RoleDTO>> getRoles(@RequestParam(defaultValue = "0") int page,
                                                  @RequestParam(defaultValue = "10") int size);
    
    // 创建角色
    @PostMapping
    public ResponseEntity<RoleDTO> createRole(@Valid @RequestBody CreateRoleRequest request);
    
    // 更新角色
    @PutMapping("/{id}")
    public ResponseEntity<RoleDTO> updateRole(@PathVariable Long id, 
                                              @Valid @RequestBody UpdateRoleRequest request);
    
    // 删除角色
    @DeleteMapping("/{id}")
    public ResponseEntity<Void> deleteRole(@PathVariable Long id);
    
    // 分配权限
    @PostMapping("/{id}/permissions")
    public ResponseEntity<Void> assignPermissions(@PathVariable Long id,
                                                  @Valid @RequestBody AssignPermissionsRequest request);
}
```

### 2. 用户管理模块 (User Management Module)

#### 数据库表设计
```sql
-- 用户详细信息表
CREATE TABLE user_profiles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,
    real_name VARCHAR(100),
    avatar_url VARCHAR(255),
    department VARCHAR(100),
    position VARCHAR(100),
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
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
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### 主要接口设计
```java
@RestController
@RequestMapping("/api/users")
public class UserController {
    
    // 获取用户列表（支持搜索、分页、排序）
    @GetMapping
    public ResponseEntity<Page<UserDTO>> getUsers(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "10") int size,
            @RequestParam(required = false) String search,
            @RequestParam(defaultValue = "id") String sortBy,
            @RequestParam(defaultValue = "asc") String sortDir);
    
    // 获取用户详情
    @GetMapping("/{id}")
    public ResponseEntity<UserDetailDTO> getUserDetail(@PathVariable Long id);
    
    // 创建用户
    @PostMapping
    public ResponseEntity<UserDTO> createUser(@Valid @RequestBody CreateUserRequest request);
    
    // 更新用户基本信息
    @PutMapping("/{id}")
    public ResponseEntity<UserDTO> updateUser(@PathVariable Long id,
                                              @Valid @RequestBody UpdateUserRequest request);
    
    // 批量导入用户
    @PostMapping("/batch-import")
    public ResponseEntity<BatchImportResponse> batchImportUsers(@RequestParam("file") MultipartFile file);
    
    // 批量导出用户
    @GetMapping("/export")
    public ResponseEntity<Resource> exportUsers(@RequestParam(required = false) String format);
    
    // 启用/禁用用户
    @PutMapping("/{id}/status")
    public ResponseEntity<Void> updateUserStatus(@PathVariable Long id,
                                                  @Valid @RequestBody UpdateUserStatusRequest request);
    
    // 重置用户密码
    @PostMapping("/{id}/reset-password")
    public ResponseEntity<ResetPasswordResponse> resetPassword(@PathVariable Long id);
    
    // 分配角色
    @PostMapping("/{id}/roles")
    public ResponseEntity<Void> assignRoles(@PathVariable Long id,
                                            @Valid @RequestBody AssignRolesRequest request);
}
```

### 3. 内容管理模块 (Content Management Module)

#### 数据库表设计
```sql
-- 内容分类表
CREATE TABLE content_categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    parent_id BIGINT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES content_categories(id)
);

-- 内容表
CREATE TABLE contents (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT,
    summary TEXT,
    category_id BIGINT,
    author_id BIGINT NOT NULL,
    status TINYINT DEFAULT 1 COMMENT '1:草稿 2:发布 3:下线',
    view_count INT DEFAULT 0,
    like_count INT DEFAULT 0,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES content_categories(id),
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
    PRIMARY KEY (content_id, tag_id),
    FOREIGN KEY (content_id) REFERENCES contents(id),
    FOREIGN KEY (tag_id) REFERENCES tags(id)
);
```

#### 主要接口设计
```java
@RestController
@RequestMapping("/api/contents")
public class ContentController {
    
    // 获取内容列表
    @GetMapping
    public ResponseEntity<Page<ContentDTO>> getContents(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "10") int size,
            @RequestParam(required = false) Long categoryId,
            @RequestParam(required = false) String status,
            @RequestParam(required = false) String search);
    
    // 获取内容详情
    @GetMapping("/{id}")
    public ResponseEntity<ContentDetailDTO> getContentDetail(@PathVariable Long id);
    
    // 创建内容
    @PostMapping
    public ResponseEntity<ContentDTO> createContent(@Valid @RequestBody CreateContentRequest request);
    
    // 更新内容
    @PutMapping("/{id}")
    public ResponseEntity<ContentDTO> updateContent(@PathVariable Long id,
                                                    @Valid @RequestBody UpdateContentRequest request);
    
    // 删除内容
    @DeleteMapping("/{id}")
    public ResponseEntity<Void> deleteContent(@PathVariable Long id);
    
    // 批量删除内容
    @DeleteMapping("/batch")
    public ResponseEntity<Void> batchDeleteContents(@Valid @RequestBody BatchDeleteRequest request);
    
    // 发布内容
    @PostMapping("/{id}/publish")
    public ResponseEntity<Void> publishContent(@PathVariable Long id);
    
    // 下线内容
    @PostMapping("/{id}/offline")
    public ResponseEntity<Void> offlineContent(@PathVariable Long id);
}

@RestController
@RequestMapping("/api/categories")
public class CategoryController {
    
    // 获取分类树
    @GetMapping("/tree")
    public ResponseEntity<List<CategoryTreeDTO>> getCategoryTree();
    
    // 获取分类列表
    @GetMapping
    public ResponseEntity<Page<CategoryDTO>> getCategories(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "10") int size);
    
    // 创建分类
    @PostMapping
    public ResponseEntity<CategoryDTO> createCategory(@Valid @RequestBody CreateCategoryRequest request);
    
    // 更新分类
    @PutMapping("/{id}")
    public ResponseEntity<CategoryDTO> updateCategory(@PathVariable Long id,
                                                      @Valid @RequestBody UpdateCategoryRequest request);
    
    // 删除分类
    @DeleteMapping("/{id}")
    public ResponseEntity<Void> deleteCategory(@PathVariable Long id);
}
```

### 4. 文件管理模块 (File Management Module)

#### 数据库表设计
```sql
-- 文件表
CREATE TABLE files (
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES file_categories(id),
    FOREIGN KEY (uploader_id) REFERENCES users(id)
);

-- 文件分类表
CREATE TABLE file_categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    allowed_extensions TEXT,
    max_file_size BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 主要接口设计
```java
@RestController
@RequestMapping("/api/files")
public class FileController {
    
    // 文件上传
    @PostMapping("/upload")
    public ResponseEntity<FileUploadResponse> uploadFile(@RequestParam("file") MultipartFile file,
                                                         @RequestParam(required = false) Long categoryId);
    
    // 批量文件上传
    @PostMapping("/batch-upload")
    public ResponseEntity<List<FileUploadResponse>> batchUploadFiles(@RequestParam("files") MultipartFile[] files,
                                                                     @RequestParam(required = false) Long categoryId);
    
    // 获取文件列表
    @GetMapping
    public ResponseEntity<Page<FileDTO>> getFiles(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "10") int size,
            @RequestParam(required = false) Long categoryId,
            @RequestParam(required = false) String search);
    
    // 获取文件详情
    @GetMapping("/{id}")
    public ResponseEntity<FileDetailDTO> getFileDetail(@PathVariable Long id);
    
    // 文件下载
    @GetMapping("/{id}/download")
    public ResponseEntity<Resource> downloadFile(@PathVariable Long id);
    
    // 文件预览
    @GetMapping("/{id}/preview")
    public ResponseEntity<Resource> previewFile(@PathVariable Long id);
    
    // 删除文件
    @DeleteMapping("/{id}")
    public ResponseEntity<Void> deleteFile(@PathVariable Long id);
    
    // 批量删除文件
    @DeleteMapping("/batch")
    public ResponseEntity<Void> batchDeleteFiles(@Valid @RequestBody BatchDeleteRequest request);
}
```

### 5. 系统管理模块 (System Management Module)

#### 数据库表设计
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
    operation VARCHAR(100) NOT NULL,
    resource_type VARCHAR(50),
    resource_id BIGINT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    request_params TEXT,
    response_status INT,
    execution_time INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 系统通知表
CREATE TABLE system_notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT,
    type VARCHAR(20) DEFAULT 'info',
    target_type VARCHAR(20) DEFAULT 'all',
    target_users TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

#### 主要接口设计
```java
@RestController
@RequestMapping("/api/system")
public class SystemController {
    
    // 获取系统配置
    @GetMapping("/configs")
    public ResponseEntity<List<SystemConfigDTO>> getConfigs();
    
    // 更新系统配置
    @PutMapping("/configs")
    public ResponseEntity<Void> updateConfigs(@Valid @RequestBody List<UpdateConfigRequest> requests);
    
    // 获取操作日志
    @GetMapping("/logs")
    public ResponseEntity<Page<OperationLogDTO>> getLogs(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "10") int size,
            @RequestParam(required = false) String operation,
            @RequestParam(required = false) Long userId,
            @RequestParam(required = false) String startDate,
            @RequestParam(required = false) String endDate);
    
    // 获取系统统计信息
    @GetMapping("/statistics")
    public ResponseEntity<SystemStatisticsDTO> getStatistics();
    
    // 系统健康检查
    @GetMapping("/health")
    public ResponseEntity<SystemHealthDTO> healthCheck();
    
    // 清理系统缓存
    @PostMapping("/cache/clear")
    public ResponseEntity<Void> clearCache(@RequestParam(required = false) String cacheType);
}

@RestController
@RequestMapping("/api/notifications")
public class NotificationController {
    
    // 获取通知列表
    @GetMapping
    public ResponseEntity<Page<NotificationDTO>> getNotifications(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "10") int size,
            @RequestParam(required = false) String type);
    
    // 创建通知
    @PostMapping
    public ResponseEntity<NotificationDTO> createNotification(@Valid @RequestBody CreateNotificationRequest request);
    
    // 标记为已读
    @PostMapping("/{id}/read")
    public ResponseEntity<Void> markAsRead(@PathVariable Long id);
    
    // 批量标记为已读
    @PostMapping("/batch-read")
    public ResponseEntity<Void> batchMarkAsRead(@Valid @RequestBody BatchReadRequest request);
    
    // 删除通知
    @DeleteMapping("/{id}")
    public ResponseEntity<Void> deleteNotification(@PathVariable Long id);
}
```

### 6. 通用支撑模块

#### 缓存模块 (Cache Module)
```java
@Service
public class CacheService {
    
    @Autowired
    private RedisTemplate<String, Object> redisTemplate;
    
    public void set(String key, Object value, long timeout, TimeUnit unit);
    public Object get(String key);
    public void delete(String key);
    public void deletePattern(String pattern);
    public boolean exists(String key);
    public void expire(String key, long timeout, TimeUnit unit);
}
```

#### 消息队列模块 (Message Queue Module)
```java
@Service
public class MessageQueueService {
    
    // 发送消息
    public void sendMessage(String topic, Object message);
    
    // 延时消息
    public void sendDelayMessage(String topic, Object message, long delay, TimeUnit unit);
    
    // 消息消费者
    @RabbitListener(queues = "user.registration")
    public void handleUserRegistration(UserRegistrationMessage message);
    
    @RabbitListener(queues = "file.upload")
    public void handleFileUpload(FileUploadMessage message);
}
```

## 模块间关系图

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   用户认证授权   │────│    用户管理     │────│    内容管理     │
│     模块       │    │     模块       │    │     模块       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         └───────────────────────┼───────────────────────┘
                                 │
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│    文件管理     │────│    系统管理     │────│   通用支撑模块   │
│     模块       │    │     模块       │    │  (缓存/消息队列)  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 技术栈建议

- **后端框架**: Spring Boot 2.7+
- **数据库**: MySQL 8.0+
- **缓存**: Redis 6.0+
- **消息队列**: RabbitMQ 3.8+
- **文件存储**: MinIO 或 阿里云OSS
- **API文档**: Swagger/OpenAPI 3.0
- **监控**: Prometheus + Grafana
- **日志**: ELK Stack (Elasticsearch + Logstash + Kibana)

## 部署架构建议

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Nginx/网关    │────│   Spring Boot   │────│     MySQL      │
│   (负载均衡)    │    │   (应用服务器)   │    │   (主数据库)    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│     Redis      │────│   RabbitMQ     │────│     MinIO      │
│   (缓存服务)    │    │  (消息队列)     │    │  (文件存储)     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

这样的模块拆分更符合后端系统的设计原则，每个模块都有明确的职责边界，便于开发、测试、部署和维护。