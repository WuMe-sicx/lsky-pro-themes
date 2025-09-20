<?php

namespace Themes\XiaoXuYa;

use App\Contracts\ThemeAbstract;
use App\Support\Attribute;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class XiaoXuYaTheme extends ThemeAbstract
{
    public string $id = 'XiaoXuYa';

    public string $name = 'XiaoXuYa';

    public ?string $description = '此默认主题使用 Vue3 + Vite + NativeUI 开发的前后端分离主题。';

    public string $author = 'XiaoXuYa';

    public string $version = '1.0.0';

    public ?string $url = 'https://www.sicx.top';

    public function routes(): void
    {
        Route::any('/{any}', fn (): View => view("{$this->id}::index"))->where('any', '^(?!api).*');
    }

    public function configurable(): array
    {
        return [
            Tabs::make()->schema([
                Tabs\Tab::make('基础设置')->schema([
                    Grid::make()->schema([
                        $this->getSiteTitleFormField(),
                        $this->getSiteSubtitleFormField(),
                    ]),
                    $this->getSiteIconUrlFormField(),
                    $this->getSiteKeywordsFormField(),
                    $this->getSiteDescriptionFormField(),
                    $this->getSiteHomepageTitleFormField(),
                    $this->getSiteHomepageDescriptionFormField(),
                    $this->getSiteNoticeFormField(),
                    $this->getSiteUserLoginTypesFormField(),
                    $this->getSitePoliceRecordFormField(),
                ]),
                Tabs\Tab::make('背景设置')->schema([
                    $this->getSiteHomepageBackgroundImageUrlFormField(),
                    $this->getSiteAuthPageBackgroundImageUrlFormField(),
                    $this->getSiteHomepageBackgroundImagesFormField(),
                    $this->getSiteAuthPageBackgroundImagesFormField(),
                ]),
                Tabs\Tab::make('友情链接')->schema([
                    $this->getFriendlyLinksFormField(),
                ]),
                Tabs\Tab::make('社交媒体')->schema([
                    $this->getSocialMediaLinksFormField(),
                ]),
                Tabs\Tab::make('模块控制')->schema([
                    Grid::make(2)->schema([
                        $this->getProductFeaturesModuleField(),
                        $this->getProductAdvantagesModuleField(),
                        $this->getPlatformStatsModuleField(),
                        $this->getPricingPlansModuleField(),
                    ]),
                ]),
                Tabs\Tab::make('产品对比')->schema([
                    $this->getPlatformComparisonFormField(),
                ]),
                Tabs\Tab::make('高级设置')->schema([
                    $this->getSiteCustomCssFormField(),
                    $this->getSiteCustomJsFormField(),
                ]),
            ])
        ];
    }

    public function casts(): array
    {
        return [
            'homepage_background_images' => new Attribute(
                get: fn($value) => is_array($value) 
                    ? array_map(fn($path) => $this->convertToFullUrl($path), $value)
                    : []
            ),
            'auth_page_background_images' => new Attribute(
                get: fn($value) => is_array($value) 
                    ? array_map(fn($path) => $this->convertToFullUrl($path), $value)
                    : []
            ),
            'friendly_links' => new Attribute(
                get: fn($value) => is_array($value) ? $value : []
            ),
            'platform_comparison' => new Attribute(
                get: fn($value) => is_array($value) ? $value : []
            ),
            'social_media_links' => new Attribute(
                get: fn($value) => is_array($value) ? $value : []
            ),
        ];
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
            ->options([
                'email' => '邮箱',
                'phone' => '手机号',
                'username' => '用户名'
            ]);
    }

    /**
     * 公安备案号
     */
    protected function getSitePoliceRecordFormField(): TextInput
    {
        return TextInput::make('payload.police_record_no')
            ->label('公安备案号')
            ->maxLength(100)
            ->placeholder('请输入公安备案号（如：京公网安备 11010502000000号）')
            ->helperText('公安部备案号，留空则不显示');
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
            ->language(CodeEditor\Enums\Language::Css)
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
            ->language(CodeEditor\Enums\Language::JavaScript)
            ->columnSpanFull();
    }

    /**
     * 友情链接管理
     */
    protected function getFriendlyLinksFormField(): Repeater
    {
        return Repeater::make('payload.friendly_links')
            ->label('友情链接管理')
            ->schema([
                Grid::make()->schema([
                    TextInput::make('name')
                        ->label('链接名称')
                        ->required()
                        ->maxLength(50)
                        ->placeholder('请输入链接名称'),
                    TextInput::make('url')
                        ->label('链接地址')
                        ->required()
                        ->url()
                        ->placeholder('https://example.com'),
                ]),
                Grid::make(2)->schema([
                    Select::make('target')
                        ->label('打开方式')
                        ->options([
                            '_self' => '当前窗口',
                            '_blank' => '新窗口'
                        ])
                        ->default('_blank'),
                    Toggle::make('enabled')
                        ->label('启用状态')
                        ->default(true),
                ]),
                Textarea::make('description')
                    ->label('链接描述')
                    ->maxLength(200)
                    ->placeholder('可选：链接描述信息')
                    ->columnSpanFull(),
            ])
            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
            ->addActionLabel('添加友情链接')
            ->deleteAction(
                fn ($action) => $action->requiresConfirmation()
            )
            ->reorderable()
            ->collapsible()
            ->columnSpanFull()
            ->default([
                [
                    'name' => 'GitHub',
                    'url' => 'https://github.com',
                    'target' => '_blank',
                    'enabled' => true,
                    'description' => '全球最大的代码托管平台'
                ],
                [
                    'name' => 'Vue.js',
                    'url' => 'https://vue.js.org',
                    'target' => '_blank',
                    'enabled' => true,
                    'description' => '渐进式JavaScript框架'
                ]
            ]);
    }

    /**
     * 平台对比表格管理
     */
    protected function getPlatformComparisonFormField(): Repeater
    {
        return Repeater::make('payload.platform_comparison')
            ->label('平台对比表格')
            ->helperText('配置产品优势对比表格的内容')
            ->schema([
                TextInput::make('feature')
                    ->label('功能特性')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('例如：存储空间、图片有效期等'),
                Grid::make(2)->schema([
                    TextInput::make('ourPlatform')
                        ->label('我们的平台')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('例如：最大6PB、长期有效等'),
                    TextInput::make('ourAdvantage')
                        ->label('我们的优势')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('例如：海量容量、永久存储等'),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('competitors')
                        ->label('竞争对手')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('例如：10G-100G、短期有效等'),
                    TextInput::make('competitorDisadvantage')
                        ->label('竞争对手劣势')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('例如：有限制、存在丢失风险等'),
                ]),
                Toggle::make('isIcon')
                    ->label('显示图标')
                    ->helperText('启用后将显示勾号/叉号图标，不显示文字内容')
                    ->default(false),
            ])
            ->itemLabel(fn (array $state): ?string => $state['feature'] ?? null)
            ->addActionLabel('添加对比项目')
            ->deleteAction(
                fn ($action) => $action->requiresConfirmation()
            )
            ->reorderable()
            ->collapsible()
            ->columnSpanFull()
            ->default([
                [
                    'feature' => '存储空间',
                    'ourPlatform' => '最大6PB',
                    'ourAdvantage' => '海量容量',
                    'competitors' => '10G-100G',
                    'competitorDisadvantage' => '有限制',
                    'isIcon' => false
                ],
                [
                    'feature' => '图片有效期',
                    'ourPlatform' => '长期有效',
                    'ourAdvantage' => '永久存储',
                    'competitors' => '短期有效',
                    'competitorDisadvantage' => '存在丢失风险',
                    'isIcon' => false
                ],
                [
                    'feature' => '图片大小',
                    'ourPlatform' => '100MB',
                    'ourAdvantage' => '大文件支持',
                    'competitors' => '5MB-50MB',
                    'competitorDisadvantage' => '大小限制',
                    'isIcon' => false
                ],
                [
                    'feature' => '图片数量',
                    'ourPlatform' => '500万张',
                    'ourAdvantage' => '数量无限制',
                    'competitors' => '1000张',
                    'competitorDisadvantage' => '严格限制',
                    'isIcon' => false
                ],
                [
                    'feature' => '单次上传限制',
                    'ourPlatform' => '1000张/次',
                    'ourAdvantage' => '批量上传',
                    'competitors' => '5-100张/次',
                    'competitorDisadvantage' => '批量限制',
                    'isIcon' => false
                ],
                [
                    'feature' => '支持格式',
                    'ourPlatform' => '全格式',
                    'ourAdvantage' => '全面支持',
                    'competitors' => '部分格式',
                    'competitorDisadvantage' => '格式限制',
                    'isIcon' => false
                ],
                [
                    'feature' => '外链性能',
                    'ourPlatform' => '商业CDN',
                    'ourAdvantage' => '高性能',
                    'competitors' => '国外服务器',
                    'competitorDisadvantage' => '速度受限',
                    'isIcon' => false
                ],
                [
                    'feature' => '流量限制',
                    'ourPlatform' => '共享云盘直链流量',
                    'ourAdvantage' => '流量无限制',
                    'competitors' => '10GB',
                    'competitorDisadvantage' => '流量限制',
                    'isIcon' => false
                ],
                [
                    'feature' => 'OpenAPI',
                    'ourPlatform' => '',
                    'ourAdvantage' => '完整API支持',
                    'competitors' => '',
                    'competitorDisadvantage' => '无API',
                    'isIcon' => true
                ],
                [
                    'feature' => '防盗链鉴权',
                    'ourPlatform' => '',
                    'ourAdvantage' => '全面保护',
                    'competitors' => '',
                    'competitorDisadvantage' => '无保护',
                    'isIcon' => true
                ],
                [
                    'feature' => 'HTTPS传输',
                    'ourPlatform' => '',
                    'ourAdvantage' => 'SSL加密',
                    'competitors' => '',
                    'competitorDisadvantage' => '无保障',
                    'isIcon' => true
                ],
                [
                    'feature' => '自定义域名',
                    'ourPlatform' => '',
                    'ourAdvantage' => '域名支持',
                    'competitors' => '',
                    'competitorDisadvantage' => '不支持',
                    'isIcon' => true
                ],
                [
                    'feature' => '广告',
                    'ourPlatform' => '无广告',
                    'ourAdvantage' => '纯净体验',
                    'competitors' => '有广告',
                    'competitorDisadvantage' => '广告干扰',
                    'isIcon' => false
                ]
            ]);
    }

    /**
     * 社交媒体链接管理
     */
    protected function getSocialMediaLinksFormField(): Repeater
    {
        return Repeater::make('payload.social_media_links')
            ->label('社交媒体链接管理')
            ->helperText('配置品牌信息区域显示的社交媒体和联系方式按钮（最多5个）')
            ->maxItems(5)
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('按钮名称')
                        ->required()
                        ->maxLength(50)
                        ->placeholder('例如：GitHub、邮箱联系等'),
                    TextInput::make('url')
                        ->label('链接地址')
                        ->required()
                        ->placeholder('https://github.com 或 mailto:admin@example.com'),
                ]),
                Grid::make(3)->schema([
                    TextInput::make('icon')
                        ->label('图标类名')
                        ->required()
                        ->placeholder('fab fa-github')
                        ->helperText('支持 FontAwesome 图标类名'),
                    Select::make('color_theme')
                        ->label('颜色主题')
                        ->options([
                            'blue' => '蓝色主题',
                            'red' => '红色主题',
                            'green' => '绿色主题',
                            'purple' => '紫色主题',
                            'orange' => '橙色主题',
                            'cyan' => '青色主题',
                            'pink' => '粉色主题',
                            'yellow' => '黄色主题',
                        ])
                        ->default('blue')
                        ->required(),
                    Select::make('target')
                        ->label('打开方式')
                        ->options([
                            '_self' => '当前窗口',
                            '_blank' => '新窗口'
                        ])
                        ->default('_blank'),
                ]),
                Grid::make(2)->schema([
                    Toggle::make('enabled')
                        ->label('启用状态')
                        ->default(true),
                    TextInput::make('sort_order')
                        ->label('排序')
                        ->numeric()
                        ->default(0)
                        ->helperText('数值越小越靠前'),
                ]),
                Textarea::make('description')
                    ->label('按钮描述')
                    ->maxLength(200)
                    ->placeholder('可选：按钮描述信息，将显示为title提示')
                    ->columnSpanFull(),
            ])
            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
            ->addActionLabel('添加社交媒体按钮')
            ->deleteAction(
                fn ($action) => $action->requiresConfirmation()
            )
            ->reorderable()
            ->collapsible()
            ->columnSpanFull()
            ->default([
                [
                    'name' => 'GitHub',
                    'url' => 'https://github.com',
                    'icon' => 'fab fa-github',
                    'color_theme' => 'blue',
                    'target' => '_blank',
                    'enabled' => true,
                    'sort_order' => 1,
                    'description' => '项目源代码仓库'
                ],
                [
                    'name' => '邮箱联系',
                    'url' => 'mailto:admin@example.com',
                    'icon' => 'fas fa-envelope',
                    'color_theme' => 'red',
                    'target' => '_blank',
                    'enabled' => true,
                    'sort_order' => 2,
                    'description' => '联系我们'
                ],
                [
                    'name' => '官方网站',
                    'url' => 'https://example.com',
                    'icon' => 'fas fa-globe',
                    'color_theme' => 'green',
                    'target' => '_blank',
                    'enabled' => true,
                    'sort_order' => 3,
                    'description' => '官方网站'
                ],
                [
                    'name' => '文档中心',
                    'url' => '/docs',
                    'icon' => 'fas fa-book',
                    'color_theme' => 'purple',
                    'target' => '_blank',
                    'enabled' => true,
                    'sort_order' => 4,
                    'description' => '使用文档'
                ],
                [
                    'name' => 'API接口',
                    'url' => '/api',
                    'icon' => 'fas fa-code',
                    'color_theme' => 'orange',
                    'target' => '_blank',
                    'enabled' => true,
                    'sort_order' => 5,
                    'description' => 'API文档'
                ]
            ]);
    }

    /**
     * 产品功能模块控制
     */
    protected function getProductFeaturesModuleField(): Toggle
    {
        return Toggle::make('payload.enable_product_features')
            ->label('产品功能模块 - 显示产品功能特性介绍区域')
            ->default(true);
    }

    /**
     * 产品优势模块控制
     */
    protected function getProductAdvantagesModuleField(): Toggle
    {
        return Toggle::make('payload.enable_product_advantages')
            ->label('产品优势模块 - 显示产品优势和对比表格')
            ->default(true);
    }

    /**
     * 平台统计模块控制
     */
    protected function getPlatformStatsModuleField(): Toggle
    {
        return Toggle::make('payload.enable_platform_stats')
            ->label('平台统计模块 - 显示平台数据统计信息')
            ->default(true);
    }

    /**
     * 价格计划模块控制
     */
    protected function getPricingPlansModuleField(): Toggle
    {
        return Toggle::make('payload.enable_pricing_plans')
            ->label('价格计划模块 - 显示服务套餐和定价信息')
            ->default(true);
    }



    /**
     * 将相对路径转换为完整URL
     */
    protected function convertToFullUrl(?string $path): string
    {
        return $path ? Storage::url($path) : '';
    }
}