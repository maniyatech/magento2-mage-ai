# ManiyaTech MageAI module for Magento 2

Magento 2 extension leverages OpenAI’s GPT models (ChatGPT) to automatically generate high-quality product descriptions, short descriptions, and SEO metadata based on product attributes like name, features, materials, and more.

MageAI helps you create rich, engaging content by analyzing product data and generating natural-sounding descriptions tailored to each item. It also generates SEO-optimized meta titles, keywords, and meta descriptions, helping improve your store's visibility in search engines.

Whether you're managing a small catalog or thousands of products, MageAI is a powerful solution to save time, ensure consistency, and improve the overall content quality across your Magento store.

## How to install ManiyaTech_MageAI module

### Composer Installation

Run the following command in Magento 2 root directory to install ManiyaTech_MageAI module via composer.

#### Install

```
composer require maniyatech/magento2-mage-ai
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy -f
```

#### Update

```
composer update maniyatech/magento2-mage-ai
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy -f
```

Run below command if your store is in the production mode:

```
php bin/magento setup:di:compile
```

### Manual Installation

If you prefer to install this module manually, kindly follow the steps described below - 

- Download the latest version [here](https://github.com/maniyatech/magento2-mage-ai/archive/refs/heads/main.zip) 
- Create a folder path like this `app/code/ManiyaTech/MageAI` and extract the `main.zip` file into it.
- Navigate to Magento root directory and execute the below commands.

```
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy -f
```
