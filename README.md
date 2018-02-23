# Wordpress Auth Parser

### Возможности:
* Авторизация в админке WP

### Инструменты:
* CURL
* PHP

```
В файле config.php хранится ф-ция которая возвращает массив данных для авторизации
$options = array(
	'user' => array(
		'log' => 'admin',
		'pwd' => 'admin',
	),
	'url' => 'http://mysite.ru',
	'path' => 'wp-login.php'
);
```