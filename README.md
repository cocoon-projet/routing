[![codecov](https://codecov.io/gh/cocoon-projet/routing/graph/badge.svg?token=9OW20TK75K)](https://codecov.io/gh/cocoon-projet/routing) [![PHP Composer](https://github.com/cocoon-projet/routing/actions/workflows/ci.yml/badge.svg)](https://github.com/cocoon-projet/routing/actions/workflows/ci.yml)

# cocoon-projet/routing

> Un routeur PHP moderne et flexible pour vos applications web

## Description

cocoon-projet/routing est une librairie de routage PHP 8 qui offre une API simple et intuitive pour gérer les routes de votre application web. Elle utilise FastRoute en interne et supporte les fonctionnalités modernes de PHP 8.

## Caractéristiques

- Compatible PHP 8.0+
- Support des méthodes HTTP (GET, POST, PUT, DELETE, PATCH, HEAD)
- Routes nommées
- Groupes de routes
- Patterns de validation de paramètres
- Cache des routes
- Support des contrôleurs avec autowiring
- Interface Facade pour une utilisation simplifiée
- Middleware PSR-15
- Tests unitaires complets

## Installation

```bash
composer require cocoon-projet/routing
```

## Utilisation

### Configuration de base

```php
use Cocoon\Routing\Router;

$router = Router::getInstance();

// Route simple
$router->get('/home', 'App\Controllers\HomeController@index');

// Route avec paramètres
$router->get('/user/{id}', 'App\Controllers\UserController@show');

// Route avec validation de paramètres
$router->get('/post/{id}', 'App\Controllers\PostController@show')
    ->with('id', '[0-9]+');
```

### Utilisation de la Facade

```php
use Cocoon\Routing\Facade\Route;

// Routes simples
Route::get('/home', 'App\Controllers\HomeController@index');
Route::post('/user', 'App\Controllers\UserController@store');

// Route avec plusieurs méthodes
Route::match(['GET', 'POST'], '/api/users', 'App\Controllers\Api\UserController@handle');

// Route avec validation de paramètres
Route::get('/post/{id}', 'App\Controllers\PostController@show')
    ->with('id', '[0-9]+');

// Routes nommées
Route::get('/user/{id}', 'App\Controllers\UserController@show')
    ->name('user.show');
```

### Groupes de routes

```php
// Groupe de routes avec préfixe
Route::group('admin', function() {
    Route::get('/dashboard', 'App\Controllers\Admin\DashboardController@index');
    Route::get('/users', 'App\Controllers\Admin\UserController@index');
});

// Groupe avec validation de paramètres
Route::group('api', function() {
    Route::get('/users/{id}', 'App\Controllers\Api\UserController@show')
        ->with('id', '[0-9]+');
});
```

### Routes RESTful

```php
// Création de routes RESTful pour une ressource
Route::resource('posts', 'App\Controllers\PostController');
```

Cela crée automatiquement les routes suivantes :
- GET /posts (index)
- GET /posts/add (add)
- POST /posts/create (create)
- GET /posts/{id} (show)
- GET /posts/{id}/edit (edit)
- PUT /posts/{id} (update)
- DELETE /posts/{id} (delete)

### Middleware

```php
use Cocoon\Routing\Middleware\DispatcherMiddleware;

// Configuration du middleware
$middleware = new DispatcherMiddleware($router);

// Utilisation avec un framework PSR-15
$response = $middleware->process($request, $handler);
```

### Cache des routes

```php
// Activation du cache des routes
$router->cache(true, __DIR__ . '/cache');
```

### Patterns de validation

```php
// Patterns prédéfinis
Route::get('/user/{id}', 'App\Controllers\UserController@show'); // {id} est automatiquement validé comme numérique

// Patterns personnalisés
Route::get('/post/{slug}', 'App\Controllers\PostController@show')
    ->with('slug', '[a-z0-9\-]+');

// Patterns multiples
Route::get('/category/{category}/post/{slug}', 'App\Controllers\PostController@show')
    ->withs([
        'category' => '[a-z]+',
        'slug' => '[a-z0-9\-]+'
    ]);
```

## Tests

La librairie inclut une suite complète de tests unitaires. Pour exécuter les tests :

```bash
composer test
```

Pour générer un rapport de couverture de code :

```bash
composer test:coverage
```

## Intégration Continue

Le projet utilise GitHub Actions pour l'intégration continue. Les tests sont exécutés automatiquement sur :
- PHP 8.0


## Contribution

Les contributions sont les bienvenues ! N'hésitez pas à :
1. Fork le projet
2. Créer une branche pour votre fonctionnalité
3. Commiter vos changements
4. Pousser vers la branche
5. Ouvrir une Pull Request

## Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.
