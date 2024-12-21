# ZCrudGen - Laravel CRUD API Generator

A sophisticated Laravel package that automates the creation of production-ready CRUD APIs. Built with modern Laravel practices in mind, ZCrudGen generates fully functional REST APIs following the Service Repository pattern, complete with advanced features like intelligent filtering, OpenAPI documentation, and AI-powered business logic.

## 🚀 Key Features

- **Complete CRUD Operations**: Generates index, show, create, update, and delete endpoints
- **Service Repository Pattern**: Follows best practices with proper separation of concerns
- **Smart Schema Detection**: Automatically analyzes your database structure
- **Advanced Filtering**: Filter by any field with support for complex queries
- **Relationship Handling**: Supports multiple related models (e.g., country -> province -> city)
- **API Documentation**: Automatic OpenAPI/Swagger documentation generation
- **Authentication & Authorization**: Built-in middleware and permission integration
- **AI-Powered Logic**: Optional AI assistance for generating business logic (powered by OpenAI)
- **Highly Customizable**: All generated code is placed in your project for full control
- **Modern PHP**: Built for PHP 8.2+ with proper type hinting and nullability
- **Well-Tested**: Comprehensive test suite ensuring reliability

## 💡 Perfect For

- Rapid API development
- Projects requiring standardized CRUD operations
- Teams looking to maintain consistent code structure
- Applications needing well-documented APIs
- Developers who value clean, maintainable code

## 🛠️ Built With

- Modern PHP 8.2+
- Laravel 10/11 Support
- Service Repository Pattern
- OpenAPI/Swagger Documentation
- AI Integration Capabilities

## ⭐ Why ZCrudGen?

- **Save Time**: Eliminate repetitive CRUD boilerplate code
- **Best Practices**: Generated code follows Laravel and PHP best practices
- **Maintainable**: Clean, well-documented, and easily modifiable code
- **Future-Proof**: Built with modern PHP features and practices
- **Flexible**: Works with existing projects and can be customized to your needs

# ZCrudGen Documentation

## Table of Contents
1. [Installation](#installation)
2. [Configuration](#configuration)
3. [Basic Usage](#basic-usage)
4. [Advanced Features](#advanced-features)
5. [AI Integration](#ai-integration)
6. [OpenAPI/Swagger](#openapi-swagger)
7. [Testing](#testing)
8. [Contributing](#contributing)

## Installation

```bash
composer require zaidysf/zcrudgen
php artisan vendor:publish --provider="ZaidYasyaf\Zcrudgen\ZcrudgenServiceProvider"
```

## Configuration
The package can be configured through the config/zcrudgen.php file. Key options include:

- Namespace customization
- Path configuration
- Authentication & authorization settings
- OpenAPI/Swagger configuration
- AI integration settings

Example Configuration
```php
return [
    'namespace' => 'App',
    'paths' => [
        'model' => app_path('Models'),
        'controller' => app_path('Http/Controllers/API'),
        // ...
    ],
    'auth' => [
        'middleware' => [
            'enabled' => true,
            'default' => ['auth:sanctum'],
        ],
        'permissions' => [
            'enabled' => true,
            'prefix' => ['create', 'read', 'update', 'delete'],
        ],
    ],
    'swagger' => [
        'enabled' => true,
        'version' => '3.0.0',
    ],
    'ai' => [
        'enabled' => false,
        'api_key' => env('OPENAI_API_KEY'),
        'model' => 'gpt-4',
    ],
];
```

## Basic Usage
Generate a basic CRUD API:
```bash
php artisan zcrudgen:make User
```
With relationships:
```bash
php artisan zcrudgen:make City --relations="country,state"
```
With middleware:
```bash
php artisan zcrudgen:make Product --middleware="auth:sanctum,verified"
```
With permissions:
```bash
php artisan zcrudgen:make Order --permissions
```

## Advanced Features

### Relationships

The package supports automatic generation of related models and their relationships:

```bash
php artisan zcrudgen:make City --relations="country,state"
```

This will:

- Set up proper model relationships
- Include related data in resources
- Add relationship validation in requests
- Generate nested API documentation

### Custom Middleware

You can specify custom middleware for your API endpoints:

```bash
php artisan zcrudgen:make Product --middleware="auth:sanctum,verified,custom"
```

### Permission Integration
When using the --permissions flag, the package will:

- Generate standard CRUD permissions (create-{model}, read-{model}, etc.)
- Add permission middleware to controllers
- Document permission requirements in OpenAPI

### Advanced Filtering
All generated APIs support advanced filtering:

```php
// Filter by exact match
/api/users?name=John

// Filter by date range
/api/users?created_at[from]=2024-01-01&created_at[to]=2024-12-31

// Filter by relationship
/api/cities?country_id=1

// Multiple filters
/api/users?status=active&role=admin
```

## AI Integration
### Configuration
Enable AI integration in your .env:

```env
ZCRUDGEN_AI_ENABLED=true
OPENAI_API_KEY=your-api-key
```

### Features
The AI integration provides:

- Intelligent business logic generation
- Smart validation rules
- Automated event handling
- Cache strategy suggestions
- Data transformation optimization

### Example AI-Generated Logic

```php
public function create(array $data): Model
{
    // AI-generated validation
    $this->validateCreationRules($data);

    // AI-suggested caching strategy
    $cacheKey = "product:{$data['sku']}";
    
    if (Cache::has($cacheKey)) {
        throw new DuplicateProductException();
    }

    DB::beginTransaction();
    try {
        $product = $this->repository->create($data);
        
        // AI-suggested event
        ProductCreated::dispatch($product);
        
        Cache::put($cacheKey, $product, now()->addDay());
        
        DB::commit();
        return $product;
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

## OpenAPI/Swagger
### Automatic Documentation
The package automatically generates OpenAPI documentation for all endpoints:

```yaml
/api/products:
  get:
    tags:
      - Products
    summary: List all products
    parameters:
      - name: name
        in: query
        schema:
          type: string
      # ... other parameters
```

### Customization
You can customize the generated documentation in `config/zcrudgen.php`:

```php
'swagger' => [
    'enabled' => true,
    'version' => '3.0.0',
    'title' => 'Your API Title',
    'description' => 'Your API Description',
],
```

## Testing
Run the test suite:
```bash
composer test
```
Run specific tests:
```bash
composer test -- --filter=TestName
```
Coverage report:
```bash
composer test-coverage
```
