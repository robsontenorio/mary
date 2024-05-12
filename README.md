<p align="center"><img width="200" src="https://github.com/robsontenorio/mary-ui.com/blob/main/public/mary.png?raw=true"></p>

<p align="center">
    <a href="https://packagist.org/packages/robsontenorio/mary">
        <img src="https://img.shields.io/packagist/dt/robsontenorio/mary?cacheSeconds=60">
    </a>
    <a href="https://packagist.org/packages/robsontenorio/mary">
        <img src="https://img.shields.io/packagist/v/robsontenorio/mary?label=stable&color=blue&cacheSeconds=60">
    </a>
    <a href="https://packagist.org/packages/robsontenorio/mary">
        <img src="https://poser.pugx.org/robsontenorio/mary/license.svg">
    </a>
</p>

## Introduction

MaryUI is a set of gorgeous Laravel Blade UI Components made for Livewire 3 and styled around daisyUI + Tailwind.

## Official Documentation

You can read the official documentation on the [maryUI website](https://mary-ui.com).

## Sponsor

Let's keep pushing it, [sponsor me](https://github.com/sponsors/robsontenorio) ❤️

## Discord 

Come to say hello on [maryUI Discord](https://discord.gg/YyFR8dnQ)


## Follow me

[@robsontenorio](https://twitter.com/robsontenorio)

## Contributing

Clone the repository into some folder **inside your app**.

```bash
git clone git@github.com:robsontenorio/mary.git
```

Change `composer.json` from **your app**

<!-- @formatter:off -->
```json
"minimum-stability": "dev", // <- change to "dev"

// Add this
"repositories": {
    "robsontenorio/mary": {
        "type": "path",
        "url": "/path/to/mary", // <- change the path
        "options": {
          "symlink": true
        }
    }
}
```
<!-- @formatter:on -->


Require the package again for local symlink.

```bash
composer require robsontenorio/mary
```

Start dev

```bash
yarn dev
```

## License

<a name="license"></a>

MaryUI is open-sourced software licensed under the [MIT license](/license.md).
