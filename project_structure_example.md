# 项目结构示例

## Maven多模块项目结构

```
my-system/
├── pom.xml                           # 父项目POM
├── README.md
├── docker-compose.yml                # 本地开发环境
├── my-system-common/                 # 公共模块
│   ├── pom.xml
│   └── src/main/java/com/mysystem/common/
│       ├── config/                   # 公共配置
│       ├── constants/                # 常量定义
│       ├── dto/                      # 数据传输对象
│       ├── enums/                    # 枚举类
│       ├── exception/                # 异常处理
│       ├── utils/                    # 工具类
│       └── vo/                       # 值对象
├── my-system-auth/                   # 认证授权模块
│   ├── pom.xml
│   └── src/main/java/com/mysystem/auth/
│       ├── controller/
│       │   ├── AuthController.java
│       │   └── RoleController.java
│       ├── entity/
│       │   ├── User.java
│       │   ├── Role.java
│       │   └── Permission.java
│       ├── repository/
│       │   ├── UserRepository.java
│       │   ├── RoleRepository.java
│       │   └── PermissionRepository.java
│       ├── service/
│       │   ├── AuthService.java
│       │   ├── UserService.java
│       │   └── RoleService.java
│       ├── dto/
│       │   ├── LoginRequest.java
│       │   ├── LoginResponse.java
│       │   └── UserDTO.java
│       └── config/
│           ├── JwtConfig.java
│           └── SecurityConfig.java
├── my-system-user/                   # 用户管理模块
│   ├── pom.xml
│   └── src/main/java/com/mysystem/user/
│       ├── controller/
│       │   └── UserController.java
│       ├── entity/
│       │   ├── UserProfile.java
│       │   └── UserSetting.java
│       ├── repository/
│       │   ├── UserProfileRepository.java
│       │   └── UserSettingRepository.java
│       ├── service/
│       │   ├── UserManagementService.java
│       │   └── UserProfileService.java
│       └── dto/
│           ├── CreateUserRequest.java
│           ├── UpdateUserRequest.java
│           └── UserDetailDTO.java
├── my-system-content/                # 内容管理模块
│   ├── pom.xml
│   └── src/main/java/com/mysystem/content/
│       ├── controller/
│       │   ├── ContentController.java
│       │   └── CategoryController.java
│       ├── entity/
│       │   ├── Content.java
│       │   ├── Category.java
│       │   └── Tag.java
│       ├── repository/
│       │   ├── ContentRepository.java
│       │   ├── CategoryRepository.java
│       │   └── TagRepository.java
│       ├── service/
│       │   ├── ContentService.java
│       │   ├── CategoryService.java
│       │   └── TagService.java
│       └── dto/
│           ├── CreateContentRequest.java
│           ├── ContentDTO.java
│           └── CategoryTreeDTO.java
├── my-system-file/                   # 文件管理模块
│   ├── pom.xml
│   └── src/main/java/com/mysystem/file/
│       ├── controller/
│       │   └── FileController.java
│       ├── entity/
│       │   ├── File.java
│       │   └── FileCategory.java
│       ├── repository/
│       │   ├── FileRepository.java
│       │   └── FileCategoryRepository.java
│       ├── service/
│       │   ├── FileService.java
│       │   └── FileStorageService.java
│       ├── dto/
│       │   ├── FileUploadResponse.java
│       │   └── FileDTO.java
│       └── config/
│           └── FileStorageConfig.java
├── my-system-system/                 # 系统管理模块
│   ├── pom.xml
│   └── src/main/java/com/mysystem/system/
│       ├── controller/
│       │   ├── SystemController.java
│       │   └── NotificationController.java
│       ├── entity/
│       │   ├── SystemConfig.java
│       │   ├── OperationLog.java
│       │   └── SystemNotification.java
│       ├── repository/
│       │   ├── SystemConfigRepository.java
│       │   ├── OperationLogRepository.java
│       │   └── NotificationRepository.java
│       ├── service/
│       │   ├── SystemConfigService.java
│       │   ├── OperationLogService.java
│       │   └── NotificationService.java
│       └── dto/
│           ├── SystemConfigDTO.java
│           └── SystemStatisticsDTO.java
├── my-system-support/                # 支撑服务模块
│   ├── pom.xml
│   └── src/main/java/com/mysystem/support/
│       ├── cache/
│       │   ├── CacheService.java
│       │   └── RedisConfig.java
│       ├── mq/
│       │   ├── MessageQueueService.java
│       │   ├── RabbitMQConfig.java
│       │   └── listeners/
│       │       ├── UserEventListener.java
│       │       └── FileEventListener.java
│       ├── monitoring/
│       │   ├── MetricsService.java
│       │   └── HealthCheckService.java
│       └── logging/
│           ├── LoggingAspect.java
│           └── OperationLogInterceptor.java
├── my-system-web/                    # Web启动模块
│   ├── pom.xml
│   └── src/main/java/com/mysystem/web/
│       ├── MySystemApplication.java  # 主启动类
│       ├── config/
│       │   ├── WebConfig.java
│       │   ├── SwaggerConfig.java
│       │   └── GlobalExceptionHandler.java
│       └── resources/
│           ├── application.yml
│           ├── application-dev.yml
│           ├── application-prod.yml
│           └── db/migration/          # Flyway数据库迁移脚本
│               ├── V1__Create_user_tables.sql
│               ├── V2__Create_content_tables.sql
│               └── V3__Create_system_tables.sql
└── my-system-test/                   # 测试模块
    ├── pom.xml
    └── src/test/java/com/mysystem/
        ├── integration/              # 集成测试
        ├── unit/                     # 单元测试
        └── TestApplication.java
```

