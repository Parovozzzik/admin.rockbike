<?php

namespace App\Settings\Routes;

/**
 * Class Routes
 * @package App\Settings\Routes
 */
class Routes
{
    /**
     * @return array
     */
    public static function get(): array
    {
        return [
            /** index */
            '#^/$#' => [
                'controller' => 'Index',
                'action' => 'Index'
            ],

            /** users */
            '#^/users$#' => [
                'controller' => 'Users',
                'action' => 'Index'
            ],
            '#^/users/registration$#' => [
                'controller' => 'Users',
                'action' => 'registration'
            ],
            '#^/users/login$#' => [
                'controller' => 'Users',
                'action' => 'login'
            ],
            '#^/users/logout$#' => [
                'controller' => 'Users',
                'action' => 'logout'
            ],
            '#^/users/view/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Users',
                'action' => 'view',
                'params' => [
                    'id',
                ],
            ],
            '#^/users/edit/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Users',
                'action' => 'edit',
                'params' => [
                    'id',
                ],
            ],
            '#^/users/restore-password$#' => [
                'controller' => 'Users',
                'action' => 'restorePassword',
            ],
            '#^/users/change-password$#' => [
                'controller' => 'Users',
                'action' => 'changePassword',
            ],
            '#^/users/confirm-email/(?P<email>.+)/(?P<code>.+)$#' => [
                'controller' => 'Users',
                'action' => 'confirmEmail',
                'params' => [
                    'email',
                    'code',
                ],
            ],
            '#^/users/resend-confirm-email$#' => [
                'controller' => 'Users',
                'action' => 'resendConfirmEmail',
            ],

