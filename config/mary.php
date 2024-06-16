<?php

return [
    /**
     * Default component prefix.
     *
     * Make sure to clear view cache after renaming with `php artisan view:clear`
     *
     *    prefix => ''
     *              <x-button />
     *              <x-card />
     *
     *    prefix => 'mary-'
     *               <x-mary-button />
     *               <x-mary-card />
     *
     */
    'prefix' => '',

    /**
     * Default route prefix.
     *
     * Some maryUI components make network request to its internal routes.
     *
     *      route_prefix => ''
     *          - Spotlight: '/mary/spotlight'
     *          - Editor: '/mary/upload'
     *          - ...
     *
     *      route_prefix => 'my-components'
     *          - Spotlight: '/my-components/mary/spotlight'
     *          - Editor: '/my-components/mary/upload'
     *          - ...
     */
    'route_prefix' => '',

    /**
     * Components settings
     */
    'components' => [
        'spotlight' => [
            'class' => 'App\Support\Spotlight',
        ]
    ]
];
