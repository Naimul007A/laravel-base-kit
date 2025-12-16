# Laravel Base Kit

A comprehensive base kit for Laravel applications providing a powerful `BaseService` for rapid API development.

## Installation

You can install the package via composer:

```bash
composer require naimul007a/laravel-base-kit
```

## Usage

### 1. Create a Service

Create a service class that extends `Naimul007A\LaravelBaseKit\Services\Base\Api\BaseService` OR
`Naimul007A\LaravelBaseKit\Services\Base\Web\BaseService` . You need to define the `$model` property and optionally the `$searchable` array for search functionality.

```php
<?php

namespace App\Services;

use App\Models\User;
//For Web Service
//use Naimul007A\LaravelBaseKit\Services\Base\Web\BaseService;
//For API Service
use Naimul007A\LaravelBaseKit\Services\Base\Api\BaseService;

class UserService extends BaseService
{
    public function __construct(User $model)
    {
        $this->model = $model;
        // Define columns that can be searched via the 'search' parameter
        $this->searchable = ['name', 'email', 'profile.bio'];
    }
}
```

### 2. Use in Controller

Inject your service into your controller. Here is a full example demonstrating how to use all available methods.

```php
<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // GET /users
    public function index(Request $request)
    {
        // Handles pagination, filtering, searching, and sorting
        $users = $this->userService->index($request->all(), ['profile']);
        return response()->json($users);
    }

    // GET /users/{id}
    public function show($id)
    {
        // Fetch a single user with relationships
        $user = $this->userService->show($id, ['profile']);
        return response()->json($user);
    }

    // GET /users/slug/{slug}
    public function showBySlug($slug)
    {
        // Fetch a user by slug
        $user = $this->userService->showBySlug($slug, ['profile']);
        return response()->json($user);
    }

    // POST /users
    public function store(Request $request)
    {
        // Validate request...
        $user = $this->userService->store($request->all());
        return response()->json($user, 201);
    }

    // PUT/PATCH /users/{id}
    public function update(Request $request, $id)
    {
        // Validate request...
        $user = $this->userService->update($id, $request->all());
        return response()->json($user);
    }

    // DELETE /users/{id}
    public function destroy($id)
    {
        $this->userService->delete($id);
        return response()->json(['message' => 'User deleted successfully']);
    }
}
```

## Service Methods

The `BaseService` provides several standardized methods to handle common API operations.

### `index`

Retrieve a paginated, filtered, and sorted list of resources.

```php
public function index(array $params = [], $withRelationships = [], $select = []): LengthAwarePaginator
```
- **[`@Advanced Filtering`](#advanced-filtering)**: See the "Advanced Filtering" section below for details on how to use filtering options.
-   **`$params`**: Request parameters (filters, sort, search, page, per_page).
-   **`$withRelationships`**: Array of relationships to eager load.
-   **`$select`**: Array of columns to select.

### `show`

Find a specific resource by its primary key (ID). Throws an exception if not found.

```php
public function show($id, array $with = [], array $select = [])
```

-   **`$id`**: The primary key of the resource.
-   **`$with`**: Array of relationships to eager load.
-   **`$select`**: Array of columns to select.

### `showBySlug`

Find a specific resource by its `slug` column. Throws an exception if not found.

```php
public function showBySlug($slug, array $with = [], array $select = [])
```

-   **`$slug`**: The slug value to search for.
-   **`$with`**: Array of relationships to eager load.
-   **`$select`**: Array of columns to select.

### `store`

Create a new resource.

```php
public function store(array $data)
```

-   **`$data`**: Associative array of data to create the model with.

### `update`

Update an existing resource. Throws an exception if the resource is not found.

```php
public function update($id, array $data)
```

-   **`$id`**: The primary key of the resource to update.
-   **`$data`**: Associative array of data to update.

### `delete`

Delete a resource. Throws an exception if the resource is not found.

```php
public function delete($id)
```

-   **`$id`**: The primary key of the resource to delete.

## Publishing Assets

To customize or modify the package's default files, you can publish them to your application.

### Main Installation Command

This command will publish core requests and either API-specific or Web-specific services and exceptions, depending on the `--api` option.

```bash
php artisan base-kit:install
```

-   **`--api`**: Use this flag to publish API-specific request classes (e.g., `FilterRequest` to `app/Http/Requests/Api`), API exceptions, and API base services.
    ```bash
    php artisan base-kit:install --api
    ```

### Publishing Individual Components

You can also publish specific parts of the package independently using `vendor:publish` with the following tags:

-   **Requests**: Publishes `Http/Requests` (like `FilterRequest`) to `app/Http/Requests`.
    ```bash
    php artisan vendor:publish --tag=base-kit-requests
    ```
-   **API Exceptions**: Publishes `Exceptions` (like `ApiException`) to `app/Exceptions`.
    ```bash
    php artisan vendor:publish --tag=base-kit-exceptions-api
    ```
-   **Web Services**: Publishes `Services/Base/Web` to `app/Services/Base`.
    ```bash
    php artisan vendor:publish --tag=base-kit-services
    ```
-   **API Services**: Publishes `Services/Base/Api` (like `BaseService`) to `app/Services/Base`.
    ```bash
    php artisan vendor:publish --tag=base-kit-services-api
    ```

## Advanced Filtering

The `filters` parameter allows for granular control over the query. It is typically passed as a nested array in the request (e.g., `?filters[status]=active`).

### 1. Basic Column Filtering
Filter by exact match.
```php
// ?filters[status]=active
['status' => 'active']
```

### 2. Array Filtering (Where In)
Filter by multiple values.
```php
// ?filters[status][]=active&filters[status][]=pending
['status' => ['active', 'pending']]
```

### 3. Date Range Filtering
**Global Date Range:** Filters by `created_at`.
```php
// ?filters[from]=2024-01-01&filters[to]=2024-12-31
['from' => '2024-01-01', 'to' => '2024-12-31']
```

**Column Specific Date Range:**
```php
// ?filters[published_at][from]=2024-01-01
['published_at' => ['from' => '2024-01-01']]
```
*Supported formats:* `DD-MM-YYYY`, `YYYY-MM-DD`, `DD/MM/YYYY`, `YYYY/MM/DD`.

### 4. Relationship Filtering
Filter based on related model columns using dot notation.
```php
// ?filters[posts.title]=My Post
['posts.title' => 'My Post']
```
*Prefix with `not:` to use `whereDoesntHave` logic.*
```php
// ?filters[not:posts.status]=archived
['not:posts.status' => 'archived']
```

### 5. Boolean Logic (OR / NOT)
**OR Condition:** Prefix the column key with `or:`.
```php
// ?filters[or:status]=pending
['or:status' => 'pending'] // Adds an OR WHERE condition
```

**NOT Condition:** Prefix the column key with `not:`.
```php
// ?filters[not:status]=banned
['not:status' => 'banned'] // Adds a WHERE NOT condition
```

**OR Groups:** Use the special `or` key to group conditions.
```php
['or' => [
    'status' => 'active',
    'role' => 'admin'
]]
// Result: ... AND (status = 'active' OR role = 'admin')
```

### 6. Soft Deletes
Retrieve trashed records.
```php
// ?filters[status]=trashed
['status' => 'trashed']
```

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License

[MIT](https://choosealicense.com/licenses/mit/)
