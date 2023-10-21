<p align="center"><img width="200" src="https://github.com/robsontenorio/mary-ui.com/blob/main/public/mary.png?raw=true""></p>

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

Mary is a set of gorgeous Laravel blade components made for Livewire 3 and styled with DaisyUI and Tailwind.

## Official Documentation

You can read the official documentation on the [Mary website](https://mary-ui.com).

## Sponsor

Let's keep pushing it, [sponsor me](https://github.com/sponsors/robsontenorio) ❤️

## Follow me

[@robsontenorio](https://twitter.com/robsontenorio)


## Contributing

Clone the repository into some folder **inside your app**.

```bash
git clone git@github.com:robsontenorio/mary.git
```

Change `composer.json` from **your app**

```json
"minimum-stability": "dev",      // <- change to "dev"

// Add this
"repositories": {
    "robsontenorio/mary": {
        "type": "path",
        "url": "/path/to/mary",  // <- change the path
        "options": {
            "symlink": true
        }
    }
}
```

Require the package again for local symlink.

```bash
composer require robsontenorio/mary
```

Start dev

```bash
yarn dev
```
