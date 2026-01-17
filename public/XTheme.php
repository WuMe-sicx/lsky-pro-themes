<?php

namespace Themes\XTheme;

use App\Contracts\ThemeAbstract;
use App\Support\Attribute;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class XTheme extends ThemeAbstract
{
    public string $id = 'XTheme';

    public string $name = 'XTheme';

    public ?string $description = '此XTheme使用 Vue3 + Vite + NativeUI 开发的前后端分离主题。';

    public string $author = 'Xiaoxuya';

    public string $version = '1.2.0';

    public ?string $url = 'https://egouu.com';

    /**
     * 版本更新检查 API 地址
     * 返回 JSON 格式: {"version": "1.3.0", "changelog": "更新说明", "download_url": "下载地址"}
     *
     * GitHub Raw 文件格式: https://raw.githubusercontent.com/用户名/仓库名/分支/version.json
     */
    protected string $updateCheckUrl = 'https://raw.githubusercontent.com/WuMe-sicx/lsky-pro-themes/refs/heads/main/public/version.json';

    public function routes(): void
    {
        Route::any('/{any}', fn (): View => view("{$this->id}::index"))->where('any', '^(?!api).*');
    }

    public function configurable(): array
    {
        // 检查更新
        $this->checkForUpdates();

        return [
            Tabs::make()->schema([
                Tabs\Tab::make('基础设置')->schema([
                    $this->getVersionInfoSection(),
                    Grid::make()->schema([
                        $this->getSiteTitleFormField(),
                        $this->getSiteSubtitleFormField(),
                    ]),
                    $this->getSiteIconUrlFormField(),
                    $this->getSiteLogoHtmlFormField(),
                    $this->getSiteKeywordsFormField(),
                    $this->getSiteDescriptionFormField(),
                    $this->getSiteHomepageTitleFormField(),
                    $this->getSiteHomepageDescriptionFormField(),
                    $this->getSiteNoticeFormField(),
                    $this->getSiteUserLoginTypesFormField(),
                    $this->getSiteFriendLinksFormField(),
                    $this->getSiteFeaturesFormField(),
                    $this->getSiteScenariosFormField(),
                    $this->getSiteFaqFormField(),
                ]),
                Tabs\Tab::make('背景设置')->schema([
                    $this->getSiteHomepageBackgroundImageUrlFormField(),
                    $this->getSiteAuthPageBackgroundImageUrlFormField(),
                    $this->getSiteHomepageBackgroundImagesFormField(),
                    $this->getSiteAuthPageBackgroundImagesFormField(),
                ]),
                Tabs\Tab::make('高级设置')->schema([
                    $this->getSiteCustomCssFormField(),
                    $this->getSiteCustomJsFormField(),
                ]),
                Tabs\Tab::make('广告设置')->schema([
                    $this->getSiteAdsFeaturesFormField(),
                    $this->getSiteAdsScenariosFormField(),
                    $this->getSiteAdsStatsFormField(),
                    $this->getSiteAdsFaqFormField(),
                ]),
            ])
        ];
    }

    public function casts(): array
    {
        return [
            'homepage_background_images' => new Attribute(
                fn($value) => is_array($value)
                    ? array_map(fn($path) => $this->convertToFullUrl($path), $value)
                    : []
            ),
            'auth_page_background_images' => new Attribute(
                fn($value) => is_array($value)
                    ? array_map(fn($path) => $this->convertToFullUrl($path), $value)
                    : []
            ),
            'friend_links' => new Attribute(
                fn($value) => $this->convertToArray($value)
            ),
            'features' => new Attribute(
                fn($value) => $this->convertToArray($value)
            ),
            'scenarios' => new Attribute(
                fn($value) => $this->convertToArray($value)
            ),
            'faq' => new Attribute(
                fn($value) => $this->convertToArray($value)
            ),
        ];
    }

    /**
     * 将值转换为纯数组（重置为数字索引）
     */
    protected function convertToArray($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, fn($item) => is_array($item)));
    }

    /**
     * 网站标题
     */
    protected function getSiteTitleFormField(): TextInput
    {
        return TextInput::make('payload.title')
            ->label('网站标题')
            ->maxLength(60)
            ->minLength(1)
            ->required()
            ->placeholder('请输入网站标题');
    }

    /**
     * 网站副标题
     */
    protected function getSiteSubtitleFormField(): TextInput
    {
        return TextInput::make('payload.subtitle')
            ->label('网站副标题')
            ->maxLength(60)
            ->placeholder('请输入网站副标题');
    }

    /**
     * 网站图标地址
     */
    protected function getSiteIconUrlFormField(): TextInput
    {
        return TextInput::make('payload.icon_url')
            ->label('网站图标地址')
            ->placeholder('请输入网站图标URL地址');
    }

    /**
     * Logo HTML代码
     */
    protected function getSiteLogoHtmlFormField(): Textarea
    {
        return Textarea::make('payload.logo_html')
            ->label('Logo HTML代码')
            ->rows(6)
            ->helperText('填写Logo的HTML代码，支持SVG、img标签等。留空则使用图标地址。示例：<img src="..." class="h-10"> 或 <svg>...</svg>')
            ->placeholder('<svg viewBox="0 0 130 40">...</svg>');
    }

    /**
     * 网站关键字
     */
    protected function getSiteKeywordsFormField(): TextInput
    {
        return TextInput::make('payload.keywords')
            ->label('网站关键字')
            ->maxLength(255)
            ->placeholder('请输入网站关键字，用英文逗号分隔');
    }

    /**
     * 网站描述
     */
    protected function getSiteDescriptionFormField(): Textarea
    {
        return Textarea::make('payload.description')
            ->label('网站描述')
            ->maxLength(500)
            ->placeholder('请输入网站描述，用于搜索引擎优化');
    }

    /**
     * 首页横幅标题
     */
    protected function getSiteHomepageTitleFormField(): TextInput
    {
        return TextInput::make('payload.homepage_title')
            ->label('首页横幅标题')
            ->maxLength(60)
            ->placeholder('请输入首页横幅标题');
    }

    /**
     * 首页横幅描述
     */
    protected function getSiteHomepageDescriptionFormField(): Textarea
    {
        return Textarea::make('payload.homepage_description')
            ->label('首页横幅描述')
            ->maxLength(400)
            ->placeholder('请输入首页横幅描述');
    }

    /**
     * 弹出公告
     */
    protected function getSiteNoticeFormField(): MarkdownEditor
    {
        return MarkdownEditor::make('payload.notice')
            ->label('弹出公告')
            ->placeholder('支持Markdown语法，留空则不显示公告');
    }

    /**
     * 登录方式
     */
    protected function getSiteUserLoginTypesFormField(): CheckboxList
    {
        return CheckboxList::make('payload.user_login_types')
            ->label('用户登录方式')
            ->default(['email', 'password'])
            ->options([
                'email' => '邮箱',
                'phone' => '手机号',
                'username' => '用户名'
            ]);
    }
    /**
     * 友情链接
     */
    protected function getSiteFriendLinksFormField(): Repeater
    {
        return Repeater::make('payload.friend_links')
            ->label('友情链接')
            ->schema([
                Grid::make()->schema([
                    TextInput::make('name')
                        ->label('名称')
                        ->required()
                        ->maxLength(50)
                        ->placeholder('请输入链接名称'),
                    TextInput::make('url')
                        ->label('链接地址')
                        ->required()
                        ->url()
                        ->maxLength(255)
                        ->placeholder('请输入链接URL'),
                ]),
            ])
            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
            ->addActionLabel('添加链接')
            ->deleteAction(fn ($action) => $action->requiresConfirmation())
            ->reorderable()
            ->collapsible()
            ->defaultItems(0)
            ->helperText('页脚友情链接，将在新标签页打开');
    }

    /**
     * 核心能力
     */
    protected function getSiteFeaturesFormField(): Repeater
    {
        return Repeater::make('payload.features')
            ->label('核心能力')
            ->schema([
                TextInput::make('icon')
                    ->label('图标名称')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('如：fa-link, fa-server, fa-code')
                    ->helperText('FontAwesome 图标名称，参考 fontawesome.com/icons'),
                TextInput::make('title')
                    ->label('标题')
                    ->required()
                    ->maxLength(20)
                    ->placeholder('请输入能力标题'),
                Textarea::make('desc')
                    ->label('描述')
                    ->required()
                    ->rows(2)
                    ->maxLength(100)
                    ->placeholder('请输入能力描述'),
            ])
            ->collapsible()
            ->collapsed()
            ->itemLabel(fn (array $state): ?string => $state['title'] ?? '新能力')
            ->addActionLabel('添加能力')
            ->reorderableWithButtons()
            ->defaultItems(0)
            ->helperText('首页核心能力展示，建议4个');
    }

    /**
     * 应用场景
     */
    protected function getSiteScenariosFormField(): Repeater
    {
        return Repeater::make('payload.scenarios')
            ->label('应用场景')
            ->schema([
                TextInput::make('icon')
                    ->label('图标名称')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('如：fa-user, fa-building, fa-newspaper')
                    ->helperText('FontAwesome 图标名称，参考 fontawesome.com/icons'),
                TextInput::make('title')
                    ->label('标题')
                    ->required()
                    ->maxLength(20)
                    ->placeholder('请输入场景标题'),
                Textarea::make('desc')
                    ->label('描述')
                    ->required()
                    ->rows(2)
                    ->maxLength(100)
                    ->placeholder('请输入场景描述'),
            ])
            ->collapsible()
            ->collapsed()
            ->itemLabel(fn (array $state): ?string => $state['title'] ?? '新场景')
            ->addActionLabel('添加场景')
            ->reorderableWithButtons()
            ->defaultItems(0)
            ->helperText('首页应用场景展示，建议4个');
    }

    /**
     * FAQ 常见问题
     */
    protected function getSiteFaqFormField(): Repeater
    {
        return Repeater::make('payload.faq')
            ->label('FAQ 常见问题')
            ->schema([
                TextInput::make('question')
                    ->label('问题')
                    ->required()
                    ->maxLength(200)
                    ->placeholder('请输入问题'),
                Textarea::make('answer')
                    ->label('答案')
                    ->required()
                    ->rows(3)
                    ->placeholder('请输入答案'),
            ])
            ->collapsible()
            ->collapsed()
            ->itemLabel(fn (array $state): ?string => $state['question'] ?? '新问题')
            ->addActionLabel('添加问题')
            ->reorderableWithButtons()
            ->defaultItems(0)
            ->helperText('添加首页FAQ手风琴展示的常见问题');
    }

    /**
     * 首页背景图地址
     */
    protected function getSiteHomepageBackgroundImageUrlFormField(): TextInput
    {
        return TextInput::make('payload.homepage_background_image_url')
            ->label('首页背景图地址')
            ->url()
            ->placeholder('请输入首页背景图URL地址');
    }

    /**
     * 授权页背景图地址
     */
    protected function getSiteAuthPageBackgroundImageUrlFormField(): TextInput
    {
        return TextInput::make('payload.auth_page_background_image_url')
            ->label('授权页背景图地址')
            ->url()
            ->placeholder('请输入授权页背景图URL地址');
    }

    /**
     * 首页背景图
     */
    protected function getSiteHomepageBackgroundImagesFormField(): FileUpload
    {
        return FileUpload::make('payload.homepage_background_images')
            ->label('首页背景图')
            ->multiple()
            ->image()
            ->imageEditor()
            ->placeholder('上传首页背景图片');
    }

    /**
     * 授权页背景图地址
     */
    protected function getSiteAuthPageBackgroundImagesFormField(): FileUpload
    {
        return FileUpload::make('payload.auth_page_background_images')
            ->label('授权页背景图')
            ->multiple()
            ->image()
            ->imageEditor()
            ->placeholder('上传授权页背景图片');
    }

    /**
     * 自定义CSS
     */
    protected function getSiteCustomCssFormField(): CodeEditor
    {
        return CodeEditor::make('payload.custom_css')
            ->label('自定义CSS')
            ->helperText('在这里输入你的自定义CSS代码')
            ->language(\Filament\Forms\Components\CodeEditor\Enums\Language::Css)
            ->columnSpanFull();
    }

    /**
     * 自定义JavaScript
     */
    protected function getSiteCustomJsFormField(): CodeEditor
    {
        return CodeEditor::make('payload.custom_js')
            ->label('自定义JavaScript')
            ->helperText('在这里输入你的自定义JavaScript代码')
            ->language(\Filament\Forms\Components\CodeEditor\Enums\Language::JavaScript)
            ->columnSpanFull();
    }

    /**
     * 将相对路径转换为完整URL
     */
    protected function convertToFullUrl(?string $path): string
    {
        return $path ? Storage::url($path) : '';
    }

    /**
     * 创建广告区块表单字段
     */
    protected function createAdsSectionFormField(string $section, string $title, string $description): \Filament\Schemas\Components\Section
    {
        $basePath = "payload.ads.{$section}";

        return \Filament\Schemas\Components\Section::make($title)
            ->description($description)
            ->collapsible()
            ->collapsed()
            ->schema([
                Grid::make(2)->schema([
                    $this->createSideAdFieldset($basePath, 'left', '左侧广告'),
                    $this->createSideAdFieldset($basePath, 'right', '右侧广告'),
                ]),
            ]);
    }

    /**
     * 创建单侧广告字段组
     */
    protected function createSideAdFieldset(string $basePath, string $side, string $label): \Filament\Schemas\Components\Fieldset
    {
        $path = "{$basePath}.{$side}";
        $typePath = "{$path}.type";

        return \Filament\Schemas\Components\Fieldset::make($label)->schema([
            \Filament\Forms\Components\Checkbox::make("{$path}.enabled")
                ->label('启用'),
            Select::make($typePath)
                ->label('类型')
                ->options(['html' => 'HTML代码', 'image' => '图片链接'])
                ->default('image')
                ->live(),
            Textarea::make("{$path}.content")
                ->label('HTML内容')
                ->rows(4)
                ->visible(fn ($get) => $get($typePath) === 'html'),
            TextInput::make("{$path}.image_url")
                ->label('图片地址')
                ->visible(fn ($get) => $get($typePath) === 'image'),
            TextInput::make("{$path}.link_url")
                ->label('点击链接')
                ->visible(fn ($get) => $get($typePath) === 'image'),
        ]);
    }

    /**
     * 核心能力模块广告
     */
    protected function getSiteAdsFeaturesFormField(): \Filament\Schemas\Components\Section
    {
        return $this->createAdsSectionFormField('features', '核心能力模块广告', '显示在核心能力区块两侧，仅在宽屏(≥1280px)显示');
    }

    /**
     * 应用场景模块广告
     */
    protected function getSiteAdsScenariosFormField(): \Filament\Schemas\Components\Section
    {
        return $this->createAdsSectionFormField('scenarios', '应用场景模块广告', '显示在应用场景区块两侧，仅在宽屏(≥1280px)显示');
    }

    /**
     * 数据统计模块广告
     */
    protected function getSiteAdsStatsFormField(): \Filament\Schemas\Components\Section
    {
        return $this->createAdsSectionFormField('stats', '数据统计模块广告', '显示在数据统计区块两侧，仅在宽屏(≥1280px)显示');
    }

    /**
     * 常见问题模块广告
     */
    protected function getSiteAdsFaqFormField(): \Filament\Schemas\Components\Section
    {
        return $this->createAdsSectionFormField('faq', '常见问题模块广告', '显示在FAQ区块两侧，仅在宽屏(≥1280px)显示');
    }

    /**
     * 版本信息展示区块
     */
    protected function getVersionInfoSection(): \Filament\Schemas\Components\Section
    {
        $updateInfo = $this->getUpdateInfo();
        $hasUpdate = $updateInfo && version_compare($updateInfo['version'], $this->version, '>');

        $description = "当前版本: v{$this->version}";
        if ($hasUpdate) {
            $description .= " | 最新版本: v{$updateInfo['version']} (有新版本可用)";
        }

        return \Filament\Schemas\Components\Section::make('主题信息')
            ->description($description)
            ->collapsed()
            ->schema([
                \Filament\Forms\Components\Placeholder::make('version_info')
                    ->label('版本信息')
                    ->content(fn () => new \Illuminate\Support\HtmlString($this->buildVersionInfoHtml($updateInfo, $hasUpdate))),
            ]);
    }

    /**
     * 构建版本信息 HTML
     */
    protected function buildVersionInfoHtml(?array $updateInfo, bool $hasUpdate): string
    {
        $html = '<div class="space-y-2">';
        $html .= '<p><strong>当前版本:</strong> v' . $this->version . '</p>';
        $html .= '<p><strong>作者:</strong> ' . $this->author . '</p>';
        $html .= '<p><strong>官网:</strong> <a href="' . $this->url . '" target="_blank" class="text-primary-500 hover:underline">' . $this->url . '</a></p>';

        if ($hasUpdate && $updateInfo) {
            $html .= '<div class="mt-4 p-4 bg-warning-50 dark:bg-warning-900/20 rounded-lg border border-warning-200 dark:border-warning-800">';
            $html .= '<p class="font-semibold text-warning-700 dark:text-warning-400">🎉 发现新版本: v' . $updateInfo['version'] . '</p>';
            if (!empty($updateInfo['changelog'])) {
                $html .= '<p class="mt-2 text-sm text-warning-600 dark:text-warning-500">更新说明: ' . e($updateInfo['changelog']) . '</p>';
            }
            if (!empty($updateInfo['download_url'])) {
                $html .= '<p class="mt-2"><a href="' . $updateInfo['download_url'] . '" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-warning-500 hover:bg-warning-600 text-white text-sm font-medium rounded-lg transition-colors">前往下载</a></p>';
            }
            $html .= '</div>';
        } elseif ($updateInfo) {
            $html .= '<p class="mt-2 text-success-600 dark:text-success-400">✓ 已是最新版本</p>';
        } else {
            $html .= '<p class="mt-2 text-gray-500">无法检查更新（未配置更新检查地址或网络问题）</p>';
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * 检查更新并发送通知
     */
    protected function checkForUpdates(): void
    {
        if (empty($this->updateCheckUrl)) {
            return;
        }

        $updateInfo = $this->getUpdateInfo();
        if (!$updateInfo) {
            return;
        }

        $hasUpdate = version_compare($updateInfo['version'], $this->version, '>');
        if ($hasUpdate) {
            Notification::make()
                ->title('XTheme 有新版本可用')
                ->body("当前版本: v{$this->version}，最新版本: v{$updateInfo['version']}")
                ->warning()
                ->persistent()
                ->send();
        }
    }

    /**
     * 获取更新信息（带缓存）
     */
    protected function getUpdateInfo(): ?array
    {
        if (empty($this->updateCheckUrl)) {
            return null;
        }

        $cacheKey = 'xtheme_update_info';

        return Cache::remember($cacheKey, now()->addHours(6), function () {
            try {
                $response = Http::timeout(5)->get($this->updateCheckUrl);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['version'])) {
                        return [
                            'version' => $data['version'],
                            'changelog' => $data['changelog'] ?? '',
                            'download_url' => $data['download_url'] ?? '',
                        ];
                    }
                }
            } catch (\Exception $e) {
                // 静默处理异常，返回 null
            }

            return null;
        });
    }

    /**
     * 清除更新缓存（可在需要时手动调用）
     */
    public function clearUpdateCache(): void
    {
        Cache::forget('xtheme_update_info');
    }
}