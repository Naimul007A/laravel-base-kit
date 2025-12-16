# Laravel Base Kit

A comprehensive base kit for Laravel applications providing a powerful `BaseService` for rapid API development.

## Installation

You can install the package via composer:

```bash
composer require naimul007a/laravel-base-kit
```

## Usage

### 1. Create a Service

Create a service class that extends `Naimul007A\LaravelBaseKit\Services\Base\Api\BaseService`. You need to define the `$model` property and optionally the `$searchable` array for search functionality.

```php
<?php

namespace App\Services;

use App\Models\User;
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

Inject your service into your controller and use the `index` method.

```php
<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        // The index method handles pagination, filtering, searching, and sorting automatically.
        return $this->userService->index($request->all());
    }
}
```

## BaseService `index` Method

The `index` method is the core of this package, providing a standardized way to retrieve paginated, filtered, and sorted data.

```php
public function index(array $params = [], $withRelationships = [], $select = []): LengthAwarePaginator
```

### Parameters

-   **`$params`**: An array of parameters (typically `$request->all()`).
    -   `search` (string): Search term applied to columns defined in `$searchable`. Supports dot notation for relationships (e.g., `relation.column`).
    -   `per_page` (int): Items per page (default: 10).
    -   `page` (int): Current page number (default: 1).
    -   `sort` (array): `['key' => 'column_name', 'value' => 'asc|desc']`. Default is `created_at` `desc`.
    -   `filters` (array): A complex array for advanced filtering (see below).
    -   `counts` (array): Filter by related model counts (e.g., `['posts' => ['min' => 5]]`).
-   **`$withRelationships`**: An array of relationships to eager load (e.g., `['posts', 'profile']`).
-   **`$select`**: An array of specific columns to select.

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