## 父项目POM配置

```xml
<?xml version="1.0" encoding="UTF-8"?>
<project xmlns="http://maven.apache.org/POM/4.0.0"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://maven.apache.org/POM/4.0.0 
         http://maven.apache.org/xsd/maven-4.0.0.xsd">
    <modelVersion>4.0.0</modelVersion>

    <groupId>com.mysystem</groupId>
    <artifactId>my-system</artifactId>
    <version>1.0.0</version>
    <packaging>pom</packaging>

    <parent>
        <groupId>org.springframework.boot</groupId>
        <artifactId>spring-boot-starter-parent</artifactId>
        <version>2.7.14</version>
        <relativePath/>
    </parent>

    <modules>
        <module>my-system-common</module>
        <module>my-system-auth</module>
        <module>my-system-user</module>
        <module>my-system-content</module>
        <module>my-system-file</module>
        <module>my-system-system</module>
        <module>my-system-support</module>
        <module>my-system-web</module>
        <module>my-system-test</module>
    </modules>

    <properties>
        <java.version>11</java.version>
        <maven.compiler.source>11</maven.compiler.source>
        <maven.compiler.target>11</maven.compiler.target>
        <spring-boot.version>2.7.14</spring-boot.version>
        <mysql.version>8.0.33</mysql.version>
        <redis.version>2.7.14</redis.version>
        <jwt.version>0.11.5</jwt.version>
        <swagger.version>3.0.0</swagger.version>
    </properties>

    <dependencyManagement>
        <dependencies>
            <!-- 内部模块依赖 -->
            <dependency>
                <groupId>com.mysystem</groupId>
                <artifactId>my-system-common</artifactId>
                <version>${project.version}</version>
            </dependency>
            <dependency>
                <groupId>com.mysystem</groupId>
                <artifactId>my-system-auth</artifactId>
                <version>${project.version}</version>
            </dependency>
            <!-- 其他模块依赖... -->
            
            <!-- 外部依赖 -->
            <dependency>
                <groupId>mysql</groupId>
                <artifactId>mysql-connector-java</artifactId>
                <version>${mysql.version}</version>
            </dependency>
            <dependency>
                <groupId>io.jsonwebtoken</groupId>
                <artifactId>jjwt-api</artifactId>
                <version>${jwt.version}</version>
            </dependency>
            <dependency>
                <groupId>io.springfox</groupId>
                <artifactId>springfox-boot-starter</artifactId>
                <version>${swagger.version}</version>
            </dependency>
        </dependencies>
    </dependencyManagement>

    <build>
        <plugins>
            <plugin>
                <groupId>org.springframework.boot</groupId>
                <artifactId>spring-boot-maven-plugin</artifactId>
            </plugin>
        </plugins>
    </build>
</project>
```

## 主启动类

```java
package com.mysystem.web;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.boot.autoconfigure.domain.EntityScan;
import org.springframework.cache.annotation.EnableCaching;
import org.springframework.data.jpa.repository.config.EnableJpaRepositories;
import org.springframework.scheduling.annotation.EnableAsync;
import org.springframework.scheduling.annotation.EnableScheduling;
import org.springframework.transaction.annotation.EnableTransactionManagement;

@SpringBootApplication(scanBasePackages = "com.mysystem")
@EntityScan(basePackages = "com.mysystem.*.entity")
@EnableJpaRepositories(basePackages = "com.mysystem.*.repository")
@EnableCaching
@EnableAsync
@EnableScheduling
@EnableTransactionManagement
public class MySystemApplication {
    public static void main(String[] args) {
        SpringApplication.run(MySystemApplication.class, args);
    }
}
```

## 配置文件示例

