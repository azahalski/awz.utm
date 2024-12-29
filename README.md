# awz.cookiessett

### [Установка модуля](https://github.com/zahalski/cookiessett/tree/main/docs/install.md)


<!-- desc-start -->

Модуль содержит Api и компонент для запроса разрешения на использование cookies для CMS 1c-Битрикс.

**Поддерживаемые редакции CMS Битрикс:**<br>
«Старт», «Стандарт», «Малый бизнес», «Бизнес», «Корпоративный портал», «Энтерпрайз», «Интернет-магазин + CRM»

<!-- desc-end -->

<!-- dev-start -->

## Документация для разработчиков

```php
use Awz\CookiesSett\App as CookieApp;
if(\Bitrix\Main\Loader::includeModule('awz.cookiessett')){

	$app = CookieApp::getInstance();
	if($app->check(CookieApp::USER_TECH)){
		//разрешены функциональные
	}
	if($app->check(CookieApp::MARKET_EXT)){
		//разрешены маркетинговые
	}
	if($app->check(CookieApp::USER_TECH & CookieApp::MARKET_EXT)){
		//разрешены маркетинговые и функциональные
	}
	if($app->check(CookieApp::USER_TECH | CookieApp::MARKET_EXT)){
		//разрешены маркетинговые или функциональные
	}
	if($app->isEmpty()){
		//пользователь еще не выбрал согласие или отмену
	}

}
```

<!-- dev-end -->

<!-- cl-start -->
## История версий

https://github.com/zahalski/cookiessett/blob/master/CHANGELOG.md

<!-- cl-end -->

