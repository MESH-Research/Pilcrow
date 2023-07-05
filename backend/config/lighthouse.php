<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Controls the HTTP route that your GraphQL server responds to.
    | You may set `route` => false, to disable the default route
    | registration and take full control.
    |
    */

    'route' => [
        /*
         * The URI the endpoint responds to, e.g. mydomain.com/graphql.
         */
        'uri' => '/graphql',

        /*
         * Lighthouse creates a named route for convenient URL generation and redirects.
         */
        'name' => 'graphql',

        /*
         * Beware that middleware defined here runs before the GraphQL execution phase,
         * make sure to return spec-compliant responses in case an error is thrown.
         */
        'middleware' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Nuwave\Lighthouse\Http\Middleware\AcceptJson::class,
            // Logs in a user if they are authenticated. In contrast to Laravel's 'auth'
            // middleware, this delegates auth and permission checks to the field level.
            \Nuwave\Lighthouse\Http\Middleware\AttemptAuthentication::class,
            // Logs every incoming GraphQL query.
            // \Nuwave\Lighthouse\Support\Http\Middleware\LogGraphQLQueries::class,
        ],

        /*
         * The `prefix` and `domain` configuration options are optional.
         */
        //'prefix' => '',
        //'domain' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guard
    |--------------------------------------------------------------------------
    |
    | The guard to use for authenticating GraphQL requests, if needed.
    | This setting is used whenever Lighthouse looks for an authenticated user, for example in directives
    | such as `@guard` and when applying the `AttemptAuthentication` middleware.
    | TODO this setting will default to 'api' in v5
    |
    */

    'guards' => ['sanctum'],

    /*
    |--------------------------------------------------------------------------
    | Schema Location
    |--------------------------------------------------------------------------
    |
    | Path to your .graphql schema file.
    | Additional schema files may be imported from within that file.
    |
    */

    'schema' => [
        'register' => base_path('graphql/schema.graphql'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema Cache
    |--------------------------------------------------------------------------
    |
    | A large part of schema generation consists of parsing and AST manipulation.
    | This operation is very expensive, so it is highly recommended to enable
    | caching of the final schema to optimize performance of large schemas.
    |
    */

    'cache' => [
        /*
         * Setting to true enables schema caching.
         */
        'enable' => env('LIGHTHOUSE_CACHE_ENABLE', env('APP_ENV') !== 'local'),

        /*
         * The name of the cache item for the schema cache.
         */
        'key' => env('LIGHTHOUSE_CACHE_KEY', 'lighthouse-schema'),

        /*
         * Allows using a specific cache store, uses the app's default if set to null.
         */
        'store' => env('LIGHTHOUSE_CACHE_STORE', null),

        /**
         * Duration in seconds the schema should remain cached, null means forever.
         */
        'ttl' => env('LIGHTHOUSE_CACHE_TTL', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    | These are the default namespaces where Lighthouse looks for classes to
    | extend functionality of the schema. You may pass in either a string
    | or an array, they are tried in order and the first match is used.
    |
    */

    'namespaces' => [
        'models' => ['App', 'App\\Models'],
        'queries' => 'App\\GraphQL\\Queries',
        'mutations' => 'App\\GraphQL\\Mutations',
        'subscriptions' => 'App\\GraphQL\\Subscriptions',
        'interfaces' => 'App\\GraphQL\\Interfaces',
        'unions' => 'App\\GraphQL\\Unions',
        'scalars' => 'App\\GraphQL\\Scalars',
        'directives' => ['App\\GraphQL\\Directives'],
        'validators' => 'App\\GraphQL\\Validators'
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    |
    | Control how Lighthouse handles security related query validation.
    | Read more at http://webonyx.github.io/graphql-php/security/
    |
    */

    'security' => [
        'max_query_complexity' => \GraphQL\Validator\Rules\QueryComplexity::DISABLED,
        'max_query_depth' => \GraphQL\Validator\Rules\QueryDepth::DISABLED,
        'disable_introspection' => \GraphQL\Validator\Rules\DisableIntrospection::DISABLED,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Set defaults for the pagination features within Lighthouse, such as
    | the @paginate directive, or paginated relation directives.
    |
    */

    'pagination' => [
        /*
         * Allow clients to query paginated lists without specifying the amount of items.
         * Setting this to `null` means clients have to explicitly ask for the count.
         */
        'default_count' => null,

        /*
         * Limit the maximum amount of items that clients can request from paginated lists.
         * Setting this to `null` means the count is unrestricted.
         */
        'max_count' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Amount Argument
    |--------------------------------------------------------------------------
    |
    | Set the name to use for the generated argument on paginated fields
    | that controls how many results are returned.
    |
    | DEPRECATED This setting will be removed in v5.
    |
    */

    'pagination_amount_argument' => 'first',

    /*
    |--------------------------------------------------------------------------
    | @orderBy input name
    |--------------------------------------------------------------------------
    |
    | Set the name to use for the generated argument on the
    | OrderByClause used for the @orderBy directive.
    |
    | DEPRECATED This setting will be removed in v5, Lighthouse will assume
    | the value 'column'. Change it soon, as you prepare for the upgrade.
    |
    */

    'orderBy' => 'field',

];
