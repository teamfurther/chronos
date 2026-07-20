<?php

namespace Chronos;

use Carbon\Carbon;
use Chronos\Models\Content;
use Chronos\Services\ImageStyleService;
use Chronos\Services\RouteMapService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ChronosServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(Gate $gate)
    {
        // require routes
        if (!$this->app->routesAreCached()) {
            $this->app['router']->group([
                'middleware' => 'web',
                'namespace' => 'Chronos\Http\Controllers',
                'prefix' => 'admin'
            ], function () {
                require __DIR__ . '/../routes/web.php';
            });
            $this->app['router']->group([
                'middleware' => App::environment('local', 'staging') ? 'api' : [
                    'auth:sanctum',
                    \Illuminate\Routing\Middleware\SubstituteBindings::class
                ],
                'namespace' => 'Chronos\Api\Controllers',
                'prefix' => 'api'
            ], function () {
                require __DIR__ . '/../routes/api.php';
            });
        }

        // publish config
        $this->publishes([
            __DIR__ . '/../config/chronos.php' => config_path('chronos.php'),
        ], 'config');
        $this->publishes([
            __DIR__ . '/../config/languages.php' => config_path('languages.php'),
        ], 'config');

        // load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // load translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'chronos');

        // load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'chronos');

        // publish views so they can be overridden
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/chronos'),
        ], 'views');

        // publish assets
        $this->publishes([
            __DIR__ . '/../assets/' => public_path('chronos'),
        ], 'public');

        // register gates
        $this->registerGates($gate);

        // register menu
        $this->registerMenu();

        // register scheduled tasks
        $this->registerSchedule();

        // register exception rendering hooks
        $this->registerExceptionRendering();
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        // default package configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/defaults.php', 'chronos'
        );
        $this->mergeConfigFrom(
            __DIR__ . '/../config/languages.php', 'languages'
        );

        // register RouteMap facade
        $this->app->singleton('routeMap', function($app) {
            return new RouteMapService();
        });
        $this->app->alias('routeMap', 'Chronos\App\Services\RouteMapService');
    }



    /**
     * Register gates.
     */
    protected function registerGates($gate)
    {
        if (class_exists('Chronos\Models\Permission') && Schema::hasTable('roles')) {
            $permissions = \Chronos\Models\Permission::all();
            foreach ($permissions as $permission) {
                $gate->define($permission->name, function ($user) use ($permission) {
                    return $user->hasPermission($permission->name);
                });
            }
        }
    }

    /**
     * Register menu and share it with all views.
     */
    protected function registerMenu()
    {
        \Menu::make('ChronosMenu', function($menu) {
//            $menu->add(trans('chronos::menu.Dashboard'), ['route' => 'chronos.dashboard'])
//                ->prepend('<span class="icon c4icon-dashboard"></span>')
//                ->data('order', 1)->data('permissions', ['view_dashboard']);

            // Content tab
            $content_menu = $menu->add(trans('chronos::menu.Content'), null)
                ->prepend('<span class="icon c4icon-pencil-3"></span>')
                ->data('order', 100)->data('permissions', ['view_content_types', 'view_media']);

            if (class_exists('Chronos\Models\ContentType') && Schema::hasTable('content_types')) {
                $types = \Chronos\Models\ContentType::orderBy('name')->get();
                if ($types) {
                    foreach ($types as $k => $type) {
                        $content_menu->add($type->name, ['route' => ['chronos.content', 'type' => $type->id]])
                            ->data('order', 100 + $k * 5)->data('permissions', ['view_content_type_' . $type->id]);
                    }
                }
            }
            $content_menu->add(trans('chronos::menu.Media'), ['route' => 'chronos.content.media'])
                ->data('order', 800)->data('permissions', ['view_media']);
            $content_menu->add(trans('chronos::menu.Content types'), ['route' => 'chronos.content.types'])
                ->data('order', 900)->data('permissions', ['view_content_types']);

            // Users tab
            $users_menu = $menu->add(trans('chronos::menu.Users'), null)
                ->prepend('<span class="icon c4icon-user-3"></span>')
                ->data('order', 800)->data('permissions', ['view_roles', 'edit_permissions']);
            $users_menu->add(trans('chronos::menu.Roles'), ['route' => 'chronos.users.roles'])
                ->data('order', 810)->data('permissions', ['view_roles']);
            $users_menu->add(trans('chronos::menu.Permissions'), ['route' => 'chronos.users.permissions'])
                ->data('order', 820)->data('permissions', ['edit_permissions']);

            // Settings tab
            $settings_menu = $menu->add(trans('chronos::menu.Settings'), null)
                ->prepend('<span class="icon c4icon-sliders-1"></span>')
                ->data('order', 900)->data('permissions', ['edit_settings', 'edit_access_tokens', 'edit_image_styles']);
            $settings_menu->add(trans('chronos::menu.Access tokens'), ['route' => 'chronos.settings.access_tokens'])
                ->data('order', 910)->data('permissions', ['edit_access_tokens']);
            $settings_menu->add(trans('chronos::menu.Image styles'), ['route' => 'chronos.settings.image_styles'])
                ->data('order', 910)->data('permissions', ['view_image_styles']);

            if (class_exists('Chronos\Models\Setting') && \Schema::hasTable('settings') && settings('is_multilanguage')) {
                $settings_menu = $menu->get(Str::camel(trans('chronos::menu.Settings')));
                $settings_permissions = $settings_menu->permissions;
                $settings_permissions[] = 'edit_languages';
                $settings_menu->data('permissions', $settings_permissions);
                $settings_menu->add(trans('chronos::menu.Languages'), ['route' => 'chronos.settings.languages'])
                    ->data('order', 920)->data('permissions', ['edit_languages']);
            }
        });

        \View::composer('*', function($view) {
            $view->with('chronos_menu', \Menu::get('ChronosMenu')->sortBy('order'));
        });
    }

    /**
     * Register scheduled tasks.
     */
    protected function registerSchedule()
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->call(function() {
                $content = Content::where('status_scheduled', '<=', Carbon::now())->get();

                foreach ($content as $item) {
                    $item->status = 1;
                    $item->status_scheduled = null;
                    $item->save();
                }
            })->everyMinute();
        });
    }

    /**
     * Register package-specific exception rendering without replacing the app handler.
     */
    protected function registerExceptionRendering()
    {
        $this->callAfterResolving(ExceptionHandler::class, function ($handler) {
            if (!method_exists($handler, 'renderable')) {
                return;
            }

            $handler->renderable(function (AuthenticationException $e, $request) {
                if ($request->is('admin') || $request->is('admin/*')) {
                    return redirect()->guest(route('chronos.auth.login'));
                }
            });

            $handler->renderable(function (TokenMismatchException $e, $request) {
                if ($request->is('admin') || $request->is('admin/*')) {
                    return response()->view('chronos::errors.token_mismatch', ['exception' => $e]);
                }
            });

            $handler->renderable(function (HttpExceptionInterface $e, $request) {
                $image_style_response = $this->renderImageStyleResponse($e, $request);
                if ($image_style_response) {
                    return $image_style_response;
                }

                $status = $e->getStatusCode();
                if ($request && ($request->is('api') || $request->is('api/*'))) {
                    return response()->json([
                        'message' => $e->getMessage(),
                        'status' => $status
                    ], $status);
                }

                $view = $request && ($request->is('admin') || $request->is('admin/*')) ? "chronos::errors.{$status}" : "errors.{$status}";
                if (view()->exists($view)) {
                    return response()->view($view, ['exception' => $e], $status, $e->getHeaders());
                }
            });
        });
    }

    /**
     * Generate image styles on demand when a configured asset URL 404s.
     */
    protected function renderImageStyleResponse(HttpExceptionInterface $e, $request)
    {
        if ($e->getStatusCode() != 404 || !$request) {
            return null;
        }

        foreach (config('chronos.upload_paths', []) as $path) {
            $full_path = parse_url($path['asset_path']);
            $check_path = isset($full_path['path']) ? trim($full_path['path'], '/') . '/*' : basename($request->path());
            $host_matches = !isset($full_path['host']) || $request->getHttpHost() == $full_path['host'];

            if (!$request->is($check_path) || !$host_matches) {
                continue;
            }

            $basename = basename($request->path());
            if (!ImageStyleService::checkIfGeneratableImageStyle($basename)) {
                break;
            }

            $upload_path = $path['upload_path'];
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }

            if (ImageStyleService::generate($upload_path, $path['asset_path'], $basename)) {
                $mime = guess_extension_from_mime($upload_path . '/' . $basename);

                return response(file_get_contents($upload_path . '/' . $basename), 200)->header('Content-Type', $mime);
            }

            break;
        }
    }

}
