### 菜单管理

#### 命令

通过 YAML 文件生成系统菜单并通过生成 TWIG 文件让它静态化。YAML 文件位于/config/packages/menu.yaml

```shell
bin/console ef:init-admin-menu --yaml
```

可以用此命令初始化系统菜单。YAML 文件会被解析并存储到 App\Entity\Platform\Menu (即 admin_menu 表)中，并在菜单管理里进行图形化管理。

[Your video title](/documents/features/menu/menus.mp4)
