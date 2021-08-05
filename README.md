# Dcat Color Icon

为 DcatAdmin 后台菜单添加彩色Icon支持的功能。。

![](http://cimg.mxrxlb.com/images/f60ea3750a00d12ac4a1a1ffe30f2d50.png)


## 安装方式

最新支持 Dcat Admin 2.1.2beta

`composer require zhy/dcat-color-icon`

或者在后台扩展中，直接上传本插件的 `.zip` 文件即可。

### 使用

在菜单 `扩展` 中启用扩展后，会自动替换原`菜单页`icon选择组件。

### 功能

- 菜单彩色Icon

- 表单扩展：表单可用 `colorIcon()` 字段类型，是 `icon` 字段的增强。

```PHP
$form->colorIcon('icon','彩色Icon');
```

## 开源协议

Dcat Color Icon 遵循 MIT 开源协议。
