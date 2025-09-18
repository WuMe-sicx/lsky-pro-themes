<img align="right" width="100" src="https://avatars.githubusercontent.com/u/100565733?s=200" alt="Lsky Pro+"/>

<h1 align="left" style="border: none!important;">Lsky Pro+ 主题仓库</h1>

兰空图床付费版本从 `V 2.3.0` 开始提供主题功能，允许开发者轻松的自定义主题，有关主题的开发文档请访问 [https://docs.lsky.pro/advanced/theme](https://docs.lsky.pro/advanced/theme)。

## 已收录主题

- [测试](./themes/demo)

## 如何提交主题？

### 提交流程

1. **Fork 本仓库**
   - 点击右上角的 "Fork" 按钮，将仓库 Fork 到你的 GitHub 账户

2. **克隆你的 Fork 仓库**
   ```bash
   git clone https://github.com/你的用户名/lsky-pro-themes.git
   cd lsky-pro-themes
   ```

3. **创建目录**
   ```bash
   mkdir themes/your-theme-name
   ```

4. **添加文件**
   - 将你的主题信息文件复制到 `themes/your-theme-name/` 目录
   - 确保包含必需的文件：`manifest.json`、`screenshot.png`

5. **更新主题列表**
   - 编辑根目录的 `index.json` 文件
   - 按照现有格式添加你的主题信息：
   ```json
   {
     "id": "your-theme-name",
     "name": "主题显示名称",
     "description": "简短描述",
     "author": "作者名称",
     "tags": ["标签1", "标签2"],
     "repo": "https://github.com/你的用户名/your-theme-repo"
   }
   ```

6. **提交 Pull Request**
   ```bash
   git add .
   git commit -m "feat: 添加 [主题名称] 主题"
   git push origin main
   ```
   - 在 GitHub 上创建 Pull Request
   - 详细描述主题特点和功能

所有提交后的主题将会在官方网站的[主题列表](https://www.lsky.pro/themes)页面展示。感谢您为 Lsky Pro+ 社区做出的贡献！