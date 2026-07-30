<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

abstract class Controller
{
    protected function applyGridSort(Builder $query, Request $request, string $modelClass, string $defaultColumn = 'created_at', string $defaultDirection = 'desc', array $aliases = []): Builder
    {
        $aliases = array_merge($this->commonGridSortAliases(), $aliases);
        $sort = $this->gridSortKey($request);
        $direction = $this->gridSortDirection($request, $defaultDirection);

        if (! is_string($sort) || trim($sort) === '') {
            return $this->applyDefaultGridSort($query, $modelClass, $defaultColumn, $defaultDirection);
        }

        $sort = Str::snake(trim($sort));
        $column = $aliases[$sort] ?? $sort;
        $column = Str::snake($column);

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass;
        $table = $model->getTable();

        if (Schema::hasColumn($table, $column)) {
            return $query->orderBy($table . '.' . $column, $direction);
        }

        if (array_key_exists($sort, $aliases) && preg_match('/^[A-Za-z0-9_]+$/', $column) && Str::endsWith($column, '_count')) {
            return $query->orderBy($column, $direction);
        }

        return $this->applyDefaultGridSort($query, $modelClass, $defaultColumn, $defaultDirection);
    }

    protected function applyGridSortToQuery(QueryBuilder $query, Request $request, string $table, array $columns, string $defaultColumn = 'created_at', string $defaultDirection = 'desc', array $aliases = []): QueryBuilder
    {
        $aliases = array_merge($this->commonGridSortAliases(), $aliases);
        $sort = $this->gridSortKey($request);
        $direction = $this->gridSortDirection($request, $defaultDirection);

        if (is_string($sort) && trim($sort) !== '') {
            $sort = Str::snake(trim($sort));
            $column = Str::snake($aliases[$sort] ?? $sort);

            if (in_array($column, $columns, true) && preg_match('/^[A-Za-z0-9_]+$/', $column)) {
                return $query->orderBy($table . '.' . $column, $direction);
            }
        }

        if (in_array($defaultColumn, $columns, true)) {
            return $query->orderBy($table . '.' . $defaultColumn, $defaultDirection);
        }

        if (in_array('id', $columns, true)) {
            return $query->orderBy($table . '.id', $defaultDirection);
        }

        return $query;
    }

    protected function applyDefaultGridSort(Builder $query, string $modelClass, string $defaultColumn = 'created_at', string $defaultDirection = 'desc'): Builder
    {
        $defaultDirection = $this->normalizeGridDirection($defaultDirection);

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass;
        $table = $model->getTable();

        if (Schema::hasColumn($table, $defaultColumn)) {
            return $query->orderBy($table . '.' . $defaultColumn, $defaultDirection);
        }

        return $query->orderBy($table . '.id', $defaultDirection);
    }

    protected function gridSortDirection(Request $request, string $defaultDirection = 'desc'): string
    {
        return $this->normalizeGridDirection((string) $request->query('direction', $request->query('dir', $defaultDirection)), $defaultDirection);
    }

    protected function gridSortKey(Request $request): mixed
    {
        return $request->query('sort', $request->query('sort_by'));
    }

    protected function commonGridSortAliases(): array
    {
        return [
            '#' => 'id',
            'dibuat' => 'created_at',
            'bergabung' => 'created_at',
        ];
    }

    protected function normalizeGridDirection(string $direction, string $fallback = 'desc'): string
    {
        $direction = strtolower($direction);
        $fallback = strtolower($fallback);
        $fallback = in_array($fallback, ['asc', 'desc'], true) ? $fallback : 'desc';

        return in_array($direction, ['asc', 'desc'], true) ? $direction : $fallback;
    }
}
