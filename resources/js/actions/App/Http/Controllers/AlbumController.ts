import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AlbumController::index
* @see app/Http/Controllers/AlbumController.php:18
* @route '/albums'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/albums',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AlbumController::index
* @see app/Http/Controllers/AlbumController.php:18
* @route '/albums'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AlbumController::index
* @see app/Http/Controllers/AlbumController.php:18
* @route '/albums'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AlbumController::index
* @see app/Http/Controllers/AlbumController.php:18
* @route '/albums'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AlbumController::index
* @see app/Http/Controllers/AlbumController.php:18
* @route '/albums'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AlbumController::index
* @see app/Http/Controllers/AlbumController.php:18
* @route '/albums'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AlbumController::index
* @see app/Http/Controllers/AlbumController.php:18
* @route '/albums'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\AlbumController::show
* @see app/Http/Controllers/AlbumController.php:96
* @route '/albums/{album}'
*/
export const show = (args: { album: number | { id: number } } | [album: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/albums/{album}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AlbumController::show
* @see app/Http/Controllers/AlbumController.php:96
* @route '/albums/{album}'
*/
show.url = (args: { album: number | { id: number } } | [album: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { album: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { album: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            album: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        album: typeof args.album === 'object'
        ? args.album.id
        : args.album,
    }

    return show.definition.url
            .replace('{album}', parsedArgs.album.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AlbumController::show
* @see app/Http/Controllers/AlbumController.php:96
* @route '/albums/{album}'
*/
show.get = (args: { album: number | { id: number } } | [album: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AlbumController::show
* @see app/Http/Controllers/AlbumController.php:96
* @route '/albums/{album}'
*/
show.head = (args: { album: number | { id: number } } | [album: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AlbumController::show
* @see app/Http/Controllers/AlbumController.php:96
* @route '/albums/{album}'
*/
const showForm = (args: { album: number | { id: number } } | [album: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AlbumController::show
* @see app/Http/Controllers/AlbumController.php:96
* @route '/albums/{album}'
*/
showForm.get = (args: { album: number | { id: number } } | [album: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AlbumController::show
* @see app/Http/Controllers/AlbumController.php:96
* @route '/albums/{album}'
*/
showForm.head = (args: { album: number | { id: number } } | [album: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

const AlbumController = { index, show }

export default AlbumController