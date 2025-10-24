# pennchas_event_subscriber

Custom Drupal module extracted from the Penn CHAS site.


What it is: A small custom Drupal module that registers a Symfony event subscriber for the Penn CHAS site. It’s packaged with the usual scaffolding (.info.yml, .services.yml, composer.json, and a src/Subscriber/ class). 

What it does (at a glance): The module’s purpose is to listen to Drupal/Symfony kernel events (e.g., request/response or similar) and run CHAS-specific logic when those events fire—centralizing behavior that would otherwise live in scattered hooks. Exact behaviors depend on the subscriber class inside src/Subscriber/, but the wiring indicates it’s meant to execute lightweight request-time actions via the event system. 
GitHub

Why it exists: To keep site behavior modular and maintainable by reacting to framework events through a tagged service (event_subscriber) instead of ad-hoc hooks—making it easier to toggle

## Install
Add this repo as a VCS repository in your site's composer.json, then:

