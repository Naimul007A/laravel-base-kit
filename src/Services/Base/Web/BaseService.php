<?php
namespace Naimul007A\LaravelBaseKit\Services\Base\Web;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BaseService {
    public function __construct(protected Model $model, protected array $searchable = []) {}

    public function index(array $params = [], $withRelationships = [], $select = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator {
        $query = $this->model::query();
        // Select specific columns if provided
        if (! empty($select)) {
            $query->select($select);
        }
        // Apply search
        if (! empty($params['search']) && count($this->searchable)) {
            $search = $params['search'];
            $query->where(function (Builder $q) use ($search) {
                foreach ($this->searchable as $column) {
                    if (str_contains($column, '.')) {
                        $parts          = explode('.', $column);
                        $relation       = array_shift($parts);
                        $relationColumn = implode('.', $parts);
                        $q->orWhereHas($relation, function (Builder $q) use ($search, $relationColumn) {
                            $q->where($relationColumn, 'LIKE', "%{$search}%");
                        });
                    } else {
                        $q->orWhere($column, 'LIKE', "%{$search}%");
                    }
                }
            });
        }
        // Apply filters
        if (! empty($params['filters'])) {
            $this->applyFilters($query, $params['filters']);
        }

                                           // Pagination
        $perPage = $params['limit'] ?? 10; //default 10 items per page
        $page    = $params['page'] ?? 1;   //default 1st page
                                           //with relationships
        if (! empty($withRelationships)) {
            $query->with($withRelationships);
        }
        if (! empty($params['sort'])) {
            $query->orderBy($params['sort']['key'], $params['sort']['value'] ?? 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
        if (isset($params["counts"]) && $params["counts"]) {
            // Handle count-based filtering
            foreach ($params["counts"] as $relation => $count) {
                if (is_array($count) && isset($count['min'])) {
                    // Filter by minimum count of related models
                    $query->has($relation, '>=', $count['min']);
                } elseif (is_array($count) && isset($count['max'])) {
                    // Filter by maximum count of related models
                    $query->has($relation, '<=', $count['max']);
                } elseif (is_array($count) && isset($count['exact'])) {
                    // Filter by exact count of related models
                    $query->has($relation, '=', $count['exact']);
                } else {
                    // Default to minimum count if just a number is provided
                    if (str_contains($relation, 'or:')) {
                        $mainRealation = str_replace('or:', '', $relation);
                        $query->orWhereHas($mainRealation, null, '>=', $count);
                    } else {
                        $query->has($relation, '>=', $count);
                    }
                }
            }
        }
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function store(array $data): Model {
        return $this->model->create($data);
    }
    public function show($id, $withRelationships = []): Model {
        $query = $this->model->query();
        if (! empty($withRelationships)) {
            $query->with($withRelationships);
        }
        return $query->findOrFail($id);
    }

    public function update(array $data, $id): Model {
        $model = $this->model->findOrFail($id);
        $model->update($data);

        return $model;
    }

    public function destroy($id): void {
        $this->model->findOrFail($id)->delete();
    }

    public function bulkDestroy(array $ids): void {
        $this->model->whereIn('id', $ids)->delete();
    }

    protected function applyFilters(Builder $query, array $filters): void {
        // Handle direct date range filters (when from/to are at root level)
        if (isset($filters['from']) || isset($filters['to'])) {
            if (isset($filters['from'])) {
                $query->whereDate('created_at', '>=', $this->formatDate($filters['from']));
            }
            if (isset($filters['to'])) {
                $query->whereDate('created_at', '<=', $this->formatDate($filters['to']));
            }
            unset($filters['from'], $filters['to']);
        }
        //retive soft deletes items
        if (isset($filters["status"]) && $filters["status"] === "trashed") {
            $query->onlyTrashed();
            unset($filters["status"]);
        }
        foreach ($filters as $column => $value) {
            // Handle date range filters for specific columns
            if (is_array($value) && isset($value['from']) || isset($value['to'])) {
                if (isset($value['from'])) {
                    $query->whereDate($column, '>=', $this->formatDate($value['from']));
                }
                if (isset($value['to'])) {
                    $query->whereDate($column, '<=', $this->formatDate($value['to']));
                }
                continue;
            }
            // Handle OR groups (special key 'or')
            if ($column === 'or' && is_array($value)) {

                $query->where(function (Builder $q) use ($value) {
                    foreach ($value as $orColumn => $orValue) {
                        $this->applyWhereCondition($q, $orColumn, $orValue, 'or');
                    }
                });
            }
            // Handle whereHas conditions (relationships)
            elseif (str_contains($column, '.')) {
                $this->applyWhereHasCondition($query, $column, $value);
            }
            // Handle AND conditions
            else {
                $this->applyWhereCondition($query, $column, $value);
            }
        }
    }

    /**
     * Format date to YYYY-MM-DD format
     * Supports multiple input formats:
     * - DD-MM-YYYY (01-01-2025)
     * - YYYY-MM-DD (2025-01-01)
     * - DD/MM/YYYY (01/01/2025)
     * - YYYY/MM/DD (2025/01/01)
     */
    protected function formatDate(string $date): string {
        // If date is already in YYYY-MM-DD format, return as is
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        // Try to parse the date
        $parsedDate = date_create_from_format('d-m-Y', $date) ?:
        date_create_from_format('Y-m-d', $date) ?:
        date_create_from_format('d/m/Y', $date) ?:
        date_create_from_format('Y/m/d', $date);

        if (! $parsedDate) {
            throw new Exception("Invalid date format. Supported formats: DD-MM-YYYY, YYYY-MM-DD, DD/MM/YYYY, YYYY/MM/DD", 400);
        }

        return $parsedDate->format('Y-m-d');
    }

    protected function applyWhereCondition(Builder $query, $column, $value, $boolean = 'and'): void {
        // Handle nested OR groups (e.g., 'or:name')
        if (str_contains($column, 'or:')) {
            $column  = str_replace('or:', '', $column);
            $boolean = 'or';
        }
        $not = false;
        if (str_contains($column, 'not:')) {
            $column = str_replace('not:', '', $column);
            $not    = true;
        }

        if (is_array($value)) {
            if ($not) {
                $method = $boolean === 'or' ? 'orWhereNotIn' : 'whereNotIn';
            } else {
                $method = $boolean === 'or' ? 'orWhereIn' : 'whereIn';
            }
        } else {
            if ($not) {
                $method = $boolean === 'or' ? 'orWhereNot' : 'whereNot';
            } else {
                $method = $boolean === 'or' ? 'orWhere' : 'where';
            }
        }
        $query->$method($column, $value);
    }
    protected function applyWhereHasCondition(Builder $query, $column, $value): void {
        $isNotRelation = str_starts_with($column, 'not:');
        if ($isNotRelation) {
            $column = substr($column, 4);
        }

        $parts        = explode('.', $column);
        $relation     = array_shift($parts);
        $nestedColumn = implode('.', $parts);

        $method = $isNotRelation ? 'whereDoesntHave' : 'whereHas';

        $query->$method($relation, function (Builder $q) use ($nestedColumn, $value) {
            // If there are still dots in the column name, it's a nested relation
            if (str_contains($nestedColumn, '.')) {
                $this->applyWhereHasCondition($q, $nestedColumn, $value);
            } else {
                $this->applyWhereCondition($q, $nestedColumn, $value);
            }
        });
    }
}
