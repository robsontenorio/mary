<p align="center"><img width="200" src="https://github.com/robsontenorio/mary-ui.com/blob/main/public/mary.png?raw=true""></p>

<p align="center">
    <a href="https://packagist.org/packages/robsontenorio/mary">
        <img src="https://poser.pugx.org/robsontenorio/mary/d/total.svg">
    </a>
    <a href="https://packagist.org/packages/robsontenorio/mary">
        <img src="https://poser.pugx.org/robsontenorio/mary/v/stable.svg">
    </a>
    <a href="https://packagist.org/packages/robsontenorio/mary">
        <img src="https://poser.pugx.org/robsontenorio/mary/license.svg">
    </a>
</p>


## Introduction
Mary is a set of gorgeous Laravel blade components made for Livewire 3 and styled with DaisyUI and Tailwind.

## Official Documentation
You can read the official documentation on the [Mary website](https://mary-ui.com).


## Contributing

Clone the repositoy into some folder **inside your app**.

```bash
git clone git@github.com:robsontenorio/mary.git`
```
  
Change `composer.json` from **your app**

```json
"minimum-stability": "dev",      // <- change to "dev"
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
