<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class AuthorizationService
{
    /**
     * Get authorized filters based on user role
     */
    public function getAuthorizedFilters(array $requestFilters): array
    {
        $user = Auth::user();
        $role = $user->getRoleNames()->first();

        if ($role === 'master') {
            return $requestFilters;
        }

        // Non-master users are restricted to their warehouse and user
        return array_merge($requestFilters, [
            'warehouse_id' => $user->warehouse_id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Check if user is master
     */
    public function isMaster(): bool
    {
        return Auth::user()->getRoleNames()->first() === 'master';
    }

    /**
     * Get user's warehouse ID
     */
    public function getUserWarehouseId(): ?int
    {
        return Auth::user()->warehouse_id;
    }

    /**
     * Get current user ID
     */
    public function getCurrentUserId(): int
    {
        return Auth::id();
    }

    /**
     * Apply role-based restrictions to query filters
     */
    public function applyRoleRestrictions(array $filters): array
    {
        if ($this->isMaster()) {
            return $filters;
        }

        // Force non-master users to use their own warehouse and user ID
        $filters['warehouse_id'] = $this->getUserWarehouseId();
        $filters['user_id'] = $this->getCurrentUserId();

        return $filters;
    }

    /**
     * Check if user can access warehouse data
     */
    public function canAccessWarehouse(?int $warehouseId): bool
    {
        if ($this->isMaster()) {
            return true;
        }

        return $warehouseId === $this->getUserWarehouseId();
    }

    /**
     * Check if user can access user data
     */
    public function canAccessUser(?int $userId): bool
    {
        if ($this->isMaster()) {
            return true;
        }

        return $userId === $this->getCurrentUserId();
    }
}
