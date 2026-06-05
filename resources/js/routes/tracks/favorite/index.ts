import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\FavoriteTrackController::store
* @see app/Http/Controllers/FavoriteTrackController.php:11
* @route '/tracks/{albumTrack}/favorite'
*/
export const store = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/tracks/{albumTrack}/favorite',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\FavoriteTrackController::store
* @see app/Http/Controllers/FavoriteTrackController.php:11
* @route '/tracks/{albumTrack}/favorite'
*/
store.url = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { albumTrack: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { albumTrack: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            albumTrack: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        albumTrack: typeof args.albumTrack === 'object'
        ? args.albumTrack.id
        : args.albumTrack,
    }

    return store.definition.url
            .replace('{albumTrack}', parsedArgs.albumTrack.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FavoriteTrackController::store
* @see app/Http/Controllers/FavoriteTrackController.php:11
* @route '/tracks/{albumTrack}/favorite'
*/
store.post = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FavoriteTrackController::store
* @see app/Http/Controllers/FavoriteTrackController.php:11
* @route '/tracks/{albumTrack}/favorite'
*/
const storeForm = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FavoriteTrackController::store
* @see app/Http/Controllers/FavoriteTrackController.php:11
* @route '/tracks/{albumTrack}/favorite'
*/
storeForm.post = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\FavoriteTrackController::destroy
* @see app/Http/Controllers/FavoriteTrackController.php:18
* @route '/tracks/{albumTrack}/favorite'
*/
export const destroy = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/tracks/{albumTrack}/favorite',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\FavoriteTrackController::destroy
* @see app/Http/Controllers/FavoriteTrackController.php:18
* @route '/tracks/{albumTrack}/favorite'
*/
destroy.url = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { albumTrack: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { albumTrack: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            albumTrack: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        albumTrack: typeof args.albumTrack === 'object'
        ? args.albumTrack.id
        : args.albumTrack,
    }

    return destroy.definition.url
            .replace('{albumTrack}', parsedArgs.albumTrack.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FavoriteTrackController::destroy
* @see app/Http/Controllers/FavoriteTrackController.php:18
* @route '/tracks/{albumTrack}/favorite'
*/
destroy.delete = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\FavoriteTrackController::destroy
* @see app/Http/Controllers/FavoriteTrackController.php:18
* @route '/tracks/{albumTrack}/favorite'
*/
const destroyForm = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FavoriteTrackController::destroy
* @see app/Http/Controllers/FavoriteTrackController.php:18
* @route '/tracks/{albumTrack}/favorite'
*/
destroyForm.delete = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const favorite = {
    store: Object.assign(store, store),
    destroy: Object.assign(destroy, destroy),
}

export default favorite