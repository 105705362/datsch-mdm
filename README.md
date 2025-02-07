Datsch-MDM 核心框架 - 数据与体验管理平台：PIM、MDM、CDP、DAM、DXP/CMS 和数字商务


## 概览
![技术和架构]

## 关键优势
### ⚒ 数据建模和 UI 设计同步进行
无论是处理非结构化的网页文档还是 MDM/PIM 的结构化数据，您都可以定义 UI 设计（网页文档通过模板，结构化数据通过直观的图形编辑器），Datsch-MDM 知道如何高效地持久化数据，并优化以便快速访问。

### 🎛 适应性强的通用数据框架
由于框架的方法，Datsch-MDM 非常灵活，能完美适应您的需求。基于著名的 Symfony 框架构建，为您的项目提供了一个坚实且现代的基础。


### 💎 在一个平台上整合您的数字世界
不再需要在 MDM/PIM、电子商务、DAM 和 Web-CMS 之间进行 API、导入/导出和同步操作。一切无缝协作，本地化地实现了 Datsch-MDM 的目标。

### ✨ 现代且直观的 UI
我们热爱设计高效且优化用户体验的漂亮用户界面，为编辑者提供了一个卓越的使用体验。

## 预览和演示
### 数据对象
![Datsch-MDM 管理界面截图 PIM/MDM]
根据预定义的数据模型管理任何结构化数据，无论是手动还是通过 API 自动进行。使用类编辑器定义对象的结构和属性。管理任何数据——产品（PIM/MDM）、类别、客户（CDP）、订单（数字商务）、博客文章（DXP/CMS）。数据对象提供了从单一来源管理多输出渠道的结构化数据的可能性。通过集中化数据，Datsch-MDM 的数据对象使您能够在更短的时间内实现更好的数据完整性和数据质量，从而在多个接触点上创建和维护一致的、最新的客户体验。

### 数字资产
![Datsch-MDM 管理界面截图 DAM]
资产是 Datsch-MDM 的 DAM 部分。存储、管理和组织图像、视频、PDF、Word/Excel 文档等数字文件。可以在 Datsch-MDM 中直接预览 200 多种文件类型，编辑图片，并为文件添加额外的元数据。图像中的面部识别可用于焦点定位。编辑者只需在系统中维护一个高分辨率版本的文件。Datsch-MDM 可以自动生成各种渠道所需的所有输出格式，例如商务、应用程序、网站。当然，还包括全面的用户管理和版本控制。

### 文档
![Datsch-MDM 管理界面截图 CMS]
Datsch-MDM 的 DXP/CMS 部分用于管理非结构化内容，例如网站的页面和导航。基于 Twig 模板，文档呈现物理的 HTML/CSS 页面，并提供管理数据呈现的能力，正如客户体验它们的方式一样。管理员可以通过排列预定义的布局元素来组成文档。Datsch-MDM 文档提供多语言和多站点功能，包括电子邮件和新闻通讯。前端灵活性使内容和商务完美融合。您还可以使用它们为离线渠道创建内容，例如印刷目录（网络到印刷）。

#### 演示 (社区版)
_用户名_：`admin`  
_密码_：`demo`

## 开始使用
_**只需 3 个命令即可开始！**_ 😎
```bash
COMPOSER_MEMORY_LIMIT=-1 composer create-project pimcore/skeleton ./my-project
cd ./my-project
./vendor/bin/pimcore-install
```

这将安装一个空的骨架应用程序，我们还提供了一个便捷的演示包——当然也是 3 个命令 💪