            /** persons */
            '#^/persons$#' => [
                'controller' => 'Persons',
                'action' => 'Index'
            ],
            '#^/persons/my$#' => [
                'controller' => 'Persons',
                'action' => 'my',
            ],
            '#^/persons/create$#' => [
                'controller' => 'Persons',
                'action' => 'create',
            ],
            '#^/persons/edit$#' => [
                'controller' => 'Persons',
                'action' => 'edit',
            ],
            '#^/persons/edit/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Persons',
                'action' => 'edit',
                'params' => [
                    'id',
                ],
            ],
            '#^/persons/view/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Persons',
                'action' => 'view',
                'params' => [
                    'id',
                ],
            ],

            /** goods */
            '#^/goods$#' => [
                'controller' => 'Goods',
                'action' => 'Index'
            ],
            '#^/goods/edit/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Goods',
                'action' => 'Edit',
                'params' => [
                    'id',
                ],
            ],
            '#^/goods/view/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Goods',
                'action' => 'View',
                'params' => [
                    'id',
                ],
            ],
            '#^/goods/delete/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Goods',
                'action' => 'Delete',
                'params' => [
                    'id',
                ],
            ],
            '#^/goods/create$#' => [
                'controller' => 'Goods',
                'action' => 'Create'
            ],
            '#^/goods/ajax-list$#' => [
                'controller' => 'Goods',
                'action' => 'ajaxList'
            ],

            /** attrs */
            '#^/attrs$#' => [
                'controller' => 'Attrs',
                'action' => 'Index'
            ],
            '#^/attrs/edit/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Attrs',
                'action' => 'Edit',
                'params' => [
                    'id',
                ],
            ],
            '#^/attrs/view/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Attrs',
                'action' => 'View',
                'params' => [
                    'id',
                ],
            ],
            '#^/attrs/delete/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Attrs',
                'action' => 'Delete',
                'params' => [
                    'id',
                ],
            ],
            '#^/attrs/create$#' => [
                'controller' => 'Attrs',
                'action' => 'Create'
            ],
            '#^/attrs/ajax-list$#' => [
                'controller' => 'Attrs',
                'action' => 'ajaxList'
            ],
            
            /** categories */
            '#^/categories$#' => [
                'controller' => 'Categories',
                'action' => 'Index'
            ],
            '#^/categories/edit/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Categories',
                'action' => 'Edit',
                'params' => [
                    'id',
                ],
            ],
            '#^/categories/view/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Categories',
                'action' => 'View',
                'params' => [
                    'id',
                ],
            ],
            '#^/categories/delete/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Categories',
                'action' => 'Delete',
                'params' => [
                    'id',
                ],
            ],
            '#^/categories/create$#' => [
                'controller' => 'Categories',
                'action' => 'Create'
            ],
            '#^/categories/ajax-list$#' => [
                'controller' => 'Categories',
                'action' => 'ajaxList'
            ],

            /** attrs categories */
            '#^/attrs-categories$#' => [
                'controller' => 'AttrsCategories',
                'action' => 'Index'
            ],
            '#^/attrs-categories/view/(?P<id>[0-9-]+)$#' => [
                'controller' => 'AttrsCategories',
                'action' => 'View',
                'params' => [
                    'id',
                ],
            ],
            '#^/attrs-categories/delete/(?P<id>[0-9-]+)$#' => [
                'controller' => 'AttrsCategories',
                'action' => 'Delete',
                'params' => [
                    'id',
                ],
            ],
            '#^/attrs-categories/create$#' => [
                'controller' => 'AttrsCategories',
                'action' => 'Create'
            ],

            /** goods categories */
            '#^/goods-categories$#' => [
                'controller' => 'GoodsCategories',
                'action' => 'Index'
            ],
            '#^/goods-categories/view/(?P<id>[0-9-]+)$#' => [
                'controller' => 'GoodsCategories',
                'action' => 'View',
                'params' => [
                    'id',
                ],
            ],
            '#^/goods-categories/delete/(?P<id>[0-9-]+)$#' => [
                'controller' => 'GoodsCategories',
                'action' => 'Delete',
                'params' => [
                    'id',
                ],
            ],
            '#^/goods-categories/create$#' => [
                'controller' => 'GoodsCategories',
                'action' => 'Create'
            ],

            /** goods attrs */
            '#^/goods-attrs$#' => [
                'controller' => 'GoodsAttrs',
                'action' => 'Index'
            ],
            '#^/goods-attrs/view/(?P<id>[0-9-]+)$#' => [
                'controller' => 'GoodsAttrs',
                'action' => 'View',
                'params' => [
                    'id',
                ],
            ],
            '#^/goods-attrs/edit/(?P<id>[0-9-]+)$#' => [
                'controller' => 'GoodsAttrs',
                'action' => 'Edit',
                'params' => [
                    'id',
                ],
            ],
            '#^/goods-attrs/delete/(?P<id>[0-9-]+)$#' => [
                'controller' => 'GoodsAttrs',
                'action' => 'Delete',
                'params' => [
                    'id',
                ],
            ],
            '#^/goods-attrs/create$#' => [
                'controller' => 'GoodsAttrs',
                'action' => 'Create'
            ],

            /** images galleries */
            '#^/images-galleries$#' => [
                'controller' => 'ImagesGalleries',
                'action' => 'Index'
            ],
            '#^/images-galleries/view/(?P<id>[0-9-]+)$#' => [
                'controller' => 'ImagesGalleries',
                'action' => 'View',
                'params' => [
                    'id',
                ],
            ],
            '#^/images-galleries/delete/(?P<id>[0-9-]+)$#' => [
                'controller' => 'ImagesGalleries',
                'action' => 'Delete',
                'params' => [
                    'id',
                ],
            ],
            '#^/images-galleries/create$#' => [
                'controller' => 'ImagesGalleries',
                'action' => 'Create'
            ],

            /** galleries */
            '#^/galleries$#' => [
                'controller' => 'Galleries',
                'action' => 'Index'
            ],
            '#^/galleries/edit/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Galleries',
                'action' => 'Edit',
                'params' => [
                    'id',
                ],
            ],
            '#^/galleries/view/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Galleries',
                'action' => 'View',
                'params' => [
                    'id',
                ],
            ],
            '#^/galleries/delete/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Galleries',
                'action' => 'Delete',
                'params' => [
                    'id',
                ],
            ],
            '#^/galleries/create$#' => [
                'controller' => 'Galleries',
                'action' => 'Create'
            ],
            '#^/galleries/upload$#' => [
                'controller' => 'Galleries',
                'action' => 'ajaxUpload'
            ],
            '#^/galleries/ajax-list$#' => [
                'controller' => 'Galleries',
                'action' => 'ajaxList'
            ],

            /** references */
            '#^/references$#' => [
                'controller' => 'References',
                'action' => 'Index'
            ],
            '#^/references/edit/(?P<id>[0-9-]+)$#' => [
                'controller' => 'References',
                'action' => 'Edit',
                'params' => [
                    'id',
                ],
            ],
            '#^/references/view/(?P<id>[0-9-]+)$#' => [
                'controller' => 'References',
                'action' => 'View',
                'params' => [
                    'id',
                ],
            ],
            '#^/references/delete/(?P<id>[0-9-]+)$#' => [
                'controller' => 'References',
                'action' => 'Delete',
                'params' => [
                    'id',
                ],
            ],
            '#^/references/create$#' => [
                'controller' => 'References',
                'action' => 'Create'
            ],
            '#^/references/ajax-list$#' => [
                'controller' => 'References',
                'action' => 'ajaxList'
            ],

            /** references values */
            '#^/references-values$#' => [
                'controller' => 'ReferencesValues',
                'action' => 'Index'
            ],
            '#^/references-values/edit/(?P<id>[0-9-]+)$#' => [
                'controller' => 'ReferencesValues',
                'action' => 'Edit',
                'params' => [
                    'id',
                ],
            ],
            '#^/references-values/view/(?P<id>[0-9-]+)$#' => [
                'controller' => 'ReferencesValues',
                'action' => 'View',
                'params' => [
                    'id',
                ],
            ],
            '#^/references-values/delete/(?P<id>[0-9-]+)$#' => [
                'controller' => 'ReferencesValues',
                'action' => 'Delete',
                'params' => [
                    'id',
                ],
            ],
            '#^/references-values/create$#' => [
                'controller' => 'ReferencesValues',
                'action' => 'Create'
            ],

            /** manufacturers */
            '#^/manufacturers$#' => [
                'controller' => 'Manufacturers',
                'action' => 'Index'
            ],
            '#^/manufacturers/edit/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Manufacturers',
                'action' => 'Edit',
                'params' => [
                    'id',
                ],
            ],
            '#^/manufacturers/view/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Manufacturers',
                'action' => 'View',
                'params' => [
                    'id',
                ],
            ],
            '#^/manufacturers/delete/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Manufacturers',
                'action' => 'Delete',
                'params' => [
                    'id',
                ],
            ],
            '#^/manufacturers/create$#' => [
                'controller' => 'Manufacturers',
                'action' => 'Create'
            ],
            '#^/manufacturers/ajax-list$#' => [
                'controller' => 'Manufacturers',
                'action' => 'ajaxList'
            ],

            /** images */
            '#^/images$#' => [
                'controller' => 'Images',
                'action' => 'Index'
            ],
            '#^/images/edit/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Images',
                'action' => 'Edit',
                'params' => [
                    'id',
                ],
            ],
            '#^/images/view/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Images',
                'action' => 'View',
                'params' => [
                    'id',
                ],
            ],
            '#^/images/delete/(?P<id>[0-9-]+)$#' => [
                'controller' => 'Images',
                'action' => 'Delete',
                'params' => [
                    'id',
                ],
            ],
            '#^/images/create$#' => [
                'controller' => 'Images',
                'action' => 'Create'
            ],
            '#^/images/join$#' => [
                'controller' => 'Images',
                'action' => 'join'
            ],
        ];
    }
}