# Hehongyuanlove WeChat Login

![License](https://img.shields.io/badge/license-MIT-blue.svg)

A [Flarum](http://flarum.org) extension. Allow users to log in with WeChat

### 说明
- 此插件是基于需求魔改的，原插件： nomiscz/flarum-ext-auth-wechat
- 注册后 自动生成 随机+@xxx.com 作为邮箱，没有做邮箱的重复性检查，所以可能会有重复的邮箱
- 注册后 密码与邮箱相同
- 注册后 自动通过邮箱验证
- 注册后 用户名为随机生成的字符串

### Installation

Use [Bazaar](https://discuss.flarum.org/d/5151-flagrow-bazaar-the-extension-marketplace) or install manually with composer:

```sh
composer require hehongyuanlove/flarum-ext-auth-wechat
```

### Updating

```sh
composer update hehongyuanlove/flarum-ext-auth-wechat
```

### Links

- [Packagist Copy From ](https://packagist.org/packages/nomiscz/flarum-ext-auth-wechat)
