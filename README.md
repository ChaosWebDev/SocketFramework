# Chaos Framework

The application skeleton for starting a new Chaos Framework project.  
Provides the base directory structure, bootstrap files, and Composer integration.  
It should be noted that this framework is designed for Socket usage, **not web usage**.

---

## Installation

Create a new project using Composer:

```
composer create-project chaoswd/framework my-app
```

This will scaffold a new project in the `my-app` directory.

---

## Features

- Preconfigured to load environment variables via `chaoswd/dotenv`
- Preconfigured to use `chaoswd/core` for configuration and console commands
- Includes example `config/app.php` file
- Ships with a `chaos` CLI bootstrap
- Public entry point (`public/index.php`)
- Example Commands in `app/Commands`

---

## Usage

Serve the app with the built-in PHP server:

```bash
php chaos serve
```

Start the Socket Server:
```bash
php chaos server:start
```

Run the CLI:

```bash
php chaos help
```

---

## Roadmap

- [ ] Add additional commands (with auto-discovery)
- [ ] Add additional config files for database, cache, logging
- [ ] Add database connections and functionality
- [ ] Add installer script for first-time setup

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

---

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