### application.yml
```yaml
spring:
  profiles:
    active: dev
  application:
    name: my-system
  
  # 数据库配置
  datasource:
    driver-class-name: com.mysql.cj.jdbc.Driver
    url: jdbc:mysql://localhost:3306/my_system?useUnicode=true&characterEncoding=utf8&serverTimezone=Asia/Shanghai
    username: ${DB_USERNAME:root}
    password: ${DB_PASSWORD:password}
    hikari:
      maximum-pool-size: 20
      minimum-idle: 5
      connection-timeout: 30000
      idle-timeout: 600000
      max-lifetime: 1800000
  
  # JPA配置
  jpa:
    hibernate:
      ddl-auto: validate
    show-sql: false
    properties:
      hibernate:
        dialect: org.hibernate.dialect.MySQL8Dialect
        format_sql: true
  
  # Redis配置
  redis:
    host: ${REDIS_HOST:localhost}
    port: ${REDIS_PORT:6379}
    password: ${REDIS_PASSWORD:}
    database: 0
    jedis:
      pool:
        max-active: 8
        max-idle: 8
        min-idle: 0
        max-wait: -1ms
  
  # RabbitMQ配置
  rabbitmq:
    host: ${RABBITMQ_HOST:localhost}
    port: ${RABBITMQ_PORT:5672}
    username: ${RABBITMQ_USERNAME:admin}
    password: ${RABBITMQ_PASSWORD:admin}
    virtual-host: /
  
  # 文件上传配置
  servlet:
    multipart:
      max-file-size: 10MB
      max-request-size: 100MB

# 系统配置
system:
  jwt:
    secret: ${JWT_SECRET:mySecretKey}
    expiration: 86400000  # 24小时
  file:
    upload-path: ${FILE_UPLOAD_PATH:/tmp/uploads}
    max-size: 10485760    # 10MB

# 日志配置
logging:
  level:
    com.mysystem: DEBUG
    org.springframework.security: DEBUG
  pattern:
    console: "%d{yyyy-MM-dd HH:mm:ss} [%thread] %-5level %logger{50} - %msg%n"
    file: "%d{yyyy-MM-dd HH:mm:ss} [%thread] %-5level %logger{50} - %msg%n"
  file:
    name: logs/my-system.log

# 监控配置
management:
  endpoints:
    web:
      exposure:
        include: health,info,metrics,prometheus
  endpoint:
    health:
      show-details: always
```

## 实体类示例

### User.java
```java
package com.mysystem.auth.entity;

import com.mysystem.common.entity.BaseEntity;
import lombok.Data;
import lombok.EqualsAndHashCode;

import javax.persistence.*;
import java.time.LocalDateTime;
import java.util.HashSet;
import java.util.Set;

@Data
@EqualsAndHashCode(callSuper = true)
@Entity
@Table(name = "users")
public class User extends BaseEntity {
    
    @Column(unique = true, nullable = false, length = 50)
    private String username;
    
    @Column(unique = true, nullable = false, length = 100)
    private String email;
    
    @Column(name = "password_hash", nullable = false)
    private String passwordHash;
    
    @Column(length = 20)
    private String phone;
    
    @Enumerated(EnumType.ORDINAL)
    @Column(nullable = false)
    private UserStatus status = UserStatus.ACTIVE;
    
    @Column(name = "last_login_time")
    private LocalDateTime lastLoginTime;
    
    @ManyToMany(fetch = FetchType.LAZY)
    @JoinTable(
        name = "user_roles",
        joinColumns = @JoinColumn(name = "user_id"),
        inverseJoinColumns = @JoinColumn(name = "role_id")
    )
    private Set<Role> roles = new HashSet<>();
    
    @OneToOne(mappedBy = "user", cascade = CascadeType.ALL, fetch = FetchType.LAZY)
    private UserProfile profile;
    
    @OneToOne(mappedBy = "user", cascade = CascadeType.ALL, fetch = FetchType.LAZY)
    private UserSetting setting;
}
```

### BaseEntity.java
```java
package com.mysystem.common.entity;

import lombok.Data;
import org.springframework.data.annotation.CreatedDate;
import org.springframework.data.annotation.LastModifiedDate;
import org.springframework.data.jpa.domain.support.AuditingEntityListener;

import javax.persistence.*;
import java.time.LocalDateTime;

@Data
@MappedSuperclass
@EntityListeners(AuditingEntityListener.class)
public abstract class BaseEntity {
    
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;
    
    @CreatedDate
    @Column(name = "created_at", nullable = false, updatable = false)
    private LocalDateTime createdAt;
    
    @LastModifiedDate
    @Column(name = "updated_at")
    private LocalDateTime updatedAt;
}
```

