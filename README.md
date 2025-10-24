# pennchas_event_subscriber

Custom Drupal module extracted from the Penn CHAS site.


What it is: A small custom Drupal module that registers a Symfony event subscriber for the Penn CHAS site. It’s packaged with the usual scaffolding (.info.yml, .services.yml, composer.json, and a src/Subscriber/ class). 

What it does (at a glance): The module’s purpose is to listen to Drupal/Symfony kernel events (e.g., request/response or similar) and run CHAS-specific logic when those events fire—centralizing behavior that would otherwise live in scattered hooks. Exact behaviors depend on the subscriber class inside src/Subscriber/, but the wiring indicates it’s meant to execute lightweight request-time actions via the event system. 
GitHub

Why it exists: To keep site behavior modular and maintainable by reacting to framework events through a tagged service (event_subscriber) instead of ad-hoc hooks—making it easier to toggle

# further details

1) DeletedNodeAccessSubscriber

Purpose: Hide nodes in a “deleted” moderation state from most users by returning a 404.
When it runs: On every request (KernelEvents::REQUEST).
Logic:

Gets the current route and node parameter.

If the node has moderation_state == 'delete', only allow access when:

the route is one of: entity.node.version_history, entity.node.canonical, entity.node.webform.results_submissions, and

the user has bypass node access.

Otherwise throws NotFoundHttpException → 404.

Risks / polish:

Runs on all requests (no main-request check).

Uses service locators (\Drupal::routeMatch()) vs injected services; harder to test.

Hard-coded route allowlist; might miss other legitimate admin routes.

The permission + route combo is a bit odd (admins only on those routes). Consider making the allowlist configurable.

Quick improvements:

Add if (!$event->isMainRequest()) return;

Inject RouteMatchInterface (or CurrentRouteMatch), AccountInterface, and maybe StateInterface/config for an allowlist.

Consider an AccessCheck (custom route access service) or entity access handler override instead of a global request subscriber.

2) LoadEventsSubscriber

Purpose: Force anonymous users who hit a 403 page or a “user/{id}” profile path to go to a custom login URL that preserves their destination.
When it runs: On every response (KernelEvents::RESPONSE) with priority 28.
Logic:

If user is anonymous and the route is system.403 or the path matches /user/{id}, redirect to /backend/login?destination=<current_path>.

Risks / polish:

Uses multiple global service calls (\Drupal::service(), \Drupal::currentUser(), \Drupal::request()); inject instead.

No main-request check.

Redirecting from RESPONSE means you’re replacing a fully built response; you could do this earlier on REQUEST to save work.

Regex on path for /user/\d+ is brittle; route-name checks are safer.

Might interfere with cache or other subscribers due to response replacement.

Quick improvements:

Switch to KernelEvents::REQUEST (earlier) and bail fast for subrequests.

Use route-name checks (user.view) instead of regex.

Inject CurrentRouteMatch, AccountProxyInterface, PathCurrent, and build the login URL via Url::fromRoute() with destination.

Ensure you don’t redirect if already on the login route (avoid loops).

3) NoCacheForPageSubscriber

Purpose: Disable HTTP caching for the /dashboard path.
When it runs: On response (kernel.response).
Logic:

If request URI is exactly /dashboard, set Cache-Control: no-cache, no-store, must-revalidate, Pragma: no-cache, Expires: 0.

Risks / polish:

Direct string match on URI; misses language prefixes, query strings, or alias changes.

Sets HTTP headers but doesn’t adjust Drupal cacheability metadata—fine for dynamic pages, but consider aligning with route/cache contexts.

Quick improvements:

Check by route name (e.g., my_module.dashboard) or by resolved system path, not raw URI.

Add if (!$event->isMainRequest()) return;.

Consider marking the controller/route with no_cache or returning responses with ::setMaxAge(0) and appropriate cache contexts in the controller—clearer than a global subscriber.

Cross-cutting best practices

DI everywhere: Replace \Drupal::… calls with injected services; add lazy: true on services if they’re inexpensive.

Main request only: if (!$event->isMainRequest()) return; for both REQUEST and RESPONSE listeners.

Security & UX: In the redirect subscriber, whitelist-safe routes and avoid redirect loops; respect destination query param if provided.

Configuration: Move magic values (allowed routes, login path, no-cache routes) to config with a schema so ops can tweak per environment.

Testing:

Unit tests: subscriber method gets the expected event + request → asserts redirect/headers/exception.

Kernel tests: dispatch route with a fake node in “delete” state → assert 404 vs allowed access for privileged users.

