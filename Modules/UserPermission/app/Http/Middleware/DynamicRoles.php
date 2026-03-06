<?php

namespace Modules\UserPermission\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Exceptions\UnauthorizedException;


class DynamicRoles
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
      //  dd(123);
        $user = auth()->user();

        // Ensure the user is logged in
        if (!$user) {
            throw UnauthorizedException::notLoggedIn();
        }

        if ($user->is_super_admin == 1) {
            return $next($request);
        }
        $routeName = Route::currentRouteName();

        $routePermissions = [
            'customers.create' => 'create_customer',
            'customers.index' => 'list_customer',
            'customers.store' => 'create_customer',
            'customers.edit' => 'edit_customer',
            'customers.update' => 'edit_customer',
            'customers.destroy' => 'delete_customer',
            'customers.show' => 'view_customer',

            'customer.groups.index' => 'customer_group_list',
            'customer.groups.create' => 'customer_group_create',
            'customer.groups.store' => 'customer_group_create',
            'customer.groups.edit' => 'customer_group_edit',
            'customer.groups.update' => 'customer_group_edit',
            'customer.groups.destroy' => 'customer_group_delete',

            'roles.index' => 'list_role',
            'roles.create' => 'create_role',
            'roles.edit' => 'update_role',
            'roles.destroy' => 'delete_role',

            'roles.assign-permissions.index' => 'list_permissions_role',
            'roles.assign-permissions' => 'assign_permissions_role',
            'roles.edit-permissions-form' => 'edit_permissions_role',


            'users.assign-roles' => 'assign_users_role',
            'user.edit' => 'edit_users_role',
            'users.delete' => 'delete_users_role',

            'user.index' => 'list_user',
            'user.create' => 'create_user',
            'user.edit' => 'update_user',
            'user.destroy' => 'delete_user',


            'roles.assign-permissions-user' => 'assign_vendor_to_users',
            'roles.edit-permissions-user' => 'edit_assigned_users',


            'products.index' => 'list_products',
            'products.create' => 'create_products',
            'products.edit' => 'edit_products',
            'products.bulk-delete' => 'delete_products',
            'products.delete' => 'delete_products',

            'products_review.index' => 'list_product_reviews',
            'products_review.create' => 'create_product_reviews',
            'products_review.edit' => 'edit_product_reviews',
            'products_review.destroy' => 'delete_product_reviews',


            'product_review_attributes.index' => 'list_product_review_attributes',
            'product_review_attributes.create' => 'create_product_review_attributes',
            'product_review_attributes.edit' => 'edit_product_review_attributes',
            'product_review_attributes.destroy' => 'delete_product_review_attributes',





            'product.attributes.index' => 'show_attribute',
            'product.attributes.create' => 'create_attribute',
            'product.attributes.edit' => 'edit_attribute',
            'product.attributes.delete' => 'delete_attribute',
            'product.attributes.bulk-delete' => 'delete_attribute',

            'product.attribute.sets.index' => 'show_attribute_set',
            'product.attribute.sets.create' => 'create_attribute_set',
            'product.attribute.sets.edit' => 'edit_attribute_set',
            'product.attribute.sets.delete' => 'delete_attribute_set',
            'product.attribute.sets.bulk-delete' => 'delete_attribute_set',

            'cms.pages' => 'list_user',
            'cms.pages.create' => 'list_user',
            'cms.pages.edit' => 'list_user',
            'cms.pages.destroy' => 'list_user',
            'cms.pages.bulk-delete' => 'list_user',

            'email.templates' => 'list_user',
            'email.templates.bulk-delete' => 'list_user',
            'roless' => 'list_user',










            'roless' => 'list_user',
        ];


        // Check if the route has a defined permission
        foreach ($routePermissions as $route => $permission) {
            if (str_contains($routeName, $route)) {
                if (!$user->can($permission)) {
                    throw UnauthorizedException::forPermissions([$permission]);
                }
                break; // Exit once a match is found
            }
        }

        return $next($request);
    }
}