## Service层示例

### UserService.java
```java
package com.mysystem.auth.service;

import com.mysystem.auth.entity.User;
import com.mysystem.auth.repository.UserRepository;
import com.mysystem.common.dto.PageResult;
import com.mysystem.common.exception.BusinessException;
import lombok.RequiredArgsConstructor;
import lombok.extern.slf4j.Slf4j;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Pageable;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Slf4j
@Service
@RequiredArgsConstructor
public class UserService {
    
    private final UserRepository userRepository;
    private final PasswordEncoder passwordEncoder;
    
    @Transactional(readOnly = true)
    public PageResult<UserDTO> getUsers(int page, int size, String search) {
        Pageable pageable = PageRequest.of(page, size);
        Page<User> userPage;
        
        if (search != null && !search.trim().isEmpty()) {
            userPage = userRepository.findByUsernameContainingOrEmailContaining(
                search, search, pageable);
        } else {
            userPage = userRepository.findAll(pageable);
        }
        
        return PageResult.of(userPage.map(this::convertToDTO));
    }
    
    @Transactional
    public UserDTO createUser(CreateUserRequest request) {
        // 检查用户名是否已存在
        if (userRepository.existsByUsername(request.getUsername())) {
            throw new BusinessException("用户名已存在");
        }
        
        // 检查邮箱是否已存在
        if (userRepository.existsByEmail(request.getEmail())) {
            throw new BusinessException("邮箱已存在");
        }
        
        User user = new User();
        user.setUsername(request.getUsername());
        user.setEmail(request.getEmail());
        user.setPasswordHash(passwordEncoder.encode(request.getPassword()));
        user.setPhone(request.getPhone());
        user.setStatus(UserStatus.ACTIVE);
        
        User savedUser = userRepository.save(user);
        log.info("创建用户成功: {}", savedUser.getUsername());
        
        return convertToDTO(savedUser);
    }
    
    @Transactional
    public UserDTO updateUser(Long id, UpdateUserRequest request) {
        User user = userRepository.findById(id)
            .orElseThrow(() -> new BusinessException("用户不存在"));
        
        user.setEmail(request.getEmail());
        user.setPhone(request.getPhone());
        
        User updatedUser = userRepository.save(user);
        log.info("更新用户成功: {}", updatedUser.getUsername());
        
        return convertToDTO(updatedUser);
    }
    
    @Transactional
    public void deleteUser(Long id) {
        User user = userRepository.findById(id)
            .orElseThrow(() -> new BusinessException("用户不存在"));
        
        userRepository.delete(user);
        log.info("删除用户成功: {}", user.getUsername());
    }
    
    private UserDTO convertToDTO(User user) {
        UserDTO dto = new UserDTO();
        dto.setId(user.getId());
        dto.setUsername(user.getUsername());
        dto.setEmail(user.getEmail());
        dto.setPhone(user.getPhone());
        dto.setStatus(user.getStatus());
        dto.setCreatedAt(user.getCreatedAt());
        dto.setLastLoginTime(user.getLastLoginTime());
        return dto;
    }
}
```

## Docker配置示例

### docker-compose.yml
```yaml
version: '3.8'

services:
  mysql:
    image: mysql:8.0
    container_name: my-system-mysql
    environment:
      MYSQL_ROOT_PASSWORD: password
      MYSQL_DATABASE: my_system
      MYSQL_USER: app
      MYSQL_PASSWORD: app123
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
      - ./sql/init.sql:/docker-entrypoint-initdb.d/init.sql
    command: --default-authentication-plugin=mysql_native_password

  redis:
    image: redis:6.2-alpine
    container_name: my-system-redis
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data

  rabbitmq:
    image: rabbitmq:3.9-management
    container_name: my-system-rabbitmq
    environment:
      RABBITMQ_DEFAULT_USER: admin
      RABBITMQ_DEFAULT_PASS: admin
    ports:
      - "5672:5672"
      - "15672:15672"
    volumes:
      - rabbitmq_data:/var/lib/rabbitmq

  minio:
    image: minio/minio:latest
    container_name: my-system-minio
    environment:
      MINIO_ACCESS_KEY: minioadmin
      MINIO_SECRET_KEY: minioadmin
    ports:
      - "9000:9000"
      - "9001:9001"
    volumes:
      - minio_data:/data
    command: server /data --console-address ":9001"

volumes:
  mysql_data:
  redis_data:
  rabbitmq_data:
  minio_data:
```

这样的项目结构完全按照后端设计思维来组织，每个模块都有明确的职责，便于开发、测试和维护。每个模块都包含完整的分层架构：Entity -> Repository -> Service -> Controller，符合现代Spring Boot应用的最佳实践。